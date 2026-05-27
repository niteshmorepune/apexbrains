@extends('layouts.admin')
@section('title', $franchise->name)
@section('page-title', $franchise->name)

@section('page-actions')
    @if($franchise->status === 'pending')
        <form method="POST" action="{{ route('admin.franchises.approve', $franchise) }}" class="inline">
            @csrf
            <button class="px-4 py-2 bg-stu text-white text-sm font-semibold rounded-xl hover:bg-stu-dark transition-colors">
                Approve
            </button>
        </form>
        <form method="POST" action="{{ route('admin.franchises.reject', $franchise) }}" class="inline"
              x-data="{}" @submit.prevent="
                  const reason = prompt('Rejection reason (optional):');
                  document.getElementById('reject-reason-{{ $franchise->id }}').value = reason ?? '';
                  $el.submit()
              ">
            @csrf
            <input type="hidden" id="reject-reason-{{ $franchise->id }}" name="reason">
            <button type="submit"
                    class="px-4 py-2 border border-red-300 text-red-500 text-sm font-semibold rounded-xl hover:bg-red-50 transition-colors">
                Reject
            </button>
        </form>
    @elseif($franchise->status === 'active')
        <form method="POST" action="{{ route('admin.franchises.suspend', $franchise) }}" class="inline">
            @csrf
            <button class="px-4 py-2 border border-red-300 text-red-500 text-sm font-semibold rounded-xl hover:bg-red-50 transition-colors">
                Suspend
            </button>
        </form>
    @elseif($franchise->status === 'suspended')
        <form method="POST" action="{{ route('admin.franchises.approve', $franchise) }}" class="inline">
            @csrf
            <button class="px-4 py-2 bg-stu text-white text-sm font-semibold rounded-xl hover:bg-stu-dark transition-colors">
                Reactivate
            </button>
        </form>
    @endif
    <a href="{{ route('admin.franchises.edit', $franchise) }}"
       class="px-4 py-2 border border-border text-gray-600 text-sm font-semibold rounded-xl hover:bg-bg-light transition-colors">
        Edit
    </a>
@endsection

@section('content')

{{-- Franchise header card --}}
<div class="bg-white rounded-2xl border border-border p-5 mb-4">
    <div class="flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <div class="w-10 h-10 rounded-xl bg-fran flex items-center justify-center text-white font-bold text-sm">
                    {{ strtoupper(substr($franchise->name, 0, 2)) }}
                </div>
                <div>
                    <h2 class="font-bold text-admin text-base">{{ $franchise->name }}</h2>
                    <p class="text-xs text-gray-400">{{ $franchise->franchise_code }}</p>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">
                Owner: {{ $franchise->owner_name }} | {{ $franchise->city }}, {{ $franchise->state }} | {{ $franchise->phone }}
            </p>
            @if($franchise->agreed_at)
                <p class="text-xs text-gray-400 mt-1">
                    Agreement: {{ $franchise->agreed_at->format('d M Y') }}
                </p>
            @endif
        </div>
        <x-status-badge :status="$franchise->status" />
    </div>
</div>

{{-- Tab navigation --}}
<div class="flex gap-1 mb-4 bg-white rounded-2xl border border-border p-1 w-fit">
    @foreach(['overview' => 'Overview', 'students' => "Students ({$franchise->students_count})", 'revenue' => 'Revenue', 'documents' => 'Documents'] as $tab => $label)
        <button onclick="showTab('{{ $tab }}')" id="tab-{{ $tab }}"
                class="px-4 py-2 rounded-xl text-sm font-medium transition-colors tab-btn
                       {{ $tab === 'overview' ? 'bg-fran text-white' : 'text-gray-500 hover:text-gray-700' }}">
            {{ $label }}
        </button>
    @endforeach
</div>

