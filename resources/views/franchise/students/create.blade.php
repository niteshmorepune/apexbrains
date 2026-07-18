@extends('layouts.franchise')
@section('title', 'Register Student')
@section('page-title', 'Register New Student')

@section('breadcrumb')
    <a href="{{ route('franchise.students.index') }}" class="text-fran hover:underline">Students</a>
    <span class="mx-1 text-gray-400">/</span>
    <span>Register</span>
@endsection

@section('page-actions')
    <a href="{{ route('franchise.students.import.page') }}"
       class="px-4 py-2 border border-white text-white rounded-xl text-sm hover:bg-blue-600 transition-colors">
        Bulk Import
    </a>
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="col-span-2">
        <form method="POST" action="{{ route('franchise.students.store') }}" enctype="multipart/form-data"
              x-data="{ studentType: '{{ old('student_type', 'internal') }}', levelFee: 0, monthlyFee: '{{ old('monthly_fee') }}', feeManuallyEdited: {{ old('monthly_fee') ? 'true' : 'false' }} }">
            @csrf

            {{-- Validation error summary --}}
            @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-2xl">
                    <p class="text-sm font-semibold text-red-700 mb-1">Please fix the following before registering:</p>
                    <ul class="list-disc list-inside text-xs text-red-600 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Photo upload --}}
            <div class="flex items-center gap-5 bg-white rounded-2xl border border-border p-5 mb-4">
                <label class="cursor-pointer" x-data="{ preview: null }">
                    <div class="w-20 h-20 rounded-full bg-fran-light border-2 border-dashed border-fran flex items-center justify-center overflow-hidden"
                         :class="preview ? '' : ''">
                        <template x-if="preview">
                            <img :src="preview" class="w-full h-full object-cover rounded-full">
                        </template>
                        <template x-if="!preview">
                            <div class="text-center">
                                <div class="text-2xl">📷</div>
                                <p class="text-xs text-fran mt-0.5">Upload</p>
                            </div>
                        </template>
                    </div>
                    <input type="file" name="photo" accept="image/*" class="hidden"
                           @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                </label>
                <div>
                    <p class="text-sm font-semibold text-admin">Student Photo</p>
                    <p class="text-xs text-gray-400 mt-0.5">Optional. JPG or PNG, max 2 MB.</p>
                </div>
                {{-- Student type toggle --}}
                <div class="ml-auto flex rounded-xl border border-border overflow-hidden">
                    <label class="cursor-pointer">
                        <input type="radio" name="student_type" value="internal" x-model="studentType" class="sr-only peer"
                               {{ old('student_type', 'internal') === 'internal' ? 'checked' : '' }}>
                        <span class="block px-4 py-2 text-sm font-medium transition-colors peer-checked:bg-fran peer-checked:text-white text-gray-500 hover:text-gray-700">Internal</span>
                    </label>
                    <label class="cursor-pointer border-l border-border">
                        <input type="radio" name="student_type" value="external" x-model="studentType" class="sr-only peer"
                               {{ old('student_type') === 'external' ? 'checked' : '' }}>
                        <span class="block px-4 py-2 text-sm font-medium transition-colors peer-checked:bg-fran peer-checked:text-white text-gray-500 hover:text-gray-700">External</span>
                    </label>
                </div>
            </div>

            {{-- Personal Information --}}
            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-fran mb-4">Personal Information</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Profile Photo</label>
                        <input type="file" name="photo" accept="image/png,image/jpeg"
                               class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-fran hover:file:bg-blue-100">
                        <p class="text-xs text-gray-400 mt-1">Optional. JPG or PNG, up to 2 MB.</p>
                        @error('photo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
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
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Home Address</label>
                        <input type="text" name="address" value="{{ old('address') }}"
                               placeholder="Area, City, Pincode"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                </div>
            </div>

            {{-- Parent Contact --}}
            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-fran mb-4">Parent Contact</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Parent Name <span class="text-red-500">*</span></label>
                        <input type="text" name="parent_name" value="{{ old('parent_name') }}" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Relationship</label>
                        <select name="parent_relationship" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="">Select</option>
                            <option value="father" @selected(old('parent_relationship') === 'father')>Father</option>
                            <option value="mother" @selected(old('parent_relationship') === 'mother')>Mother</option>
                            <option value="guardian" @selected(old('parent_relationship') === 'guardian')>Guardian</option>
                        </select>
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
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Parent Email</label>
                        <input type="email" name="parent_email" value="{{ old('parent_email') }}"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                </div>
            </div>

            {{-- Level Assignment (both types — Competition Practice access is now Level-gated for external too) --}}
            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-fran mb-4">Level Assignment</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Assign Level <span class="text-red-500">*</span></label>
                        <select name="current_level_id" required
                                x-on:change="levelFee = $event.target.options[$event.target.selectedIndex].dataset.fee || 0; if (!feeManuallyEdited) monthlyFee = levelFee"
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="">Select Level</option>
                            @foreach($levels as $level)
                                <option value="{{ $level->id }}" data-fee="{{ $level->fee_per_month }}"
                                        @selected(old('current_level_id') == $level->id)>
                                    Level {{ $level->number }} — {{ $level->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('current_level_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div x-show="studentType === 'internal'">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Monthly Fee (₹)</label>
                        <input type="number" name="monthly_fee" x-model="monthlyFee" x-on:input="feeManuallyEdited = true" min="0" step="1"
                               placeholder="Auto-filled from level — editable"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('monthly_fee') border-red-400 @enderror">
                        <p class="text-xs text-gray-400 mt-1">Defaults to the level's fee — change it to set a custom rate for this student.</p>
                        @error('monthly_fee')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Course Enrollment (internal only) --}}
            <div x-show="studentType === 'internal'" class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-fran mb-4">Course Enrollment</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Class Schedule</label>
                        <input type="text" name="class_schedule" value="{{ old('class_schedule') }}"
                               placeholder="e.g. Mon/Wed/Fri 4:00 PM"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Start Date</label>
                        <input type="date" name="enrollment_date" value="{{ old('enrollment_date', now()->toDateString()) }}"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                </div>
            </div>

            {{-- External competition (external only) --}}
            <div x-show="studentType === 'external'" class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-fran mb-4">Competition Enrollment</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Competition (optional)</label>
                        <select name="competition_id" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="">No competition selected</option>
                            @foreach($competitions as $comp)
                                <option value="{{ $comp->id }}" @selected(old('competition_id') == $comp->id)>{{ $comp->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Registration Fee (₹)</label>
                        <input type="number" name="registration_fee" value="{{ old('registration_fee') }}" min="0" step="0.01"
                               placeholder="e.g. 500"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('registration_fee') border-red-400 @enderror">
                        @error('registration_fee')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        <p class="text-xs text-gray-400 mt-1">Creates a competition registration fee record.</p>
                    </div>
                </div>
            </div>

            {{-- Special Notes --}}
            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-fran mb-4">Special Notes</h2>
                <textarea name="notes" rows="3" placeholder="Allergies, learning difficulties, parent preferences..."
                          class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran resize-none">{{ old('notes') }}</textarea>
            </div>

            {{-- Login credentials (required — open by default so it isn't missed) --}}
            <div class="bg-white rounded-2xl border border-border p-6 mb-6" x-data="{ open: true }">
                <button type="button" @click="open = !open"
                        class="flex items-center justify-between w-full text-sm font-bold text-fran">
                    <span>Login Credentials (Portal Access) <span class="text-red-500">*</span></span>
                    <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-transition class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Login Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="student@example.com"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('email') border-red-400 @enderror">
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div></div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" autocomplete="new-password" placeholder="Min 8 characters"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('password') border-red-400 @enderror">
                        @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" autocomplete="new-password" placeholder="Re-enter password"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('franchise.students.index') }}"
                   class="px-5 py-2.5 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light">Cancel</a>
                <button type="submit" name="draft" value="1"
                        class="px-5 py-2.5 border border-fran text-fran rounded-xl text-sm font-semibold hover:bg-fran-light transition-colors">
                    Save Draft
                </button>
                <button type="submit"
                        class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                    Register Student
                </button>
            </div>
        </form>
    </div>

    {{-- After Registration sidebar --}}
    <div class="bg-white rounded-2xl border border-border p-5 h-fit">
        <h3 class="text-sm font-bold text-fran mb-3">After Registration</h3>
        <p class="text-xs text-gray-400 mb-4">The following will happen automatically:</p>
        <div class="space-y-3">
            @foreach([
                'Student ID auto-generated',
                'Parent SMS notification sent',
                'Fee schedule created',
                'Level materials assigned',
                'First class scheduled',
            ] as $item)
                <div class="flex items-center gap-2.5">
                    <div class="w-5 h-5 rounded-full bg-stu-light flex items-center justify-center flex-shrink-0">
                        <svg class="w-3 h-3 text-stu" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-700">{{ $item }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

@endsection
