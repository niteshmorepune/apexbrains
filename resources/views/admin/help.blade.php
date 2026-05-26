@extends('layouts.admin')
@section('title', 'Help Guide')
@section('page-title', 'Help Guide')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Intro --}}
    <div class="bg-white rounded-xl border border-border p-6">
        <h2 class="text-lg font-bold text-admin mb-1">Apex Brains Admin — Quick Reference</h2>
        <p class="text-text-muted text-sm">Everything you need to manage the platform. Click any section to expand.</p>
    </div>

    {{-- Accordion sections --}}
    <div class="space-y-3" x-data="{ open: 'franchises' }">

        {{-- Franchises --}}
        <div class="bg-white rounded-xl border border-border overflow-hidden">
            <button @click="open = open === 'franchises' ? null : 'franchises'"
                    class="w-full flex items-center justify-between px-6 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-fran" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.building')</svg>
                    </div>
                    <span class="font-semibold text-admin">Franchise Management</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'franchises' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'franchises'" x-collapse class="border-t border-border px-6 pb-5 pt-4 space-y-3 text-sm text-text-muted">
                <p><span class="font-semibold text-admin">Add a franchise:</span> Go to Franchises → New Franchise. Fill in the owner name, email, phone, and franchise code. Set status to <em>Active</em> to grant portal access.</p>
                <p><span class="font-semibold text-admin">Approve / Suspend:</span> Open any franchise and use the Approve or Suspend button. Suspended franchises cannot log in.</p>
                <p><span class="font-semibold text-admin">Franchise code:</span> Used as the login identifier for franchise admins. Must be unique across all franchises.</p>
            </div>
        </div>

        {{-- Question Bank --}}
        <div class="bg-white rounded-xl border border-border overflow-hidden">
            <button @click="open = open === 'questions' ? null : 'questions'"
                    class="w-full flex items-center justify-between px-6 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.help-circle')</svg>
                    </div>
                    <span class="font-semibold text-admin">Question Bank</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'questions' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'questions'" x-collapse class="border-t border-border px-6 pb-5 pt-4 space-y-3 text-sm text-text-muted">
                <p><span class="font-semibold text-admin">Add questions manually:</span> Question Bank → New Question. Select a level, enter the question text, four options (A–D), and mark the correct answer.</p>
                <p><span class="font-semibold text-admin">Audio Question Generator:</span> Question Bank → Generate Audio Questions. Upload a PDF or enter text; the system generates MCQ questions automatically. Review and approve before they appear in exams.</p>
                <p><span class="font-semibold text-admin">Approve / Reject:</span> New questions are in <em>pending</em> status. Use the Approve or Reject action on each question. Only approved questions appear in student exams.</p>
                <p><span class="font-semibold text-admin">PDF Upload:</span> Upload scanned question papers. The OCR system extracts questions for review.</p>
            </div>
        </div>

        {{-- Curriculum --}}
        <div class="bg-white rounded-xl border border-border overflow-hidden">
            <button @click="open = open === 'curriculum' ? null : 'curriculum'"
                    class="w-full flex items-center justify-between px-6 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.layers')</svg>
                    </div>
                    <span class="font-semibold text-admin">Curriculum (Levels)</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'curriculum' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'curriculum'" x-collapse class="border-t border-border px-6 pb-5 pt-4 space-y-3 text-sm text-text-muted">
                <p><span class="font-semibold text-admin">Levels:</span> The system supports up to 14 levels. Each level has a number (1–14), a title, and an active/inactive toggle.</p>
                <p><span class="font-semibold text-admin">Assigning levels to students:</span> Done by franchise admins via the Promotions section in their panel. Admin sets up the levels; franchise manages student progression.</p>
            </div>
        </div>

        {{-- Competitions --}}
        <div class="bg-white rounded-xl border border-border overflow-hidden">
            <button @click="open = open === 'competitions' ? null : 'competitions'"
                    class="w-full flex items-center justify-between px-6 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-yellow-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.trophy')</svg>
                    </div>
                    <span class="font-semibold text-admin">Competitions</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'competitions' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'competitions'" x-collapse class="border-t border-border px-6 pb-5 pt-4 space-y-3 text-sm text-text-muted">
                <p><span class="font-semibold text-admin">Create a competition:</span> Competitions → New Competition. Set the title, date range, max participants per franchise, and registration fee.</p>
                <p><span class="font-semibold text-admin">Practice papers:</span> Competition Papers → New Paper. Add questions to each paper via the paper questions section. Papers are available to all students (internal and external) for practice.</p>
                <p><span class="font-semibold text-admin">Registrations:</span> Franchise admins register their students for competitions. You can view registrations from the competition detail page.</p>
            </div>
        </div>

        {{-- Finance --}}
        <div class="bg-white rounded-xl border border-border overflow-hidden">
            <button @click="open = open === 'finance' ? null : 'finance'"
                    class="w-full flex items-center justify-between px-6 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.bar-chart')</svg>
                    </div>
                    <span class="font-semibold text-admin">Finance & Commissions</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'finance' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'finance'" x-collapse class="border-t border-border px-6 pb-5 pt-4 space-y-3 text-sm text-text-muted">
                <p><span class="font-semibold text-admin">Revenue dashboard:</span> Finance shows total revenue, monthly trends, and per-franchise breakdowns. Filter by date range.</p>
                <p><span class="font-semibold text-admin">Commissions:</span> Go to Finance → Commissions. Calculate commissions for a given month and mark them as paid once transferred to the franchise owner.</p>
            </div>
        </div>

        {{-- Demo credentials --}}
        <div class="bg-white rounded-xl border border-border overflow-hidden">
            <button @click="open = open === 'demo' ? null : 'demo'"
                    class="w-full flex items-center justify-between px-6 py-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">@include('components.icons.user')</svg>
                    </div>
                    <span class="font-semibold text-admin">Demo Accounts</span>
                </div>
                <svg class="w-4 h-4 text-text-muted transition-transform" :class="open === 'demo' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === 'demo'" x-collapse class="border-t border-border px-6 pb-5 pt-4 text-sm text-text-muted">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-admin font-semibold border-b border-border"><th class="pb-2">Email</th><th class="pb-2">Role</th><th class="pb-2">Password</th></tr></thead>
                    <tbody class="divide-y divide-border">
                        <tr class="py-2"><td class="py-2">admin@apexbrains.in</td><td>Super Admin</td><td class="font-mono">password</td></tr>
                        <tr class="py-2"><td class="py-2">kothrud@apexbrains.in</td><td>Franchise Admin</td><td class="font-mono">password</td></tr>
                        <tr class="py-2"><td class="py-2">arjun@student.in</td><td>Internal Student</td><td class="font-mono">password</td></tr>
                        <tr class="py-2"><td class="py-2">external@test.in</td><td>External Student</td><td class="font-mono">password</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Support footer --}}
    <div class="bg-admin rounded-xl p-5 text-center">
        <p class="text-white font-semibold text-sm">Need further assistance?</p>
        <p class="text-gray-400 text-xs mt-1">Contact the development team or raise an issue on the project repository.</p>
    </div>

</div>
@endsection
