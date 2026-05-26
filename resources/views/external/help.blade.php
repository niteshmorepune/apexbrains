@extends('layouts.external')
@section('title', 'Help Guide')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 space-y-5">

    <div class="bg-white rounded-2xl border border-border p-5">
        <h1 class="text-lg font-bold text-fran mb-1">Help Guide</h1>
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
                <p>Tap <strong>Practice</strong> to see all 50 available competition practice papers.</p>
                <p>Each paper has a set number of questions and a time limit. Start a paper and answer the questions before the timer runs out.</p>
                <p>Your score is shown immediately after submission. You can re-attempt a paper as many times as you like — your best score is displayed on the list.</p>
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
                <p>Tap <strong>Compete</strong> to see upcoming competitions you are registered for.</p>
                <p>Registrations are handled by your institute/centre. If you don't see a competition, contact your teacher.</p>
                <p>Use the practice papers to prepare before the actual competition date.</p>
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
