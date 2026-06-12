@extends('layouts.external')
@section('title', 'Help Guide')

@section('content')
<x-student-header title="Help Guide" :back="route('external.home')" />
<div class="max-w-2xl mx-auto px-4 pb-6 space-y-4">

    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-text-muted text-sm">How to use the Apex Brains Competition Portal.</p>
    </div>

    <div class="space-y-3" x-data="{ open: 'practice' }">

        {{-- Practice papers --}}
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <button @click="open = open === 'practice' ? null : 'practice'"
                    class="w-full flex items-center justify-between px-5 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-fran" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.zap')</svg>
                    </div>
                    <span class="font-semibold text-gray-800">Practice Papers</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'practice' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'practice'" x-collapse class="border-t border-border px-5 pb-5 pt-4 space-y-2 text-sm text-text-muted">
                <p>The <strong>Practice</strong> tile generates a fresh set of questions from the Question Bank each time. Pick a difficulty (Easy / Medium / Hard / Mixed) and the session starts immediately.</p>
                <p>Each session is timed. Tap the 🔊 speaker to hear the sum read aloud. Answer each question to move to the next; the timer runs across the whole session and doesn't reset per question.</p>
                <p>After submitting you'll see your accuracy and average speed. Your past sessions are listed under <em>Results</em>.</p>
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
                <p>Tap <strong>Exams</strong> to see your Upcoming and Past competitions.</p>
                <p>Open a competition to view its rules, duration, and question count. When it's open and you're registered, use <em>I am Ready — Start Exam</em>.</p>
                <p>Registrations are handled by your institute/centre. If you don't see a competition, contact your teacher. Use the practice papers to prepare beforehand.</p>
            </div>
        </div>

        {{-- During an attempt --}}
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <button @click="open = open === 'attempt' ? null : 'attempt'"
                    class="w-full flex items-center justify-between px-5 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-purple-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.help-circle')</svg>
                    </div>
                    <span class="font-semibold text-gray-800">During a Paper Attempt</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'attempt' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'attempt'" x-collapse class="border-t border-border px-5 pb-5 pt-4 text-sm text-text-muted">
                <ul class="space-y-2 list-disc list-inside">
                    <li>Your answer is saved automatically when you tap an option — no submit button per question.</li>
                    <li>A countdown timer is shown at the top. The paper auto-submits when time runs out.</li>
                    <li>You can change your answer any time before submitting.</li>
                    <li>Tap <strong>Submit Paper</strong> when you're done. You cannot change answers after submission.</li>
                    <li>Your score and correct answers are shown on the results page.</li>
                </ul>
            </div>
        </div>

        {{-- Results & Certificates --}}
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <button @click="open = open === 'results' ? null : 'results'"
                    class="w-full flex items-center justify-between px-5 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-teal-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.bar-chart-2')</svg>
                    </div>
                    <span class="font-semibold text-gray-800">Results &amp; Certificates</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'results' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'results'" x-collapse class="border-t border-border px-5 pb-5 pt-4 space-y-2 text-sm text-text-muted">
                <p>Tap <strong>Results</strong> in the bottom menu for your paper attempt history, average score, and overall progress.</p>
                <p>Your certificates live in the <em>Certificate Vault</em> (open it from the <em>Results &amp; Certificate</em> tile on Home). Each certificate has a QR code and can be downloaded as a PDF.</p>
            </div>
        </div>

        {{-- Profile --}}
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <button @click="open = open === 'profile' ? null : 'profile'"
                    class="w-full flex items-center justify-between px-5 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-gray-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.user')</svg>
                    </div>
                    <span class="font-semibold text-gray-800">Your Profile</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'profile' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'profile'" x-collapse class="border-t border-border px-5 pb-5 pt-4 space-y-2 text-sm text-text-muted">
                <p>Tap <strong>Profile</strong> to view your registration details and change your password.</p>
                <p>To update your name or contact details, contact your institute — those fields are managed by your centre.</p>
            </div>
        </div>

    </div>

    <div class="bg-fran rounded-2xl p-5 text-center">
        <p class="text-white font-semibold text-sm">Need help? Contact your institute or teacher.</p>
    </div>

</div>
@endsection
