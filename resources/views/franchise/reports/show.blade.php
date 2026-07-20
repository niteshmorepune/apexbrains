@extends('layouts.franchise')
@section('title', $student->full_name . ' — Report')
@section('page-title', $student->full_name . ' — Progress Report')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('page-actions')
    <a href="{{ route('franchise.reports.pdf', $student) }}"
       class="px-4 py-2 bg-white text-fran rounded-xl text-sm font-semibold hover:bg-blue-50">
        Export PDF
    </a>
    <a href="{{ route('franchise.reports.index') }}"
       class="px-4 py-2 border border-white text-white rounded-xl text-sm hover:bg-blue-600 transition-colors">← Reports</a>
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="col-span-2 space-y-4">

        {{-- Score history chart --}}
        <div class="bg-white rounded-2xl border border-border p-5">
            <h2 class="text-sm font-semibold text-fran mb-4">Exam Score Trend</h2>
            @if($attempts->count() >= 2)
                <div class="h-40">
                    <canvas id="scoreChart"></canvas>
                </div>
            @elseif($attempts->count() === 1)
                <p class="text-sm text-gray-400 text-center py-6">Only 1 exam taken — chart appears after 2+ exams.</p>
            @else
                <p class="text-sm text-gray-400 text-center py-6">No exams taken yet.</p>
            @endif
        </div>

        {{-- Topic checklist — curriculum level progression --}}
        @if($student->student_type === 'internal')
            @php $currentNum = $student->currentLevel?->number ?? 0; @endphp
            <div class="bg-white rounded-2xl border border-border p-5">
                <h2 class="text-sm font-semibold text-fran mb-1">Topic Checklist</h2>
                <p class="text-xs text-gray-400 mb-4">Curriculum progress across the abacus levels.</p>
                <ul class="space-y-1">
                    @foreach($levels as $lvl)
                        @php $state = $lvl->number < $currentNum ? 'done' : ($lvl->number === $currentNum ? 'current' : 'upcoming'); @endphp
                        <li class="flex items-center gap-3 py-1.5 px-2 rounded-lg {{ $state === 'current' ? 'bg-blue-50' : '' }}">
                            @if($state === 'done')
                                <span class="w-5 h-5 rounded-full bg-stu flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </span>
                            @elseif($state === 'current')
                                <span class="w-5 h-5 rounded-full border-2 border-fran flex items-center justify-center flex-shrink-0">
                                    <span class="w-2 h-2 rounded-full bg-fran"></span>
                                </span>
                            @else
                                <span class="w-5 h-5 rounded-full border-2 border-gray-200 flex-shrink-0"></span>
                            @endif
                            <span class="text-sm flex-1 {{ $state === 'upcoming' ? 'text-gray-400' : 'text-gray-800 font-medium' }}">
                                {{ $lvl->title }}
                            </span>
                            @if($state === 'done')
                                <span class="text-xs text-stu">Completed</span>
                            @elseif($state === 'current')
                                <span class="text-xs text-fran font-semibold">In Progress</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
                @if($student->currentLevel && filled($student->currentLevel->learning_objectives))
                    <div class="mt-4 pt-4 border-t border-border">
                        <p class="text-xs font-semibold text-gray-600 mb-2">Focus areas — {{ $student->currentLevel->title }}</p>
                        <ul class="space-y-1">
                            @foreach($student->currentLevel->learning_objectives as $obj)
                                <li class="flex items-start gap-2 text-xs text-gray-600"><span class="text-fran mt-0.5">•</span><span>{{ $obj }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        {{-- Radar / Performance breakdown chart --}}
        <div class="bg-white rounded-2xl border border-border p-5">
            <h2 class="text-sm font-semibold text-fran mb-4">Performance Breakdown</h2>
            @if($attempts->count() > 0)
                <div class="h-52 flex items-center justify-center">
                    <canvas id="radarChart"></canvas>
                </div>
            @else
                <p class="text-sm text-gray-400 text-center py-6">No data available yet.</p>
            @endif
        </div>

        {{-- Attempt table --}}
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border">
                <h2 class="text-sm font-semibold text-fran">All Exam Attempts</h2>
            </div>
            <div class="overflow-x-auto"><table class="w-full min-w-[640px] text-sm">
                <thead>
                    <tr class="bg-fran">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-white">Exam</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-white">Date</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-white">Score</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-white">Result</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($attempts as $attempt)
                        <tr class="hover:bg-bg-light">
                            <td class="px-5 py-3 font-medium text-gray-800">{{ $attempt->exam?->title ?? 'Exam #' . $attempt->exam_id }}</td>
                            <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $attempt->submitted_at?->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-right font-bold
                                {{ $attempt->percentage >= 75 ? 'text-stu' : ($attempt->percentage >= 50 ? 'text-logo-amber' : 'text-red-500') }}">
                                {{ number_format($attempt->percentage, 1) }}%
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($attempt->is_passed)
                                    <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full">Passed</span>
                                @else
                                    <span class="text-xs bg-red-50 text-red-600 px-2 py-0.5 rounded-full">Failed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">No exam attempts yet.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-5">
            <h3 class="text-sm font-bold text-fran mb-3">Student Info</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Level</dt><dd class="font-medium">{{ $student->currentLevel?->title ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Enrolled</dt><dd>{{ $student->enrollment_date?->format('d M Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Total Exams</dt><dd class="font-bold text-fran">{{ $attempts->count() }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Avg Score</dt><dd class="font-bold {{ $attempts->avg('percentage') >= 75 ? 'text-stu' : 'text-logo-amber' }}">{{ $attempts->count() ? number_format($attempts->avg('percentage'), 1) . '%' : '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Passed</dt><dd class="text-stu font-medium">{{ $attempts->where('is_passed', true)->count() }}</dd></div>
            </dl>
        </div>
        <a href="{{ route('franchise.certificates.index') }}"
           class="block text-center py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
            Generate Certificate
        </a>
    </div>
</div>

@push('scripts')
@if($attempts->count() >= 2)
<script>
const chartData = @json($chartData);
new Chart(document.getElementById('scoreChart'), {
    type: 'line',
    data: {
        labels: chartData.map(d => d.label),
        datasets: [{
            data: chartData.map(d => d.score),
            borderColor: '#1A73E8',
            backgroundColor: 'rgba(26,115,232,0.08)',
            borderWidth: 2, fill: true, tension: 0.4, pointRadius: 4, pointBackgroundColor: '#1A73E8'
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { min: 0, max: 100, ticks: { callback: v => v + '%', font: { size: 10 } } },
            x: { ticks: { font: { size: 10 } } }
        }
    }
});
</script>
@endif
@if($attempts->count() > 0)
<script>
const radarData = @json($radarData);
new Chart(document.getElementById('radarChart'), {
    type: 'radar',
    data: {
        labels: radarData.labels,
        datasets: [{
            data: radarData.values,
            borderColor: '#1A73E8',
            backgroundColor: 'rgba(26,115,232,0.15)',
            borderWidth: 2,
            pointBackgroundColor: '#1A73E8',
            pointRadius: 4
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            r: {
                min: 0, max: 100,
                ticks: { stepSize: 25, font: { size: 9 }, callback: v => v + '%' },
                pointLabels: { font: { size: 10 } }
            }
        }
    }
});
</script>
@endif
@endpush

@endsection
