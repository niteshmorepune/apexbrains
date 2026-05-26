@extends('layouts.franchise')
@section('title', 'Students')
@section('page-title', 'Student List')

@section('page-actions')
    <a href="{{ route('franchise.students.import.template') }}"
       class="px-3 py-2 border border-white text-white rounded-xl text-xs font-medium hover:bg-blue-600 transition-colors">
        CSV Template
    </a>
    <a href="{{ route('franchise.students.create') }}"
       class="px-4 py-2 bg-white text-fran rounded-xl text-sm font-semibold hover:bg-blue-50 transition-colors">
        + Register Student
    </a>
@endsection

@section('content')

{{-- Filters --}}
<div class="bg-white rounded-2xl border border-border p-4 mb-4">
    <form method="GET" action="{{ route('franchise.students.index') }}" class="flex items-center gap-3 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or code..."
               class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran flex-1 min-w-48">
        <select name="level" class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            <option value="">All Levels</option>
            @foreach($levels as $level)
                <option value="{{ $level->id }}" @selected(request('level') == $level->id)>Level {{ $level->number }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-fran text-white rounded-xl text-sm font-semibold">Filter</button>
        @if(request('search') || request('level'))
            <a href="{{ route('franchise.students.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
        @endif

        {{-- CSV import --}}
        <form method="POST" action="{{ route('franchise.students.import') }}" enctype="multipart/form-data" class="flex items-center gap-2 ml-auto">
            @csrf
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="file" name="csv_file" accept=".csv" class="hidden" onchange="this.form.submit()">
                <span class="px-3 py-2 border border-border rounded-xl text-xs text-gray-600 hover:bg-bg-light transition-colors">
                    Bulk Import CSV
                </span>
            </label>
        </form>
    </form>
</div>

{{-- Student table --}}
<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="px-5 py-4 border-b border-border flex items-center justify-between">
        <h2 class="text-sm font-semibold text-fran">All Students</h2>
        <span class="text-xs text-gray-400">{{ $students->total() }} students</span>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-fran">
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Student</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Code</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Level</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Gender</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Enrolled</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($students as $s)
                <tr class="hover:bg-bg-light">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-fran flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($s->first_name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ $s->full_name }}</p>
                                <p class="text-xs text-gray-400">{{ $s->date_of_birth?->format('d M Y') }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center font-mono text-xs text-gray-500">{{ $s->student_code }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($s->currentLevel)
                            <span class="text-xs bg-blue-50 text-fran px-2 py-0.5 rounded-full font-medium">L{{ $s->currentLevel->number }}</span>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-gray-500 capitalize">{{ $s->gender }}</td>
                    <td class="px-4 py-3 text-center text-xs text-gray-500">
                        {{ $s->enrollment_date?->format('d M Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <a href="{{ route('franchise.students.show', $s) }}" class="text-xs text-fran hover:underline">View</a>
                            <a href="{{ route('franchise.students.edit', $s) }}" class="text-xs text-gray-500 hover:underline">Edit</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                        No students found. <a href="{{ route('franchise.students.create') }}" class="text-fran underline">Register your first student</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if($students->hasPages())
        <div class="px-5 py-4 border-t border-border flex items-center justify-between">
            <span class="text-xs text-gray-500">Showing {{ $students->firstItem() }}–{{ $students->lastItem() }} of {{ $students->total() }}</span>
            {{ $students->links('pagination::tailwind') }}
        </div>
    @endif
</div>

@endsection
