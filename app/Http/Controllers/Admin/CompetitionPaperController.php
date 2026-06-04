<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompetitionPaperQuestion;
use App\Models\CompetitionPracticePaper;
use App\Models\Level;
use App\Models\QuestionBank;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CompetitionPaperController extends Controller
{
    public function index(Request $request): View
    {
        $query = CompetitionPracticePaper::with('level')->withCount('paperQuestions');

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        $papers = $query->orderBy('paper_number')->paginate(25)->withQueryString();

        return view('admin.competition-papers.index', compact('papers'));
    }

    public function create(): View
    {
        $nextNumber = CompetitionPracticePaper::max('paper_number') + 1;
        $levels     = Level::orderBy('number')->get();

        return view('admin.competition-papers.create', compact('nextNumber', 'levels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'            => ['required', 'string', 'max:200'],
            'description'      => ['nullable', 'string'],
            'level_id'         => ['required', 'exists:levels,id'],
            'total_questions'  => ['required', 'integer', 'min:1', 'max:200'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:180'],
            'difficulty'       => ['required', 'in:easy,medium,hard'],
            'paper_number'     => ['required', 'integer', 'min:1', 'unique:competition_practice_papers,paper_number'],
            'is_active'        => ['boolean'],
        ]);

        $data['created_by'] = Auth::id();
        $data['is_active']  = $request->boolean('is_active', true);

        $paper = CompetitionPracticePaper::create($data);

        $pulled = $this->syncQuestions($paper, (int) $data['total_questions']);

        if ($pulled === 0) {
            $paper->delete();

            return back()
                ->withInput()
                ->withErrors(['level_id' => 'No approved questions are available for this level yet. Add questions to the Question Bank first.']);
        }

        AuditLogger::log('practice_paper_created', 'CompetitionPracticePaper', $paper->id);

        $note = $pulled < (int) $data['total_questions']
            ? " (only {$pulled} approved questions were available)"
            : '';

        return redirect()->route('admin.competition-papers.index')
            ->with('success', "Practice Paper #{$paper->paper_number} created with {$pulled} questions{$note}.");
    }

    public function show(CompetitionPracticePaper $competitionPaper): View
    {
        $competitionPaper->load('level')->loadCount('paperQuestions');

        return view('admin.competition-papers.show', ['paper' => $competitionPaper]);
    }

    public function edit(CompetitionPracticePaper $competitionPaper): View
    {
        $levels = Level::orderBy('number')->get();

        return view('admin.competition-papers.edit', ['paper' => $competitionPaper, 'levels' => $levels]);
    }

    public function update(Request $request, CompetitionPracticePaper $competitionPaper): RedirectResponse
    {
        $data = $request->validate([
            'title'            => ['required', 'string', 'max:200'],
            'description'      => ['nullable', 'string'],
            'level_id'         => ['required', 'exists:levels,id'],
            'total_questions'  => ['required', 'integer', 'min:1', 'max:200'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:180'],
            'difficulty'       => ['required', 'in:easy,medium,hard'],
            'is_active'        => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        // Re-pull the question set if the level or requested size changed.
        $needsResync = $competitionPaper->level_id !== (int) $data['level_id']
            || $competitionPaper->paperQuestions()->count() !== (int) $data['total_questions'];

        $competitionPaper->update($data);

        if ($needsResync) {
            $pulled = $this->syncQuestions($competitionPaper, (int) $data['total_questions']);

            if ($pulled === 0) {
                return back()
                    ->withInput()
                    ->withErrors(['level_id' => 'No approved questions are available for this level. The paper was kept with its previous questions.']);
            }
        }

        AuditLogger::log('practice_paper_updated', 'CompetitionPracticePaper', $competitionPaper->id);

        return redirect()->route('admin.competition-papers.index')
            ->with('success', "Practice Paper #{$competitionPaper->paper_number} updated.");
    }

    public function destroy(CompetitionPracticePaper $competitionPaper): RedirectResponse
    {
        $num = $competitionPaper->paper_number;
        $competitionPaper->delete();
        AuditLogger::log('practice_paper_deleted', 'CompetitionPracticePaper', null);

        return redirect()->route('admin.competition-papers.index')
            ->with('success', "Practice Paper #{$num} deleted.");
    }

    /**
     * Auto-pull up to $count random approved questions for the paper's level
     * (falling back to the general, level-agnostic approved pool) and attach
     * them to the paper. Replaces any previously attached questions and keeps
     * total_questions in sync with what was actually available.
     *
     * @return int Number of questions actually attached.
     */
    protected function syncQuestions(CompetitionPracticePaper $paper, int $count): int
    {
        $questions = QuestionBank::where('status', 'approved')
            ->where(fn ($q) => $q->where('level_id', $paper->level_id)->orWhereNull('level_id'))
            ->inRandomOrder()
            ->limit($count)
            ->get();

        // Atomically replace the existing set.
        $paper->paperQuestions()->delete();

        foreach ($questions as $i => $q) {
            CompetitionPaperQuestion::create([
                'paper_id'    => $paper->id,
                'question_id' => $q->id,
                'sort_order'  => $i + 1,
            ]);
        }

        // Keep the displayed count honest about what was actually pulled.
        if ($paper->total_questions !== $questions->count()) {
            $paper->update(['total_questions' => $questions->count()]);
        }

        return $questions->count();
    }
}
