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

        $query = Student::with([
                'currentLevel',
                'primaryParent',
                'examAttempts' => fn ($q) => $q->where('status', 'submitted')->latest('submitted_at')->limit(1),
            ])
            ->where('is_active', true);

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

        return view('franchise.students.index', compact('students', 'levels', 'tab', 'internalCount', 'externalCount', 'allCount'));
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

    public function importPage(): \Illuminate\View\View
    {
        return view('franchise.students.bulk-import', ['preview' => session('import_preview')]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file     = $request->file('csv_file');
        $rows     = array_filter(array_map('str_getcsv', file($file->getRealPath())));
        $header   = array_shift($rows);
        $required = ['Name', 'DOB', 'Gender', 'Parent Name', 'Mobile', 'Level'];
        $preview  = [];

        $existingMobiles = \App\Models\Student::withoutGlobalScopes()
            ->pluck('parent_mobile')->toArray();

        foreach ($rows as $i => $row) {
            $row = array_values($row);
            $name   = $row[0] ?? '';
            $mobile = $row[4] ?? '';
            $level  = $row[5] ?? '';

            if (count($row) < count($required) || empty($name)) {
                $status = 'error';
                $issue  = 'Missing required fields';
            } elseif (in_array($mobile, $existingMobiles)) {
                $status = 'duplicate';
                $issue  = 'Duplicate mobile number';
            } else {
                $status = 'valid';
                $issue  = '';
            }

            $preview[] = [
                'row'    => $i + 1,
                'name'   => $name,
                'level'  => $level,
                'mobile' => $mobile,
                'status' => $status,
                'issue'  => $issue,
            ];
        }

        $counts = [
            'valid'     => collect($preview)->where('status', 'valid')->count(),
            'errors'    => collect($preview)->where('status', 'error')->count(),
            'duplicate' => collect($preview)->where('status', 'duplicate')->count(),
        ];

        return redirect()->route('franchise.students.import.page')
            ->with('import_preview', ['rows' => $preview, 'counts' => $counts]);
    }

    public function importTemplate(): Response
    {
        $csv = "Name,DOB,Gender,Parent Name,Mobile,Level\nArjun Patil,2015-06-15,male,Suresh Patil,9876543210,1\n";
        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_import_template.csv"',
        ]);
    }
}
