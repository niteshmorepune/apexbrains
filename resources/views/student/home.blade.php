@extends('layouts.student')
@section('title', 'Home')

@section('content')
<div class="p-4 space-y-4">

    {{-- Greeting --}}
    @php
        $levelColors = [
            1=>'#87CEEB', 2=>'#2ECC71', 3=>'#00BCD4', 4=>'#FFD54F', 5=>'#F5A623',
            6=>'#FF69B4', 7=>'#D42B2B', 8=>'#9C27B0', 9=>'#1A73E8', 10=>'#00897B',
            11=>'#FF6F00', 12=>'#AD1457', 13=>'#283593', 14=>'#212121',
        ];
        $lvlColor = $student?->currentLevel ? ($levelColors[$student->currentLevel->number] ?? '#2ECC71') : '#2ECC71';
    @endphp
    <div class="bg-stu rounded-2xl p-5 text-white">
        <p class="text-white/70 text-sm">
            @php
                $hour = now()->hour;
                echo $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
            @endphp
        </p>
        <p class="text-xl font-bold mt-0.5">{{ auth()->user()->name }}</p>
        @if($student?->franchise)
            <p class="text-white/60 text-xs mt-0.5">{{ $student->franchise->name }}, {{ $student->franchise->city }}</p>
        @endif
        @if($student?->currentLevel)
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs font-bold px-3 py-1 rounded-full"
                      style="background-color: {{ $lvlColor }}; color: white;">
                    L{{ $student->currentLevel->number }}
                </span>
                @if($student->currentLevel->title)
                    <span class="text-white/70 text-xs">{{ $student->currentLevel->title }}</span>
                @endif
            </div>
            {{-- Level progress bar --}}
            <div class="mt-3">
                @php
                    $totalTopics = count($student->currentLevel->learning_objectives ?? []);
                    $doneTopics  = $totalTopics > 0 ? (int) round($levelProgress / 100 * $totalTopics) : 0;
                @endphp
                <div class="flex items-center justify-between mb-1">
                    <span class="text-white/60 text-xs">{{ $doneTopics }} of {{ $totalTopics }} topics completed</span>
                    <span class="text-white/80 text-xs font-semibold">{{ $levelProgress }}%</span>
                </div>
                <div class="h-1.5 bg-white/20 rounded-full overflow-hidden">
                    <div class="h-full bg-white rounded-full transition-all"
                         style="width: {{ max(5, $levelProgress) }}%"></div>
                </div>
            </div>
        @endif
    </div>

    {{-- Daily Streak --}}
    @if($streak > 0)
        <div class="bg-white rounded-2xl border border-border p-4 flex items-center gap-3">
            <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-xl">🔥</span>
            </div>
            <div class="flex-1">
                <p class="text-sm font-bold text-orange-500">Fire {{ $streak }}-Day Streak</p>
                <p class="text-xs text-gray-400">Best: {{ $bestStreak }} days</p>
            </div>
        </div>
    @endif

    {{-- Quick Stats --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl border border-border p-4 text-center">
            <p class="text-2xl font-black text-stu">{{ $practiceThisWeek }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Practice this week</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4 text-center">
            <p class="text-2xl font-black text-fran">{{ $recentAttempts->where('is_passed', true)->count() }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Exams passed</p>
        </div>
    </div>

    {{-- Upcoming Exam --}}
    @if($upcomingExam)
        <div class="bg-white rounded-2xl border border-border p-4">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Upcoming Exam</p>
            <p class="font-semibold text-gray-800">{{ $upcomingExam->title }}</p>
            <div class="flex items-center justify-between mt-2">
                <p class="text-xs text-gray-500">{{ $upcomingExam->scheduled_at?->format('d M Y, H:i') }}</p>
                <a href="{{ route('student.exams.show', $upcomingExam) }}"
                   class="text-xs bg-fran text-white px-3 py-1.5 rounded-lg font-medium">
                    View →
                </a>
            </div>
        </div>
    @endif

    {{-- Quick Actions --}}
    <div class="grid grid-cols-2 gap-3">
        @foreach([
            ['route' => 'student.practice.index', 'emoji' => '🎯', 'label' => 'Practice', 'sub' => 'Tap to open', 'color' => 'stu'],
            ['route' => 'student.exams.index',    'emoji' => '📝', 'label' => 'My Exams',  'sub' => 'Tap to open', 'color' => 'fran'],
            ['route' => 'student.results',         'emoji' => '🏆', 'label' => 'Results',   'sub' => 'Tap to open', 'color' => 'logo-amber'],
            ['route' => 'student.certificates.index', 'emoji' => '🎓', 'label' => 'Certs', 'sub' => 'Tap to open', 'color' => 'stu'],
        ] as $action)
            <a href="{{ route($action['route']) }}"
               class="bg-white rounded-2xl border border-border p-4 flex flex-col items-center gap-2 hover:border-{{ $action['color'] }} transition-colors">
                <span class="text-2xl">{{ $action['emoji'] }}</span>
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-700">{{ $action['label'] }}</p>
                    <p class="text-xs text-gray-400">{{ $action['sub'] }}</p>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Recent Activity --}}
    @if($recentAttempts->isNotEmpty())
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <div class="px-4 py-3 border-b border-border flex items-center justify-between">
                <p class="text-sm font-semibold text-gray-700">Recent Activity</p>
                <a href="{{ route('student.results') }}" class="text-xs text-fran">View All</a>
            </div>
            <div class="divide-y divide-border">
                @foreach($recentAttempts as $attempt)
                    <div class="px-4 py-3 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                            {{ $attempt->is_passed ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-500' }}">
                            <span class="text-xs font-bold">{{ $attempt->is_passed ? '✓' : '✗' }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $attempt->exam?->title }}</p>
                            <p class="text-xs text-gray-400">{{ $attempt->submitted_at?->format('d M') }}</p>
                        </div>
                        <span class="text-sm font-bold {{ $attempt->is_passed ? 'text-green-600' : 'text-red-500' }}">
                            {{ number_format($attempt->percentage, 0) }}%
                        </span>
                    </div>
                @endforeach
            </div>
            <div class="px-4 py-3 border-t border-border">
                <a href="{{ route('student.results') }}" class="text-xs text-fran font-medium">View All →</a>
            </div>
        </div>
    @endif

</div>
@endsection
