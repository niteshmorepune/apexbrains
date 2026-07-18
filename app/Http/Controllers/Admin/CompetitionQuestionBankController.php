<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompetitionQuestionBank;
use App\Models\CompetitionQuestionCategory;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CompetitionQuestionBankController extends Controller
{
    public function index(Request $request): View
    {
        $query = CompetitionQuestionBank::with(['category', 'type']);

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('question_text', 'like', '%' . $request->search . '%');
        }

        $questions = $query->latest()->paginate(20)->withQueryString();
        $categories = CompetitionQuestionCategory::with('types')->orderBy('sort_order')->get();

        $stats = [
            'total' => CompetitionQuestionBank::count(),
            'approved' => CompetitionQuestionBank::where('status', 'approved')->count(),
        ];

        return view('admin.competition-questions.index', compact('questions', 'categories', 'stats'));
    }

    public function create(): View
    {
        $categories = CompetitionQuestionCategory::with('types')->orderBy('sort_order')->get();

        return view('admin.competition-questions.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateQuestion($request);

        $data['correct_answer'] = strtolower($data['correct_answer']);
        $data['status'] = 'approved';
        $data['approved_by'] = Auth::id();
        $data['approved_at'] = now();

        $question = CompetitionQuestionBank::create($data);
        AuditLogger::log('competition_question_created', 'CompetitionQuestionBank', $question->id);

        return redirect()->route('admin.competition-questions.index')->with('success', 'Question added to bank.');
    }

    public function show(CompetitionQuestionBank $question): View
    {
        $question->load('category', 'type', 'approvedBy');

        return view('admin.competition-questions.show', ['question' => $question]);
    }

    public function edit(CompetitionQuestionBank $question): View
    {
        $categories = CompetitionQuestionCategory::with('types')->orderBy('sort_order')->get();

        return view('admin.competition-questions.edit', ['question' => $question, 'categories' => $categories]);
    }

    public function update(Request $request, CompetitionQuestionBank $question): RedirectResponse
    {
        $data = $this->validateQuestion($request);
        $data['correct_answer'] = strtolower($data['correct_answer']);

        $question->update($data);
        AuditLogger::log('competition_question_updated', 'CompetitionQuestionBank', $question->id);

        return redirect()->route('admin.competition-questions.index')->with('success', 'Question updated.');
    }

    public function destroy(CompetitionQuestionBank $question): RedirectResponse
    {
        $question->delete();
        AuditLogger::log('competition_question_deleted', 'CompetitionQuestionBank', $question->id);

        return redirect()->route('admin.competition-questions.index')->with('success', 'Question removed from bank.');
    }

    public function approve(CompetitionQuestionBank $question): RedirectResponse
    {
        $question->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        AuditLogger::log('competition_question_approved', 'CompetitionQuestionBank', $question->id);

        return back()->with('success', 'Question approved and added to bank.');
    }

    public function reject(CompetitionQuestionBank $question): RedirectResponse
    {
        $question->update(['status' => 'rejected']);
        AuditLogger::log('competition_question_rejected', 'CompetitionQuestionBank', $question->id);

        return back()->with('success', 'Question rejected.');
    }

    private function validateQuestion(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:competition_question_categories,id'],
            'type_id' => ['required', 'exists:competition_question_types,id'],
            'question_text' => ['required', 'string', 'max:2000'],
            'option_a' => ['required', 'string', 'max:500'],
            'option_b' => ['required', 'string', 'max:500'],
            'option_c' => ['nullable', 'string', 'max:500'],
            'option_d' => ['nullable', 'string', 'max:500'],
            'correct_answer' => ['required', 'in:A,B,C,D,a,b,c,d'],
        ]);
    }
}
