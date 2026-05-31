@extends('layouts.franchise')
@section('title', $student->full_name)
@section('page-title', $student->full_name)

@section('page-actions')
    <a href="{{ route('franchise.students.edit', $student) }}"
       class="px-4 py-2 bg-white text-fran rounded-xl text-sm font-semibold hover:bg-blue-50">Edit</a>
    <a href="{{ route('franchise.students.index') }}"
       class="px-4 py-2 border border-white text-white rounded-xl text-sm hover:bg-blue-600 transition-colors">← Back</a>
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="col-span-2 space-y-4">

        {{-- Header card --}}
        <div class="bg-white rounded-2xl border border-border p-6 flex items-start gap-4">
            <div class="w-14 h-14 rounded-2xl bg-fran flex items-center justify-center text-white text-xl font-bold flex-shrink-0">
                {{ strtoupper(substr($student->first_name, 0, 1)) }}
            </div>
            <div class="flex-1">
                <h2 class="text-lg font-bold text-fran">{{ $student->full_name }}</h2>
                <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $student->student_code }}</p>
                <div class="flex items-center gap-3 mt-2 flex-wrap">
                    @if($student->student_type === 'internal')
                        <span class="text-xs bg-fran text-white px-2 py-0.5 rounded-full font-semibold">Internal</span>
                    @else
                        <span class="text-xs bg-yellow-500 text-white px-2 py-0.5 rounded-full font-semibold">External</span>
                    @endif
                    @if($student->currentLevel)
                        <span class="text-xs bg-blue-50 text-fran px-2 py-0.5 rounded-full font-medium">Level {{ $student->currentLevel->number }} — {{ $student->currentLevel->title }}</span>
                    @endif
                    <span class="text-xs capitalize text-gray-500">{{ $student->gender }}</span>
                    <span class="text-xs {{ $student->is_active ? 'text-stu' : 'text-gray-400' }}">
                        {{ $student->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Details --}}
        <div class="bg-white rounded-2xl border border-border p-6">
            <h3 class="text-sm font-bold text-fran mb-4">Personal Details</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div><dt class="text-xs text-gray-500">Date of Birth</dt><dd class="font-medium">{{ $student->date_of_birth?->format('d M Y') ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Enrollment Date</dt><dd class="font-medium">{{ $student->enrollment_date?->format('d M Y') ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">City</dt><dd class="font-medium">{{ $student->city ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Pincode</dt><dd class="font-medium">{{ $student->pincode ?? '—' }}</dd></div>
                <div class="col-span-2"><dt class="text-xs text-gray-500">Address</dt><dd class="font-medium">{{ $student->address ?? '—' }}</dd></div>
            </dl>
        </div>

        {{-- Parent info --}}
        @if($student->parents->count())
            <div class="bg-white rounded-2xl border border-border p-6">
                <h3 class="text-sm font-bold text-fran mb-4">Parent / Guardian</h3>
                @foreach($student->parents as $p)
                    <div class="flex items-center gap-4">
                        <div class="w-9 h-9 rounded-full bg-bg-mid flex items-center justify-center text-gray-600 text-sm font-bold">
                            {{ strtoupper(substr($p->name, 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-800">{{ $p->name }}</p>
                            <div class="flex gap-4 mt-1 text-xs text-gray-500">
                                <span>{{ $p->phone }}</span>
                                @if($p->whatsapp) <span>WA: {{ $p->whatsapp }}</span> @endif
                                @if($p->email) <span>{{ $p->email }}</span> @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Competition registrations (external students) --}}
        @if($student->student_type === 'external')
            <div class="bg-white rounded-2xl border border-border overflow-hidden">
                <div class="px-5 py-4 border-b border-border">
                    <h3 class="text-sm font-bold text-fran">Competition Registrations</h3>
                </div>
                @if($student->competitionRegistrations->isNotEmpty())
                    <div class="divide-y divide-border">
                        @foreach($student->competitionRegistrations as $reg)
                            <div class="px-5 py-3 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $reg->competition?->title ?? '—' }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Registered {{ $reg->created_at?->format('d M Y') }}</p>
                                </div>
                                <span class="text-xs capitalize px-2 py-0.5 rounded-full
                                    {{ $reg->status === 'confirmed' ? 'bg-stu-light text-stu-dark' : 'bg-bg-mid text-gray-500' }}">
                                    {{ $reg->status ?? 'pending' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="px-5 py-6 text-sm text-gray-400">No competition registrations yet.</p>
                @endif
            </div>
        @endif

        {{-- Recent payments --}}
        @if($student->payments->count())
            <div class="bg-white rounded-2xl border border-border p-6">
                <h3 class="text-sm font-bold text-fran mb-4">Recent Payments</h3>
                <div class="divide-y divide-border">
                    @foreach($student->payments->take(5) as $pay)
                        <div class="flex items-center justify-between py-2">
                            <div>
                                <p class="text-sm font-medium text-gray-800">₹{{ number_format($pay->amount) }}</p>
                                <p class="text-xs text-gray-400">{{ $pay->payment_date?->format('d M Y') }} · {{ $pay->payment_mode }}</p>
                            </div>
                            <span class="text-xs font-mono text-gray-400">{{ $pay->receipt_number }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-5">
            <h3 class="text-sm font-bold text-fran mb-3">Quick Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('franchise.fees.index') }}"
                   class="block text-center py-2 border border-fran text-fran rounded-xl text-sm font-medium hover:bg-fran hover:text-white transition-colors">
                    View Fee Status
                </a>
                <a href="{{ route('franchise.reports.show', $student) }}"
                   class="block text-center py-2 border border-border text-gray-600 rounded-xl text-sm font-medium hover:bg-bg-light transition-colors">
                    Progress Report
                </a>
                <a href="{{ route('franchise.promotions.index') }}"
                   class="block text-center py-2 border border-border text-gray-600 rounded-xl text-sm font-medium hover:bg-bg-light transition-colors">
                    Promotions
                </a>
            </div>
        </div>

        @if($student->student_type === 'internal')
            <div class="bg-white rounded-2xl border border-border p-5">
                <h3 class="text-sm font-bold text-fran mb-3">Exam Stats</h3>
                <div class="text-center">
                    <p class="text-2xl font-bold text-fran">{{ $student->examAttempts->count() }}</p>
                    <p class="text-xs text-gray-500">Exams taken</p>
                </div>
                @if($student->examAttempts->count())
                    <p class="text-xs text-gray-500 text-center mt-2">
                        Avg: {{ number_format($student->examAttempts->avg('percentage'), 1) }}%
                    </p>
                @endif
            </div>
        @else
            <div class="bg-white rounded-2xl border border-border p-5">
                <h3 class="text-sm font-bold text-fran mb-3">Competition Stats</h3>
                <div class="text-center">
                    <p class="text-2xl font-bold text-yellow-500">{{ $student->competitionRegistrations->count() }}</p>
                    <p class="text-xs text-gray-500">Competitions registered</p>
                </div>
                @if($student->competitionRegistrations->where('status', 'confirmed')->count())
                    <p class="text-xs text-stu text-center mt-2">
                        {{ $student->competitionRegistrations->where('status', 'confirmed')->count() }} confirmed
                    </p>
                @endif
            </div>
        @endif
    </div>
</div>

@endsection
