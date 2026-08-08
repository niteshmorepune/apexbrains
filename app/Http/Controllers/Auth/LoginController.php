<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\StudentParent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'identifier' => ['required', 'string'],
            'password'   => ['required'],
        ]);

        $identifier = trim($data['identifier']);
        $email = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? $identifier : null;

        if (! $email) {
            // Not an email — treat it as a mobile number and resolve the
            // student's account via their parent's phone on file.
            $parentRecord = StudentParent::where('phone', $identifier)
                ->whereHas('student.user')
                ->with('student.user')
                ->first();
            $email = $parentRecord?->student?->user?->email;
        }

        if (! $email || ! Auth::attempt(['email' => $email, 'password' => $data['password']])) {
            return back()->withErrors(['identifier' => 'Invalid credentials.'])->onlyInput('identifier');
        }

        $user = Auth::user();

        if ($user->hasRole('super_admin')) {
            Auth::logout();
            return back()->withErrors(['identifier' => 'Admin accounts must sign in at /admin/login.'])->onlyInput('identifier');
        }

        if ($user->hasRole('franchise_admin')) {
            Auth::logout();
            return back()->withErrors(['identifier' => 'Franchise accounts must sign in at /franchise/login.'])->onlyInput('identifier');
        }

        if (! $user->is_active) {
            Auth::logout();
            return back()->withErrors(['identifier' => 'Your account has been deactivated.']);
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $request->session()->regenerate();

        return $this->redirectByRole();
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectByRole(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasRole('super_admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('franchise_admin')) {
            return redirect()->route('franchise.dashboard');
        }

        if ($user->hasRole('student')) {
            if ($user->student_type === 'external') {
                return redirect()->route('external.home');
            }
            return redirect()->route('student.home');
        }

        return redirect('/');
    }
}
