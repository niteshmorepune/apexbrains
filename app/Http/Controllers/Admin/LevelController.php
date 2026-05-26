<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LevelController extends Controller
{
    public function index(): View
    {
        $levels = Level::withCount('studentLevels as students_count')
            ->orderBy('sort_order')
            ->get();

        return view('admin.levels.index', compact('levels'));
    }

    public function create(): View
    {
        $nextNumber = Level::max('number') + 1;
        return view('admin.levels.create', compact('nextNumber'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'number'              => ['required', 'integer', 'min:1', 'unique:levels,number'],
            'title'               => ['required', 'string', 'max:100'],
            'description'         => ['nullable', 'string'],
            'fee_per_month'       => ['required', 'numeric', 'min:0'],
            'learning_objectives' => ['nullable', 'array'],
            'is_active'           => ['boolean'],
        ]);

        $data['sort_order'] = $data['number'];
        $data['slug'] = 'level-' . $data['number'];
        $data['is_active'] = $request->boolean('is_active', true);

        $level = Level::create($data);
        AuditLogger::log('level_created', "Level {$level->number} '{$level->title}' created", $level->id, 'level');

        return redirect()->route('admin.levels.index')
            ->with('success', "Level {$level->number} created successfully.");
    }

    public function show(Level $level): View
    {
        $level->loadCount('studentLevels as students_count');
        return view('admin.levels.show', compact('level'));
    }

    public function edit(Level $level): View
    {
        return view('admin.levels.edit', compact('level'));
    }

    public function update(Request $request, Level $level): RedirectResponse
    {
        $data = $request->validate([
            'title'               => ['required', 'string', 'max:100'],
            'description'         => ['nullable', 'string'],
            'fee_per_month'       => ['required', 'numeric', 'min:0'],
            'learning_objectives' => ['nullable', 'array'],
            'learning_objectives.*' => ['string', 'max:200'],
            'is_active'           => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $level->update($data);
        AuditLogger::log('level_updated', "Level {$level->number} '{$level->title}' updated", $level->id, 'level');

        return redirect()->route('admin.levels.index')
            ->with('success', "Level {$level->number} updated.");
    }

    public function destroy(Level $level): RedirectResponse
    {
        if ($level->students_count > 0) {
            return back()->with('error', "Cannot delete Level {$level->number} — it has active students.");
        }

        $level->delete();
        return redirect()->route('admin.levels.index')
            ->with('success', "Level {$level->number} deleted.");
    }
}
