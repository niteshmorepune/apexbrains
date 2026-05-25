<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification — Apex Brains</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-bg-light min-h-screen flex items-center justify-center p-4 font-sans">
<div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-border p-8 text-center">
    <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center {{ $certificate ? ($certificate->is_revoked ? 'bg-red-100' : 'bg-green-100') : 'bg-gray-100' }}">
        @if($certificate && !$certificate->is_revoked)
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        @else
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        @endif
    </div>

    @if($certificate)
        @if($certificate->is_revoked)
            <h1 class="text-xl font-bold text-red-600">Certificate Revoked</h1>
            <p class="text-gray-500 text-sm mt-2">This certificate has been revoked.</p>
        @else
            <h1 class="text-xl font-bold text-green-600">Certificate Verified</h1>
            <div class="mt-4 text-left bg-bg-light rounded-xl p-4 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Student</span><span class="font-medium">{{ $certificate->student->full_name }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Type</span><span class="font-medium capitalize">{{ str_replace('_', ' ', $certificate->type) }}</span></div>
                @if($certificate->level)<div class="flex justify-between"><span class="text-gray-500">Level</span><span class="font-medium">{{ $certificate->level->title }}</span></div>@endif
                <div class="flex justify-between"><span class="text-gray-500">Issued</span><span class="font-medium">{{ $certificate->issued_at->format('d M Y') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Certificate #</span><span class="font-medium text-xs">{{ $certificate->certificate_number }}</span></div>
            </div>
        @endif
    @else
        <h1 class="text-xl font-bold text-gray-900">Not Found</h1>
        <p class="text-gray-500 text-sm mt-2">No certificate found for this verification code.</p>
    @endif

    <p class="text-xs text-gray-400 mt-6">Apex Brains Academy Pvt. Ltd., Pune</p>
</div>
</body>
</html>
