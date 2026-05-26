@extends('layouts.admin')
@section('title', 'Commission Calculator')
@section('page-title', 'Commission Calculator')

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
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Override Commission Rate (%)</label>
            <input type="number" name="commission_rate" step="0.5" min="0" max="100" placeholder="Use per-franchise rate"
                   class="border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran w-52">
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

    <table class="w-full text-sm">
        <thead>
            <tr class="bg-admin">
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Franchise</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Gross Revenue</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Rate</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Commission Due</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Paid</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($franchises as $f)
                <tr class="hover:bg-bg-light">
                    <td class="px-5 py-3 font-medium text-admin">{{ $f->name }}</td>
                    <td class="px-4 py-3 text-right">₹{{ number_format($f->gross_revenue ?? 0) }}</td>
                    <td class="px-4 py-3 text-right text-gray-500">{{ $f->commission_rate }}%</td>
                    <td class="px-4 py-3 text-right font-semibold text-fran">
                        ₹{{ number_format($f->commission_due) }}
                    </td>
                    <td class="px-4 py-3 text-right text-stu">
                        ₹{{ number_format($f->commission_paid ?? 0) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if(($f->commission_paid ?? 0) >= $f->commission_due && $f->commission_due > 0)
                            <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full font-medium">Paid</span>
                        @elseif($f->gross_revenue > 0)
                            <span class="text-xs bg-yellow-50 text-yellow-700 px-2 py-0.5 rounded-full font-medium">Pending</span>
                        @else
                            <span class="text-xs bg-bg-mid text-gray-400 px-2 py-0.5 rounded-full">No Revenue</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center text-gray-400">
                        No active franchises. Click Calculate All to generate commissions.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

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
