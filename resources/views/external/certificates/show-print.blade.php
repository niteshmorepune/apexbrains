<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate — {{ $certificate->certificate_number }}</title>
    @php
        $compTitle = $certificate->competition?->title ?? 'the Competition';
        $verifyUrl = $certificate->qr_data ?: route('certificate.verify', $certificate->verification_code);
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #1f2937; padding: 28px; }
        .cert { position: relative; border: 10px solid #1A73E8; overflow: hidden; }
        .cert-inner { border: 2px solid #bcd3f5; margin: 10px; padding: 34px 48px 30px; text-align: center; }

        .corner { position: absolute; width: 0; height: 0; border-style: solid; }
        .c-bl-1 { bottom: 0; left: 0;  border-width: 0 0 90px 90px; border-color: transparent transparent #F59E0B transparent; }
        .c-bl-2 { bottom: 0; left: 0;  border-width: 0 0 60px 60px; border-color: transparent transparent #EF4444 transparent; }
        .c-br-1 { bottom: 0; right: 0; border-width: 0 90px 90px 0; border-color: transparent #06B6D4 transparent transparent; }
        .c-br-2 { bottom: 0; right: 0; border-width: 0 60px 60px 0; border-color: transparent #1A73E8 transparent transparent; }
        .c-tr-1 { top: 0; right: 0;    border-width: 0 70px 70px 0; border-color: transparent #8B5CF6 transparent transparent; }

        .head { width: 100%; }
        .head td { vertical-align: middle; }
        .logo { height: 54px; }
        .logo-text { font-size: 22px; font-weight: bold; color: #1A73E8; }
        .iso { display: inline-block; width: 52px; height: 52px; border: 3px solid #d1d5db; border-radius: 50%;
               font-size: 9px; font-weight: bold; color: #9ca3af; line-height: 1.1; padding-top: 12px; text-align: center; }

        .title { font-size: 24px; font-weight: bold; letter-spacing: 2px; color: #111827; margin-top: 18px; }
        .rule { width: 90px; height: 3px; background: #1A73E8; margin: 8px auto 0; }
        .muted { color: #6b7280; font-size: 13px; }
        .name { font-family: 'DejaVu Serif', Georgia, serif; font-style: italic; font-weight: bold;
                font-size: 38px; color: #1A73E8; margin: 12px 0; }
        .compbox { display: inline-block; background: #eef4fe; border-radius: 10px; padding: 10px 26px; margin: 10px 0 4px; }
        .compbox .ev { font-size: 17px; font-weight: bold; color: #1A73E8; }
        .compbox .ty { font-size: 11px; color: #6b7280; }
        .issued { font-size: 12px; color: #9ca3af; margin-top: 14px; }

        .foot { width: 100%; margin-top: 26px; }
        .foot td { width: 33.33%; text-align: center; vertical-align: bottom; }
        .sigline { width: 150px; border-bottom: 1px solid #9ca3af; margin: 0 auto 4px; height: 26px; }
        .sig-name { font-size: 12px; font-weight: 600; color: #374151; }
        .sig-role { font-size: 10px; color: #9ca3af; }
        .qr { width: 88px; height: 88px; margin: 0 auto; }
        .qr svg { width: 88px; height: 88px; }
        .certno { font-size: 10px; color: #c2c8d0; margin-top: 18px; font-family: 'DejaVu Sans Mono', monospace; }
    </style>
</head>
<body>
    <div class="cert">
        <span class="corner c-tr-1"></span>
        <span class="corner c-bl-1"></span>
        <span class="corner c-bl-2"></span>
        <span class="corner c-br-1"></span>
        <span class="corner c-br-2"></span>

        <div class="cert-inner">
            <table class="head">
                <tr>
                    <td style="text-align:left;">
                        @if($logo ?? null)
                            <img class="logo" src="{{ $logo }}" alt="Apex Brains">
                        @else
                            <span class="logo-text">Apex Brains</span>
                        @endif
                    </td>
                    <td style="text-align:right;"><span class="iso">ISO<br>9001</span></td>
                </tr>
            </table>

            <div class="title">CERTIFICATE OF ACHIEVEMENT</div>
            <div class="rule"></div>

            <p class="muted" style="margin-top:22px;">This is to certify that</p>
            <div class="name">{{ $certificate->student?->full_name ?? 'Participant' }}</div>
            <p class="muted">has successfully participated in</p>

            <div class="compbox">
                <div class="ev">{{ $compTitle }}</div>
                <div class="ty">Competition Certificate</div>
            </div>

            <p class="issued">Issued on {{ optional($certificate->issued_at)->format('d F Y') }}</p>

            <table class="foot">
                <tr>
                    <td>
                        <div class="sigline"></div>
                        <div class="sig-name">Apex Brains Academy</div>
                        <div class="sig-role">Authorised Signatory</div>
                    </td>
                    <td>
                        <div class="qr">{!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(88)->margin(0)->generate($verifyUrl) !!}</div>
                        <div class="sig-role" style="margin-top:4px;">Scan to verify</div>
                    </td>
                    <td>
                        <div class="sigline"></div>
                        <div class="sig-name">Director</div>
                        <div class="sig-role">Apex Brains Academy</div>
                    </td>
                </tr>
            </table>

            <div class="certno">{{ $certificate->certificate_number }}</div>
        </div>
    </div>
</body>
</html>
