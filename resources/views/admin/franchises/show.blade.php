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
    @foreach(['overview' => 'Overview', 'students' => "Students ({$franchise->students_count})", 'revenue' => 'Revenue', 'documents' => 'Documents', 'settings' => 'Settings'] as $tab => $label)
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
            <p class="text-xs text-gray-500 mb-1">Monthly Revenue</p>
            <p class="text-2xl font-bold text-fran">₹{{ number_format($monthlyRevenue) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4">
            <p class="text-xs text-gray-500 mb-1">Avg Score</p>
            <p class="text-2xl font-bold text-logo-amber">{{ $avgScore ? $avgScore . '%' : '—' }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4">
            <p class="text-xs text-gray-500 mb-1">Active Students</p>
            <p class="text-2xl font-bold text-stu">{{ number_format($franchiseStudents->count()) }}</p>
        </div>
    </div>

    {{-- Charts row --}}
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div class="bg-white rounded-2xl border border-border p-5">
            <h3 class="text-sm font-semibold text-admin mb-4">Student Growth</h3>
            <canvas id="growthChart" height="140"></canvas>
        </div>
        <div class="bg-white rounded-2xl border border-border p-5">
            <h3 class="text-sm font-semibold text-admin mb-4">Level Distribution</h3>
            <canvas id="levelChart" height="140"></canvas>
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
                @if($franchise->city || $franchise->state)
                    <div class="flex justify-between">
                        <span class="text-gray-500">City / State</span>
                        <span class="text-gray-700">{{ implode(', ', array_filter([$franchise->city, $franchise->state])) ?: '—' }}</span>
                    </div>
                @endif
                @if($franchise->pincode)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Pincode</span>
                        <span class="text-gray-700 font-mono">{{ $franchise->pincode }}</span>
                    </div>
                @endif
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
    <div class="bg-white rounded-2xl border border-border overflow-hidden">
        <div class="px-5 py-4 border-b border-border flex items-center justify-between">
            <h3 class="text-sm font-semibold text-admin">Students — {{ $franchise->name }}</h3>
            <span class="text-xs text-gray-400">{{ $franchiseStudents->count() }} total</span>
        </div>
        @if($franchiseStudents->isEmpty())
            <div class="px-5 py-10 text-center text-gray-400 text-sm">No students enrolled yet.</div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-admin">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-white">Student</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-white">Code</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-white">Type</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-white">Level</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-white">Enrolled</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($franchiseStudents as $s)
                        <tr class="hover:bg-bg-light">
                            <td class="px-5 py-2.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-fran flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                        {{ strtoupper(substr($s->first_name, 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-800">{{ $s->full_name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-2.5 text-center font-mono text-xs text-gray-500">{{ $s->student_code }}</td>
                            <td class="px-4 py-2.5 text-center">
                                @if($s->student_type === 'internal')
                                    <span class="text-xs bg-fran-light text-fran px-2 py-0.5 rounded-full">Internal</span>
                                @else
                                    <span class="text-xs bg-yellow-50 text-yellow-700 px-2 py-0.5 rounded-full">External</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                @if($s->currentLevel)
                                    <span class="text-xs bg-bg-mid text-gray-600 px-2 py-0.5 rounded-full font-medium">L{{ $s->currentLevel->number }}</span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-center text-xs text-gray-500">
                                {{ $s->enrollment_date?->format('d M Y') ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- Revenue tab (hidden) --}}
<div id="content-revenue" class="hidden">
    <div class="grid grid-cols-3 gap-4 mb-4">
        <div class="bg-white rounded-2xl border border-border p-4">
            <p class="text-xs text-gray-500 mb-1">Monthly Revenue</p>
            <p class="text-2xl font-bold text-fran">₹{{ number_format($monthlyRevenue) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $franchise->students_count }} students × ₹{{ number_format($franchise->fee_per_student) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4">
            <p class="text-xs text-gray-500 mb-1">Commission Rate</p>
            <p class="text-2xl font-bold text-logo-amber">{{ $franchise->commission_rate }}%</p>
            <p class="text-xs text-gray-400 mt-1">Commission due: ₹{{ number_format($monthlyRevenue * $franchise->commission_rate / 100) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4">
            <p class="text-xs text-gray-500 mb-1">Net Revenue</p>
            <p class="text-2xl font-bold text-stu">₹{{ number_format($monthlyRevenue - ($monthlyRevenue * $franchise->commission_rate / 100)) }}</p>
            <p class="text-xs text-gray-400 mt-1">After commission deduction</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-admin">Revenue Trend</h3>
            <a href="{{ route('admin.revenue') }}" class="text-xs text-fran hover:underline">Full Revenue Report →</a>
        </div>
        <canvas id="revenueChart" height="100"></canvas>
    </div>
</div>

{{-- Documents tab (hidden) --}}
<div id="content-documents" class="hidden">
    <div class="bg-white rounded-2xl border border-border overflow-hidden">
        <div class="px-5 py-4 border-b border-border">
            <h3 class="text-sm font-semibold text-admin">Franchise Documents</h3>
            <p class="text-xs text-gray-400 mt-0.5">Upload verification documents for this franchise.</p>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.franchises.update', $franchise) }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-5">
                    @foreach([
                        'doc_gst'       => 'GST Certificate',
                        'doc_pan'       => 'PAN Card Copy',
                        'doc_aadhaar'   => 'Aadhaar Card',
                        'doc_address'   => 'Address Proof',
                        'doc_agreement' => 'Franchise Agreement',
                        'doc_bank'      => 'Bank Details / Cancelled Cheque',
                    ] as $field => $label)
                    <div class="border border-border rounded-xl p-4">
                        <label class="block text-xs font-medium text-gray-700 mb-2">{{ $label }}</label>
                        @if($franchise->$field ?? null)
                            <p class="text-xs text-stu mb-2">✓ Uploaded</p>
                        @else
                            <p class="text-xs text-gray-400 mb-2">Not yet uploaded</p>
                        @endif
                        <input type="file" name="{{ $field }}" accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-fran-light file:text-fran hover:file:bg-fran hover:file:text-white file:cursor-pointer">
                        <p class="text-xs text-gray-300 mt-1">PDF, JPG or PNG — max 5 MB</p>
                    </div>
                    @endforeach
                </div>
                <div class="mt-5 flex items-center gap-3">
                    <button type="submit"
                            class="px-5 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                        Save Documents
                    </button>
                    <a href="{{ route('admin.franchises.approve', $franchise) }}"
                       onclick="return confirm('Approve this franchise?')"
                       class="{{ $franchise->status === 'pending' ? '' : 'hidden' }} px-5 py-2.5 bg-stu text-white rounded-xl text-sm font-semibold hover:bg-stu-dark transition-colors">
                        Approve &amp; Activate
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Settings tab (hidden) --}}
<div id="content-settings" class="hidden">
    <div class="grid grid-cols-2 gap-5">
        <div class="bg-white rounded-2xl border border-border p-5">
            <h3 class="text-sm font-semibold text-admin mb-4">Franchise Settings</h3>
            <form method="POST" action="{{ route('admin.franchises.update', $franchise) }}" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Commission Rate (%)</label>
                    <input type="number" name="commission_rate" step="0.01" min="0" max="100"
                           value="{{ $franchise->commission_rate }}"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Fee per Student (₹)</label>
                    <input type="number" name="fee_per_student" step="0.01" min="0"
                           value="{{ $franchise->fee_per_student }}"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Franchise Status</label>
                    <select name="status" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        @foreach(['active' => 'Active', 'pending' => 'Pending', 'suspended' => 'Suspended'] as $val => $lbl)
                            <option value="{{ $val }}" {{ $franchise->status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                        class="w-full bg-fran text-white text-sm font-semibold py-2.5 rounded-xl hover:bg-fran-dark transition-colors">
                    Save Settings
                </button>
            </form>
        </div>
        <div class="bg-white rounded-2xl border border-border p-5">
            <h3 class="text-sm font-semibold text-admin mb-4">Danger Zone</h3>
            <div class="border border-red-200 rounded-xl p-4 bg-red-50">
                <p class="text-sm font-medium text-red-700 mb-1">Delete Franchise</p>
                <p class="text-xs text-red-500 mb-3">This action is irreversible. All students and records will be unlinked.</p>
                <form method="POST" action="{{ route('admin.franchises.destroy', $franchise) }}"
                      onsubmit="return confirm('Are you sure? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 bg-red-500 text-white text-xs font-semibold rounded-lg hover:bg-red-600 transition-colors">
                        Delete Franchise
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function showTab(tab) {
    ['overview','students','revenue','documents','settings'].forEach(t => {
        document.getElementById('content-' + t).classList.toggle('hidden', t !== tab);
        const btn = document.getElementById('tab-' + t);
        btn.classList.toggle('bg-fran', t === tab);
        btn.classList.toggle('text-white', t === tab);
        btn.classList.toggle('text-gray-500', t !== tab);
    });
}
@if(session('openTab'))
document.addEventListener('DOMContentLoaded', () => showTab('{{ session('openTab') }}'));
@endif

document.addEventListener('DOMContentLoaded', function () {
    const growthLabels = @json($growthLabels);
    const growthData   = @json($growthData);
    const levelLabels  = @json($levelDist->pluck('title'));
    const levelData    = @json($levelDist->pluck('cnt'));

    new Chart(document.getElementById('growthChart'), {
        type: 'line',
        data: {
            labels: growthLabels,
            datasets: [{ label: 'New Students', data: growthData,
                borderColor: '#1A73E8', backgroundColor: 'rgba(26,115,232,0.1)',
                tension: 0.4, fill: true, pointRadius: 4 }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    if (levelLabels.length) {
        new Chart(document.getElementById('levelChart'), {
            type: 'bar',
            data: {
                labels: levelLabels,
                datasets: [{ label: 'Students', data: levelData,
                    backgroundColor: '#1A73E8', borderRadius: 6 }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });
    }

    // Revenue chart (same growth data as proxy)
    const revCanvas = document.getElementById('revenueChart');
    if (revCanvas) {
        new Chart(revCanvas, {
            type: 'bar',
            data: {
                labels: growthLabels,
                datasets: [{ label: 'Revenue (₹)', data: growthData.map(d => d * {{ $franchise->fee_per_student }}),
                    backgroundColor: 'rgba(26,115,232,0.7)', borderRadius: 6 }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }
});
</script>
@endpush
