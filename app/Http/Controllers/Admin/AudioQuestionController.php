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

/**
 * Audio questions are a Regular Practice / Class Practice concept only —
 * the Competition Question Bank is MCQ-only.
 */
class AudioQuestionController extends Controller
{
    public function index(): View
    {
        $audioQuestions = RegularQuestionBank::with(['category', 'type'])
            ->where('answer_format', 'audio')
            ->latest()
            ->paginate(15);

        $categories = RegularQuestionCategory::with('types')->orderBy('sort_order')->get();

        return view('admin.audio-generator', compact('audioQuestions', 'categories'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:regular_question_categories,id'],
            'type_id' => ['required', 'exists:regular_question_types,id'],
            'question_text' => ['required', 'string', 'max:2000'],
            'voice' => ['nullable', 'in:male,female'],
            'speed' => ['nullable', 'in:0.75,1,1.5,2'],
            'pause' => ['nullable', 'in:none,short,medium,long'],
            'difficulty' => ['nullable', 'in:easy,medium,hard'],
        ]);

        // Placeholder audio path — real TTS integration in Phase 4
        $audioPath = 'audio/questions/' . uniqid('q_') . '.mp3';

        $question = RegularQuestionBank::create([
            'category_id' => $data['category_id'],
            'type_id' => $data['type_id'],
            'question_text' => $data['question_text'],
            'answer_format' => 'audio',
            'difficulty' => $data['difficulty'] ?? 'medium',
            'audio_file_path' => $audioPath,
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        AuditLogger::log('audio_question_generated', 'RegularQuestionBank', $question->id);

        return redirect()->route('admin.questions.audio')
            ->with('success', 'Audio question generated and added to bank.');
    }
}
