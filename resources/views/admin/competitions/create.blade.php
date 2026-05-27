@extends('layouts.admin')
@section('title', 'New Competition')
@section('page-title', 'Create Competition')

@section('content')

<div class="grid grid-cols-3 gap-6">
    <div class="col-span-2">
        <form method="POST" action="{{ route('admin.competitions.store') }}">
            @csrf

            {{-- Basic Info --}}
            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-admin mb-4">Competition Details</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                               placeholder="e.g. Pune Regional Abacus Championship 2026"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('title') border-red-400 @enderror">
                        @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                        <textarea name="description" rows="3"
                                  class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran resize-none"
                                  placeholder="Brief description of the competition...">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Type <span class="text-red-500">*</span>
                            </label>
                            <select name="competition_type" required
                                    class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                                <option value="local"     @selected(old('competition_type') === 'local')>Local</option>
                                <option value="regional"  @selected(old('competition_type', 'regional') === 'regional')>Regional</option>
                                <option value="national"  @selected(old('competition_type') === 'national')>National</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Entry Fee (₹) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="fee_amount" value="{{ old('fee_amount', 0) }}" min="0" step="50" required
                                   class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Max Participants</label>
                        <input type="number" name="max_participants" value="{{ old('max_participants') }}" min="1"
                               placeholder="Leave blank for unlimited"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                </div>
            </div>

            {{-- Dates --}}
            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-admin mb-4">Dates</h2>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Registration Deadline <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="registration_deadline" value="{{ old('registration_deadline') }}" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('registration_deadline') border-red-400 @enderror">
                        @error('registration_deadline')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Start Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('start_date') border-red-400 @enderror">
                        @error('start_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            End Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('end_date') border-red-400 @enderror">
                        @error('end_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Settings --}}
            <div class="bg-white rounded-2xl border border-border p-6 mb-6">
                <h2 class="text-sm font-bold text-admin mb-4">Settings</h2>
                <div class="flex items-center gap-8">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <div class="relative">
                            <input type="hidden" name="is_open_to_external" value="0">
                            <input type="checkbox" name="is_open_to_external" value="1" class="sr-only peer"
                                   {{ old('is_open_to_external', '1') ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-fran rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-fran after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Open to External Students</p>
                            <p class="text-xs text-gray-400">Allow students from outside franchises to register</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <div class="relative">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-fran rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-fran after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Active</p>
                            <p class="text-xs text-gray-400">Visible and open for registration</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.competitions.index') }}"
                   class="px-5 py-2.5 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                    Create Competition
                </button>
            </div>
        </form>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-admin rounded-2xl p-5 text-white">
            <h3 class="text-sm font-bold mb-3">Competition Types</h3>
            <dl class="space-y-3 text-xs">
                <div>
                    <dt class="text-stu font-semibold">Local</dt>
                    <dd class="text-gray-400 mt-0.5">Single franchise — internal students only</dd>
                </div>
                <div>
                    <dt class="text-fran font-semibold">Regional</dt>
                    <dd class="text-gray-400 mt-0.5">Multiple franchises — inter-branch</dd>
                </div>
                <div>
                    <dt class="text-logo-amber font-semibold">National</dt>
                    <dd class="text-gray-400 mt-0.5">Open to all — external students eligible</dd>
                </div>
            </dl>
        </div>
    </div>
</div>

@endsection
