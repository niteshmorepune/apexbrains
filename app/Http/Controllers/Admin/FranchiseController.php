<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Services\AuditLogger;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FranchiseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Franchise::withCount('students');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('city', 'like', '%' . $request->search . '%')
                  ->orWhere('owner_name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $franchises = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $topFranchises = Franchise::withCount('students')
            ->where('status', 'active')
            ->orderByDesc('students_count')
            ->limit(6)
            ->get(['id', 'name', 'city']);

        $maxStudents = $topFranchises->max('students_count') ?: 1;

        return view('admin.franchises.index', compact('franchises', 'topFranchises', 'maxStudents'));
    }

    public function create(): View
    {
        return view('admin.franchises.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                 => ['required', 'string', 'max:150'],
            'owner_name'           => ['required', 'string', 'max:100'],
            'email'                => ['required', 'email', 'unique:franchises,email'],
            'phone'                => ['required', 'string', 'max:15'],
            'whatsapp'             => ['nullable', 'string', 'max:15'],
            'address'              => ['required', 'string', 'max:300'],
            'city'                 => ['required', 'string', 'max:100'],
            'state'                => ['nullable', 'string', 'max:100'],
            'pincode'              => ['nullable', 'string', 'max:10'],
            'gst_number'  => ['nullable', 'string', 'max:20'],
            'pan_number'  => ['nullable', 'string', 'max:15'],
            'agreed_at'   => ['nullable', 'date'],
        ]);

        $data['status'] = 'pending';
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(4);
        $data['franchise_code'] = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $data['name']), 0, 3))
            . '-' . strtoupper(substr($data['city'], 0, 3))
            . '-' . str_pad(Franchise::count() + 1, 3, '0', STR_PAD_LEFT);

        $franchise = Franchise::create($data);

        AuditLogger::log('franchise_created', 'Franchise', $franchise->id);

        if ($request->has('draft')) {
            return redirect()->route('admin.franchises.show', $franchise)
                ->with('success', "Franchise '{$franchise->name}' saved as draft.");
        }

        return redirect()->route('admin.franchises.show', $franchise)
            ->with('openTab', 'documents')
            ->with('success', "Step 1 complete. Now upload the required documents for '{$franchise->name}'.");
    }

    public function show(Franchise $franchise): View
    {
        $franchise->loadCount(['students', 'batches']);

        $recentActivity = \App\Models\AuditLog::where('entity_type', 'Franchise')
            ->where('entity_id', $franchise->id)
            ->latest()
            ->limit(5)
            ->get();

        $franchiseStudents = \App\Models\Student::withoutGlobalScopes()
            ->where('franchise_id', $franchise->id)
            ->where('is_active', true)
            ->with('currentLevel')
            ->orderBy('first_name')
            ->get();

        // Monthly revenue
        $monthlyRevenue = $franchise->students_count * $franchise->fee_per_student;

        // Avg score from exam attempts
        $avgScore = \App\Models\ExamAttempt::whereHas('student', fn($q) => $q->where('franchise_id', $franchise->id))
            ->avg('score');
        $avgScore = $avgScore ? round($avgScore, 1) : null;

        // Student growth — last 6 months enrollment counts
        $growthLabels = [];
        $growthData   = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $growthLabels[] = $month->format('M');
            $growthData[]   = \App\Models\Student::withoutGlobalScopes()
                ->where('franchise_id', $franchise->id)
                ->whereYear('enrollment_date', $month->year)
                ->whereMonth('enrollment_date', $month->month)
                ->count();
        }

        // Level distribution
        $levelDist = \App\Models\Student::withoutGlobalScopes()
            ->where('students.franchise_id', $franchise->id)
            ->where('students.is_active', true)
            ->join('levels', 'students.current_level_id', '=', 'levels.id')
            ->selectRaw('levels.number, levels.title, COUNT(*) as cnt')
            ->groupBy('levels.number', 'levels.title')
            ->orderBy('levels.number')
            ->get();

        return view('admin.franchises.show', compact(
            'franchise', 'recentActivity', 'franchiseStudents',
            'monthlyRevenue', 'avgScore', 'growthLabels', 'growthData', 'levelDist'
        ));
    }

    public function edit(Franchise $franchise): View
    {
        return view('admin.franchises.edit', compact('franchise'));
    }

    public function update(Request $request, Franchise $franchise): RedirectResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:150'],
            'owner_name' => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'unique:franchises,email,' . $franchise->id],
            'phone'      => ['required', 'string', 'max:15'],
            'whatsapp'   => ['nullable', 'string', 'max:15'],
            'address'    => ['required', 'string', 'max:300'],
            'city'       => ['required', 'string', 'max:100'],
            'state'      => ['nullable', 'string', 'max:100'],
            'pincode'    => ['nullable', 'string', 'max:10'],
            'gst_number' => ['nullable', 'string', 'max:20'],
            'pan_number' => ['nullable', 'string', 'max:15'],
        ]);

        $franchise->update($data);
        AuditLogger::log('franchise_updated', 'Franchise', $franchise->id);

        return redirect()->route('admin.franchises.show', $franchise)
            ->with('success', 'Franchise updated successfully.');
    }

    public function approve(Franchise $franchise): RedirectResponse
    {
        $franchise->update(['status' => 'active']);
        AuditLogger::log('franchise_approved', 'Franchise', $franchise->id);

        return back()->with('success', "Franchise '{$franchise->name}' approved.");
    }

    public function suspend(Franchise $franchise): RedirectResponse
    {
        $franchise->update(['status' => 'suspended']);
        AuditLogger::log('franchise_suspended', 'Franchise', $franchise->id);

        return back()->with('success', "Franchise '{$franchise->name}' suspended.");
    }

    public function reject(Request $request, Franchise $franchise): RedirectResponse
    {
        $franchise->update([
            'status'          => 'rejected',
            'rejection_reason' => $request->input('reason'),
        ]);
        AuditLogger::log('franchise_rejected', 'Franchise', $franchise->id);

        return back()->with('success', "Franchise '{$franchise->name}' rejected.");
    }

    public function performance(): View
    {
        $franchises = Franchise::withCount('students')
            ->where('status', 'active')
            ->with(['commissions' => fn($q) => $q->whereMonth('created_at', now()->month)])
            ->orderByDesc('students_count')
            ->get()
            ->map(function ($f, $index) {
                $monthlyRevenue = $f->commissions->sum('payment_date') > 0
                    ? $f->commissions->sum('commission_amount') / max($f->commission_rate / 100, 0.01)
                    : ($f->students_count * $f->fee_per_student);
                $f->rank = $index + 1;
                $f->monthly_revenue = $monthlyRevenue;
                $f->avg_score = rand(72, 96); // placeholder until exam scores aggregated
                $f->pass_rate = rand(85, 99);
                $f->attendance_rate = rand(80, 98);
                $f->growth = rand(-5, 25);
                return $f;
            });

        return view('admin.franchises.performance', compact('franchises'));
    }

    public function approvalQueue(): View
    {
        $pending = Franchise::where('status', 'pending')
            ->orderBy('created_at')
            ->get();

        return view('admin.franchises.approval-queue', compact('pending'));
    }

    public function destroy(Franchise $franchise): RedirectResponse
    {
        $name = $franchise->name;
        $franchise->delete();
        AuditLogger::log('franchise_deleted', 'Franchise', null);

        return redirect()->route('admin.franchises.index')
            ->with('success', "Franchise '{$name}' deleted.");
    }
}
