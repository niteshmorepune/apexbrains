@extends('layouts.franchise')
@section('title', 'Help Guide')
@section('page-title', 'Help Guide')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="bg-white rounded-xl border border-border p-6">
        <h2 class="text-lg font-bold text-fran mb-1">Franchise Admin — Help Guide</h2>
        <p class="text-text-muted text-sm">Step-by-step guidance for managing your branch. Click any section to expand.</p>
    </div>

    <div class="space-y-3" x-data="{ open: 'students' }">

        {{-- Students --}}
        <div class="bg-white rounded-xl border border-border overflow-hidden">
            <button @click="open = open === 'students' ? null : 'students'"
                    class="w-full flex items-center justify-between px-6 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-fran" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.users')</svg>
                    </div>
                    <span class="font-semibold text-gray-800">Student Management</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'students' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'students'" x-collapse class="border-t border-border px-6 pb-5 pt-4 space-y-3 text-sm text-text-muted">
                <p><span class="font-semibold text-gray-700">Register a student:</span> Students → Register. Add the photo, personal info, parent contact (with relationship), course enrolment (level, class schedule, auto-filled monthly fee), and any special notes. A login account is created automatically.</p>
                <p><span class="font-semibold text-gray-700">Bulk import:</span> Students → Bulk Import. Download the CSV template, fill it in, and upload — you get a <em>preview</em> showing valid / error / duplicate rows before confirming the import.</p>
                <p><span class="font-semibold text-gray-700">Filter the list:</span> Use the Internal/External and level pills to narrow the student list.</p>
                <p><span class="font-semibold text-gray-700">Edit / deactivate:</span> Click a student's name to open their profile, then Edit to update details or toggle active status.</p>
                <p><span class="font-semibold text-gray-700">Student login:</span> Internal students log in at <code class="bg-gray-100 px-1 rounded">/login</code> with their registered email and password.</p>
            </div>
        </div>

        {{-- Fees --}}
        <div class="bg-white rounded-xl border border-border overflow-hidden">
            <button @click="open = open === 'fees' ? null : 'fees'"
                    class="w-full flex items-center justify-between px-6 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.credit-card')</svg>
                    </div>
                    <span class="font-semibold text-gray-800">Fees & Payments</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'fees' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'fees'" x-collapse class="border-t border-border px-6 pb-5 pt-4 space-y-3 text-sm text-text-muted">
                <p><span class="font-semibold text-gray-700">Fee Collection:</span> Shows KPI cards (collected / outstanding / overdue / collection rate) and a status-filtered table (All / Paid / Due / Partial / Overdue). Use the <em>Quick Record</em> side panel for fast walk-in entries, or the <strong>Record</strong> button for the full payment form.</p>
                <p><span class="font-semibold text-gray-700">Record a payment:</span> Choose the student, amount, date, and payment mode (cash / UPI / card / cheque / bank transfer). A live receipt preview is shown; on save a receipt is generated. Partial amounts set the fee status to <em>Partial</em>.</p>
                <p><span class="font-semibold text-gray-700">Fee Reminders:</span> Fees → Fee Reminders lists outstanding fees with days overdue and a <em>Priority</em> badge. Send reminders per row via <strong>WhatsApp</strong>, <strong>SMS</strong>, or <strong>Call</strong>.</p>
                <p><span class="font-semibold text-gray-700">Receipts:</span> Each receipt has a QR code for verification and can be downloaded as a <strong>PDF</strong>, printed, or shared on <strong>WhatsApp</strong>.</p>
            </div>
        </div>

        {{-- Exams --}}
        <div class="bg-white rounded-xl border border-border overflow-hidden">
            <button @click="open = open === 'exams' ? null : 'exams'"
                    class="w-full flex items-center justify-between px-6 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.file-text')</svg>
                    </div>
                    <span class="font-semibold text-gray-800">Exams</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'exams' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'exams'" x-collapse class="border-t border-border px-6 pb-5 pt-4 space-y-3 text-sm text-text-muted">
                <p><span class="font-semibold text-gray-700">Create an exam:</span> Exams → New Exam. Set title, level, number of questions, duration (minutes), pass percentage, and optional schedule/expiry dates.</p>
                <p><span class="font-semibold text-gray-700">Questions are auto-assigned:</span> When a student starts the exam, questions are randomly selected from the approved question bank for their level.</p>
                <p><span class="font-semibold text-gray-700">View results:</span> Open any exam to see all student attempts, scores, and pass/fail status. Tab-switch counts are recorded for integrity monitoring.</p>
                <p><span class="font-semibold text-gray-700">Max attempts:</span> Set a maximum attempt limit on the exam. Leave blank for unlimited attempts.</p>
            </div>
        </div>

        {{-- Class Practice --}}
        <div class="bg-white rounded-xl border border-border overflow-hidden">
            <button @click="open = open === 'practice' ? null : 'practice'"
                    class="w-full flex items-center justify-between px-6 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.monitor')</svg>
                    </div>
                    <span class="font-semibold text-gray-800">Class Practice</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'practice' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'practice'" x-collapse class="border-t border-border px-6 pb-5 pt-4 space-y-3 text-sm text-text-muted">
                <p><span class="font-semibold text-gray-700">Set up a session:</span> Class Practice → New Session. Choose the level, time per step, number of questions, session length, and whether to play <em>Audio Dictation</em> automatically. A unique session code is generated.</p>
                <p><span class="font-semibold text-gray-700">Project to the class:</span> Open the session and use <strong>Project</strong> for the full-screen view. Use Next Question / Restart Question / End Practice from the top bar; the completion screen shows the score and progress.</p>
                <p><span class="font-semibold text-gray-700">View results:</span> After the session ends, scores and individual performance are available in the session detail.</p>
            </div>
        </div>

        {{-- Promotions --}}
        <div class="bg-white rounded-xl border border-border overflow-hidden">
            <button @click="open = open === 'promotions' ? null : 'promotions'"
                    class="w-full flex items-center justify-between px-6 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.trending-up')</svg>
                    </div>
                    <span class="font-semibold text-gray-800">Promotions</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'promotions' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'promotions'" x-collapse class="border-t border-border px-6 pb-5 pt-4 space-y-3 text-sm text-text-muted">
                <p><span class="font-semibold text-gray-700">Promote a student:</span> Promotions → select a student → choose the new level → confirm. The student's current level is updated immediately.</p>
                <p><span class="font-semibold text-gray-700">History:</span> Each promotion is logged with the date and the admin who approved it.</p>
            </div>
        </div>

        {{-- Certificates --}}
        <div class="bg-white rounded-xl border border-border overflow-hidden">
            <button @click="open = open === 'certs' ? null : 'certs'"
                    class="w-full flex items-center justify-between px-6 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-yellow-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.award')</svg>
                    </div>
                    <span class="font-semibold text-gray-800">Certificates</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'certs' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'certs'" x-collapse class="border-t border-border px-6 pb-5 pt-4 space-y-3 text-sm text-text-muted">
                <p><span class="font-semibold text-gray-700">Issue a certificate:</span> Certificates → New Certificate. Select the student, certificate type (level completion, competition, achievement), and issue date.</p>
                <p><span class="font-semibold text-gray-700">Download PDF:</span> Each certificate has a QR code for verification. Download as PDF for printing.</p>
                <p><span class="font-semibold text-gray-700">Student access:</span> Students can view and download their certificates from their portal.</p>
            </div>
        </div>

    </div>

    <div class="bg-fran rounded-xl p-5 text-center">
        <p class="text-white font-semibold text-sm">Need help with something not listed here?</p>
        <p class="text-blue-100 text-xs mt-1">Contact the Apex Brains support team or your administrator.</p>
    </div>

</div>
@endsection
