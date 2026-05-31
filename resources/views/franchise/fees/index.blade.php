@extends('layouts.franchise')
@section('title', 'Fee Management')
@section('page-title', 'Fee Collection — ' . \Carbon\Carbon::parse($month . '-01')->format('M Y'))

@section('page-actions')
    <a href="{{ route('franchise.fees.reminders') }}"
       class="px-4 py-2 border border-white text-white rounded-xl text-sm font-medium hover:bg-blue-600 transition-colors">
        Fee Reminders
    </a>
    <a href="{{ route('franchise.fees.record') }}"
       class="px-4 py-2 bg-white text-fran rounded-xl text-sm font-semibold hover:bg-blue-50 transition-colors">
        + Record
    </a>
@endsection

@section('content')

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Collected</p>
        <p class="text-2xl font-bold text-stu">₹{{ number_format($stats['collected']) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $stats['paid_count'] }} students paid</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Outstanding</p>
        <p class="text-2xl font-bold text-logo-amber">₹{{ number_format($stats['outstanding']) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $stats['pending_count'] }} students pending</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Overdue 30d+</p>
        <p class="text-2xl font-bold text-red-500">₹{{ number_format($stats['overdue']) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $stats['overdue_count'] }} students overdue</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Collection Rate</p>
        <p class="text-2xl font-bold text-fran">{{ $stats['collection_rate'] }}%</p>
        <p class="text-xs text-gray-400 mt-1">vs {{ $stats['prev_rate'] ?? '—' }}% last month</p>
    </div>
</div>

{{-- Month + Search filter --}}
<div class="bg-white rounded-2xl border border-border p-4 mb-4">
    <form method="GET" action="{{ route('franchise.fees.index') }}" class="flex items-center gap-3 flex-wrap">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="month" name="month" value="{{ $month }}"
               class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
        <select name="student_type" class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            <option value="">All Types</option>
            <option value="internal" @selected(request('student_type') === 'internal')>Internal</option>
            <option value="external" @selected(request('student_type') === 'external')>External</option>
        </select>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search student..."
               class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran flex-1 min-w-40">
        <button type="submit" class="px-4 py-2 bg-fran text-white rounded-xl text-sm font-semibold">Filter</button>
    </form>
</div>

<div class="grid grid-cols-[1fr_280px] gap-5 items-start">

<div>{{-- Main table column --}}

