@extends('layouts.admin')
@section('title', 'System Audit Log')
@section('page-title', 'System Audit Log')

@section('page-actions')
    <a href="{{ route('admin.audit-log.export', request()->except('page')) }}"
       class="px-4 py-2 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
        Export CSV
    </a>
@endsection

@section('content')

{{-- Filters --}}
<div class="bg-white rounded-2xl border border-border p-4 mb-4">
    <form method="GET" action="{{ route('admin.audit-log') }}" class="flex items-center gap-3 flex-wrap">
        <div class="flex items-center gap-2 border border-border rounded-xl px-3 py-2">
            <input type="date" name="from" value="{{ request('from') }}"
                   class="text-sm border-none outline-none bg-transparent">
            <span class="text-gray-400">→</span>
            <input type="date" name="to" value="{{ request('to') }}"
                   class="text-sm border-none outline-none bg-transparent">
        </div>
        <select name="action" class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            <option value="all">All Actions</option>
            @foreach($actions as $action)
                <option value="{{ $action }}" @selected(request('action') === $action)>{{ str_replace('_', ' ', ucfirst($action)) }}</option>
            @endforeach
        </select>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
               class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran w-48">
        <button type="submit" class="px-4 py-2 bg-fran text-white rounded-xl text-sm font-semibold">Filter</button>
        @if(request('from') || request('to') || request('action') || request('search'))
            <a href="{{ route('admin.audit-log') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
        @endif
    </form>
</div>

{{-- Log table --}}
<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="px-5 py-4 border-b border-border flex items-center justify-between">
        <h2 class="text-sm font-semibold text-admin">Activity Log</h2>
        <span class="text-xs text-gray-400">{{ number_format($logs->total()) }} entries</span>
    </div>

    <div class="overflow-x-auto"><table class="w-full min-w-[640px] text-sm">
        <thead>
            <tr class="bg-admin">
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Timestamp</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-white">User</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-white">Action</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-white">Entity</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-white">Details</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-white">IP Address</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($logs as $log)
                <tr class="hover:bg-bg-light">
                    <td class="px-5 py-3 text-gray-500 text-xs whitespace-nowrap">
                        {{ $log->created_at->format('d M Y, H:i:s') }}
                    </td>
                    <td class="px-4 py-3">
                        @if($log->user)
                            <p class="font-medium text-admin text-xs">{{ $log->user->name }}</p>
                            <p class="text-xs text-gray-400">{{ $log->user->email }}</p>
                        @else
                            <span class="text-xs text-gray-400">System</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $actionColor = match(true) {
                                str_contains($log->action, 'created') || str_contains($log->action, 'approved') => 'bg-stu-light text-stu-dark',
                                str_contains($log->action, 'deleted') || str_contains($log->action, 'rejected') || str_contains($log->action, 'suspend') => 'bg-red-50 text-red-600',
                                str_contains($log->action, 'updated') || str_contains($log->action, 'calculated') => 'bg-blue-50 text-fran',
                                default => 'bg-bg-mid text-gray-600',
                            };
                        @endphp
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $actionColor }}">
                            {{ str_replace('_', ' ', $log->action) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600">
                        @if($log->entity_type)
                            {{ $log->entity_type }}
                            @if($log->entity_id)
                                <span class="text-gray-400">#{{ $log->entity_id }}</span>
                            @endif
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500 max-w-xs">
                        {{ $log->description ?? str_replace('_', ' ', ucfirst($log->action)) }}
                    </td>
                    <td class="px-4 py-3 text-xs font-mono text-gray-400">
                        {{ $log->ip_address ?? '—' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                        No audit log entries found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table></div>

    @if($logs->hasPages())
        <div class="px-5 py-4 border-t border-border flex items-center justify-between">
            <span class="text-xs text-gray-500">
                Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ number_format($logs->total()) }} entries
            </span>
            {{ $logs->links('pagination::tailwind') }}
        </div>
    @endif
</div>

@endsection
