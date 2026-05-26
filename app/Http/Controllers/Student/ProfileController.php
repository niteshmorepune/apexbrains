<?php

namespace App\Http\Controllers\Student;

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
        $student = $user->student()->with('currentLevel', 'examAttempts', 'practiceSessions')->first();

        $examCount    = $student?->examAttempts->whereNotNull('submitted_at')->count() ?? 0;
        $passedCount  = $student?->examAttempts->where('is_passed', true)->count() ?? 0;
        $practiceCount = $student?->practiceSessions->count() ?? 0;

        return view('student.profile', compact('user', 'student', 'examCount', 'passedCount', 'practiceCount'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:100'],
            'current_password'      => ['nullable', 'string'],
            'password'              => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $user->password = Hash::make($data['password']);
        }

        $user->name = $data['name'];
        $user->save();

        return back()->with('success', 'Profile updated.');
    }
}
