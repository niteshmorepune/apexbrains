<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function index(): View
    {
        $student = Auth::user()->student()->firstOrFail();

        $certificates = Certificate::where('student_id', $student->id)
            ->where('is_revoked', false)
            ->with(['level', 'issuedBy'])
            ->orderByDesc('issued_at')
            ->get();

        return view('student.certificates.index', compact('certificates'));
    }

    public function download(Certificate $certificate): View
    {
        $student = Auth::user()->student()->firstOrFail();

        if ($certificate->student_id !== $student->id) {
            abort(403);
        }

        $certificate->load(['student', 'level', 'issuedBy']);

        return view('franchise.certificates.show', compact('certificate'));
    }
}
