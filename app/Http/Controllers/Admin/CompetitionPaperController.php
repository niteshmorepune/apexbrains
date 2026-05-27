<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompetitionPracticePaper;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CompetitionPaperController extends Controller
{
    public function index(Request $request): View
    {
        $query = CompetitionPracticePaper::query();

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        $papers = $query->orderBy('paper_number')->paginate(25)->withQueryString();

        return view('admin.competition-papers.index', compact('papers'));
    }

    public function create(): View
    {
        $nextNumber = CompetitionPracticePaper::max('paper_number') + 1;
        return view('admin.competition-papers.create', compact('nextNumber'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'            => ['required', 'string', 'max:200'],
            'description'      => ['nullable', 'string'],
            'total_questions'  => ['required', 'integer', 'min:1', 'max:200'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:180'],
            'difficulty'       => ['required', 'in:easy,medium,hard'],
            'paper_number'     => ['required', 'integer', 'min:1', 'unique:competition_practice_papers,paper_number'],
            'is_active'        => ['boolean'],
        ]);

        $data['created_by'] = Auth::id();
        $data['is_active']  = $request->boolean('is_active', true);

        $paper = CompetitionPracticePaper::create($data);
        AuditLogger::log('practice_paper_created', 'CompetitionPracticePaper', $paper->id);

        return redirect()->route('admin.competition-papers.index')
            ->with('success', "Practice Paper #{$paper->paper_number} created.");
    }

    public function show(CompetitionPracticePaper $competitionPaper): View
    {
        return view('admin.competition-papers.show', ['paper' => $competitionPaper]);
    }

    public function edit(CompetitionPracticePaper $competitionPaper): View
    {
        return view('admin.competition-papers.edit', ['paper' => $competitionPaper]);
    }

    public function update(Request $request, CompetitionPracticePaper $competitionPaper): RedirectResponse
    {
        $data = $request->validate([
            'title'            => ['required', 'string', 'max:200'],
            'description'      => ['nullable', 'string'],
            'total_questions'  => ['required', 'integer', 'min:1', 'max:200'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:180'],
            'difficulty'       => ['required', 'in:easy,medium,hard'],
            'is_active'        => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $competitionPaper->update($data);
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
}
