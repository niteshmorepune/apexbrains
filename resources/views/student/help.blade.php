@extends('layouts.student')
@section('title', 'Help Guide')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 space-y-5">

    <div class="bg-white rounded-2xl border border-border p-5">
        <h1 class="text-lg font-bold text-stu mb-1">Student Help Guide</h1>
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
                <p>Tap <strong>Practice</strong> in the bottom menu to start a timed practice session.</p>
                <p>Questions are randomly selected from your current level. Answer as many as you can before the timer ends.</p>
                <p>Your results are saved automatically — check your progress on the Home screen.</p>
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
                <p>Once you start an exam, the timer begins immediately. Do not switch tabs — tab switches are recorded.</p>
                <p>Your score and pass/fail result are shown immediately after submission.</p>
                <p>You can review your answers on the result page. Past attempts are listed on the exam detail page.</p>
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
                <p>Tap <strong>Compete</strong> to see competitions your franchise has registered you for.</p>
                <p>Practice papers are available to help you prepare. You can attempt each practice paper as many times as you like.</p>
                <p>Your best score for each practice paper is displayed on the practice list.</p>
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
                <p>Tap <strong>Certs</strong> to view all certificates issued by your franchise.</p>
                <p>Each certificate has a QR code that can be scanned to verify authenticity.</p>
                <p>Download your certificate as a PDF for printing or sharing.</p>
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
                    <li>Use the practice papers to prepare before competitions.</li>
                </ul>
            </div>
        </div>

    </div>

    <div class="bg-stu rounded-2xl p-5 text-center">
        <p class="text-white font-semibold text-sm">Questions? Talk to your teacher or franchise admin.</p>
    </div>

</div>
@endsection
