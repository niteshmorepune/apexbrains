<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegularQuestionBank;
use App\Models\RegularQuestionCategory;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegularQuestionBankController extends Controller
{
    public function index(Request $request): View
    {
        $query = RegularQuestionBank::with(['category', 'type']);

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $tab = $request->get('tab', 'all');
        match ($tab) {
            'mcq' => $query->where('answer_format', 'mcq'),
            'audio' => $query->where('answer_format', 'audio'),
            default => null,
        };

        if ($request->filled('search')) {
            $query->where('question_text', 'like', '%' . $request->search . '%');
        }

        $questions = $query->latest()->paginate(20)->withQueryString();
        $categories = RegularQuestionCategory::with('types')->orderBy('sort_order')->get();

        $stats = [
            'total' => RegularQuestionBank::count(),
            'mcq' => RegularQuestionBank::where('answer_format', 'mcq')->count(),
            'audio' => RegularQuestionBank::where('answer_format', 'audio')->count(),
            'approved' => RegularQuestionBank::where('status', 'approved')->count(),
        ];

        return view('admin.regular-questions.index', compact('questions', 'categories', 'stats', 'tab'));
    }

    public function create(): View
    {
        $categories = RegularQuestionCategory::with('types')->orderBy('sort_order')->get();

        return view('admin.regular-questions.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateQuestion($request);

        $data['correct_answer'] = ! empty($data['correct_answer']) ? strtolower($data['correct_answer']) : null;
        $data['status'] = 'approved';
        $data['approved_by'] = Auth::id();
        $data['approved_at'] = now();

        $question = RegularQuestionBank::create($data);
        AuditLogger::log('regular_question_created', 'RegularQuestionBank', $question->id);

        return redirect()->route('admin.regular-questions.index')->with('success', 'Question added to bank.');
    }

    public function show(RegularQuestionBank $question): View
    {
        $question->load('category', 'type', 'approvedBy');

        return view('admin.regular-questions.show', ['question' => $question]);
    }

    public function edit(RegularQuestionBank $question): View
    {
        $categories = RegularQuestionCategory::with('types')->orderBy('sort_order')->get();

        return view('admin.regular-questions.edit', ['question' => $question, 'categories' => $categories]);
    }

    public function update(Request $request, RegularQuestionBank $question): RedirectResponse
    {
        $data = $this->validateQuestion($request);
        $data['correct_answer'] = ! empty($data['correct_answer']) ? strtolower($data['correct_answer']) : null;

        $question->update($data);
        AuditLogger::log('regular_question_updated', 'RegularQuestionBank', $question->id);

        return redirect()->route('admin.regular-questions.index')->with('success', 'Question updated.');
    }

    public function destroy(RegularQuestionBank $question): RedirectResponse
    {
        $question->delete();
        AuditLogger::log('regular_question_deleted', 'RegularQuestionBank', $question->id);

        return redirect()->route('admin.regular-questions.index')->with('success', 'Question removed from bank.');
    }

    public function approve(RegularQuestionBank $question): RedirectResponse
    {
        $question->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        AuditLogger::log('regular_question_approved', 'RegularQuestionBank', $question->id);

        return back()->with('success', 'Question approved and added to bank.');
    }

    public function reject(RegularQuestionBank $question): RedirectResponse
    {
        $question->update(['status' => 'rejected']);
        AuditLogger::log('regular_question_rejected', 'RegularQuestionBank', $question->id);

        return back()->with('success', 'Question rejected.');
    }

    private function validateQuestion(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:regular_question_categories,id'],
            'type_id' => ['required', 'exists:regular_question_types,id'],
            'question_text' => ['required', 'string', 'max:2000'],
            'answer_format' => ['required', 'in:mcq,audio'],
            'option_a' => ['nullable', 'string', 'max:500'],
            'option_b' => ['nullable', 'string', 'max:500'],
            'option_c' => ['nullable', 'string', 'max:500'],
            'option_d' => ['nullable', 'string', 'max:500'],
            'correct_answer' => ['nullable', 'in:A,B,C,D,a,b,c,d'],
        ]);
    }
}
