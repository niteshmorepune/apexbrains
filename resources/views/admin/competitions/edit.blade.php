@extends('layouts.admin')
@section('title', 'Edit Competition')
@section('page-title', 'Edit: ' . $competition->title)

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="col-span-2">
        <form id="competition-edit-form" method="POST" action="{{ route('admin.competitions.update', $competition) }}">
            @csrf @method('PUT')

            {{-- Basic Info --}}
            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-admin mb-4">Competition Details</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $competition->title) }}" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('title') border-red-400 @enderror">
                        @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                        <textarea name="description" rows="3"
                                  class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran resize-none">{{ old('description', $competition->description) }}</textarea>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Type <span class="text-red-500">*</span></label>
                            <select name="competition_type" required
                                    class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                                @foreach(['local', 'regional', 'national'] as $type)
                                    <option value="{{ $type }}" @selected(old('competition_type', $competition->competition_type) === $type)>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Entry Fee (₹) <span class="text-red-500">*</span></label>
                            <input type="number" name="fee_amount" value="{{ old('fee_amount', $competition->fee_amount) }}" min="0" step="50" required
                                   class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Max Participants</label>
                        <input type="number" name="max_participants" value="{{ old('max_participants', $competition->max_participants) }}" min="1"
                               placeholder="Leave blank for unlimited"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                </div>
            </div>

            {{-- Dates --}}
            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-admin mb-4">Dates</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach([
                        'registration_deadline' => 'Registration Deadline',
                        'start_date'            => 'Start Date',
                        'end_date'              => 'End Date',
                    ] as $field => $label)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }} <span class="text-red-500">*</span></label>
                            <input type="date" name="{{ $field }}"
                                   value="{{ old($field, $competition->$field?->format('Y-m-d')) }}" required
                                   class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error($field) border-red-400 @enderror">
                            @error($field)<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Settings --}}
            <div class="bg-white rounded-2xl border border-border p-6 mb-6">
                <h2 class="text-sm font-bold text-admin mb-4">Settings</h2>
                <div class="flex items-center gap-8">
                    @foreach([
                        ['is_open_to_external', 'Open to External Students', 'Allow students from outside franchises to register'],
                        ['is_active',            'Active',                   'Visible and open for registration'],
                    ] as [$field, $label, $hint])
                        <label class="flex items-center gap-3 cursor-pointer">
                            <div class="relative">
                                <input type="hidden" name="{{ $field }}" value="0">
                                <input type="checkbox" name="{{ $field }}" value="1" class="sr-only peer"
                                       {{ old($field, $competition->$field) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-fran rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-fran after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700">{{ $label }}</p>
                                <p class="text-xs text-gray-400">{{ $hint }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

        </form>

        <div class="flex items-center gap-3 mt-6">
            <a href="{{ route('admin.competitions.index') }}"
               class="px-5 py-2.5 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
                Cancel
            </a>
            <button type="submit" form="competition-edit-form"
                    class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                Save Changes
            </button>
            <form method="POST" action="{{ route('admin.competitions.destroy', $competition) }}" class="ml-auto"
                  onsubmit="return confirm('Delete this competition permanently?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2.5 text-red-500 text-sm hover:underline">Delete</button>
            </form>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-5">
            <h3 class="text-sm font-bold text-admin mb-3">Competition Info</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Created</dt>
                    <dd>{{ $competition->created_at->format('d M Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Registrations</dt>
                    <dd class="font-semibold text-fran">{{ $competition->registrations_count ?? 0 }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Max Slots</dt>
                    <dd>{{ $competition->max_participants ?? 'Unlimited' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>

@endsection
