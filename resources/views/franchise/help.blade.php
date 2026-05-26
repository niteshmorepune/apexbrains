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
                <p><span class="font-semibold text-gray-700">Add a student:</span> Students → New Student. Fill in name, date of birth, gender, and contact details. A login account is created automatically.</p>
                <p><span class="font-semibold text-gray-700">Bulk import:</span> Students → Import. Download the CSV template, fill it in, and upload. Students are created in bulk with auto-generated codes.</p>
                <p><span class="font-semibold text-gray-700">Edit / deactivate:</span> Click a student's name to open their profile. Use the Edit button to update details or toggle active status.</p>
                <p><span class="font-semibold text-gray-700">Student login:</span> Students log in at <code class="bg-gray-100 px-1 rounded">/student</code> using their registered email and password.</p>
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
                <p><span class="font-semibold text-gray-700">Fee tabs:</span> The Fees page shows <em>Due</em>, <em>Partial</em>, <em>Overdue</em>, and <em>Paid</em> tabs. Click any row to open the fee detail.</p>
                <p><span class="font-semibold text-gray-700">Record a payment:</span> Open a fee → click <em>Record Payment</em>. Enter the amount, payment mode (cash/UPI/card/cheque/bank transfer), transaction reference, and payment date. A receipt is generated automatically.</p>
                <p><span class="font-semibold text-gray-700">Partial payment:</span> Enter an amount less than the balance. The fee status changes to <em>Partial</em> automatically.</p>
                <p><span class="font-semibold text-gray-700">Receipts:</span> Each payment generates a printable receipt accessible from the payment history on the fee detail page.</p>
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
                <p><span class="font-semibold text-gray-700">Start a session:</span> Class Practice → New Session. Select a level and number of questions. A unique session PIN is generated.</p>
                <p><span class="font-semibold text-gray-700">Students join:</span> Students enter the PIN on their portal to join the live session and answer questions simultaneously.</p>
                <p><span class="font-semibold text-gray-700">View results:</span> After the session ends, scores and individual student performance are available in the session detail.</p>
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
