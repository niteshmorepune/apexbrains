<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\QuestionsImport;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuestionImportController extends Controller
{
    public function index(): View
    {
        return view('admin.questions.import');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        $import = new QuestionsImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not read the file. Make sure it matches the template. ('
                . $e->getMessage() . ')');
        }

        AuditLogger::log('questions_imported', 'QuestionBank', null);

        $message = "{$import->imported} question(s) imported.";
        if (count($import->errors) > 0) {
            $message .= ' ' . count($import->errors) . ' row(s) skipped.';
        }

        return redirect()->route('admin.questions.index')
            ->with($import->imported > 0 ? 'success' : 'error', $message)
            ->with('importErrors', $import->errors);
    }

    public function template(): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="question-import-template.csv"',
        ];

        $columns = ['level', 'question_text', 'type', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer', 'difficulty', 'question_category'];
        $samples = [
            ['1', 'What is 2 + 3?', 'mcq', '4', '5', '6', '7', 'b', 'easy', 'Addition'],
            ['2', '5 + 7 + 3 = ?', 'mcq', '12', '15', '13', '14', 'b', 'medium', 'Addition'],
            ['3', 'Listen and write the sum', 'audio', '', '', '', '', '', 'medium', 'Listening'],
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
