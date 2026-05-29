<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate — {{ $certificate->certificate_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; margin: 0; padding: 40px; color: #1A2332; }
        .frame { border: 6px double #1A73E8; padding: 40px; text-align: center; }
        .logo { font-size: 28px; font-weight: bold; color: #D42B2B; }
        .iso { font-size: 11px; color: #888; margin-top: 4px; }
        .title { font-size: 14px; letter-spacing: 4px; text-transform: uppercase; color: #888; margin: 30px 0 12px; }
        .name { font-size: 32px; font-weight: bold; margin: 8px 0; }
        .body { font-size: 14px; color: #555; margin: 8px 0; }
        .meta { font-size: 11px; color: #999; margin-top: 20px; }
        .footer { display: flex; justify-content: space-between; margin-top: 50px; font-size: 11px; }
        .sig { border-top: 1px solid #ccc; padding-top: 4px; width: 160px; }
    </style>
</head>
<body>
    <div class="frame">
        <div class="logo">Apex Brains Academy</div>
        <div class="iso">ISO 9001:2015 Certified</div>

        <div class="title">Certificate of Achievement</div>
        <div class="body">This is to certify that</div>
        <div class="name">{{ $certificate->student?->full_name }}</div>
        <div class="body">
            has successfully participated in
            <strong>{{ $certificate->title ?? 'the Competition' }}</strong>
        </div>

        <div class="meta">
            Issued: {{ $certificate->issued_at?->format('F Y') }}
            &nbsp;·&nbsp; ID: {{ $certificate->certificate_number }}
        </div>

        <div class="footer">
            <div>
                Authorised by: Apex Brains Academy
                <div class="sig">Signature</div>
            </div>
            <div>
                {!! QrCode::format('svg')->size(80)->generate(route('certificate.verify', $certificate->verification_code)) !!}
                <div style="font-size:10px;color:#999;margin-top:4px;">Scan to verify</div>
            </div>
        </div>
    </div>
</body>
</html>
