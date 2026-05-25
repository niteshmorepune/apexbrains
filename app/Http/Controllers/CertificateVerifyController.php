<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\View\View;

class CertificateVerifyController extends Controller
{
    public function show(string $verificationCode): View
    {
        $certificate = Certificate::with(['student', 'level', 'franchise', 'competition'])
            ->where('verification_code', $verificationCode)
            ->first();

        return view('certificate.verify', compact('certificate'));
    }
}
