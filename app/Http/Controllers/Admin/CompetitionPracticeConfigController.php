<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\CompetitionPracticeConfigImport;
use App\Models\CompetitionPracticeConfig;
use App\Models\CompetitionPracticeLevel;
use App\Models\Level;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompetitionPracticeConfigController extends Controller
{
    public function index(): View
    {
        $levels = Level::with(['competitionPracticeConfigs.category', 'competitionPracticeConfigs.type', 'competitionPracticeSetting'])
            ->orderBy('number')
            ->get();

        return view('admin.competition-practice-config.index', compact('levels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        $import = new CompetitionPracticeConfigImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not read the file. Make sure it matches the template. ('
                . $e->getMessage() . ')');
        }

        AuditLogger::log('competition_practice_config_imported', 'CompetitionPracticeConfig', null);

        $message = $import->imported > 0
            ? "Configuration updated — {$import->imported} row(s) applied."
            : 'No valid rows found — the configuration was not changed.';
        if (count($import->errors) > 0) {
            $message .= ' ' . count($import->errors) . ' row(s) skipped.';
        }

        return redirect()->route('admin.competition-practice-config.index')
            ->with($import->imported > 0 ? 'success' : 'error', $message)
            ->with('importErrors', $import->errors);
    }

    public function updateDuration(Request $request, Level $level): RedirectResponse
    {
        $data = $request->validate([
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:180'],
        ]);

        CompetitionPracticeLevel::updateOrCreate(['level_id' => $level->id], $data);

        AuditLogger::log('competition_practice_duration_updated', 'CompetitionPracticeLevel', $level->id);

        return back()->with('success', "Duration updated for Level {$level->number}.");
    }

    public function template(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="competition-practice-config-template.csv"',
        ];

        $columns = ['level', 'category', 'type', 'question_count'];
        $samples = [
            ['1', 'Without Partners', '1 Digit - 5 Rows', '100'],
            ['2', 'Without Partners', '1 Digit - 5 Rows', '50'],
        ];

        return response()->stream(function () use ($columns, $samples) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            foreach ($samples as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 200, $headers);
    }
}
