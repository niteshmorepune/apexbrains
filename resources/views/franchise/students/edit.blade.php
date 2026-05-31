@extends('layouts.franchise')
@section('title', 'Edit Student')
@section('page-title', 'Edit Student')

@section('page-actions')
    <a href="{{ route('franchise.students.show', $student) }}"
       class="px-4 py-2 border border-white text-white rounded-xl text-sm hover:bg-blue-600 transition-colors">← Back</a>
@endsection

@section('content')

<div class="max-w-2xl">
    <form method="POST" action="{{ route('franchise.students.update', $student) }}">
        @csrf @method('PUT')

        <div class="bg-white rounded-2xl border border-border p-6 mb-4">
            <h2 class="text-sm font-bold text-fran mb-4">Student Information</h2>
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
                            <option value="{{ $level->id }}" @selected(old('current_level_id', $student->current_level_id) == $level->id)>Level {{ $level->number }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                    <input type="text" name="city" value="{{ old('city', $student->city) }}"
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
