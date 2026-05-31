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
    studentId: '{{ $selectedStudent?->id ?? '' }}',
    studentName: '{{ $selectedStudent?->full_name ?? '' }}',
    studentCode: '{{ $selectedStudent?->student_code ?? '' }}',
    levelNum: '{{ $selectedStudent?->currentLevel?->number ?? '' }}',
    feeAmount: {{ $selectedStudent?->currentLevel?->fee_per_month ?? 0 }},
    paymentMode: 'cash',
    amount: {{ $selectedStudent?->currentLevel?->fee_per_month ?? 0 }},
    note: '',
    students: {{ $students->map(fn($s) => [
        'id'        => $s->id,
        'name'      => $s->full_name,
        'code'      => $s->student_code,
        'level'     => $s->currentLevel?->number,
        'fee'       => $s->currentLevel?->fee_per_month ?? 0,
        'due_since' => $s->fees->first()?->due_date?->format('d M Y') ?? 'No pending fee',
    ])->values()->toJson() }},
    get selectedStudentObj() {
        return this.students.find(s => s.id == this.studentId) || null;
    },
    selectStudent(id) {
        const s = this.students.find(st => st.id == id);
        if (s) {
            this.studentId   = s.id;
            this.studentName = s.name;
            this.studentCode = s.code;
            this.levelNum    = s.level;
            this.feeAmount   = s.fee;
            this.amount      = s.fee;
        }
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
                <select name="student_id" required x-model="studentId" @change="selectStudent($event.target.value)"
                        class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    <option value="">Search student name or ID...</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}" {{ ($selectedStudent?->id === $s->id) ? 'selected' : '' }}>
                            {{ $s->full_name }} — L{{ $s->currentLevel?->number ?? '?' }} ({{ $s->student_code }})
                        </option>
                    @endforeach
                </select>
                {{-- Student details line --}}
                <template x-if="selectedStudentObj">
                    <p class="text-xs text-fran mt-1.5 bg-blue-50 px-3 py-1.5 rounded-lg">
                        Student found: <span x-text="selectedStudentObj.name" class="font-semibold"></span>
                        — Monthly Fee: ₹<span x-text="Number(selectedStudentObj.fee).toLocaleString('en-IN')"></span>
                        | Due Since: <span x-text="selectedStudentObj.due_since"></span>
                    </p>
                </template>
            </div>

            {{-- Amount --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Payment Amount (₹) <span class="text-red-500">*</span></label>
                <input type="number" name="amount" :value="amount" x-model="amount" required min="1" step="0.01"
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
                <textarea name="note" x-model="note" rows="2" placeholder="Any additional notes..."
                          class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran resize-none"></textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" name="draft" value="1"
                        class="flex-1 py-2.5 border border-fran text-fran rounded-xl text-sm font-semibold hover:bg-fran-light transition-colors">
                    Save Draft
                </button>
                <button type="submit"
                        class="flex-1 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
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
                <div class="w-9 h-9 rounded-lg bg-logo-red flex items-center justify-center text-white font-black text-xs">AB</div>
                <div>
                    <p class="font-black text-admin text-sm">Apex Brains</p>
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
                    <span x-text="studentName || '—'" class="font-medium"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Level</span>
                    <span x-text="levelNum ? 'Level ' + levelNum : '—'"></span>
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
            <button type="button"
                    class="w-full py-2 bg-fran text-white rounded-xl text-sm font-medium">
                Download PDF
            </button>
            <button type="button"
                    class="w-full py-2 bg-stu text-white rounded-xl text-sm font-medium">
                Share WhatsApp
            </button>
            <button type="button" onclick="window.print()"
                    class="w-full py-2 border border-border text-gray-600 rounded-xl text-sm font-medium hover:bg-bg-light">
                Print Receipt
            </button>
        </div>
    </div>

</div>

@endsection
