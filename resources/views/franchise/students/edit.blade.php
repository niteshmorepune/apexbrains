@extends('layouts.franchise')
@section('title', 'Edit Student')
@section('page-title', 'Edit Student')

@section('page-actions')
    <a href="{{ route('franchise.students.show', $student) }}"
       class="px-4 py-2 border border-white text-white rounded-xl text-sm hover:bg-blue-600 transition-colors">← Back</a>
@endsection

@section('content')

<div class="max-w-2xl">
    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('franchise.students.update', $student) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="bg-white rounded-2xl border border-border p-6 mb-4">
            <h2 class="text-sm font-bold text-fran mb-4">Student Information</h2>

            {{-- Profile photo --}}
            <div class="flex items-center gap-4 mb-5">
                @if($student->photo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($student->photo) }}"
                         alt="{{ $student->full_name }}" class="w-16 h-16 rounded-2xl object-cover border border-border">
                @else
                    <div class="w-16 h-16 rounded-2xl bg-fran flex items-center justify-center text-white text-xl font-bold">
                        {{ strtoupper(substr($student->first_name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Profile Photo</label>
                    <input type="file" name="photo" accept="image/png,image/jpeg"
                           class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-fran hover:file:bg-blue-100">
                    <p class="text-xs text-gray-400 mt-1">JPG or PNG, up to 2 MB. Leave blank to keep the current photo.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Gender</label>
                    <select name="gender" required class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('gender', $student->gender) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Current Level</label>
                    <select name="current_level_id" required class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        @foreach($levels as $level)
                            <option value="{{ $level->id }}" @selected(old('current_level_id', $student->current_level_id) == $level->id)>{{ $level->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Enrollment Date</label>
                    <input type="date" name="enrollment_date" value="{{ old('enrollment_date', $student->enrollment_date?->format('Y-m-d')) }}" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Login Email</label>
                    <input type="email" name="email" value="{{ old('email', $student->user?->email) }}" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                    <input type="text" name="city" value="{{ old('city', $student->city) }}"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Pincode</label>
                    <input type="text" name="pincode" value="{{ old('pincode', $student->pincode) }}"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
                    <input type="text" name="address" value="{{ old('address', $student->address) }}"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div class="col-span-2 flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" id="is_active"
                           {{ old('is_active', $student->is_active) ? 'checked' : '' }} class="accent-fran">
                    <label for="is_active" class="text-sm text-gray-700">Active Student</label>
                </div>
            </div>
        </div>

        {{-- Parent / Guardian --}}
        <div class="bg-white rounded-2xl border border-border p-6 mb-4">
            <h2 class="text-sm font-bold text-fran mb-4">Parent / Guardian</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Name</label>
                    <input type="text" name="parent_name" value="{{ old('parent_name', $student->primaryParent?->name) }}" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Relationship</label>
                    <select name="parent_relationship" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        @foreach(['father' => 'Father', 'mother' => 'Mother', 'guardian' => 'Guardian'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('parent_relationship', $student->primaryParent?->relationship) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                    <input type="text" name="parent_phone" value="{{ old('parent_phone', $student->primaryParent?->phone) }}" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">WhatsApp</label>
                    <input type="text" name="parent_whatsapp" value="{{ old('parent_whatsapp', $student->primaryParent?->whatsapp) }}"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Parent Email</label>
                    <input type="email" name="parent_email" value="{{ old('parent_email', $student->primaryParent?->email) }}"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('franchise.students.show', $student) }}"
               class="px-5 py-2.5 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light">Cancel</a>
            <button type="submit"
                    class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark">
                Save Changes
            </button>
        </div>
    </form>
</div>

@endsection
