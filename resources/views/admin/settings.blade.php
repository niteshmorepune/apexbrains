@extends('layouts.admin')
@section('title', 'System Settings')
@section('page-title', 'System Settings')

@section('content')

<div x-data="{ tab: '{{ old('_tab', 'general') }}' }">

    {{-- Tab navigation --}}
    <div class="flex gap-1 mb-6 bg-white rounded-2xl border border-border p-1 w-fit">
        @foreach(['general' => 'General', 'security' => 'Security', 'notifications' => 'Notifications'] as $key => $label)
            <button type="button" @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'bg-fran text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="px-5 py-2 rounded-xl text-sm font-medium transition-colors">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="_tab" :value="tab">

        {{-- General Settings --}}
        <div x-show="tab === 'general'" class="grid grid-cols-3 gap-6">
            <div class="col-span-2 space-y-4">
                <div class="bg-white rounded-2xl border border-border p-6">
                    <h2 class="text-sm font-bold text-admin mb-4">General Settings</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Academy Name</label>
                            <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name']) }}" required
                                   class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Support Email</label>
                            <input type="email" name="support_email" value="{{ old('support_email', $settings['support_email']) }}" required
                                   class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Support Phone</label>
                            <input type="text" name="support_phone" value="{{ old('support_phone', $settings['support_phone']) }}"
                                   class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Timezone</label>
                            <select name="timezone" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                                @foreach(['Asia/Kolkata' => 'IST (Asia/Kolkata)', 'UTC' => 'UTC', 'Asia/Mumbai' => 'Asia/Mumbai'] as $tz => $label)
                                    <option value="{{ $tz }}" @selected(($settings['timezone'] ?? 'Asia/Kolkata') === $tz)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-border p-6">
                    <h2 class="text-sm font-bold text-admin mb-4">Display Settings</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Date Format</label>
                            <select name="date_format" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                                @foreach(['d/m/Y' => 'DD/MM/YYYY', 'm/d/Y' => 'MM/DD/YYYY', 'Y-m-d' => 'YYYY-MM-DD'] as $fmt => $label)
                                    <option value="{{ $fmt }}" @selected(($settings['date_format'] ?? 'd/m/Y') === $fmt)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Currency Symbol</label>
                            <input type="text" name="currency" value="{{ old('currency', $settings['currency']) }}" maxlength="10"
                                   class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-white rounded-2xl border border-border p-5" x-data="{ preview: null }">
                    <h3 class="text-sm font-bold text-admin mb-3">Logo &amp; Branding</h3>
                    <div class="flex items-center justify-center h-20 rounded-xl border-2 border-dashed border-border mb-3 bg-bg-light overflow-hidden cursor-pointer relative"
                         @click="$refs.logoInput.click()">
                        <template x-if="preview">
                            <img :src="preview" class="max-h-16 max-w-full object-contain">
                        </template>
                        <template x-if="!preview">
                            @if(!empty($settings['logo_path']))
                                <img src="{{ Storage::url($settings['logo_path']) }}" class="max-h-16 max-w-full object-contain">
                            @else
                                <div class="text-center">
                                    <svg class="w-6 h-6 text-gray-300 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <p class="text-xs text-gray-400">Click to upload logo</p>
                                </div>
                            @endif
                        </template>
                    </div>
                    <input type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml"
                           x-ref="logoInput" class="hidden"
                           @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                    <p class="text-xs text-gray-400">Recommended: 200×60px PNG with transparent background</p>
                    @if(!empty($settings['logo_path']))
                        <p class="text-xs text-green-600 mt-1">Logo uploaded. Click above to replace.</p>
                    @endif
                </div>

                <div class="bg-admin rounded-2xl p-5">
                    <h3 class="text-sm font-bold text-white mb-1">Current Version</h3>
                    <p class="text-xs text-gray-300">Apex Brains v1.0.0</p>
                    <p class="text-xs text-gray-400 mt-1">Laravel {{ app()->version() }}</p>
                </div>
            </div>
        </div>

        {{-- Security Settings --}}
        <div x-show="tab === 'security'" class="max-w-2xl space-y-4">
            <div class="bg-white rounded-2xl border border-border p-6">
                <h2 class="text-sm font-bold text-admin mb-4">Security Settings</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Session Lifetime (minutes)</label>
                        <input type="number" name="session_lifetime" value="{{ old('session_lifetime', $settings['session_lifetime']) }}"
                               min="15" max="1440" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        <p class="text-xs text-gray-400 mt-1">15–1440 minutes</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Max Login Attempts</label>
                        <input type="number" name="max_login_attempts" value="{{ old('max_login_attempts', $settings['max_login_attempts']) }}"
                               min="3" max="20" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        <p class="text-xs text-gray-400 mt-1">Before temporary lockout</p>
                    </div>
                </div>
            </div>

            <div class="bg-yellow-50 rounded-2xl border border-yellow-200 p-5">
                <h3 class="text-sm font-bold text-yellow-800 mb-2">Security Reminders</h3>
                <ul class="text-xs text-yellow-700 space-y-1">
                    <li>• APP_DEBUG must be false in production</li>
                    <li>• All login routes have throttle:6,1 rate limiting</li>
                    <li>• Exam integrity: IP, user_agent, tab switches logged per attempt</li>
                </ul>
            </div>
        </div>

        {{-- Notifications --}}
        <div x-show="tab === 'notifications'" class="max-w-2xl">
            <div class="bg-white rounded-2xl border border-border p-6">
                <h2 class="text-sm font-bold text-admin mb-4">Notification Preferences</h2>
                <div class="space-y-4">
                    @foreach([
                        'notify_new_franchise' => ['New Franchise Registered', 'Get notified when a new franchise submits an application'],
                        'notify_new_student'   => ['New Student Enrolled', 'Get notified when a new student is added'],
                        'notify_payment_due'   => ['Payment Due Alerts', 'Get notified when payments are overdue'],
                        'notify_commission'    => ['Commission Reports', 'Get notified when monthly commissions are calculated'],
                    ] as $key => $info)
                        <div class="flex items-start justify-between py-3 border-b border-border last:border-b-0">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $info[0] }}</p>
                                <p class="text-xs text-gray-500">{{ $info[1] }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer ml-4 flex-shrink-0">
                                <input type="checkbox" name="{{ $key }}" value="1"
                                       {{ ($settings[$key] ?? true) ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-5 peer-checked:bg-fran after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button type="submit"
                    class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                Save All Settings
            </button>
            @if(session('success'))
                <span class="text-sm text-stu font-medium">{{ session('success') }}</span>
            @endif
        </div>
    </form>
</div>

@endsection
