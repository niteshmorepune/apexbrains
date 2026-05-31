@extends('layouts.admin')
@section('title', 'Commission Calculator')
@section('page-title', 'Commission Calculator')

@section('page-actions')
    <a href="{{ route('admin.commissions.export-pdf', ['month' => $month]) }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
        </svg>
        Export PDF
    </a>
@endsection

@section('content')

{{-- Config panel --}}
<div class="bg-white rounded-2xl border border-border p-6 mb-6">
    <h2 class="text-sm font-bold text-admin mb-4">Global Fee Configuration</h2>
    <form method="POST" action="{{ route('admin.commissions.calculate') }}" class="flex flex-wrap items-end gap-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Month</label>
            <input type="month" name="month" value="{{ $month }}"
                   class="border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Fee per Student (₹)</label>
            <input type="number" name="fee_per_student" step="1" min="0" placeholder="Use per-franchise rate"
                   class="border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran w-44">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Override Commission Rate (%)</label>
            <input type="number" name="commission_rate" step="0.5" min="0" max="100" placeholder="Use per-franchise rate"
                   class="border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran w-44">
        </div>
        <button type="submit"
                class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
            Calculate All
        </button>
        <a href="{{ route('admin.commissions.index', ['month' => $month]) }}"
           class="px-5 py-2.5 border border-border text-gray-600 rounded-xl text-sm hover:bg-bg-light transition-colors">
            View Report
        </a>
    </form>
</div>

{{-- Report --}}
<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="px-5 py-4 border-b border-border flex items-center justify-between">
        <h2 class="text-sm font-semibold text-admin">
            Commission Report — {{ \Carbon\Carbon::parse($month . '-01')->format('M Y') }}
        </h2>
        <form method="GET" action="{{ route('admin.commissions.index') }}" class="flex items-center gap-2">
            <input type="month" name="month" value="{{ $month }}"
                   class="border border-border rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            <button class="px-3 py-1.5 bg-bg-mid text-gray-600 rounded-lg text-sm">Go</button>
        </form>
    </div>

    <div class="overflow-x-auto"><table class="w-full min-w-[640px] text-sm">
        <thead>
            <tr class="bg-admin">
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Franchise</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-white">City</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Students</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Fee/Student</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Gross Revenue</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Commission %</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Commission Due</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Status</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($franchises as $f)
                <tr class="hover:bg-bg-light">
                    <td class="px-5 py-3 font-medium text-admin">{{ $f->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $f->city }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ number_format($f->students_count ?? 0) }}</td>
                    <td class="px-4 py-3 text-right text-gray-500">₹{{ number_format($f->fee_per_student) }}</td>
                    <td class="px-4 py-3 text-right">₹{{ number_format($f->gross_revenue ?? 0) }}</td>
                    <td class="px-4 py-3 text-right text-gray-500">{{ $f->commission_rate }}%</td>
                    <td class="px-4 py-3 text-right font-semibold text-fran">
                        ₹{{ number_format($f->commission_due) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($f->commission_record?->status === 'paid')
                            <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full font-medium">Paid</span>
                            <span class="block text-[11px] text-gray-400 mt-0.5">₹{{ number_format($f->commission_paid) }}</span>
                        @elseif($f->commission_due > 0)
                            <span class="text-xs bg-yellow-50 text-yellow-700 px-2 py-0.5 rounded-full font-medium">Pending</span>
                        @else
                            <span class="text-xs bg-bg-mid text-gray-400 px-2 py-0.5 rounded-full">No Revenue</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            @if($f->commission_record && $f->commission_record->status !== 'paid')
                                <form method="POST"
                                      action="{{ route('admin.commissions.mark-paid', $f->commission_record) }}">
                                    @csrf
                                    <button type="submit"
                                            class="text-xs bg-stu text-white px-2.5 py-1 rounded-lg hover:bg-stu-dark transition-colors font-medium">
                                        Mark Paid
                                    </button>
                                </form>
                            @elseif($f->commission_record?->status === 'paid')
                                <span class="text-xs text-gray-400">✓ Paid</span>
                            @endif
                            <a href="{{ route('admin.commissions.export-pdf', ['month' => $month]) }}"
                               class="text-xs border border-border text-gray-500 px-2.5 py-1 rounded-lg hover:bg-bg-light transition-colors">
                                Download
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-5 py-8 text-center text-gray-400">
                        No active franchises. Click Calculate All to generate commissions.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table></div>

    {{-- Summary footer --}}
    @if($franchises->isNotEmpty())
        <div class="px-5 py-4 border-t border-border bg-admin-mid flex items-center justify-between">
            <span class="text-sm text-gray-300 font-medium">
                Total Gross Revenue: <span class="text-white font-bold">₹{{ number_format($totalGross) }}</span>
            </span>
            <span class="text-sm text-gray-300 font-medium">
                Total Commission Due: <span class="text-logo-amber font-bold">₹{{ number_format($totalCommission) }}</span>
            </span>
        </div>
    @endif
</div>

@endsection
