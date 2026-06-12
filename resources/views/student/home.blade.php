@extends('layouts.student')
@section('title', 'Home')

@section('content')
@php
    $levelColors = [
        1=>'#87CEEB', 2=>'#2ECC71', 3=>'#00BCD4', 4=>'#FFD54F', 5=>'#F5A623',
        6=>'#FF69B4', 7=>'#D42B2B', 8=>'#9C27B0', 9=>'#1A73E8', 10=>'#00897B',
        11=>'#FF6F00', 12=>'#AD1457', 13=>'#283593', 14=>'#212121',
    ];
    $lvlNum   = $student?->currentLevel?->number;
    $lvlColor = $lvlNum ? ($levelColors[$lvlNum] ?? '#2ECC71') : '#2ECC71';
    $hour     = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $firstName = explode(' ', trim(auth()->user()->name))[0];
    $daysLeft = $upcomingExam?->scheduled_at ? max(0, (int) ceil(now()->diffInDays($upcomingExam->scheduled_at, false))) : null;
    $totalTopics = count($student?->currentLevel?->learning_objectives ?? []);
    $doneTopics  = $totalTopics > 0 ? (int) round($levelProgress / 100 * $totalTopics) : 0;
@endphp

<div class="pb-4">

    {{-- Header panel: light-blue background + soft decorative circles (Figma S31) --}}
    <div class="relative bg-[#D7E8FC] rounded-b-3xl overflow-hidden px-4 pt-5 pb-6">
    <div class="absolute -top-12 -right-12 w-48 h-48 rounded-full bg-white/30 pointer-events-none"></div>
    <div class="absolute top-7 -right-6 w-28 h-28 rounded-full bg-white/20 pointer-events-none"></div>

    {{-- Brand row + avatar --}}
    <div class="relative flex items-center justify-between">
        <div class="flex items-center gap-1.5">
            @if(!empty($appSettings['logo_path']))
                <img src="{{ Storage::url($appSettings['logo_path']) }}" alt="{{ $appSettings['app_name'] ?? 'Apex Brains' }}" class="h-8 w-auto">
            @else
                <span class="text-xl leading-none">🧮</span>
                <span class="text-base font-extrabold tracking-tight leading-none">
                    <span class="text-logo-red">A</span><span class="text-fran">p</span><span class="text-[#34A853]">e</span><span class="text-logo-amber">x</span><span class="text-logo-red"> B</span><span class="text-fran">r</span><span class="text-[#34A853]">a</span><span class="text-logo-amber">i</span><span class="text-logo-red">n</span><span class="text-fran">s</span>
                </span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('student.notifications.index') }}"
               class="w-9 h-9 rounded-full bg-white border border-border flex items-center justify-center text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </a>
            <a href="{{ route('student.profile') }}"
               class="w-9 h-9 rounded-full bg-stu text-white flex items-center justify-center text-sm font-bold">
                {{ strtoupper(substr($firstName, 0, 1)) . strtoupper(substr(explode(' ', trim(auth()->user()->name))[1] ?? '', 0, 1)) }}
            </a>
        </div>
    </div>

    {{-- Greeting --}}
    <div class="relative mt-3">
        <p class="text-sm text-gray-500">{{ $greeting }},</p>
        <h1 class="text-2xl font-black text-gray-900 -mt-0.5">{{ $firstName }}!</h1>
        <div class="flex items-center gap-2 mt-1.5">
            @if($lvlNum)
                <span class="text-[11px] font-bold text-white px-2.5 py-0.5 rounded-full" style="background-color: {{ $lvlColor }}">L{{ $lvlNum }}</span>
            @endif
            <p class="text-xs text-gray-500">
                {{ $lvlNum ? 'Level '.$lvlNum.' Student' : '' }}{{ $student?->franchise ? ' · '.$student->franchise->name : '' }}
            </p>
        </div>
    </div>
    </div>{{-- /header panel --}}

    {{-- Lower content --}}
    <div class="px-4 pt-4 space-y-4">

    {{-- Streak + Next Exam --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl border border-border p-4">
            <span class="text-2xl">🔥</span>
            <p class="text-sm font-bold text-gray-800 mt-1.5">{{ $streak > 0 ? $streak.'-Day Streak' : 'Start a streak' }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $streak > 0 ? 'Keep it up! Best: '.$bestStreak.'d' : 'Practice today 🎯' }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4">
            <span class="text-2xl">📅</span>
            <p class="text-sm font-bold text-gray-800 mt-1.5">Next Exam</p>
            @if($upcomingExam)
                <p class="text-xs text-gray-400 mt-0.5">
                    @if($daysLeft === null)
                        Available now
                    @elseif($daysLeft === 0)
                        Today
                    @else
                        {{ $daysLeft.' day'.($daysLeft === 1 ? '' : 's').' left' }}
                    @endif
                    · {{ \Illuminate\Support\Str::limit($upcomingExam->title, 18) }}
                </p>
            @else
                <p class="text-xs text-gray-400 mt-0.5">None scheduled</p>
            @endif
        </div>
    </div>

    {{-- Level Progress --}}
    @if($lvlNum)
        <div class="bg-white rounded-2xl border border-border p-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-bold text-gray-800">Level {{ $lvlNum }} Progress</p>
                <span class="text-sm font-bold text-stu">{{ $levelProgress }}%</span>
            </div>
            <div class="h-2 bg-bg-mid rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all" style="width: {{ max(4, $levelProgress) }}%; background-color: {{ $lvlColor }}"></div>
            </div>
            <div class="flex items-center justify-between mt-2">
                <span class="text-xs text-gray-400">{{ $doneTopics }} of {{ $totalTopics }} topics completed</span>
                <a href="{{ route('student.learning-path') }}" class="text-xs font-semibold text-fran">Continue →</a>
            </div>
        </div>
    @endif

    {{-- Quick Actions --}}
    <div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Quick Actions</p>
        <div class="grid grid-cols-2 gap-3">
            @foreach([
                ['route' => 'student.practice.index',     'emoji' => '🎯', 'label' => 'Practice',  'bg' => 'bg-stu-light'],
                ['route' => 'student.exams.index',        'emoji' => '📝', 'label' => 'My Exams',  'bg' => 'bg-fran-light'],
                ['route' => 'student.results',            'emoji' => '🏆', 'label' => 'Results',   'bg' => 'bg-amber-50'],
                ['route' => 'student.certificates.index', 'emoji' => '🎓', 'label' => 'Certificate', 'bg' => 'bg-pink-50'],
            ] as $action)
                <a href="{{ route($action['route']) }}" class="bg-white rounded-2xl border border-border p-4 flex items-center gap-3">
                    <span class="w-11 h-11 rounded-xl {{ $action['bg'] }} flex items-center justify-center text-xl flex-shrink-0">{{ $action['emoji'] }}</span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-800 truncate">{{ $action['label'] }}</p>
                        <p class="text-[11px] text-gray-400">Tap to open</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Recent Activity --}}
    @if($recentAttempts->isNotEmpty())
        <div>
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Recent Activity</p>
                <a href="{{ route('student.results') }}" class="text-xs font-semibold text-fran">View All</a>
            </div>
            <div class="bg-white rounded-2xl border border-border divide-y divide-border overflow-hidden">
                @foreach($recentAttempts as $attempt)
                    <div class="px-4 py-3 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0 {{ $attempt->is_passed ? 'bg-stu' : 'bg-red-400' }}"></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $attempt->exam?->title ?? 'Exam' }}</p>
                            <p class="text-xs text-gray-400">{{ $attempt->submitted_at?->diffForHumans() }}</p>
                        </div>
                        <span class="text-sm font-bold {{ $attempt->is_passed ? 'text-stu' : 'text-red-500' }}">{{ number_format($attempt->percentage, 0) }}%</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    </div>{{-- /lower content --}}
</div>
@endsection
