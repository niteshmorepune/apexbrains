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
            'level_id'                  => ['required', 'exists:levels,id'],
            'total_questions'           => ['required', 'in:100,120,150'],
            'time_per_question_seconds' => ['required', 'in:2,2.5,3'],
            'session_length_minutes'    => ['nullable', 'in:8,10'],
            'audio_dictation'           => ['nullable', 'boolean'],
            'batch_id'                  => ['nullable', 'exists:batches,id'],
        ]);

        $level = Level::findOrFail($data['level_id']);

        // Prefer questions tagged to the level, but fall back to the general approved pool
        // (the seeded question bank is not level-tagged).
        $questions = QuestionBank::where('status', 'approved')
            ->where(fn ($q) => $q->where('level_id', $data['level_id'])->orWhereNull('level_id'))
            ->inRandomOrder()
            ->limit((int) $data['total_questions'])
            ->get();

        if ($questions->isEmpty()) {
            return back()->withErrors(['level_id' => 'No approved questions are available yet. Add questions to the bank first.']);
        }

        $session = ClassPracticeSession::create([
            'franchise_id'              => Auth::user()->franchise_id,
            'teacher_id'                => Auth::id(),
            'title'                     => 'Level ' . $level->number . ' Practice — ' . now()->format('d M Y, g:i A'),
            'level_id'                  => $data['level_id'],
            'question_category'         => 'level_practice',
            'total_questions'           => $questions->count(),
            'time_per_question_seconds' => $data['time_per_question_seconds'],
            'session_length_minutes'    => $data['session_length_minutes'] ?? null,
            'audio_dictation'           => $request->boolean('audio_dictation'),
            'batch_id'                  => $data['batch_id'] ?? null,
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
