<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Models\Franchise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function index(Request $request): View
    {
        $levelFilter     = $request->get('level');
        $franchiseFilter = $request->get('franchise');

        $query = ExamAttempt::query()
            ->select(
                'student_id',
                'franchise_id',
                DB::raw('AVG(percentage) as avg_score'),
                DB::raw('COUNT(*) as exam_count'),
                DB::raw('AVG(TIMESTAMPDIFF(SECOND, started_at, submitted_at)) as avg_seconds')
            )
            ->where('status', 'submitted')
            ->whereNotNull('submitted_at')
            ->groupBy('student_id', 'franchise_id')
            ->orderByDesc('avg_score')
            ->orderBy('avg_seconds');

        if ($franchiseFilter) {
            $query->where('franchise_id', $franchiseFilter);
        }

        $rows = $query->with(['student.currentLevel', 'student.franchise'])
            ->limit(50)
            ->get();

        // Filter by level after eager loading (level is on the student)
        if ($levelFilter) {
            $rows = $rows->filter(fn($r) => $r->student?->current_level_id == $levelFilter);
        }

        $franchises = Franchise::where('status', 'active')->orderBy('name')->get();

        return view('admin.leaderboard', compact('rows', 'franchises', 'levelFilter', 'franchiseFilter'));
    }
}
