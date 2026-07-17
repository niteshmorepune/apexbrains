<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\LevelUpExamPaperItemsImport;
use App\Models\Exam;
use App\Models\LevelUpExamPaper;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Level-Up Exam question paper upload — mirrors Admin\CompetitionQuestionPaperController
 * exactly, but scoped to a single exam_id (the level is already fixed on the
 * parent Exam row, so there's no level picker here).
 */
class LevelUpExamPaperController extends Controller
{
    public function store(Request $request, Exam $exam): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:200'],
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        // Only one active paper per exam — replace, don't accumulate.
        $exam->papers()->where('is_active', true)->update(['is_active' => false]);

        $paper = $exam->papers()->create([
            'title' => $data['title'] ?? null,
            'total_questions' => 0,
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        $import = new LevelUpExamPaperItemsImport($paper->id);

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
        $exam->update(['total_questions' => $import->imported]);

        AuditLogger::log('level_up_exam_paper_uploaded', 'LevelUpExamPaper', $paper->id);

        $message = "{$import->imported} question(s) uploaded to '{$exam->title}'.";
        if (count($import->errors) > 0) {
            $message .= ' ' . count($import->errors) . ' row(s) skipped.';
        }

        return redirect()->route('admin.exams.show', $exam)
            ->with('success', $message)
            ->with('importErrors', $import->errors);
    }

    public function destroy(Exam $exam, LevelUpExamPaper $paper): RedirectResponse
    {
        abort_unless($paper->exam_id === $exam->id, 404);

        $paper->delete(); // cascades to items

        AuditLogger::log('level_up_exam_paper_deleted', 'LevelUpExamPaper', null);

        return redirect()->route('admin.exams.show', $exam)
            ->with('success', 'Question paper deleted.');
    }

    public function template(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="level-up-exam-paper-template.csv"',
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