{{-- Fee table with tabs --}}
<div class="bg-white rounded-2xl border border-border overflow-hidden">
    {{-- Tabs --}}
    <div class="flex border-b border-border">
        @foreach(['all' => 'All', 'paid' => 'Paid', 'pending' => 'Due', 'partial' => 'Partial', 'overdue' => 'Overdue'] as $key => $label)
            <a href="{{ route('franchise.fees.index', array_merge(request()->except('tab', 'page'), ['tab' => $key])) }}"
               class="px-5 py-3 text-sm font-medium border-b-2 transition-colors
                      {{ $tab === $key ? 'border-fran text-fran' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
            </a>
        @endforeach
        @if($stats['pending_count'] > 0)
            <a href="{{ route('franchise.fees.index', ['month' => $month]) }}"
               class="ml-auto px-5 py-3 text-sm text-logo-amber font-medium hover:underline self-center">
                Send All Reminders ({{ $stats['pending_count'] }})
            </a>
        @endif
    </div>

    <div class="overflow-x-auto"><table class="w-full min-w-[640px] text-sm">
        <thead>
            <tr class="bg-fran">
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Student</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Type</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Level</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Amount</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Paid</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Due Date</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Status</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($fees as $fee)
                <tr class="hover:bg-bg-light">
                    <td class="px-5 py-3 font-medium text-gray-800">
                        <a href="{{ route('franchise.students.show', $fee->student) }}" class="hover:text-fran hover:underline">
                            {{ $fee->student?->full_name ?? '—' }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($fee->student_type === 'external')
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">External</span>
                        @else
                            <span class="text-xs bg-blue-50 text-fran px-2 py-0.5 rounded-full">Internal</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($fee->student?->currentLevel)
                            <span class="text-xs bg-blue-50 text-fran px-2 py-0.5 rounded-full">L{{ $fee->student->currentLevel->number }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-medium">₹{{ number_format($fee->amount) }}</td>
                    <td class="px-4 py-3 text-right text-stu">₹{{ number_format($fee->paid_amount ?? 0) }}</td>
                    <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $fee->due_date?->format('d M') }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ match($fee->status) {
                                'paid'    => 'bg-stu-light text-stu-dark',
                                'overdue' => 'bg-red-50 text-red-600',
                                'partial' => 'bg-yellow-100 text-yellow-700',
                                default   => 'bg-bg-mid text-gray-500',
                            } }}">
                            {{ ucfirst($fee->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            @if($fee->status !== 'paid')
                                <button onclick="openPaymentModal({{ $fee->id }}, {{ $fee->student?->full_name ? "'" . addslashes($fee->student->full_name) . "'" : "'—'" }}, {{ $fee->amount - ($fee->paid_amount ?? 0) }})"
                                        class="text-xs text-fran font-medium hover:underline">
                                    Record
                                </button>
                                <a href="{{ route('franchise.fees.reminder', $fee) }}"
                                   onclick="return confirm('Send reminder to parent?')"
                                   class="text-xs text-logo-amber hover:underline">Remind</a>
                            @else
                                <span class="text-xs text-gray-300">—</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-5 py-10 text-center text-gray-400">No fees for this month.</td>
                </tr>
            @endforelse
        </tbody>
    </table></div>

    @if($fees->hasPages())
        <div class="px-5 py-4 border-t border-border">
            {{ $fees->links('pagination::tailwind') }}
        </div>
    @endif
</div>

</div>{{-- end main table column --}}

{{-- Quick Record Payment Sidebar --}}
<div class="bg-white rounded-2xl border border-border p-5 sticky top-5">
    <h3 class="text-sm font-bold text-fran mb-1">Quick Record Payment</h3>
    <p class="text-xs text-gray-400 mb-4">Fast entry for walk-in payments</p>
    <form method="POST" action="{{ route('franchise.payments.store') }}" class="space-y-3">
        @csrf
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Student</label>
            <select name="fee_id" required class="w-full border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                <option value="">Select student...</option>
                @foreach($fees->filter(fn($f) => $f->status !== 'paid') as $f)
                    <option value="{{ $f->id }}">{{ $f->student?->full_name }} — ₹{{ number_format($f->amount - ($f->paid_amount ?? 0)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Amount (₹)</label>
            <input type="number" name="amount" step="0.01" required placeholder="0.00"
                   class="w-full border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Date</label>
            <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required
                   class="w-full border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Mode</label>
            <select name="payment_mode" required class="w-full border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                <option value="cash">Cash</option>
                <option value="upi">UPI / GPay</option>
                <option value="card">Card</option>
                <option value="cheque">Cheque</option>
            </select>
        </div>
        <button type="submit"
                class="w-full py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
            Record &amp; Receipt
        </button>
        <a href="{{ route('franchise.fees.record') }}"
           class="block text-center text-xs text-fran hover:underline mt-1">
            Full record form →
        </a>
    </form>
</div>

</div>{{-- end grid --}}

{{-- Quick Record Payment Modal --}}
<div id="paymentModal" class="fixed inset-0 bg-black/40 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
        <h3 class="text-sm font-bold text-fran mb-1">Record Payment</h3>
        <p id="modalStudentName" class="text-xs text-gray-500 mb-4"></p>
        <form method="POST" action="{{ route('franchise.payments.store') }}">
            @csrf
            <input type="hidden" name="fee_id" id="modalFeeId">
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Amount (₹)</label>
                    <input type="number" name="amount" id="modalAmount" step="0.01" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Payment Date</label>
                    <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Mode</label>
                    <select name="payment_mode" required class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI / GPay / PhonePe</option>
                        <option value="card">Card</option>
                        <option value="cheque">Cheque</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Transaction Ref (UPI/Cheque)</label>
                    <input type="text" name="transaction_reference" placeholder="Optional"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button type="button" onclick="closePaymentModal()"
                        class="flex-1 py-2.5 border border-border rounded-xl text-sm text-gray-600">Cancel</button>
                <button type="submit"
                        class="flex-1 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold">Record &amp; Generate Receipt</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openPaymentModal(feeId, studentName, dueAmount) {
    document.getElementById('modalFeeId').value = feeId;
    document.getElementById('modalStudentName').textContent = studentName + ' — Amount due: ₹' + dueAmount.toLocaleString('en-IN');
    document.getElementById('modalAmount').value = dueAmount;
    document.getElementById('paymentModal').classList.remove('hidden');
    document.getElementById('paymentModal').classList.add('flex');
}
function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
    document.getElementById('paymentModal').classList.remove('flex');
}
</script>
@endpush

@endsection
