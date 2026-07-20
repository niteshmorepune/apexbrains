<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\CompetitionRegistration;
use App\Models\Fee;
use App\Models\Level;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'all');
        if (! in_array($tab, ['all', 'internal', 'external'])) {
            $tab = 'all';
        }

        $status = $request->get('status', 'active');
        if (! in_array($status, ['active', 'inactive', 'all'])) {
            $status = 'active';
        }

        $query = Student::with([
                'currentLevel',
                'primaryParent',
                'examAttempts' => fn ($q) => $q->where('status', 'submitted')->latest('submitted_at')->limit(1),
            ]);

        if ($status !== 'all') {
            $query->where('is_active', $status === 'active');
        }

        if ($tab !== 'all') {
            $query->where('student_type', $tab);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('student_code', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('level_group')) {
            [$min, $max] = match ($request->level_group) {
                '1-3'   => [1, 3],
                '4-6'   => [4, 6],
                '7-9'   => [7, 9],
                '10'    => [10, 99],
                default => [null, null],
            };
            if ($min !== null) {
                $query->whereHas('currentLevel', fn ($q) => $q->whereBetween('number', [$min, $max]));
            }
        }

        $students = $query->orderBy('first_name')->paginate(20)->withQueryString();
        $levels   = Level::orderBy('number')->get();

        $internalCount = Student::where('is_active', true)->where('student_type', 'internal')->count();
        $externalCount = Student::where('is_active', true)->where('student_type', 'external')->count();
        $allCount      = $internalCount + $externalCount;

        $activeCount   = Student::where('is_active', true)->count();
        $inactiveCount = Student::where('is_active', false)->count();

        return view('franchise.students.index', compact(
            'students', 'levels', 'tab', 'status',
            'internalCount', 'externalCount', 'allCount',
            'activeCount', 'inactiveCount'
        ));
    }

    public function create(): View
    {
        $levels       = Level::where('is_active', true)->orderBy('number')->get();
        $competitions = Competition::where('is_active', true)
            ->where('is_open_to_external', true)
            ->orderByDesc('start_date')
            ->get();
        return view('franchise.students.create', compact('levels', 'competitions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $studentType = $request->input('student_type', 'internal');
        $isInternal  = $studentType === 'internal';

        $rules = [
            'student_type'  => ['required', 'in:internal,external'],
            'first_name'    => ['required', 'string', 'max:100'],
            'last_name'     => ['required', 'string', 'max:100'],
            'photo'         => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender'        => ['required', 'in:male,female,other'],
            'enrollment_date' => ['required', 'date'],
            'email'         => ['required', 'email', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
            'phone'         => ['nullable', 'string', 'max:15'],
            'address'       => ['nullable', 'string', 'max:300'],
            'city'          => ['nullable', 'string', 'max:100'],
            'pincode'       => ['nullable', 'string', 'max:10'],
            'parent_name'   => ['required', 'string', 'max:100'],
            'parent_relationship' => ['nullable', 'in:father,mother,guardian'],
            'parent_phone'  => ['required', 'string', 'max:15'],
            'parent_whatsapp' => ['nullable', 'string', 'max:15'],
            'parent_email'  => ['nullable', 'email', 'max:150'],
            'current_level_id' => ['required', 'exists:levels,id'],
            'monthly_fee'   => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ];

        if (! $isInternal) {
            $rules['competition_id']    = ['nullable', 'exists:competitions,id'];
            $rules['registration_fee']  = ['nullable', 'numeric', 'min:0'];
        }

        $data = $request->validate($rules);

        $franchiseId = Auth::user()->franchise_id;

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('student-photos', 'public')
            : null;

        DB::transaction(function () use ($data, $franchiseId, $isInternal, $photoPath, &$student) {
            $user = User::create([
                'name'         => $data['first_name'] . ' ' . $data['last_name'],
                'email'        => $data['email'],
                'password'     => Hash::make($data['password']),
                'franchise_id' => $franchiseId,
                'student_type' => $isInternal ? 'internal' : 'external',
            ]);
            $user->assignRole('student');

            $franchise   = Auth::user()->franchise;
            $studentCode = Student::generateCode(
                $franchise,
                \Illuminate\Support\Carbon::parse($data['enrollment_date'])
            );

            $student = Student::create([
                'franchise_id'     => $franchiseId,
                'user_id'          => $user->id,
                'student_code'     => $studentCode,
                'student_type'     => $isInternal ? 'internal' : 'external',
                'first_name'       => $data['first_name'],
                'last_name'        => $data['last_name'],
                'photo'            => $photoPath,
                'date_of_birth'    => $data['date_of_birth'],
                'gender'           => $data['gender'],
                'current_level_id' => $data['current_level_id'],
                'monthly_fee'      => $isInternal ? ($data['monthly_fee'] ?? null) : null,
                'enrollment_date'  => $data['enrollment_date'],
                'address'          => $data['address'] ?? null,
                'city'             => $data['city'] ?? null,
                'pincode'          => $data['pincode'] ?? null,
                'is_active'        => true,
            ]);

            StudentParent::create([
                'student_id'   => $student->id,
                'name'         => $data['parent_name'],
                'relationship' => $data['parent_relationship'] ?? 'guardian',
                'phone'        => $data['parent_phone'],
                'whatsapp'     => $data['parent_whatsapp'] ?? $data['parent_phone'],
                'email'        => $data['parent_email'] ?? null,
                'is_primary'   => true,
            ]);

            // External: link to competition
            $competitionRegistration = null;
            if (!$isInternal && !empty($data['competition_id'])) {
                $competitionRegistration = CompetitionRegistration::create([
                    'competition_id'    => $data['competition_id'],
                    'student_id'        => $student->id,
                    'franchise_id'      => $franchiseId,
                    'registered_by'     => Auth::id(),
                    'registration_date' => now()->toDateString(),
                    'student_type'      => 'external',
                    'status'            => 'registered',
                ]);
            }

            // External: create a competition registration fee record
            if (!$isInternal && !empty($data['registration_fee'])) {
                Fee::create([
                    'franchise_id' => $franchiseId,
                    'student_id'   => $student->id,
                    'level_id'     => null,
                    'competition_registration_id' => $competitionRegistration?->id,
                    'student_type' => 'external',
                    'amount'       => $data['registration_fee'],
                    'month'        => now()->startOfMonth()->toDateString(),
                    'due_date'     => now()->addDays(7)->toDateString(),
                    'status'       => 'pending',
                    'paid_amount'  => 0,
                    'fee_type'     => 'competition_registration',
                ]);
            }
        });

        // Internal students: seed the enrollment-month tuition fee so the recurring
        // monthly cycle starts (next month rolls forward when this one is paid).
        app(\App\Services\MonthlyFeeService::class)->ensureFirstFee($student);

        AuditLogger::log('student_registered', 'Student', $student->id);

        $tab = $isInternal ? 'internal' : 'external';
        return redirect()->route('franchise.students.index', ['tab' => $tab])
            ->with('success', "Student {$data['first_name']} {$data['last_name']} registered successfully.");
    }

    public function show(Student $student): View
    {
        $student->load('currentLevel', 'parents', 'examAttempts.exam', 'payments', 'fees',
            'competitionRegistrations.competition',
            'certificates.level',
            'competitionPracticeAttempts.level',
            'practiceSessions.level');
        $levels = Level::orderBy('number')->get();
        return view('franchise.students.show', compact('student', 'levels'));
    }

    public function edit(Student $student): View
    {
        $student->load('user', 'primaryParent');
        $levels = Level::where('is_active', true)->orderBy('number')->get();
        return view('franchise.students.edit', compact('student', 'levels'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $userId = $student->user_id;

        $data = $request->validate([
            'first_name'          => ['required', 'string', 'max:100'],
            'last_name'           => ['required', 'string', 'max:100'],
            'photo'               => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'date_of_birth'       => ['required', 'date'],
            'gender'              => ['required', 'in:male,female,other'],
            'current_level_id'    => ['required', 'exists:levels,id'],
            'enrollment_date'     => ['required', 'date'],
            'email'               => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)->whereNull('deleted_at')],
            'address'             => ['nullable', 'string', 'max:300'],
            'city'                => ['nullable', 'string', 'max:100'],
            'pincode'             => ['nullable', 'string', 'max:10'],
            'is_active'           => ['boolean'],
            'parent_name'         => ['required', 'string', 'max:100'],
            'parent_relationship' => ['nullable', 'in:father,mother,guardian'],
            'parent_phone'        => ['required', 'string', 'max:15'],
            'parent_whatsapp'     => ['nullable', 'string', 'max:15'],
            'parent_email'        => ['nullable', 'email', 'max:150'],
        ]);

        DB::transaction(function () use ($request, $data, $student) {
            $studentData = collect($data)->only([
                'first_name', 'last_name', 'date_of_birth', 'gender',
                'current_level_id', 'enrollment_date', 'address', 'city', 'pincode',
            ])->all();
            $studentData['is_active'] = $request->boolean('is_active');

            if ($request->hasFile('photo')) {
                if ($student->photo) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($student->photo);
                }
                $studentData['photo'] = $request->file('photo')->store('student-photos', 'public');
            }

            $student->update($studentData);

            // Keep the linked login email in sync.
            if ($student->user) {
                $student->user->update([
                    'name'  => $data['first_name'] . ' ' . $data['last_name'],
                    'email' => $data['email'],
                ]);
            }

            // Update (or create) the primary parent/guardian record.
            $student->parents()->updateOrCreate(
                ['is_primary' => true],
                [
                    'name'         => $data['parent_name'],
                    'relationship' => $data['parent_relationship'] ?? 'guardian',
                    'phone'        => $data['parent_phone'],
                    'whatsapp'     => $data['parent_whatsapp'] ?? $data['parent_phone'],
                    'email'        => $data['parent_email'] ?? null,
                ]
            );
        });

        AuditLogger::log('student_updated', 'Student', $student->id);

        return redirect()->route('franchise.students.show', $student)
            ->with('success', 'Student updated.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        AuditLogger::log('student_deleted', 'Student', $student->id);
        $student->delete();

        return redirect()->route('franchise.students.index')
            ->with('success', 'Student deleted.');
    }

    public function importPage(Request $request): \Illuminate\View\View
    {
        if ($request->boolean('reset')) {
            session()->forget(['import_preview', 'import_rows']);
        }

        return view('franchise.students.bulk-import', ['preview' => session('import_preview')]);
    }

    public function import(Request $request): RedirectResponse
    {
        if ($request->boolean('confirm_import')) {
            return $this->commitImport();
        }

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file     = $request->file('csv_file');
        $rows     = array_filter(array_map('str_getcsv', file($file->getRealPath())));
        array_shift($rows); // header
        $required = ['Name', 'DOB', 'Gender', 'Parent Name', 'Mobile', 'Level', 'Email', 'Password'];

        $existingMobiles = StudentParent::pluck('phone')->map(fn ($p) => trim((string) $p))->toArray();
        $existingEmails  = User::withoutGlobalScopes()->pluck('email')
            ->map(fn ($e) => strtolower(trim($e)))->toArray();
        $levelIds = Level::where('is_active', true)->pluck('id', 'number');

        $seenMobiles = [];
        $seenEmails  = [];
        $preview     = [];
        $importRows  = [];

        foreach ($rows as $i => $row) {
            $row        = array_values($row);
            $name       = trim($row[0] ?? '');
            $dob        = trim($row[1] ?? '');
            $gender     = strtolower(trim($row[2] ?? ''));
            $parentName = trim($row[3] ?? '');
            $mobile     = trim($row[4] ?? '');
            $level      = trim($row[5] ?? '');
            $email      = strtolower(trim($row[6] ?? ''));
            $password   = (string) ($row[7] ?? '');

            $issue  = null;
            $status = 'valid';

            if (count($row) < count($required) || $name === '' || $dob === '' || $parentName === '' || $mobile === '' || $email === '' || $password === '') {
                $issue = 'Missing required fields';
            } elseif (! \DateTime::createFromFormat('Y-m-d', $dob)) {
                $issue = 'Invalid DOB (use YYYY-MM-DD)';
            } elseif (! in_array($gender, ['male', 'female', 'other'], true)) {
                $issue = 'Invalid gender';
            } elseif (! isset($levelIds[(int) $level])) {
                $issue = 'Invalid level';
            } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $issue = 'Invalid email';
            } elseif (strlen($password) < 8) {
                $issue = 'Password must be at least 8 characters';
            }

            if (! $issue) {
                if (in_array($mobile, $existingMobiles, true) || isset($seenMobiles[$mobile])) {
                    $issue  = 'Duplicate mobile number';
                    $status = 'duplicate';
                } elseif (in_array($email, $existingEmails, true) || isset($seenEmails[$email])) {
                    $issue  = 'Duplicate email';
                    $status = 'duplicate';
                }
            }

            if ($issue && $status !== 'duplicate') {
                $status = 'error';
            }

            if ($status === 'valid') {
                $seenMobiles[$mobile] = true;
                $seenEmails[$email]   = true;
                [$firstName, $lastName] = array_pad(explode(' ', $name, 2), 2, '');
                $importRows[] = [
                    'first_name'    => $firstName,
                    'last_name'     => $lastName !== '' ? $lastName : '-',
                    'date_of_birth' => $dob,
                    'gender'        => $gender,
                    'parent_name'   => $parentName,
                    'mobile'        => $mobile,
                    'level_id'      => $levelIds[(int) $level],
                    'email'         => $email,
                    'password'      => $password,
                ];
            }

            $preview[] = [
                'row'    => $i + 1,
                'name'   => $name,
                'level'  => $level,
                'mobile' => $mobile,
                'status' => $status,
                'issue'  => $issue ?? '',
            ];
        }

        $counts = [
            'valid'     => collect($preview)->where('status', 'valid')->count(),
            'errors'    => collect($preview)->where('status', 'error')->count(),
            'duplicate' => collect($preview)->where('status', 'duplicate')->count(),
        ];

        session(['import_preview' => ['rows' => $preview, 'counts' => $counts], 'import_rows' => $importRows]);

        return redirect()->route('franchise.students.import.page');
    }

    private function commitImport(): RedirectResponse
    {
        $importRows = session('import_rows', []);

        if (empty($importRows)) {
            return redirect()->route('franchise.students.import.page')
                ->with('error', 'Nothing to import — please upload a CSV first.');
        }

        $franchiseId = Auth::user()->franchise_id;
        $franchise   = Auth::user()->franchise;
        $imported    = 0;

        DB::transaction(function () use ($importRows, $franchiseId, $franchise, &$imported) {
            foreach ($importRows as $row) {
                $user = User::create([
                    'name'         => trim($row['first_name'] . ' ' . $row['last_name']),
                    'email'        => $row['email'],
                    'password'     => Hash::make($row['password']),
                    'franchise_id' => $franchiseId,
                    'student_type' => 'internal',
                ]);
                $user->assignRole('student');

                $studentCode = Student::generateCode($franchise, now());

                $student = Student::create([
                    'franchise_id'     => $franchiseId,
                    'user_id'          => $user->id,
                    'student_code'     => $studentCode,
                    'student_type'     => 'internal',
                    'first_name'       => $row['first_name'],
                    'last_name'        => $row['last_name'],
                    'date_of_birth'    => $row['date_of_birth'],
                    'gender'           => $row['gender'],
                    'current_level_id' => $row['level_id'],
                    'enrollment_date'  => now()->toDateString(),
                    'is_active'        => true,
                ]);

                StudentParent::create([
                    'student_id'   => $student->id,
                    'name'         => $row['parent_name'],
                    'relationship' => 'guardian',
                    'phone'        => $row['mobile'],
                    'whatsapp'     => $row['mobile'],
                    'is_primary'   => true,
                ]);

                app(\App\Services\MonthlyFeeService::class)->ensureFirstFee($student);

                AuditLogger::log('student_registered', 'Student', $student->id);

                $imported++;
            }
        });

        session()->forget(['import_preview', 'import_rows']);

        return redirect()->route('franchise.students.index', ['tab' => 'internal'])
            ->with('success', "{$imported} students imported successfully.");
    }

    public function importTemplate(): Response
    {
        $csv = "Name,DOB,Gender,Parent Name,Mobile,Level,Email,Password\n"
             . "Arjun Patil,2015-06-15,male,Suresh Patil,9876543210,1,arjun.patil@example.com,Passw0rd123\n";
        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_import_template.csv"',
        ]);
    }
}
