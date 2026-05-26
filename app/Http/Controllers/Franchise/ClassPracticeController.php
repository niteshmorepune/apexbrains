<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\ClassPracticeResult;
use App\Models\ClassPracticeSession;
use App\Models\ClassPracticeSessionQuestion;
use App\Models\Level;
use App\Models\QuestionBank;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ClassPracticeController extends Controller
{
    public function index(): View
    {
        $sessions = ClassPracticeSession::with(['level', 'batch', 'result'])
            ->latest()
            ->paginate(20);

        return view('franchise.class-practice.index', compact('sessions'));
    }

    public function create(): View
    {
        $levels  = Level::orderBy('number')->get();
        $batches = Batch::where('is_active', true)->orderBy('name')->get();

        return view('franchise.class-practice.create', compact('levels', 'batches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'                     => ['required', 'string', 'max:100'],
            'level_id'                  => ['required', 'exists:levels,id'],
            'question_category'         => ['required', 'in:mcq,abacus,mental_math,mixed'],
            'total_questions'           => ['required', 'integer', 'min:1', 'max:50'],
            'time_per_question_seconds' => ['required', 'integer', 'min:5', 'max:300'],
            'batch_id'                  => ['nullable', 'exists:batches,id'],
        ]);

        $questionQuery = QuestionBank::where('level_id', $data['level_id'])
            ->where('status', 'approved');

        if ($data['question_category'] !== 'mixed') {
            $questionQuery->where('type', $data['question_category']);
        }

        $questions = $questionQuery->inRandomOrder()->limit($data['total_questions'])->get();

        if ($questions->isEmpty()) {
            return back()->withErrors(['level_id' => 'No approved questions found for the selected level and category.']);
        }

        $session = ClassPracticeSession::create([
            'franchise_id'              => Auth::user()->franchise_id,
            'teacher_id'                => Auth::id(),
            'title'                     => $data['title'],
            'level_id'                  => $data['level_id'],
            'question_category'         => $data['question_category'],
            'total_questions'           => $questions->count(),
            'time_per_question_seconds' => $data['time_per_question_seconds'],
            'batch_id'                  => $data['batch_id'],
            'status'                    => 'pending',
            'current_question_index'    => 0,
            'session_code'              => strtoupper(substr(md5(uniqid()), 0, 6)),
        ]);

        foreach ($questions as $i => $q) {
            ClassPracticeSessionQuestion::create([
                'session_id'  => $session->id,
                'question_id' => $q->id,
                'sort_order'  => $i + 1,
            ]);
        }

        AuditLogger::log('class_practice_created', 'ClassPracticeSession', $session->id);

        return redirect()->route('franchise.class-practice.show', $session);
    }

    public function show(ClassPracticeSession $session): View
    {
        $session->load(['level', 'batch', 'sessionQuestions.question', 'result']);

        return view('franchise.class-practice.show', compact('session'));
    }

    public function project(ClassPracticeSession $session): View
    {
        return view('franchise.class-practice.project', compact('session'));
    }

    public function state(ClassPracticeSession $session): JsonResponse
    {
        $currentQ = null;
        $revealed  = Cache::get("cp_revealed_{$session->id}", false);

        if ($session->status === 'active' && $session->current_question_index > 0) {
            $sq = $session->sessionQuestions()
                ->where('sort_order', $session->current_question_index)
                ->with('question')
                ->first();

            if ($sq) {
                $currentQ = [
                    'text'           => $sq->question->question_text,
                    'option_a'       => $sq->question->option_a,
                    'option_b'       => $sq->question->option_b,
                    'option_c'       => $sq->question->option_c,
                    'option_d'       => $sq->question->option_d,
                    'correct_answer' => $revealed ? $sq->question->correct_answer : null,
                ];
            }
        }

        return response()->json([
            'status'            => $session->status,
            'current_index'     => $session->current_question_index,
            'total'             => $session->total_questions,
            'time_per_question' => $session->time_per_question_seconds,
            'revealed'          => $revealed,
            'question'          => $currentQ,
        ]);
    }

    public function next(ClassPracticeSession $session): RedirectResponse
    {
        if ($session->status === 'pending') {
            $session->update([
                'status'                 => 'active',
                'current_question_index' => 1,
                'started_at'             => now(),
            ]);
        } elseif ($session->status === 'active') {
            $nextIndex = $session->current_question_index + 1;

            if ($nextIndex > $session->total_questions) {
                return $this->end($session);
            }

            $session->update(['current_question_index' => $nextIndex]);
        }

        Cache::forget("cp_revealed_{$session->id}");

        return redirect()->route('franchise.class-practice.project', $session);
    }

    public function reveal(ClassPracticeSession $session): RedirectResponse
    {
        Cache::put("cp_revealed_{$session->id}", true, now()->addHours(4));

        return redirect()->route('franchise.class-practice.project', $session);
    }

    public function end(ClassPracticeSession $session): RedirectResponse
    {
        if ($session->status !== 'ended') {
            $session->update([
                'status'   => 'ended',
                'ended_at' => now(),
            ]);

            ClassPracticeResult::create([
                'session_id'            => $session->id,
                'franchise_id'          => $session->franchise_id,
                'total_questions_shown' => $session->current_question_index,
                'completed_at'          => now(),
            ]);

            Cache::forget("cp_revealed_{$session->id}");

            AuditLogger::log('class_practice_ended', 'ClassPracticeSession', $session->id);
        }

        return redirect()->route('franchise.class-practice.results', $session);
    }

    public function results(ClassPracticeSession $session): View
    {
        $session->load(['level', 'batch', 'sessionQuestions.question', 'result']);

        return view('franchise.class-practice.results', compact('session'));
    }
}
