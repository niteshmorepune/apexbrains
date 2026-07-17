<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegularQuestionCategory;
use App\Models\RegularQuestionType;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegularQuestionTaxonomyController extends Controller
{
    public function index(): View
    {
        $categories = RegularQuestionCategory::with('types')
            ->withCount('questions')
            ->orderBy('sort_order')
            ->get();

        return view('admin.regular-questions.taxonomy', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:regular_question_categories,name'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $category = RegularQuestionCategory::create($data);
        AuditLogger::log('regular_category_created', 'RegularQuestionCategory', $category->id);

        return back()->with('success', "Category '{$category->name}' added.");
    }

    public function storeType(Request $request, RegularQuestionCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $exists = RegularQuestionType::where('category_id', $category->id)
            ->whereRaw('LOWER(name) = ?', [strtolower($data['name'])])
            ->exists();

        if ($exists) {
            return back()->with('error', "Type '{$data['name']}' already exists under {$category->name}.");
        }

        $type = $category->types()->create($data);
        AuditLogger::log('regular_type_created', 'RegularQuestionType', $type->id);

        return back()->with('success', "Type '{$type->name}' added under {$category->name}.");
    }

    public function destroyType(RegularQuestionType $type): RedirectResponse
    {
        $name = $type->name;
        $type->delete();
        AuditLogger::log('regular_type_deleted', 'RegularQuestionType', null);

        return back()->with('success', "Type '{$name}' removed.");
    }
}
