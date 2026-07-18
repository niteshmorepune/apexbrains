<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\RegularPracticeAccessImport;
use App\Models\Level;
use App\Models\RegularPracticeAccess;
use App\Models\RegularQuestionCategory;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegularPracticeAccessController extends Controller
{
    public function index(): View
    {
        $levels = Level::orderBy('number')->get();
        $categories = RegularQuestionCategory::with('types')->orderBy('sort_order')->get();

        // Set of "level_id:type_id" for quick lookup in the QA grid view.
        $accessSet = RegularPracticeAccess::all(['level_id', 'type_id'])
            ->map(fn ($row) => "{$row->level_id}:{$row->type_id}")
            ->flip()
            ->toArray();

        return view('admin.regular-practice-access.index', compact('levels', 'categories', 'accessSet'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        $import = new RegularPracticeAccessImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not read the file. Make sure it matches the template. ('
                . $e->getMessage() . ')');
        }

        AuditLogger::log('regular_practice_access_imported', 'RegularPracticeAccess', null);

        $message = $import->imported > 0
            ? "Access grid updated — {$import->imported} row(s) applied."
            : 'No valid rows found — the grid was not changed.';
        if (count($import->errors) > 0) {
            $message .= ' ' . count($import->errors) . ' row(s) skipped.';
        }

        return redirect()->route('admin.regular-practice-access.index')
            ->with($import->imported > 0 ? 'success' : 'error', $message)
            ->with('importErrors', $import->errors);
    }

    public function template(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="regular-practice-access-template.csv"',
        ];

        $columns = ['level', 'category', 'type'];
        $samples = [
            ['Junior 1', 'Without Partners', '1 Digit - 5 Rows'],
            ['Junior 1', 'Without Partners', '1 Digit - 8 Rows'],
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
