<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $user    = Auth::user();
        $student = $user->student()->with('currentLevel')->first();

        return view('external.profile', compact('user', 'student'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }
}
