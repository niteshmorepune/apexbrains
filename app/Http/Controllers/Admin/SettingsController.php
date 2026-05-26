<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private string $settingsPath = 'settings.json';

    public function index(): View
    {
        $settings = $this->loadSettings();
        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'app_name'               => ['required', 'string', 'max:100'],
            'support_email'          => ['required', 'email', 'max:150'],
            'support_phone'          => ['nullable', 'string', 'max:20'],
            'timezone'               => ['required', 'string', 'max:50'],
            'date_format'            => ['required', 'in:d/m/Y,m/d/Y,Y-m-d'],
            'currency'               => ['required', 'string', 'max:10'],
            'session_lifetime'       => ['required', 'integer', 'min:15', 'max:1440'],
            'max_login_attempts'     => ['required', 'integer', 'min:3', 'max:20'],
            'notify_new_franchise'   => ['nullable', 'boolean'],
            'notify_new_student'     => ['nullable', 'boolean'],
            'notify_payment_due'     => ['nullable', 'boolean'],
            'notify_commission'      => ['nullable', 'boolean'],
            'logo'                   => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg', 'max:1024'],
        ]);

        $data['notify_new_franchise'] = $request->boolean('notify_new_franchise');
        $data['notify_new_student']   = $request->boolean('notify_new_student');
        $data['notify_payment_due']   = $request->boolean('notify_payment_due');
        $data['notify_commission']    = $request->boolean('notify_commission');

        $existing = $this->loadSettings();
        $data['logo_path'] = $existing['logo_path'] ?? null;

        if ($request->hasFile('logo')) {
            if (!empty($existing['logo_path'])) {
                Storage::disk('public')->delete($existing['logo_path']);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        unset($data['logo']);
        $this->saveSettings($data);
        AuditLogger::log('settings_updated', 'Settings');

        return redirect()->route('admin.settings')
            ->with('success', 'Settings saved successfully.');
    }

    private function loadSettings(): array
    {
        if (Storage::disk('local')->exists($this->settingsPath)) {
            return json_decode(Storage::disk('local')->get($this->settingsPath), true) ?? $this->defaults();
        }
        return $this->defaults();
    }

    private function saveSettings(array $data): void
    {
        Storage::disk('local')->put($this->settingsPath, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function defaults(): array
    {
        return [
            'app_name'             => 'Apex Brains Academy',
            'support_email'        => 'support@apexbrains.in',
            'support_phone'        => '',
            'timezone'             => 'Asia/Kolkata',
            'date_format'          => 'd/m/Y',
            'currency'             => '₹',
            'session_lifetime'     => 120,
            'max_login_attempts'   => 5,
            'notify_new_franchise' => true,
            'notify_new_student'   => true,
            'notify_payment_due'   => true,
            'notify_commission'    => true,
            'logo_path'            => null,
        ];
    }
}
