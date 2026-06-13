<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\ClassPracticeResult;
use App\Models\CompetitionPracticePaper;
use App\Models\ClassPracticeSession;
use App\Models\ClassPracticeSessionQuestion;
use App\Models\Level;
use App\Models\QuestionBank;
use App\Services\AuditLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ClassPracticeController extends Controller
{
    public function index(Request $request): View
    {
        $activeLevel = $request->integer('level') ?: null;

        $query = ClassPracticeSession::with(['level', 'batch', 'result'])->latest();

        if ($activeLevel) {
            $query->where('level_id', $activeLevel);
        }

        $sessions = $query->paginate(20)->withQueryString();

        // Levels that actually have sessions in this franchise — drives the filter tabs.
        $levelIds = ClassPracticeSession::query()->distinct()->pluck('level_id')->filter();
        $levels   = Level::whereIn('id', $levelIds)->orderBy('number')->get();

        return view('franchise.class-practice.index', compact('sessions', 'levels', 'activeLevel'));
    }

    public function create(): View
    {
        $levels  = Level::orderBy('number')->get();
        $batches = Batch::where('is_active', true)->orderBy('name')->get();

        return view('franchise.class-practice.create', compact('levels', 'batches'));
    }

    /**
     * Catalogue of ready-made practice papers (Figma F42), grouped by level.
     * These are the Admin-authored Practice Papers (single source of truth);
     * the franchise presents them through the flashcard player.
     */
    public function papers(): View
    {
        $papers = CompetitionPracticePaper::with('level')
            ->where('is_active', true)
            ->orderBy('level_id')
            ->orderBy('paper_number')
            ->get();

        return view('franchise.class-practice.papers', compact('papers'));
    }

    /**
     * Launch the flashcard player for a paper's fixed question set.
     */
    public function attemptPaper(CompetitionPracticePaper $paper): RedirectResponse
    {
        $questions = $paper->paperQuestions()->orderBy('sort_order')->get();

        if ($questions->isEmpty()) {
            return redirect()
                ->route('franchise.class-practice.papers')
                ->with('error', 'This paper has no questions yet.');
        }

        $session = ClassPracticeSession::create([
            'franchise_id'              => Auth::user()->franchise_id,
            'teacher_id'                => Auth::id(),
            'title'                     => $paper->title,
            'level_id'                  => $paper->level_id,
            'question_category'         => 'level_practice',
            'total_questions'           => $questions->count(),
            'time_per_question_seconds' => 2,
            'audio_dictation'           => true,
            'status'                    => 'pending',
            'current_question_index'    => 0,
            'session_code'              => strtoupper(substr(md5(uniqid()), 0, 6)),
        ]);

        foreach ($questions as $pq) {
            ClassPracticeSessionQuestion::create([
                'session_id'  => $session->id,
                'question_id' => $pq->question_id,
                'sort_order'  => $pq->sort_order,
            ]);
        }

        AuditLogger::log('class_practice_paper_attempted', 'CompetitionPracticePaper', $paper->id);

        return redirect()->route('franchise.class-practice.project', $session);
    }

    /**
     * Download the answer key for a paper as a PDF.
     */
    public function paperAnswers(CompetitionPracticePaper $paper): Response
    {
        $paper->load('level');
        $questions = $paper->paperQuestions()->with('question')->orderBy('sort_order')->get();

        $pdf = Pdf::loadView('franchise.class-practice.paper-pdf', compact('paper', 'questions'));

        $slug = str_replace(' ', '-', strtolower($paper->title));

        return $pdf->download('answer-key-' . $slug . '.pdf');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'level_id'                  => ['required', 'exists:levels,id'],
            'total_questions'           => ['required', 'in:10,20,30'],
            'time_per_question_seconds' => ['required', 'in:0.5,1,1.5,2,2.5,3'],
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
        $session->load('level');

        $currentQuestion = null;
        if ($session->status === 'active' && $session->current_question_index > 0) {
            $sq = $session->sessionQuestions()
                ->where('sort_order', $session->current_question_index)
                ->with('question')
                ->first();

            $currentQuestion = $sq?->question;
        }

        // On the end screen we project a classroom answer key so students can
        // self-check against their notebooks.
        $answerKey = collect();
        $shown     = $session->total_questions;
        if ($session->status === 'ended') {
            $answerKey = $session->sessionQuestions()->with('question')->orderBy('sort_order')->get();
            $shown     = $session->result?->total_questions_shown ?? $session->current_question_index;
        }

        return view('franchise.class-practice.project', compact('session', 'currentQuestion', 'answerKey', 'shown'));
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

        // Stay on the projector so the class sees the answer key end screen.
        return redirect()->route('franchise.class-practice.project', $session);
    }

    public function results(ClassPracticeSession $session): View
    {
        $session->load(['level', 'batch', 'sessionQuestions.question', 'result']);

        return view('franchise.class-practice.results', compact('session'));
    }

    /**
     * Replay the exact same question set (same order) as a fresh session,
     * preserving the completed session as history.
     */
    public function replay(ClassPracticeSession $session): RedirectResponse
    {
        $new = $this->cloneSession($session, reuseQuestions: true);

        return redirect()->route('franchise.class-practice.project', $new);
    }

    /**
     * Run the same settings (level, timer, count, audio) again with a freshly
     * randomized question set.
     */
    public function again(ClassPracticeSession $session): RedirectResponse
    {
        $new = $this->cloneSession($session, reuseQuestions: false);

        if ($new === null) {
            return redirect()
                ->route('franchise.class-practice.results', $session)
                ->with('error', 'No approved questions are available for this level right now.');
        }

        return redirect()->route('franchise.class-practice.project', $new);
    }

    /**
     * Create a new pending session from an existing one. When $reuseQuestions is
     * true the same questions/order are copied; otherwise a fresh random set is drawn.
     */
    protected function cloneSession(ClassPracticeSession $session, bool $reuseQuestions): ?ClassPracticeSession
    {
        $session->loadMissing('level');

        if ($reuseQuestions) {
            $source = $session->sessionQuestions()->orderBy('sort_order')->get();
        } else {
            $source = QuestionBank::where('status', 'approved')
                ->where(fn ($q) => $q->where('level_id', $session->level_id)->orWhereNull('level_id'))
                ->inRandomOrder()
                ->limit($session->total_questions)
                ->get();

            if ($source->isEmpty()) {
                return null;
            }
        }

        $new = ClassPracticeSession::create([
            'franchise_id'              => $session->franchise_id,
            'teacher_id'                => Auth::id(),
            'title'                     => 'Level ' . $session->level?->number . ' Practice — ' . now()->format('d M Y, g:i A'),
            'level_id'                  => $session->level_id,
            'question_category'         => $session->question_category,
            'total_questions'           => $reuseQuestions ? $source->count() : $session->total_questions,
            'time_per_question_seconds' => $session->time_per_question_seconds,
            'audio_dictation'           => $session->audio_dictation,
            'batch_id'                  => $session->batch_id,
            'status'                    => 'pending',
            'current_question_index'    => 0,
            'session_code'              => strtoupper(substr(md5(uniqid()), 0, 6)),
        ]);

        foreach ($source as $i => $row) {
            ClassPracticeSessionQuestion::create([
                'session_id'  => $new->id,
                'question_id' => $reuseQuestions ? $row->question_id : $row->id,
                'sort_order'  => $reuseQuestions ? $row->sort_order : $i + 1,
            ]);
        }

        AuditLogger::log(
            $reuseQuestions ? 'class_practice_replayed' : 'class_practice_cloned',
            'ClassPracticeSession',
            $new->id
        );

        return $new;
    }
}
