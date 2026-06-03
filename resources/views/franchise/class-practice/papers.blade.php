@extends('layouts.franchise')
@section('title', 'Class Practice Papers')

@section('content')

<div>
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-[13px] text-gray-400 mb-1">
        <a href="{{ route('franchise.class-practice.index') }}" class="hover:text-gray-600">Franchises</a>
        <span>/</span>
        <span class="font-semibold text-gray-700">Class Practice</span>
    </nav>
    <h1 class="text-[26px] font-extrabold text-gray-900 mb-4">Class Practice</h1>

    {{-- Sessions / Papers tabs --}}
    <div class="inline-flex items-center gap-1 bg-bg-light border border-border rounded-full p-1 mb-5">
        <a href="{{ route('franchise.class-practice.index') }}"
           class="px-4 py-1.5 rounded-full text-sm font-semibold text-gray-500 hover:text-gray-700">
            Sessions
        </a>
        <a href="{{ route('franchise.class-practice.papers') }}"
           class="px-4 py-1.5 rounded-full text-sm font-semibold bg-white text-fran shadow-sm">
            Practice Papers
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-border shadow-sm overflow-hidden max-w-[1140px]">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead>
                    <tr class="bg-gray-800 text-white text-left">
                        <th class="px-5 py-3.5 font-semibold w-16 text-center">List</th>
                        <th class="px-5 py-3.5 font-semibold">Paper</th>
                        <th class="px-5 py-3.5 font-semibold">Type</th>
                        <th class="px-5 py-3.5 font-semibold text-center">Attempt</th>
                        <th class="px-5 py-3.5 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($papers as $i => $paper)
                        <tr class="{{ $i % 2 === 0 ? 'bg-bg-light/50' : 'bg-white' }} hover:bg-blue-50/40">
                            <td class="px-5 py-3.5 text-center text-gray-500">{{ $i + 1 }}</td>
                            <td class="px-5 py-3.5 font-medium text-gray-800">{{ $paper->title }}</td>
                            <td class="px-5 py-3.5 text-gray-600">Practice Test</td>
                            <td class="px-5 py-3.5 text-center">
                                <form method="POST" action="{{ route('franchise.class-practice.papers.attempt', $paper) }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center justify-center px-5 py-1.5 rounded-full border border-green-500 text-green-600 text-xs font-semibold hover:bg-green-50 transition-colors">
                                        Attempt
                                    </button>
                                </form>
                            </td>
                            <td class="px-5 py-3.5">
                                <a href="{{ route('franchise.class-practice.papers.answers', $paper) }}"
                                   class="text-fran font-medium underline underline-offset-2 hover:text-fran-dark">
                                    Download Answer Pdf
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-center text-gray-400">
                                <p class="text-base mb-1">No practice papers yet</p>
                                <p class="text-sm">Papers are generated from the approved question bank.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
