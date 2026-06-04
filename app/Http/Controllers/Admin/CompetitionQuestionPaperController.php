<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\CompetitionPaperQuestionsImport;
use App\Models\Competition;
use App\Models\CompetitionQuestionPaper;
use App\Models\Level;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompetitionQuestionPaperController extends Controller
{
    public function create(Competition $competition): View
    {
        $levels = Level::orderBy('number')->get();

        return view('admin.competition-question-papers.create', compact('competition', 'levels'));
    }

    public function store(Request $request, Competition $competition): RedirectResponse
    {
        $data = $request->validate([
            'title'            => ['required', 'string', 'max:200'],
            'level_id'         => ['required', 'exists:levels,id'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:180'],
            'pass_percentage'  => ['required', 'integer', 'min:1', 'max:100'],
            'file'             => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        $paper = $competition->questionPapers()->create([
            'level_id'         => $data['level_id'],
            'title'            => $data['title'],
            'duration_minutes' => $data['duration_minutes'],
            'pass_percentage'  => $data['pass_percentage'],
            'total_questions'  => 0,
            'is_active'        => true,
            'created_by'       => Auth::id(),
        ]);

        $import = new CompetitionPaperQuestionsImport($paper->id);

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            $paper->delete();

            return back()->withInput()->with('error',
                'Could not read the file. Make sure it matches the template. (' . $e->getMessage() . ')');
        }

        if ($import->imported === 0) {
            $paper->delete();

            return back()->withInput()
                ->with('error', 'No valid questions found in the file.')
                ->with('importErrors', $import->errors);
        }

        $paper->update(['total_questions' => $import->imported]);

        AuditLogger::log('competition_paper_uploaded', 'CompetitionQuestionPaper', $paper->id);

        $message = "{$import->imported} question(s) uploaded to '{$paper->title}'.";
        if (count($import->errors) > 0) {
            $message .= ' ' . count($import->errors) . ' row(s) skipped.';
        }

        return redirect()->route('admin.competitions.show', $competition)
            ->with('success', $message)
            ->with('importErrors', $import->errors);
    }

    public function destroy(Competition $competition, CompetitionQuestionPaper $paper): RedirectResponse
    {
        abort_unless($paper->competition_id === $competition->id, 404);

        $title = $paper->title;
        $paper->delete(); // cascades to items + attempts

        AuditLogger::log('competition_paper_deleted', 'CompetitionQuestionPaper', null);

        return redirect()->route('admin.competitions.show', $competition)
            ->with('success', "Paper '{$title}' deleted.");
    }

    public function template(): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="competition-paper-template.csv"',
        ];

        $columns = ['question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer'];
        $samples = [
            ['What is 12 + 34?', '46', '44', '48', '45', 'a'],
            ['What is 56 - 19?', '37', '38', '36', '35', 'a'],
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
