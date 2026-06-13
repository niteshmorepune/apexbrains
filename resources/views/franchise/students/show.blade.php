@extends('layouts.franchise')
@section('title', $student->full_name)
@section('page-title', $student->full_name)

@section('page-actions')
    <a href="{{ route('franchise.students.edit', $student) }}"
       class="px-4 py-2 bg-white text-fran rounded-xl text-sm font-semibold hover:bg-blue-50">Edit</a>
    <a href="{{ route('franchise.students.index') }}"
       class="px-4 py-2 border border-white text-white rounded-xl text-sm hover:bg-blue-600 transition-colors">← Back</a>
@endsection

@section('content')

@php
    $attempts    = $student->examAttempts->where('status', 'submitted');
    $examCount   = $attempts->count();
    $avgScore    = $examCount ? $attempts->avg('percentage') : null;
    $passed      = $attempts->where('is_passed', true)->count();
    $outstanding = $student->fees->whereIn('status', ['pending', 'partial', 'overdue'])
        ->sum(fn($f) => max(0, (float) $f->amount - (float) $f->paid_amount));

    // Tabs vary by student type
    $tabs = ['overview' => 'Overview', 'fees' => 'Fees & Payments'];
    $tabs['practice'] = 'Practice Sessions';
    $tabs['comp-practice'] = 'Competition Practice';
    if ($student->student_type === 'external' || $student->competitionRegistrations->isNotEmpty()) {
        $tabs['comp-reg'] = 'Competition Registrations';
    }
    $tabs['certificates'] = 'Certificates';
    if ($student->student_type === 'internal') $tabs['progress'] = 'Progress';
@endphp

