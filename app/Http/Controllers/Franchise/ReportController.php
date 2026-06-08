<?php

namespace App\Http\Controllers\Franchise;

use App\Exports\StudentReportExport;
use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = Student::with(['currentLevel', 'examAttempts'])
            ->where('is_active', true);

        $type = $request->get('type', 'all');
        if (! in_array($type, ['all', 'internal', 'external'])) {
            $type = 'all';
        }
        if ($type !== 'all') {
            $query->where('student_type', $type);
        }

        if ($request->filled('search')) {
            $query->where(fn($q) => $q->where('first_name', 'like', '%' . $request->search . '%')
                ->orWhere('last_name', 'like', '%' . $request->search . '%'));
        }
        if ($request->filled('level')) {
            $query->where('current_level_id', $request->level);
        }

        $internalCount = Student::where('is_active', true)->where('student_type', 'internal')->count();
        $externalCount = Student::where('is_active', true)->where('student_type', 'external')->count();

        $sort = $request->get('sort', 'best_score');
        $students = $query->get()->map(function ($s) {
            $lastAttempt         = $s->examAttempts->sortByDesc('submitted_at')->first();
            $s->avg_score        = $s->examAttempts->avg('percentage') ?? 0;
            $s->exam_count       = $s->examAttempts->count();
            $s->last_score       = $lastAttempt?->percentage ?? 0;
            $s->last_exam_date   = $lastAttempt?->submitted_at;
            $s->avg_speed        = $s->examAttempts->filter(fn($a) => $a->submitted_at && $a->started_at)
                ->avg(fn($a) => $a->submitted_at->diffInSeconds($a->started_at));
            $s->eligible         = $s->avg_score >= 80 && $s->currentLevel && $s->currentLevel->number < 14;
            return $s;
        })->sortByDesc($sort === 'best_score' ? 'avg_score' : 'first_name');

        // Top student for radar preview
        $topStudent = $students->first();
        [$topAttempts, $topChartData, $topRadarData] = $topStudent
            ? $this->buildReportData($topStudent)
            : [collect(), [], []];

        return view('franchise.reports.index', compact('students', 'topStudent', 'topRadarData', 'type', 'internalCount', 'externalCount'));
    }

    public function show(Student $student): View
    {
        [$attempts, $chartData, $radarData] = $this->buildReportData($student);
        $levels = Level::orderBy('number')->get();

        return view('franchise.reports.show', compact('student', 'attempts', 'chartData', 'radarData', 'levels'));
    }

    public function downloadPdf(Student $student): Response
    {
        [$attempts, $chartData, $radarData] = $this->buildReportData($student);
        $levels = Level::orderBy('number')->get();

        $pdf = Pdf::loadView('franchise.reports.show', compact('student', 'attempts', 'chartData', 'radarData', 'levels'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('progress-report-' . $student->student_code . '.pdf');
    }

    private function buildReportData(Student $student): array
    {
        $student->load('currentLevel', 'examAttempts.exam', 'practiceSessions');

        $attempts = $student->examAttempts
            ->where('status', 'submitted')
            ->sortByDesc('submitted_at')
            ->values();

        $chartData = $attempts->map(fn($a) => [
            'label' => $a->submitted_at?->format('d M'),
            'score' => (float) ($a->percentage ?? 0),
        ])->values();

        $count       = $attempts->count();
        $avgScore    = $count ? (float) $attempts->avg('percentage') : 0;
        $passRate    = $count ? round($attempts->where('is_passed', true)->count() / $count * 100) : 0;
        $scores      = $attempts->pluck('percentage')->map(fn($v) => (float) $v)->toArray();
        $stdDev      = $count >= 2 ? $this->stdDev($scores) : 0;
        $consistency = max(0, min(100, round(100 - $stdDev)));
        $practiceAvg = $student->practiceSessions->isNotEmpty()
            ? round($student->practiceSessions->avg('score') ?? 0)
            : 0;
        $latestScore = $count ? round((float) $attempts->first()?->percentage) : 0;

        $radarData = [
            'labels' => ['Accuracy', 'Consistency', 'Pass Rate', 'Practice', 'Latest Score'],
            'values' => [$avgScore, $consistency, $passRate, $practiceAvg, $latestScore],
        ];

        return [$attempts, $chartData, $radarData];
    }

    private function stdDev(array $values): float
    {
        $n = count($values);
        if ($n < 2) return 0;
        $mean = array_sum($values) / $n;
        $variance = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $values)) / ($n - 1);
        return sqrt($variance);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $filename = 'student-report-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new StudentReportExport($request->search, $request->level),
            $filename
        );
    }
}
