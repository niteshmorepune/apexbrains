@extends('layouts.franchise')
@section('title', 'Promotion Review')
@section('page-title', 'Promotion Review')

@section('page-actions')
    @if($eligible->count())
        <div class="flex items-center gap-3">
            <span class="text-sm font-bold bg-logo-amber/10 text-logo-amber border border-logo-amber/30 px-3 py-1.5 rounded-full">
                {{ $eligible->count() }} Eligible
            </span>
            <form method="POST" action="{{ route('franchise.promotions.promote', 0) }}"
                  onsubmit="return confirm('Batch promote all {{ $eligible->count() }} eligible students?')">
                @csrf
                <input type="hidden" name="batch" value="1">
                <button type="submit"
                        class="px-5 py-2 bg-white text-fran border border-white rounded-xl text-sm font-semibold hover:bg-blue-50 transition-colors">
                    Batch Promote All ({{ $eligible->count() }})
                </button>
            </form>
        </div>
    @endif
@endsection

@section('content')

@if($eligible->count())
    <div class="bg-white rounded-2xl border border-border overflow-hidden">
        <div class="overflow-x-auto"><table class="w-full min-w-[640px] text-sm">
            <thead>
                <tr class="bg-fran">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-white">Student</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-white">Current Level</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-white">Exam Score</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-white">Pass Marks</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-white">Speed</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-white">Accuracy</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-white">Attempts</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @foreach($eligible as $student)
                    @php $nextLevel = $levels->firstWhere('number', $student->currentLevel->number + 1); @endphp
                    <tr class="hover:bg-bg-light">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-fran flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">{{ $student->full_name }}</p>
                                    <p class="text-xs text-gray-400">→ L{{ $student->currentLevel->number + 1 }} after promotion</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs bg-blue-50 text-fran px-2 py-0.5 rounded-full font-medium">L{{ $student->currentLevel->number }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold {{ $student->exam_score >= 80 ? 'text-stu' : 'text-logo-amber' }}">
                            {{ number_format($student->exam_score, 1) }}%
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500">80%</td>
                        <td class="px-4 py-3 text-right text-gray-600 text-xs">
                            @if($student->exam_speed)
                                {{ $student->exam_speed >= 60 ? floor($student->exam_speed/60).'m '.($student->exam_speed%60).'s' : $student->exam_speed.'s' }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($student->exam_accuracy, 1) }}%</td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ $student->exam_attempts }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table></div>
    </div>
@else
    <div class="bg-white rounded-2xl border border-border p-16 text-center text-gray-400">
        <div class="text-4xl mb-3">🎓</div>
        <p class="text-base font-medium text-gray-500 mb-1">No students eligible for promotion</p>
        <p class="text-sm">Students need to pass their current level exam (≥80%) to appear here.</p>
    </div>
@endif

@endsection
