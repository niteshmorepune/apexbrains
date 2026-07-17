@extends('layouts.student')
@section('title', 'Help Guide')

@section('content')
<x-student-header title="Help Guide" :back="route('student.home')" />
<div class="max-w-2xl mx-auto px-4 pb-6 space-y-4">

    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-text-muted text-sm">Everything you need to know about using your Apex Brains student portal.</p>
    </div>

    <div class="space-y-3" x-data="{ open: 'practice' }">

        {{-- Practice --}}
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <button @click="open = open === 'practice' ? null : 'practice'"
                    class="w-full flex items-center justify-between px-5 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-green-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-stu" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.zap')</svg>
                    </div>
                    <span class="font-semibold text-gray-800">Daily Practice</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'practice' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'practice'" x-collapse class="border-t border-border px-5 pb-5 pt-4 space-y-2 text-sm text-text-muted">
                <p>Tap <strong>Practice</strong> in the bottom menu, then pick a type: <em>Regular Practice</em> or <em>Competition Practice</em>.</p>
                <p><strong>Regular Practice:</strong> pick a category, then a type within it (only the ones unlocked for your level appear), then how many questions (10 / 20 / 30) — your session starts right away. Numbers appear large on screen for mental-math drills, and you can tap the 🔊 speaker to hear the sum read aloud.</p>
                <p><strong>Competition Practice:</strong> no picking at all — tap Start and your full question set is generated automatically to match your level, with a countdown timer.</p>
                <p>One question shows at a time. Tap your answer to move to the next.</p>
                <p>After each session you'll see your accuracy, average speed (Regular Practice), or score and time (Competition Practice).</p>
            </div>
        </div>

        {{-- Exams --}}
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <button @click="open = open === 'exams' ? null : 'exams'"
                    class="w-full flex items-center justify-between px-5 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-purple-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.file-text')</svg>
                    </div>
                    <span class="font-semibold text-gray-800">Exams</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'exams' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'exams'" x-collapse class="border-t border-border px-5 pb-5 pt-4 space-y-2 text-sm text-text-muted">
                <p>Tap <strong>Exams</strong> to see available exams assigned by your franchise.</p>
                <p>Once you start an exam, the timer begins immediately and runs for the whole exam. Do not switch tabs — tab switches are recorded.</p>
                <p>Questions appear one at a time and move on automatically when you answer — there are no back/next buttons. Tap the 🔊 speaker to hear the sum read aloud. <strong>Submit Exam</strong> appears on the last question.</p>
                <p>Your score and pass/fail result are shown immediately after submission. Past attempts are listed on the exam detail page.</p>
            </div>
        </div>

        {{-- Competitions --}}
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <button @click="open = open === 'competitions' ? null : 'competitions'"
                    class="w-full flex items-center justify-between px-5 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-yellow-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.trophy')</svg>
                    </div>
                    <span class="font-semibold text-gray-800">Competitions</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'competitions' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'competitions'" x-collapse class="border-t border-border px-5 pb-5 pt-4 space-y-2 text-sm text-text-muted">
                <p>Open <strong>Competitions</strong> to see the competitions your franchise has registered you for. You don't register yourself — your branch does that for you.</p>
                <p>Open a competition for its rules and your level's question paper. When the competition's start date has arrived and you're registered, tap <em>I am Ready — Start Exam</em>. Before the start date it shows when it opens.</p>
                <p>Each level sits its own paper, so you'll always get the paper for your current level. Use Practice → Competition Practice to prepare beforehand.</p>
            </div>
        </div>

        {{-- Certificates --}}
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <button @click="open = open === 'certs' ? null : 'certs'"
                    class="w-full flex items-center justify-between px-5 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-orange-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.award')</svg>
                    </div>
                    <span class="font-semibold text-gray-800">Certificates</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'certs' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'certs'" x-collapse class="border-t border-border px-5 pb-5 pt-4 space-y-2 text-sm text-text-muted">
                <p>Open the <strong>Certs</strong> tile on your Home screen to reach your <em>Certificate Vault</em>, split into Exam and Competition tabs.</p>
                <p>Tap any certificate to see a full preview with a QR code that verifies its authenticity.</p>
                <p>From the preview you can <strong>Download as PDF</strong>, <strong>Print</strong>, or <strong>Share via WhatsApp</strong>. The downloaded PDF is a single-page certificate carrying the academy logo — anyone can scan its QR code to confirm it's genuine on the public verification page.</p>
            </div>
        </div>

        {{-- Results --}}
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <button @click="open = open === 'results' ? null : 'results'"
                    class="w-full flex items-center justify-between px-5 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-teal-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.bar-chart-2')</svg>
                    </div>
                    <span class="font-semibold text-gray-800">Results &amp; Profile</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'results' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'results'" x-collapse class="border-t border-border px-5 pb-5 pt-4 space-y-2 text-sm text-text-muted">
                <p>Tap <strong>Results</strong> in the bottom menu to see your full exam history with scores and pass/fail status, plus your average score and exams passed.</p>
                <p>Tap <strong>Profile</strong> to update your details or change your password.</p>
            </div>
        </div>

        {{-- Tips --}}
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <button @click="open = open === 'tips' ? null : 'tips'"
                    class="w-full flex items-center justify-between px-5 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-fran" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.help-circle')</svg>
                    </div>
                    <span class="font-semibold text-gray-800">Tips for Success</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'tips' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'tips'" x-collapse class="border-t border-border px-5 pb-5 pt-4 text-sm text-text-muted">
                <ul class="space-y-2 list-disc list-inside">
                    <li>Practice every day — even 10 minutes helps build speed.</li>
                    <li>During exams, stay on the exam tab. Tab switches are recorded.</li>
                    <li>Read all four options before selecting an answer.</li>
                    <li>Check your result immediately after an exam to see which questions you got wrong.</li>
                    <li>Use Competition Practice to prepare before competitions.</li>
                </ul>
            </div>
        </div>

    </div>

    <div class="bg-stu rounded-2xl p-5 text-center">
        <p class="text-white font-semibold text-sm">Questions? Talk to your teacher or franchise admin.</p>
    </div>

</div>
@endsection
