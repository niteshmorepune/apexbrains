@extends('layouts.franchise')
@section('title', 'Register Student')
@section('page-title', 'Register New Student')

@section('page-actions')
    <a href="{{ route('franchise.students.index') }}"
       class="px-4 py-2 border border-white text-white rounded-xl text-sm hover:bg-blue-600 transition-colors">
        ← Back
    </a>
@endsection

@section('content')

<div class="grid grid-cols-3 gap-6">
    <div class="col-span-2">
        <form method="POST" action="{{ route('franchise.students.store') }}"
              x-data="{ studentType: '{{ old('student_type', 'internal') }}' }">
            @csrf

            {{-- Student Type Selector --}}
            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-fran mb-3">Student Type</h2>
                <div class="flex gap-3">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="student_type" value="internal" x-model="studentType" class="sr-only peer"
                               {{ old('student_type', 'internal') === 'internal' ? 'checked' : '' }}>
                        <div class="rounded-xl border-2 p-4 transition-colors peer-checked:border-fran peer-checked:bg-blue-50 border-border">
                            <p class="font-semibold text-sm text-gray-700">Internal Student</p>
                            <p class="text-xs text-gray-400 mt-0.5">Enrolled in regular abacus classes. Has a level, schedule, and monthly fees.</p>
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="student_type" value="external" x-model="studentType" class="sr-only peer"
                               {{ old('student_type') === 'external' ? 'checked' : '' }}>
                        <div class="rounded-xl border-2 p-4 transition-colors peer-checked:border-fran peer-checked:bg-blue-50 border-border">
                            <p class="font-semibold text-sm text-gray-700">External Student</p>
                            <p class="text-xs text-gray-400 mt-0.5">Competition-only participant. No level or schedule. Can register for external competitions.</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Student Details --}}
            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-fran mb-4">Student Information</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('first_name') border-red-400 @enderror">
                        @error('first_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        @error('last_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Date of Birth <span class="text-red-500">*</span></label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Gender <span class="text-red-500">*</span></label>
                        <select name="gender" required class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="">Select</option>
                            <option value="male" @selected(old('gender') === 'male')>Male</option>
                            <option value="female" @selected(old('gender') === 'female')>Female</option>
                            <option value="other" @selected(old('gender') === 'other')>Other</option>
                        </select>
                    </div>

                    {{-- Internal-only fields --}}
                    <template x-if="studentType === 'internal'">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Starting Level <span class="text-red-500">*</span></label>
                            <select name="current_level_id" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                                <option value="">Select Level</option>
                                @foreach($levels as $level)
                                    <option value="{{ $level->id }}" @selected(old('current_level_id') == $level->id)>Level {{ $level->number }} — {{ $level->title }}</option>
                                @endforeach
                            </select>
                            @error('current_level_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </template>

                    {{-- External-only fields --}}
                    <template x-if="studentType === 'external'">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Competition (optional)</label>
                            <select name="competition_id" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                                <option value="">No competition selected</option>
                                @foreach($competitions as $comp)
                                    <option value="{{ $comp->id }}" @selected(old('competition_id') == $comp->id)>{{ $comp->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </template>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Enrollment Date <span class="text-red-500">*</span></label>
                        <input type="date" name="enrollment_date" value="{{ old('enrollment_date', now()->toDateString()) }}" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Login Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="student@example.com"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('email') border-red-400 @enderror">
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
                        <input type="text" name="address" value="{{ old('address') }}"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                        <input type="text" name="city" value="{{ old('city') }}"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Pincode</label>
                        <input type="text" name="pincode" value="{{ old('pincode') }}"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                </div>
            </div>

            {{-- Parent/Guardian --}}
            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-fran mb-4">Parent / Guardian</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Parent Name <span class="text-red-500">*</span></label>
                        <input type="text" name="parent_name" value="{{ old('parent_name') }}" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Mobile <span class="text-red-500">*</span></label>
                        <input type="text" name="parent_phone" value="{{ old('parent_phone') }}" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">WhatsApp</label>
                        <input type="text" name="parent_whatsapp" value="{{ old('parent_whatsapp') }}"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Parent Email</label>
                        <input type="email" name="parent_email" value="{{ old('parent_email') }}"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('franchise.students.index') }}"
                   class="px-5 py-2.5 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light">Cancel</a>
                <button type="submit"
                        class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark">
                    Register Student
                </button>
            </div>
        </form>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-5">
            <h3 class="text-sm font-bold text-fran mb-3">Bulk Import</h3>
            <p class="text-xs text-gray-500 mb-3">Register multiple students at once using a CSV file.</p>
            <a href="{{ route('franchise.students.import.template') }}"
               class="block text-center py-2 border border-fran text-fran rounded-xl text-sm font-medium hover:bg-fran hover:text-white transition-colors mb-2">
                Download Template
            </a>
            <form method="POST" action="{{ route('franchise.students.import') }}" enctype="multipart/form-data">
                @csrf
                <label class="block w-full text-center py-2 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light cursor-pointer transition-colors">
                    Upload CSV
                    <input type="file" name="csv_file" accept=".csv" class="hidden" onchange="this.form.submit()">
                </label>
            </form>
        </div>

        <div class="bg-blue-50 rounded-2xl border border-blue-100 p-5">
            <h3 class="text-sm font-bold text-fran mb-2">Required CSV Fields</h3>
            <ul class="text-xs text-gray-600 space-y-1">
                <li>• Name (full name)</li>
                <li>• DOB (YYYY-MM-DD)</li>
                <li>• Gender (male/female/other)</li>
                <li>• Parent Name</li>
                <li>• Mobile (10 digits)</li>
                <li>• Level (1–14)</li>
            </ul>
        </div>
    </div>
</div>

@endsection
