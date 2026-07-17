<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompetitionQuestionCategory;
use App\Models\CompetitionQuestionType;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompetitionQuestionTaxonomyController extends Controller
{
    public function index(): View
    {
        $categories = CompetitionQuestionCategory::with('types')
            ->withCount('questions')
            ->orderBy('sort_order')
            ->get();

        return view('admin.competition-questions.taxonomy', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:competition_question_categories,name'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $category = CompetitionQuestionCategory::create($data);
        AuditLogger::log('competition_category_created', 'CompetitionQuestionCategory', $category->id);

        return back()->with('success', "Category '{$category->name}' added.");
    }

    public function storeType(Request $request, CompetitionQuestionCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $exists = CompetitionQuestionType::where('category_id', $category->id)
            ->whereRaw('LOWER(name) = ?', [strtolower($data['name'])])
            ->exists();

        if ($exists) {
            return back()->with('error', "Type '{$data['name']}' already exists under {$category->name}.");
        }

        $type = $category->types()->create($data);
        AuditLogger::log('competition_type_created', 'CompetitionQuestionType', $type->id);

        return back()->with('success', "Type '{$type->name}' added under {$category->name}.");
    }

    public function destroyType(CompetitionQuestionType $type): RedirectResponse
    {
        $name = $type->name;
        $type->delete();
        AuditLogger::log('competition_type_deleted', 'CompetitionQuestionType', null);

        return back()->with('success', "Type '{$name}' removed.");
    }
}
