<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink($request->only('email'));

        // Same message regardless of whether the email exists — don't leak account existence.
        return back()->with('status', 'If an account exists for that email, a password reset link has been sent.');
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $resetUser = null;

        $status = Password::reset(
            $data,
            function (User $user, string $password) use (&$resetUser) {
                $resetUser = $user;

                // The User model casts `password` as `hashed` — assigning the plain
                // value hashes it automatically, do NOT Hash::make() it again.
                $user->password = $password;
                $user->setRememberToken(Str::random(60));
                $user->save();

                AuditLog::create([
                    'user_id' => $user->id,
                    'franchise_id' => $user->franchise_id,
                    'action' => 'password_reset_self_service',
                    'entity_type' => User::class,
                    'entity_id' => $user->id,
                    'ip_address' => request()->ip() ?? '0.0.0.0',
                    'user_agent' => request()->userAgent(),
                    'created_at' => now(),
                ]);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            $message = match ($status) {
                Password::INVALID_USER => 'We could not find an account with that email address.',
                Password::RESET_THROTTLED => 'Please wait a moment before trying again.',
                default => 'This password reset link is invalid or has expired. Please request a new one.',
            };

            return back()->withErrors(['email' => $message])->withInput($request->only('email'));
        }

        $loginRoute = match (true) {
            $resetUser->hasRole('super_admin') => route('admin.login'),
            $resetUser->hasRole('franchise_admin') => route('franchise.login'),
            default => route('login'),
        };

        return redirect($loginRoute)->with('status', 'Your password has been reset. Please sign in.');
    }
}
