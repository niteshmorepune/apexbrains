<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\QuestionBank;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AudioQuestionController extends Controller
{
    public function index(): View
    {
        $audioQuestions = QuestionBank::with('level')
            ->where('type', 'audio')
            ->latest()
            ->paginate(15);

        $levels = Level::where('is_active', true)->orderBy('number')->get();

        return view('admin.audio-generator', compact('audioQuestions', 'levels'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'level_id'          => ['required', 'exists:levels,id'],
            'question_text'     => ['required', 'string', 'max:2000'],
            'voice'             => ['required', 'in:male,female'],
            'speed'             => ['required', 'in:slow,normal,fast'],
            'difficulty'        => ['required', 'in:easy,medium,hard'],
            'question_category' => ['nullable', 'string', 'max:100'],
        ]);

        // Placeholder audio path — real TTS integration in Phase 4
        $audioPath = 'audio/questions/' . uniqid('q_') . '.mp3';

        $question = QuestionBank::create([
            'level_id'          => $data['level_id'],
            'question_text'     => $data['question_text'],
            'type'              => 'audio',
            'difficulty'        => $data['difficulty'],
            'question_category' => $data['question_category'] ?? null,
            'audio_file_path'   => $audioPath,
            'status'            => 'approved',
            'approved_by'       => Auth::id(),
            'approved_at'       => now(),
        ]);

        AuditLogger::log('audio_question_generated', 'QuestionBank', $question->id);

        return redirect()->route('admin.questions.audio')
            ->with('success', 'Audio question generated and added to bank.');
    }
}
