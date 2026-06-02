<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\QuestionBank;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class QuestionBankController extends Controller
{
    public function index(Request $request): View
    {
        $query = QuestionBank::with('level');

        if ($request->filled('level')) {
            $query->where('level_id', $request->level);
        }

        $tab = $request->get('tab', 'all');
        match ($tab) {
            'mcq'     => $query->where('type', 'mcq'),
            'audio'   => $query->where('type', 'audio'),
            'pending' => $query->where('status', 'pending'),
            default   => null,
        };

        // Sidebar "By Type" filter (separate from the top tabs).
        if ($request->filled('type') && in_array($request->type, ['mcq', 'audio'], true)) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where('question_text', 'like', '%' . $request->search . '%');
        }

        $questions = $query->latest()->paginate(20)->withQueryString();
        $levels    = Level::orderBy('number')->get();

        $stats = [
            'total'       => QuestionBank::count(),
            'mcq'         => QuestionBank::where('type', 'mcq')->count(),
            'audio'       => QuestionBank::where('type', 'audio')->count(),
            'pending'     => QuestionBank::where('status', 'pending')->count(),
            'pdf_sources' => QuestionBank::whereNotNull('source_pdf')->distinct('source_pdf')->count('source_pdf'),
        ];

        return view('admin.questions.index', compact('questions', 'levels', 'stats', 'tab'));
    }

    public function create(): View
    {
        $levels = Level::where('is_active', true)->orderBy('number')->get();
        return view('admin.questions.create', compact('levels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'level_id'          => ['required', 'exists:levels,id'],
            'question_text'     => ['required', 'string', 'max:2000'],
            'type'              => ['required', 'in:mcq,audio'],
            'option_a'          => ['nullable', 'string', 'max:500'],
            'option_b'          => ['nullable', 'string', 'max:500'],
            'option_c'          => ['nullable', 'string', 'max:500'],
            'option_d'          => ['nullable', 'string', 'max:500'],
            'correct_answer'    => ['nullable', 'in:A,B,C,D'],
            'difficulty'        => ['required', 'in:easy,medium,hard'],
            'question_category' => ['nullable', 'string', 'max:100'],
        ]);

        $data['correct_answer'] = !empty($data['correct_answer']) ? strtolower($data['correct_answer']) : null;
        $data['status']      = 'approved';
        $data['approved_by'] = Auth::id();
        $data['approved_at'] = now();

        $question = QuestionBank::create($data);
        AuditLogger::log('question_created', 'QuestionBank', $question->id);

        return redirect()->route('admin.questions.index')
            ->with('success', 'Question added to bank.');
    }

    public function show(QuestionBank $question): View
    {
        $question->load('level', 'approvedBy');
        return view('admin.questions.show', compact('question'));
    }

    public function edit(QuestionBank $question): View
    {
        $levels = Level::where('is_active', true)->orderBy('number')->get();
        return view('admin.questions.edit', compact('question', 'levels'));
    }

    public function update(Request $request, QuestionBank $question): RedirectResponse
    {
        $data = $request->validate([
            'level_id'          => ['required', 'exists:levels,id'],
            'question_text'     => ['required', 'string', 'max:2000'],
            'type'              => ['required', 'in:mcq,audio'],
            'option_a'          => ['nullable', 'string', 'max:500'],
            'option_b'          => ['nullable', 'string', 'max:500'],
            'option_c'          => ['nullable', 'string', 'max:500'],
            'option_d'          => ['nullable', 'string', 'max:500'],
            'correct_answer'    => ['nullable', 'in:A,B,C,D'],
            'difficulty'        => ['required', 'in:easy,medium,hard'],
            'question_category' => ['nullable', 'string', 'max:100'],
        ]);

        $data['correct_answer'] = !empty($data['correct_answer']) ? strtolower($data['correct_answer']) : null;

        $question->update($data);
        AuditLogger::log('question_updated', 'QuestionBank', $question->id);

        return redirect()->route('admin.questions.index')
            ->with('success', 'Question updated.');
    }

    public function destroy(QuestionBank $question): RedirectResponse
    {
        $question->delete();
        AuditLogger::log('question_deleted', 'QuestionBank', $question->id);

        return redirect()->route('admin.questions.index')
            ->with('success', 'Question removed from bank.');
    }

    public function approve(QuestionBank $question): RedirectResponse
    {
        $question->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        AuditLogger::log('question_approved', 'QuestionBank', $question->id);

        return back()->with('success', 'Question approved and added to bank.');
    }

    public function reject(QuestionBank $question): RedirectResponse
    {
        $question->update(['status' => 'rejected']);
        AuditLogger::log('question_rejected', 'QuestionBank', $question->id);

        return back()->with('success', 'Question rejected.');
    }
}
