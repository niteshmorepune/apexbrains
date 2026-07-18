@extends('layouts.franchise')
@section('title', 'Record Payment')
@section('page-title', 'Record Payment')

@section('breadcrumb')
    <a href="{{ route('franchise.fees.index') }}" class="text-white/70 hover:text-white">Fees</a>
    <span class="mx-1 text-white/40">/</span>
    <span>Record Payment</span>
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6" x-data="{
    students: {{ $students->map(fn($s) => [
        'id'    => $s->id,
        'name'  => $s->full_name,
        'code'  => $s->student_code,
        'type'  => $s->student_type,
        'level' => $s->currentLevel?->number,
        'fees'  => $s->fees->map(fn($f) => [
            'id'       => $f->id,
            'type'     => $f->fee_type,
            'due'      => round((float) $f->amount - (float) $f->paid_amount, 2),
            'due_date' => $f->due_date?->format('d M Y'),
            'label'    => $f->fee_type === 'competition_registration'
                          ? 'Competition Registration — ' . ($f->competitionRegistration?->competition?->title ?? $f->month?->format('M Y'))
                          : 'Monthly Fee — ' . $f->month?->format('M Y'),
        ])->values(),
    ])->values()->toJson() }},
    studentId: '{{ $selectedStudent?->id ?? '' }}',
    feeId: '',
    paymentMode: 'cash',
    amount: 0,
    note: '',
    get student() { return this.students.find(s => s.id == this.studentId) || null; },
    get fees() { return this.student ? this.student.fees : []; },
    get fee() { return this.fees.find(f => f.id == this.feeId) || null; },
    get typeLabel() { return this.student ? (this.student.type === 'internal' ? 'Internal' : 'External') : ''; },
    onStudentChange() {
        this.feeId = this.fees.length ? this.fees[0].id : '';
        this.onFeeChange();
    },
    onFeeChange() {
        this.amount = this.fee ? this.fee.due : 0;
    },
    init() {
        if (this.studentId) this.onStudentChange();
    }
}">

    {{-- Left: Payment form --}}
    <div class="bg-white rounded-2xl border border-border p-6">
        <h2 class="text-sm font-bold text-fran mb-4">Payment Details</h2>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('franchise.payments.store') }}" class="space-y-4">
            @csrf

            {{-- Student search --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Student <span class="text-red-500">*</span></label>
                <select name="student_id" required x-model="studentId" @change="onStudentChange()"
                        class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    <option value="">Search student name or ID...</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}" {{ ($selectedStudent?->id === $s->id) ? 'selected' : '' }}>
                            {{ $s->full_name }} ({{ $s->student_code }}) — {{ ucfirst($s->student_type) }}{{ $s->student_type === 'internal' ? ' · L' . ($s->currentLevel?->number ?? '?') : '' }}
                        </option>
                    @endforeach
                </select>
                {{-- Student details line --}}
                <template x-if="student">
                    <p class="text-xs mt-1.5 px-3 py-1.5 rounded-lg flex items-center gap-2"
                       :class="student.type === 'internal' ? 'bg-blue-50 text-fran' : 'bg-amber-50 text-amber-700'">
                        <span class="font-semibold px-1.5 py-0.5 rounded-full border text-[10px] uppercase"
                              :class="student.type === 'internal' ? 'border-green-400 text-green-600' : 'border-red-400 text-red-500'"
                              x-text="typeLabel"></span>
                        <span x-text="student.name" class="font-semibold"></span>
                        <span x-text="fees.length + ' pending fee' + (fees.length === 1 ? '' : 's')"></span>
                    </p>
                </template>
            </div>

            {{-- Fee to pay --}}
            <div x-show="student">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Fee to Pay <span class="text-red-500">*</span></label>
                <select name="fee_id" x-model="feeId" @change="onFeeChange()" required
                        class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran"
                        x-show="fees.length">
                    <template x-for="f in fees" :key="f.id">
                        <option :value="f.id"
                                x-text="f.label + ' — ₹' + Number(f.due).toLocaleString('en-IN') + ' due (by ' + f.due_date + ')'"></option>
                    </template>
                </select>
                <template x-if="fees.length === 0">
                    <p class="text-xs text-amber-700 bg-amber-50 px-3 py-1.5 rounded-lg">
                        No pending fees for this student — nothing to record.
                    </p>
                </template>
            </div>

            {{-- Amount --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Payment Amount (₹) <span class="text-red-500">*</span></label>
                <input type="number" name="amount" x-model="amount" required min="1" step="0.01"
                       class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            </div>

            {{-- Payment Date --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Payment Date <span class="text-red-500">*</span></label>
                <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required
                       class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            </div>

            {{-- Payment Mode --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Mode <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach(['cash' => 'Cash', 'upi' => 'UPI/GPay/PhonePe', 'card' => 'Card', 'cheque' => 'Cheque', 'bank_transfer' => 'Bank Transfer'] as $val => $lbl)
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_mode" value="{{ $val }}" x-model="paymentMode"
                                   {{ $val === 'cash' ? 'checked' : '' }} class="sr-only peer">
                            <span class="block text-center px-2 py-2 rounded-xl border text-xs font-medium transition-colors
                                         peer-checked:bg-fran peer-checked:text-white peer-checked:border-fran
                                         border-border text-gray-600 hover:border-fran">{{ $lbl }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- UPI fields --}}
            <div x-show="paymentMode === 'upi'" x-transition class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Transaction ID</label>
                    <input type="text" name="transaction_reference" placeholder="UTR/Transaction ID"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">UPI App</label>
                    <select name="upi_app" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        <option value="">Select app</option>
                        <option>GPay</option>
                        <option>PhonePe</option>
                        <option>Paytm</option>
                        <option>BHIM</option>
                        <option>Other UPI</option>
                    </select>
                </div>
            </div>

            {{-- Note --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Note (optional)</label>
                <textarea name="notes" x-model="note" rows="2" placeholder="Any additional notes..."
                          class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran resize-none"></textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="w-full py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                    Record and Generate Receipt
                </button>
            </div>
        </form>
    </div>

    {{-- Right: Live receipt preview --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <h3 class="text-sm font-bold text-fran mb-4">Receipt Preview</h3>

        <div class="border border-border rounded-xl p-5 text-sm">
            {{-- Header --}}
            <div class="flex items-center gap-2 mb-3 pb-3 border-b border-border">
                @php
                    $previewLogo = !empty($appSettings['logo_path'] ?? null)
                        ? \Illuminate\Support\Facades\Storage::url($appSettings['logo_path'])
                        : asset('images/apex-logo.png');
                @endphp
                <img src="{{ $previewLogo }}" alt="{{ $appSettings['app_name'] ?? 'Apex Brains' }}"
                     class="w-9 h-9 rounded-lg object-contain">
                <div>
                    <p class="font-black text-admin text-sm">{{ $appSettings['app_name'] ?? 'Apex Brains' }}</p>
                    <p class="text-xs text-gray-400">ISO 9001:2015</p>
                </div>
                <div class="ml-auto text-right">
                    <p class="text-xs font-bold text-gray-500 uppercase">Payment Receipt</p>
                </div>
            </div>
            <div class="space-y-1.5 text-xs mb-3">
                <div class="flex justify-between">
                    <span class="text-gray-400">Receipt No</span>
                    <span class="font-mono">Auto-generated</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Student</span>
                    <span x-text="student ? student.name : '—'" class="font-medium"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Type</span>
                    <span x-text="typeLabel || '—'"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Fee</span>
                    <span x-text="fee ? fee.label : '—'" class="text-right"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Amount</span>
                    <span class="font-bold text-fran" x-text="amount ? '₹' + Number(amount).toLocaleString('en-IN') : '—'"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Mode</span>
                    <span class="capitalize" x-text="paymentMode.replace('_', ' ')"></span>
                </div>
            </div>
            <div class="flex justify-between items-end pt-3 border-t border-border">
                <div>
                    <p class="text-xs text-gray-400">Received by: {{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-300 mt-1">Signature: ___________</p>
                </div>
                <div class="w-12 h-12 bg-bg-mid rounded flex items-center justify-center text-xs text-gray-400">QR</div>
            </div>
        </div>

        <div class="mt-4 space-y-2">
            <p class="text-xs text-gray-400 text-center">
                This is a live preview. Click <span class="font-semibold text-fran">Record and Generate Receipt</span> to
                save the payment — the official receipt with Download PDF, Share WhatsApp &amp; Print opens next.
            </p>
            <button type="button" disabled title="Available after the payment is recorded"
                    class="w-full py-2 bg-bg-mid text-gray-400 rounded-xl text-sm font-medium cursor-not-allowed">
                Download PDF
            </button>
            <button type="button" disabled title="Available after the payment is recorded"
                    class="w-full py-2 bg-bg-mid text-gray-400 rounded-xl text-sm font-medium cursor-not-allowed">
                Share WhatsApp
            </button>
            <button type="button" disabled title="Available after the payment is recorded"
                    class="w-full py-2 border border-border text-gray-400 rounded-xl text-sm font-medium cursor-not-allowed">
                Print Receipt
            </button>
        </div>
    </div>

</div>

@endsection
