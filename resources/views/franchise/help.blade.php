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
                <p><span class="font-semibold text-gray-700">Filter the list:</span> Use the Internal/External, level, and Active/Inactive/All Status pills to narrow the student list. Deactivating a student (via Edit → uncheck Active Student) keeps their record — switch the Status filter to Inactive or All to find and reactivate them.</p>
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
                <p><span class="font-semibold text-gray-700">Monthly fees roll forward automatically:</span> a student's first month fee is created at registration, and each time a monthly fee is fully paid the next month's fee is generated automatically — no manual setup needed.</p>
                <p><span class="font-semibold text-gray-700">Fee Reminders:</span> Fees → Fee Reminders lists outstanding fees with whole days overdue and a <em>Priority</em> badge. Send reminders per row via <strong>WhatsApp</strong>, <strong>SMS</strong>, or <strong>Call</strong>.</p>
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
                <p><span class="font-semibold text-gray-700">Create an exam:</span> Admin sets title, level, duration (minutes), pass percentage, and optional schedule/expiry dates, then uploads a question paper (CSV) for that level. Franchise can view exams here but they're authored centrally by Admin.</p>
                <p><span class="font-semibold text-gray-700">Questions come from the uploaded paper:</span> an exam isn't attemptable by students until Admin uploads its question paper — the total question count comes from that file, not a manual entry.</p>
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
                <p><span class="font-semibold text-gray-700">Set up a session:</span> Class Practice → New Session. Choose the level, then a <em>Category</em> and a <em>Type</em> within it (only the ones unlocked for that level appear), the number of questions (10 / 20 / 30), time per step, and whether to play <em>Audio Dictation</em> automatically.</p>
                <p><span class="font-semibold text-gray-700">Run the player:</span> Open a session → <strong>Project</strong> opens the flashcard player. Each question's numbers flash <em>one at a time</em> (abacus style); the speaker icon replays the audio and the pause icon holds the flow. Use <strong>Restart Question</strong> to replay, <strong>Next Question</strong> to advance, and <strong>End Practice</strong> to finish.</p>
                <p><span class="font-semibold text-gray-700">After completion:</span> a <em>Well Done</em> screen shows the questions covered and level. From there use <strong>Replay Same Set</strong> (same questions again), <strong>New Practice</strong> (fresh questions, same category/type/settings), or <strong>Return to Setup</strong>. (It is a teacher-led drill, so no per-student score is recorded.)</p>
                <p><span class="font-semibold text-gray-700">Filter:</span> use the level tabs on the Sessions list to view sessions for a specific level.</p>
                <p><span class="font-semibold text-gray-700">Delete a session:</span> use the <strong>Delete</strong> button on a session row (or <strong>Delete Session</strong> on its detail page) to permanently remove it, including its questions and any recorded result.</p>
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
                <p><span class="font-semibold text-gray-700">Generate a certificate:</span> Certificates → fill <strong>Select Student</strong>, <strong>Certificate Level</strong> (auto-fills to the student's current level), <strong>Issue Date</strong>, <strong>Certificate Series</strong>, and the <strong>Certificate Type</strong> pill (Level Completion / Merit Award / Excellence Award). The right panel shows a <em>live preview</em>.</p>
                <p><span class="font-semibold text-gray-700">Generate and Send:</span> creates the certificate and marks it <em>Sent</em>. <strong>Preview Certificate</strong> just updates the on-screen preview.</p>
                <p class="text-xs text-gray-400">Note: a <em>Level Completion</em> certificate can only be generated once the student has <strong>passed the level-up exam</strong> for that level. Merit and Excellence awards are not gated.</p>
                <p><span class="font-semibold text-gray-700">Recently Generated list:</span> each row shows a <strong>Status</strong> (Generated / Sent / Revoked) and a QR check, with actions: <strong>Download</strong> (branded PDF), <strong>WhatsApp</strong> (share link), <strong>Print</strong>, <strong>Mark Sent</strong> (for unsent ones), and <strong>Revoke</strong>.</p>
                <p><span class="font-semibold text-gray-700">PDF &amp; verification:</span> the PDF is a clean branded certificate with a QR code that links to a public verification page. Students can view and download their own certificates from their portal.</p>
                <p class="text-xs text-gray-400">Note: sending is manual — "Generate and Send"/"Mark Sent" record delivery and the WhatsApp action opens a share link; there is no automated email yet.</p>
            </div>
        </div>

    </div>

    <div class="bg-fran rounded-xl p-5 text-center">
        <p class="text-white font-semibold text-sm">Need help with something not listed here?</p>
        <p class="text-blue-100 text-xs mt-1">Contact the Apex Brains support team or your administrator.</p>
    </div>

</div>
@endsection
