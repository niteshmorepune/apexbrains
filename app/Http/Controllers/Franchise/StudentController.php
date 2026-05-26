<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
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
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Student::with('currentLevel')
            ->where('is_active', true);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('student_code', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('level')) {
            $query->where('current_level_id', $request->level);
        }

        $students = $query->orderBy('first_name')->paginate(20)->withQueryString();
        $levels   = Level::orderBy('number')->get();

        return view('franchise.students.index', compact('students', 'levels'));
    }

    public function create(): View
    {
        $levels = Level::where('is_active', true)->orderBy('number')->get();
        return view('franchise.students.create', compact('levels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name'       => ['required', 'string', 'max:100'],
            'last_name'        => ['required', 'string', 'max:100'],
            'date_of_birth'    => ['required', 'date', 'before:today'],
            'gender'           => ['required', 'in:male,female,other'],
            'current_level_id' => ['required', 'exists:levels,id'],
            'enrollment_date'  => ['required', 'date'],
            'email'            => ['required', 'email', 'unique:users,email'],
            'phone'            => ['nullable', 'string', 'max:15'],
            'address'          => ['nullable', 'string', 'max:300'],
            'city'             => ['nullable', 'string', 'max:100'],
            'pincode'          => ['nullable', 'string', 'max:10'],
            // Parent info
            'parent_name'      => ['required', 'string', 'max:100'],
            'parent_phone'     => ['required', 'string', 'max:15'],
            'parent_whatsapp'  => ['nullable', 'string', 'max:15'],
            'parent_email'     => ['nullable', 'email', 'max:150'],
        ]);

        $franchiseId = Auth::user()->franchise_id;

        DB::transaction(function () use ($data, $franchiseId, &$student) {
            // Create user account
            $user = User::create([
                'name'     => $data['first_name'] . ' ' . $data['last_name'],
                'email'    => $data['email'],
                'password' => Hash::make(Str::random(12)),
                'franchise_id' => $franchiseId,
            ]);
            $user->assignRole('student');

            // Generate student code: FranchiseCode-Year-Seq
            $franchise    = Auth::user()->franchise;
            $seq          = Student::withoutGlobalScopes()->where('franchise_id', $franchiseId)->count() + 1;
            $studentCode  = strtoupper(substr($franchise->franchise_code ?? 'ST', 0, 2))
                          . '-' . now()->year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $student = Student::create([
                'franchise_id'     => $franchiseId,
                'user_id'          => $user->id,
                'student_code'     => $studentCode,
                'student_type'     => 'internal',
                'first_name'       => $data['first_name'],
                'last_name'        => $data['last_name'],
                'date_of_birth'    => $data['date_of_birth'],
                'gender'           => $data['gender'],
                'current_level_id' => $data['current_level_id'],
                'enrollment_date'  => $data['enrollment_date'],
                'address'          => $data['address'] ?? null,
                'city'             => $data['city'] ?? null,
                'pincode'          => $data['pincode'] ?? null,
                'is_active'        => true,
            ]);

            StudentParent::create([
                'student_id'   => $student->id,
                'name'         => $data['parent_name'],
                'relationship' => 'parent',
                'phone'        => $data['parent_phone'],
                'whatsapp'     => $data['parent_whatsapp'] ?? $data['parent_phone'],
                'email'        => $data['parent_email'] ?? null,
                'is_primary'   => true,
            ]);
        });

        AuditLogger::log('student_registered', 'Student', $student->id);

        return redirect()->route('franchise.students.index')
            ->with('success', "Student {$data['first_name']} {$data['last_name']} registered successfully.");
    }

    public function show(Student $student): View
    {
        $student->load('currentLevel', 'parents', 'examAttempts', 'payments');
        return view('franchise.students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        $levels = Level::where('is_active', true)->orderBy('number')->get();
        return view('franchise.students.edit', compact('student', 'levels'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'first_name'       => ['required', 'string', 'max:100'],
            'last_name'        => ['required', 'string', 'max:100'],
            'date_of_birth'    => ['required', 'date'],
            'gender'           => ['required', 'in:male,female,other'],
            'current_level_id' => ['required', 'exists:levels,id'],
            'address'          => ['nullable', 'string', 'max:300'],
            'city'             => ['nullable', 'string', 'max:100'],
            'pincode'          => ['nullable', 'string', 'max:10'],
            'is_active'        => ['boolean'],
        ]);

        $student->update($data);
        AuditLogger::log('student_updated', 'Student', $student->id);

        return redirect()->route('franchise.students.show', $student)
            ->with('success', 'Student updated.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->update(['is_active' => false]);
        AuditLogger::log('student_deactivated', 'Student', $student->id);

        return redirect()->route('franchise.students.index')
            ->with('success', 'Student deactivated.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        // Store file, process in a real job in Phase 4 — for now validate and count
        $file       = $request->file('csv_file');
        $rows       = array_filter(array_map('str_getcsv', file($file->getRealPath())));
        $header     = array_shift($rows);
        $required   = ['Name', 'DOB', 'Gender', 'Parent Name', 'Mobile', 'Level'];
        $valid      = 0;
        $errors     = 0;

        foreach ($rows as $row) {
            if (count($row) >= count($required) && !empty($row[0])) {
                $valid++;
            } else {
                $errors++;
            }
        }

        return redirect()->route('franchise.students.index')
            ->with('success', "CSV parsed: {$valid} valid rows, {$errors} errors. Full import processing coming soon.");
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
