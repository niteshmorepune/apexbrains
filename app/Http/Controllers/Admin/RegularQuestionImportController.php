<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\RegularQuestionBankImport;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegularQuestionImportController extends Controller
{
    public function index(): View
    {
        return view('admin.regular-questions.import');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        $import = new RegularQuestionBankImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not read the file. Make sure it matches the template. ('
                . $e->getMessage() . ')');
        }

        AuditLogger::log('regular_questions_imported', 'RegularQuestionBank', null);

        $message = "{$import->imported} question(s) imported.";
        if (count($import->errors) > 0) {
            $message .= ' ' . count($import->errors) . ' row(s) skipped.';
        }

        return redirect()->route('admin.regular-questions.index')
            ->with($import->imported > 0 ? 'success' : 'error', $message)
            ->with('importErrors', $import->errors);
    }

    public function template(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="regular-question-import-template.csv"',
        ];

        $columns = ['category', 'type', 'question_text', 'answer_format', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer'];
        $samples = [
            ['Without Partners', '1 Digit - 5 Rows', '2 + 3 + 1 + 4 + 2 = ?', 'mcq', '12', '13', '11', '14', 'a'],
            ['Grouping', '2 Digit - 5 Rows', 'Listen and write the sum', 'audio', '', '', '', '', ''],
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