<div x-data="{ tab: 'overview' }">

    {{-- Header card --}}
    <div class="bg-white rounded-2xl border border-border p-6 flex items-start gap-4 mb-4">
        @if($student->photo)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($student->photo) }}"
                 alt="{{ $student->full_name }}"
                 class="w-14 h-14 rounded-2xl object-cover border border-border flex-shrink-0">
        @else
            <div class="w-14 h-14 rounded-2xl bg-fran flex items-center justify-center text-white text-xl font-bold flex-shrink-0">
                {{ strtoupper(substr($student->first_name, 0, 1)) }}
            </div>
        @endif
        <div class="flex-1">
            <h2 class="text-lg font-bold text-fran">{{ $student->full_name }}</h2>
            <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $student->student_code }}</p>
            <div class="flex items-center gap-3 mt-2 flex-wrap">
                @if($student->student_type === 'internal')
                    <span class="text-xs bg-fran text-white px-2 py-0.5 rounded-full font-semibold">Internal</span>
                @else
                    <span class="text-xs bg-yellow-500 text-white px-2 py-0.5 rounded-full font-semibold">External</span>
                @endif
                @if($student->currentLevel)
                    <span class="text-xs bg-blue-50 text-fran px-2 py-0.5 rounded-full font-medium">Level {{ $student->currentLevel->number }} — {{ $student->currentLevel->title }}</span>
                @endif
                <span class="text-xs capitalize text-gray-500">{{ $student->gender }}</span>
                <span class="text-xs {{ $student->is_active ? 'text-stu' : 'text-gray-400' }}">{{ $student->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main column: tabs + panels --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Tab bar --}}
            <div class="bg-white rounded-2xl border border-border p-1.5 flex gap-1 overflow-x-auto">
                @foreach($tabs as $key => $label)
                    <button type="button" @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'bg-fran text-white' : 'text-gray-600 hover:bg-bg-light'"
                            class="whitespace-nowrap px-4 py-2 rounded-xl text-sm font-medium transition-colors">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Overview --}}
            <div x-show="tab === 'overview'" x-cloak class="space-y-4">
                <div class="bg-white rounded-2xl border border-border p-6">
                    <h3 class="text-sm font-bold text-fran mb-4">Personal Details</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-xs text-gray-500">Date of Birth</dt><dd class="font-medium">{{ $student->date_of_birth?->format('d M Y') ?? '—' }}</dd></div>
                        <div><dt class="text-xs text-gray-500">Enrollment Date</dt><dd class="font-medium">{{ $student->enrollment_date?->format('d M Y') ?? '—' }}</dd></div>
                        <div><dt class="text-xs text-gray-500">City</dt><dd class="font-medium">{{ $student->city ?? '—' }}</dd></div>
                        <div><dt class="text-xs text-gray-500">Pincode</dt><dd class="font-medium">{{ $student->pincode ?? '—' }}</dd></div>
                        <div class="col-span-2"><dt class="text-xs text-gray-500">Address</dt><dd class="font-medium">{{ $student->address ?? '—' }}</dd></div>
                    </dl>
                </div>
                @if($student->parents->count())
                    <div class="bg-white rounded-2xl border border-border p-6">
                        <h3 class="text-sm font-bold text-fran mb-4">Parent / Guardian</h3>
                        @foreach($student->parents as $p)
                            <div class="flex items-center gap-4">
                                <div class="w-9 h-9 rounded-full bg-bg-mid flex items-center justify-center text-gray-600 text-sm font-bold">{{ strtoupper(substr($p->name, 0, 1)) }}</div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-800">{{ $p->name }} <span class="text-xs text-gray-400 capitalize">· {{ $p->relationship }}</span></p>
                                    <div class="flex gap-4 mt-1 text-xs text-gray-500">
                                        <span>{{ $p->phone }}</span>
                                        @if($p->whatsapp) <span>WA: {{ $p->whatsapp }}</span> @endif
                                        @if($p->email) <span>{{ $p->email }}</span> @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Fees & Payments --}}
            <div x-show="tab === 'fees'" x-cloak class="space-y-4">
                <div class="bg-white rounded-2xl border border-border overflow-hidden">
                    <div class="px-5 py-4 border-b border-border flex items-center justify-between">
                        <h3 class="text-sm font-bold text-fran">Fee Schedule</h3>
                        <a href="{{ route('franchise.fees.record', ['student_id' => $student->id]) }}" class="text-xs text-fran hover:underline">Record Payment</a>
                    </div>
                    @if($student->fees->isNotEmpty())
                        <div class="overflow-x-auto"><table class="w-full min-w-[560px] text-sm">
                            <thead>
                                <tr class="bg-fran">
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-white">Month</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-white">Type</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-white">Amount</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-white">Paid</th>
                                    <th class="text-center px-5 py-3 text-xs font-semibold text-white">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach($student->fees->sortByDesc('month') as $fee)
                                    <tr class="hover:bg-bg-light">
                                        <td class="px-5 py-3 text-xs text-gray-600">{{ $fee->month?->format('M Y') ?? '—' }}</td>
                                        <td class="px-4 py-3 text-xs capitalize text-gray-500">{{ str_replace('_', ' ', $fee->fee_type) }}</td>
                                        <td class="px-4 py-3 text-right text-gray-800">₹{{ number_format($fee->amount) }}</td>
                                        <td class="px-4 py-3 text-right text-gray-500">₹{{ number_format($fee->paid_amount) }}</td>
                                        <td class="px-5 py-3 text-center">
                                            <span class="text-xs capitalize px-2 py-0.5 rounded-full
                                                {{ $fee->status === 'paid' ? 'bg-stu-light text-stu-dark' : ($fee->status === 'overdue' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700') }}">{{ $fee->status }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table></div>
                    @else
                        <p class="px-5 py-6 text-sm text-gray-400">No fees recorded for this student yet.</p>
                    @endif
                </div>

                <div class="bg-white rounded-2xl border border-border p-6">
                    <h3 class="text-sm font-bold text-fran mb-4">Payment History</h3>
                    @if($student->payments->count())
                        <div class="divide-y divide-border">
                            @foreach($student->payments->sortByDesc('payment_date') as $pay)
                                <div class="flex items-center justify-between py-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">₹{{ number_format($pay->amount) }}</p>
                                        <p class="text-xs text-gray-400">{{ $pay->payment_date?->format('d M Y') }} · {{ $pay->payment_mode }}</p>
                                    </div>
                                    <a href="{{ route('franchise.payments.receipt', $pay) }}" class="text-xs font-mono text-fran hover:underline">{{ $pay->receipt_number }}</a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-400">No payments recorded yet.</p>
                    @endif
                </div>
            </div>

            {{-- Practice Sessions (all students — completed only) --}}
            <div x-show="tab === 'practice'" x-cloak>
                @php
                    $completedSessions = $student->practiceSessions->filter(fn($ps) => $ps->completed_at !== null)->sortByDesc('completed_at');
                    $totalSessions = $completedSessions->count();
                    $displaySessions = $completedSessions->take(50);
                @endphp
                <div class="bg-white rounded-2xl border border-border overflow-hidden">
                    <div class="px-5 py-4 border-b border-border flex items-center justify-between">
                        <h3 class="text-sm font-bold text-fran">Practice Sessions</h3>
                        @if($totalSessions > 0)
                            <span class="text-xs text-gray-400">{{ $totalSessions }} total</span>
                        @endif
                    </div>
                    @if($displaySessions->isNotEmpty())
                        <div class="overflow-x-auto"><table class="w-full min-w-[560px] text-sm">
                            <thead>
                                <tr class="bg-fran">
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-white">Date</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-white">Difficulty</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-white">Questions</th>
                                    <th class="text-right px-5 py-3 text-xs font-semibold text-white">Accuracy</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach($displaySessions as $ps)
                                    <tr class="hover:bg-bg-light">
                                        <td class="px-5 py-3 text-xs text-gray-600">{{ $ps->completed_at->format('d M Y') }}</td>
                                        <td class="px-4 py-3 text-center text-xs capitalize text-gray-600">{{ $ps->difficulty ?? 'Mixed' }}</td>
                                        <td class="px-4 py-3 text-center text-xs text-gray-600">{{ $ps->questions_correct }}/{{ $ps->total_questions }}</td>
                                        <td class="px-5 py-3 text-right font-bold {{ $ps->accuracy >= 75 ? 'text-stu' : 'text-logo-amber' }}">{{ number_format((float) $ps->accuracy, 0) }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table></div>
                        @if($totalSessions > 50)
                            <p class="px-5 py-3 text-xs text-gray-400 border-t border-border">Showing 50 most recent of {{ $totalSessions }} sessions.</p>
                        @endif
                    @else
                        <p class="px-5 py-6 text-sm text-gray-400">No completed practice sessions for this student yet.</p>
                    @endif
                </div>
            </div>

            {{-- Competition Practice (submitted only) --}}
            <div x-show="tab === 'comp-practice'" x-cloak>
                @php $submittedAttempts = $student->competitionPracticeAttempts->where('status', 'submitted')->sortByDesc('submitted_at'); @endphp
                <div class="bg-white rounded-2xl border border-border overflow-hidden">
                    <div class="px-5 py-4 border-b border-border"><h3 class="text-sm font-bold text-fran">Competition Practice Papers</h3></div>
                    @if($submittedAttempts->isNotEmpty())
                        <div class="overflow-x-auto"><table class="w-full min-w-[560px] text-sm">
                            <thead>
                                <tr class="bg-fran">
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-white">Paper</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-white">Date</th>
                                    <th class="text-right px-5 py-3 text-xs font-semibold text-white">Score</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach($submittedAttempts as $att)
                                    <tr class="hover:bg-bg-light">
                                        <td class="px-5 py-3 font-medium text-gray-800">{{ $att->paper?->title ?? 'Paper #' . $att->paper_id }}</td>
                                        <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $att->submitted_at->format('d M Y') }}</td>
                                        <td class="px-5 py-3 text-right font-bold {{ $att->percentage >= 75 ? 'text-stu' : ($att->percentage >= 50 ? 'text-logo-amber' : 'text-red-500') }}">{{ number_format((float) $att->percentage, 0) }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table></div>
                    @else
                        <p class="px-5 py-6 text-sm text-gray-400">No competition practice papers submitted by this student yet.</p>
                    @endif
                </div>
            </div>

            {{-- Competition Registrations --}}
            @if($student->student_type === 'external' || $student->competitionRegistrations->isNotEmpty())
                <div x-show="tab === 'comp-reg'" x-cloak>
                    <div class="bg-white rounded-2xl border border-border overflow-hidden">
                        <div class="px-5 py-4 border-b border-border"><h3 class="text-sm font-bold text-fran">Competition Registrations</h3></div>
                        @if($student->competitionRegistrations->isNotEmpty())
                            <div class="divide-y divide-border">
                                @foreach($student->competitionRegistrations->sortByDesc('registration_date') as $reg)
                                    <div class="px-5 py-3 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-800">{{ $reg->competition?->title ?? '—' }}</p>
                                            <p class="text-xs text-gray-400 mt-0.5">
                                                Registered {{ ($reg->registration_date ?? $reg->created_at)?->format('d M Y') }}
                                                @if($reg->competition?->start_date)
                                                    · Held {{ $reg->competition->start_date->format('d M Y') }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            @if($reg->payment_status === 'paid')
                                                <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full">Paid</span>
                                            @elseif($reg->payment_status === 'pending')
                                                <span class="text-xs bg-amber-50 text-amber-600 px-2 py-0.5 rounded-full">Payment Pending</span>
                                            @endif
                                            <span class="text-xs capitalize px-2 py-0.5 rounded-full {{ $reg->status === 'confirmed' ? 'bg-stu-light text-stu-dark' : 'bg-bg-mid text-gray-500' }}">{{ $reg->status ?? 'pending' }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="px-5 py-6 text-sm text-gray-400">No competition registrations yet.</p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Certificates --}}
            <div x-show="tab === 'certificates'" x-cloak>
                <div class="bg-white rounded-2xl border border-border overflow-hidden">
                    <div class="px-5 py-4 border-b border-border"><h3 class="text-sm font-bold text-fran">Certificates</h3></div>
                    @if($student->certificates->isNotEmpty())
                        <div class="divide-y divide-border">
                            @foreach($student->certificates->sortByDesc('issued_at') as $cert)
                                <div class="px-5 py-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800 capitalize">{{ str_replace('_', ' ', $cert->type) }}{{ $cert->level ? ' — Level ' . $cert->level->number : '' }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ $cert->certificate_number }} · {{ $cert->issued_at?->format('d M Y') }}</p>
                                    </div>
                                    @if($cert->is_revoked)
                                        <span class="text-xs bg-red-50 text-red-600 px-2 py-0.5 rounded-full">Revoked</span>
                                    @else
                                        <a href="{{ route('franchise.certificates.pdf', $cert) }}" class="text-xs text-fran hover:underline">Download PDF</a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="px-5 py-6 text-sm text-gray-400">No certificates issued to this student yet.</p>
                    @endif
                </div>
            </div>

            {{-- Progress (internal) --}}
            @if($student->student_type === 'internal')
                <div x-show="tab === 'progress'" x-cloak class="space-y-4">
                    <div class="bg-white rounded-2xl border border-border p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-fran">Exam Performance</h3>
                            <a href="{{ route('franchise.reports.show', $student) }}" class="text-xs text-fran hover:underline">Open full report →</a>
                        </div>
                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="bg-bg-light rounded-xl py-3"><p class="text-xl font-bold text-fran">{{ $examCount }}</p><p class="text-xs text-gray-500">Exams</p></div>
                            <div class="bg-bg-light rounded-xl py-3"><p class="text-xl font-bold {{ ($avgScore ?? 0) >= 75 ? 'text-stu' : 'text-logo-amber' }}">{{ $avgScore !== null ? number_format($avgScore, 0) . '%' : '—' }}</p><p class="text-xs text-gray-500">Avg Score</p></div>
                            <div class="bg-bg-light rounded-xl py-3"><p class="text-xl font-bold text-stu">{{ $passed }}</p><p class="text-xs text-gray-500">Passed</p></div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl border border-border overflow-hidden">
                        <div class="px-5 py-4 border-b border-border"><h3 class="text-sm font-bold text-fran">Recent Exam Attempts</h3></div>
                        @if($attempts->count())
                            <div class="overflow-x-auto"><table class="w-full min-w-[560px] text-sm">
                                <thead>
                                    <tr class="bg-fran">
                                        <th class="text-left px-5 py-3 text-xs font-semibold text-white">Exam</th>
                                        <th class="text-center px-4 py-3 text-xs font-semibold text-white">Date</th>
                                        <th class="text-right px-4 py-3 text-xs font-semibold text-white">Score</th>
                                        <th class="text-center px-5 py-3 text-xs font-semibold text-white">Result</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    @foreach($attempts->sortByDesc('submitted_at') as $a)
                                        <tr class="hover:bg-bg-light">
                                            <td class="px-5 py-3 font-medium text-gray-800">{{ $a->exam?->title ?? 'Exam #' . $a->exam_id }}</td>
                                            <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $a->submitted_at?->format('d M Y') }}</td>
                                            <td class="px-4 py-3 text-right font-bold {{ $a->percentage >= 75 ? 'text-stu' : ($a->percentage >= 50 ? 'text-logo-amber' : 'text-red-500') }}">{{ number_format((float) $a->percentage, 0) }}%</td>
                                            <td class="px-5 py-3 text-center">
                                                @if($a->is_passed)
                                                    <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full">Passed</span>
                                                @else
                                                    <span class="text-xs bg-red-50 text-red-600 px-2 py-0.5 rounded-full">Failed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table></div>
                        @else
                            <p class="px-5 py-6 text-sm text-gray-400">No exam attempts yet.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar: Actions + at-a-glance --}}
        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-border p-5" x-data="{ certOpen: false, promoteOpen: false }">
                <h3 class="text-sm font-bold text-fran mb-3">Actions</h3>
                <div class="space-y-2">
                    {{-- Record Payment --}}
                    <a href="{{ route('franchise.fees.record', ['student_id' => $student->id]) }}"
                       class="block text-center py-2 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                        Record Payment
                    </a>

                    {{-- Generate Certificate (inline form) --}}
                    <button type="button" @click="certOpen = !certOpen"
                            class="w-full text-center py-2 border border-fran text-fran rounded-xl text-sm font-medium hover:bg-fran-light transition-colors">
                        Generate Certificate
                    </button>
                    <form x-show="certOpen" x-cloak method="POST" action="{{ route('franchise.certificates.generate') }}" class="p-3 bg-bg-light rounded-xl space-y-2">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $student->id }}">
                        @if($student->student_type === 'external')
                            {{-- Participation certificate tied to a competition --}}
                            <input type="hidden" name="type" value="competition">
                            <select name="competition_id" required class="w-full border border-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                                <option value="">Select competition…</option>
                                @foreach($student->competitionRegistrations as $reg)
                                    @if($reg->competition)
                                        <option value="{{ $reg->competition->id }}">{{ $reg->competition->title }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @if($student->competitionRegistrations->whereNotNull('competition')->isEmpty())
                                <p class="text-xs text-amber-600">Register this student for a competition first.</p>
                            @endif
                        @else
                            <select name="type" required class="w-full border border-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                                <option value="level_completion">Level Completion</option>
                                <option value="merit">Merit</option>
                                <option value="excellence">Excellence</option>
                            </select>
                        @endif
                        <button type="submit" class="w-full py-2 bg-fran text-white rounded-lg text-sm font-semibold hover:bg-fran-dark">Generate</button>
                    </form>

                    {{-- Promote (internal, inline form) --}}
                    @if($student->student_type === 'internal')
                        <button type="button" @click="promoteOpen = !promoteOpen"
                                class="w-full text-center py-2 border border-border text-gray-600 rounded-xl text-sm font-medium hover:bg-bg-light transition-colors">
                            Promote Student
                        </button>
                        <form x-show="promoteOpen" x-cloak method="POST" action="{{ route('franchise.promotions.promote', $student) }}" class="p-3 bg-bg-light rounded-xl space-y-2">
                            @csrf
                            <select name="new_level_id" required class="w-full border border-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                                <option value="">Select new level…</option>
                                @php
                                    // Junior 4 (number 4) skips directly to Regular 3 (number 7).
                                    $suggestedNum = $student->currentLevel
                                        ? ($student->currentLevel->number === 4 ? 7 : $student->currentLevel->number + 1)
                                        : null;
                                @endphp
                                @foreach($levels as $lvl)
                                    <option value="{{ $lvl->id }}" @selected($lvl->number === $suggestedNum)>Level {{ $lvl->number }} — {{ $lvl->title }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="w-full py-2 bg-fran text-white rounded-lg text-sm font-semibold hover:bg-fran-dark">Promote</button>
                        </form>
                    @endif

                    {{-- Full progress report --}}
                    @if($student->student_type === 'internal')
                        <a href="{{ route('franchise.reports.show', $student) }}"
                           class="block text-center py-2 border border-border text-gray-600 rounded-xl text-sm font-medium hover:bg-bg-light transition-colors">
                            Full Progress Report
                        </a>
                    @endif
                </div>
            </div>

            {{-- At a glance --}}
            <div class="bg-white rounded-2xl border border-border p-5">
                <h3 class="text-sm font-bold text-fran mb-3">At a Glance</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Level</dt><dd class="font-medium">{{ $student->currentLevel ? 'Level ' . $student->currentLevel->number : '—' }}</dd></div>
                    @if($student->student_type === 'internal')
                        <div class="flex justify-between"><dt class="text-gray-500">Avg Score</dt><dd class="font-bold {{ ($avgScore ?? 0) >= 75 ? 'text-stu' : 'text-logo-amber' }}">{{ $avgScore !== null ? number_format($avgScore, 0) . '%' : '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Exams Taken</dt><dd class="font-bold text-fran">{{ $examCount }}</dd></div>
                    @else
                        <div class="flex justify-between"><dt class="text-gray-500">Competitions</dt><dd class="font-bold text-fran">{{ $student->competitionRegistrations->count() }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt class="text-gray-500">Fees Due</dt><dd class="font-bold {{ $outstanding > 0 ? 'text-red-500' : 'text-stu' }}">₹{{ number_format($outstanding) }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</div>

@endsection