{{-- Overview tab --}}
<div id="content-overview">
    <div class="grid grid-cols-4 gap-4 mb-4">
        <div class="bg-white rounded-2xl border border-border p-4">
            <p class="text-xs text-gray-500 mb-1">Total Students</p>
            <p class="text-2xl font-bold text-fran">{{ number_format($franchise->students_count) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4">
            <p class="text-xs text-gray-500 mb-1">Active Batches</p>
            <p class="text-2xl font-bold text-admin">{{ number_format($franchise->batches_count) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4">
            <p class="text-xs text-gray-500 mb-1">Commission Rate</p>
            <p class="text-2xl font-bold text-logo-amber">{{ $franchise->commission_rate }}%</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4">
            <p class="text-xs text-gray-500 mb-1">Fee / Student</p>
            <p class="text-2xl font-bold text-stu">₹{{ number_format($franchise->fee_per_student) }}</p>
        </div>
    </div>

    {{-- Info + Recent Activity --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border border-border p-5">
            <h3 class="text-sm font-semibold text-admin mb-3">Contact & Business</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Email</span>
                    <span class="text-gray-700">{{ $franchise->email }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Phone</span>
                    <span class="text-gray-700">{{ $franchise->phone }}</span>
                </div>
                @if($franchise->whatsapp)
                    <div class="flex justify-between">
                        <span class="text-gray-500">WhatsApp</span>
                        <span class="text-gray-700">{{ $franchise->whatsapp }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-500">Address</span>
                    <span class="text-gray-700 text-right max-w-xs">{{ $franchise->address }}</span>
                </div>
                @if($franchise->gst_number)
                    <div class="flex justify-between">
                        <span class="text-gray-500">GST</span>
                        <span class="text-gray-700 font-mono text-xs">{{ $franchise->gst_number }}</span>
                    </div>
                @endif
                @if($franchise->pan_number)
                    <div class="flex justify-between">
                        <span class="text-gray-500">PAN</span>
                        <span class="text-gray-700 font-mono text-xs">{{ $franchise->pan_number }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-border p-5">
            <h3 class="text-sm font-semibold text-admin mb-3">Recent Activity</h3>
            @forelse($recentActivity as $log)
                <div class="flex items-start gap-3 py-2 border-b border-border last:border-0">
                    <div class="w-1.5 h-1.5 rounded-full bg-fran mt-1.5 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-700">{{ $log->action }}</p>
                        <p class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400">No recent activity.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Students tab (hidden) --}}
<div id="content-students" class="hidden">
    <div class="bg-white rounded-2xl border border-border p-6 text-center text-gray-400">
        <p class="text-sm">Student list for this franchise.</p>
        <a href="{{ route('admin.franchises.index') }}" class="text-fran text-sm hover:underline mt-2 block">
            Manage from franchise panel →
        </a>
    </div>
</div>

{{-- Revenue tab (hidden) --}}
<div id="content-revenue" class="hidden">
    <div class="bg-white rounded-2xl border border-border p-6 text-center text-gray-400">
        <p class="text-sm">Revenue analytics for {{ $franchise->name }}.</p>
        <a href="{{ route('admin.revenue') }}" class="text-fran text-sm hover:underline mt-2 block">
            View full revenue report →
        </a>
    </div>
</div>

{{-- Documents tab (hidden) --}}
<div id="content-documents" class="hidden">
    <div class="bg-white rounded-2xl border border-border p-6 text-center text-gray-400">
        <p class="text-sm">Franchise documents (GST, PAN, Agreement, etc.)</p>
    </div>
</div>

@endsection

@push('scripts')
<script>
function showTab(tab) {
    ['overview','students','revenue','documents'].forEach(t => {
        document.getElementById('content-' + t).classList.toggle('hidden', t !== tab);
        const btn = document.getElementById('tab-' + t);
        btn.classList.toggle('bg-fran', t === tab);
        btn.classList.toggle('text-white', t === tab);
        btn.classList.toggle('text-gray-500', t !== tab);
    });
}
</script>
@endpush
