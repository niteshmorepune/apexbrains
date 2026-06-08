@php
    $isPdf   = $pdf ?? false;
    $student = $certificate->student;

    // External (competition) students get a participation certificate; internal get level completion.
    $isParticipation = $certificate->type === 'competition';
    $competition = $certificate->competition;
    $compTitle   = $competition?->title ?? 'Apex Brains Competition';
    $compDate    = $competition?->start_date?->format('d F Y');

    $levelLine = $certificate->level
        ? 'Level ' . $certificate->level->number . ' — ' . ($certificate->level->title ?: 'Abacus Mental Math')
        : 'Abacus Programme';
    $typeLine  = ucwords(str_replace('_', ' ', $certificate->type)) . ' Certificate';

    $docTitle  = $isParticipation ? 'CERTIFICATE OF PARTICIPATION' : 'CERTIFICATE OF COMPLETION';
    $verifyUrl = $certificate->qr_data ?: route('certificate.verify', $certificate->verification_code);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Certificate — {{ $certificate->certificate_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #1f2937; background: #eef2f7; }

        .toolbar {
            background: #1A73E8; padding: 12px 20px; text-align: center;
        }
        .toolbar a, .toolbar button {
            display: inline-block; margin: 0 4px; padding: 8px 16px; border-radius: 8px;
            font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; border: 1px solid #fff;
        }
        .toolbar .primary { background: #fff; color: #1A73E8; }
        .toolbar .ghost { background: transparent; color: #fff; }

        .page { width: 1040px; max-width: 100%; margin: 24px auto; }
        @if($isPdf)
            .page { width: 100%; margin: 0; }
            body { background: #fff; }
        @endif

        .cert {
            position: relative; background: #fff; border: 10px solid #1A73E8;
            padding: 0; overflow: hidden;
        }
        .cert-inner { border: 2px solid #bcd3f5; margin: 10px; padding: 34px 48px 30px; text-align: center; }

        /* Decorative corner triangles (border-based — dompdf safe) */
        .corner { position: absolute; width: 0; height: 0; border-style: solid; }
        .c-bl-1 { bottom: 0; left: 0;  border-width: 0 0 90px 90px; border-color: transparent transparent #F59E0B transparent; }
        .c-bl-2 { bottom: 0; left: 0;  border-width: 0 0 60px 60px; border-color: transparent transparent #EF4444 transparent; }
        .c-br-1 { bottom: 0; right: 0; border-width: 0 90px 90px 0; border-color: transparent #06B6D4 transparent transparent; }
        .c-br-2 { bottom: 0; right: 0; border-width: 0 60px 60px 0; border-color: transparent #1A73E8 transparent transparent; }
        .c-tr-1 { top: 0; right: 0;    border-width: 0 70px 70px 0; border-color: transparent #8B5CF6 transparent transparent; }

        .head { width: 100%; }
        .head td { vertical-align: middle; }
        .logo { height: 54px; }
        .iso {
            display: inline-block; width: 52px; height: 52px; border: 3px solid #d1d5db; border-radius: 50%;
            font-size: 9px; font-weight: bold; color: #9ca3af; line-height: 1.1; padding-top: 12px; text-align: center;
        }

        .title { font-size: 26px; font-weight: bold; letter-spacing: 2px; color: #111827; margin-top: 18px; }
        .rule { width: 90px; height: 3px; background: #1A73E8; margin: 8px auto 0; }
        .muted { color: #6b7280; font-size: 13px; }
        .name {
            font-family: 'DejaVu Serif', Georgia, serif; font-style: italic; font-weight: bold;
            font-size: 40px; color: #1A73E8; margin: 12px 0;
        }
        .levelbox {
            display: inline-block; background: #eef4fe; border-radius: 10px; padding: 10px 26px; margin: 10px 0 4px;
        }
        .levelbox .lv { font-size: 17px; font-weight: bold; color: #1A73E8; }
        .levelbox .ty { font-size: 11px; color: #6b7280; }
        .issued { font-size: 12px; color: #9ca3af; margin-top: 14px; }

        .foot { width: 100%; margin-top: 26px; }
        .foot td { width: 33.33%; text-align: center; vertical-align: bottom; }
        .sigline { width: 150px; border-bottom: 1px solid #9ca3af; margin: 0 auto 4px; height: 26px; }
        .sig-name { font-size: 12px; font-weight: 600; color: #374151; }
        .sig-role { font-size: 10px; color: #9ca3af; }
        .qr { width: 92px; height: 92px; margin: 0 auto; }
        .qr svg { width: 92px; height: 92px; }
        .certno { font-size: 10px; color: #c2c8d0; margin-top: 18px; font-family: 'DejaVu Sans Mono', monospace; }
    </style>
</head>
<body>

    @unless($isPdf)
        <div class="toolbar no-print">
            @if($pdfUrl ?? false)<a class="primary" href="{{ $pdfUrl }}">Download PDF</a>@endif
            <button class="ghost" onclick="window.print()">Print</button>
            @if($backUrl ?? false)<a class="ghost" href="{{ $backUrl }}">&larr; Back</a>@endif
        </div>
    @endunless

    <div class="page">
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
                                <span style="font-size:22px;font-weight:bold;color:#1A73E8;">Apex Brains</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <span class="iso">ISO<br>9001</span>
                        </td>
                    </tr>
                </table>

                <div class="title">{{ $docTitle }}</div>
                <div class="rule"></div>

                <p class="muted" style="margin-top:22px;">This is to certify that</p>
                <div class="name">{{ $student?->full_name ?? 'Student Name' }}</div>

                @if($isParticipation)
                    <p class="muted">participated in</p>
                    <div class="levelbox">
                        <div class="lv">{{ $compTitle }}</div>
                        @if($compDate)<div class="ty">Held on {{ $compDate }}</div>@endif
                    </div>
                @else
                    <p class="muted">has successfully completed</p>
                    <div class="levelbox">
                        <div class="lv">{{ $levelLine }}</div>
                        <div class="ty">{{ $typeLine }}</div>
                    </div>
                @endif

                <p class="issued">
                    Issued on {{ optional($certificate->issued_at)->format('d F Y') }}
                    @if($certificate->series) &middot; Series {{ $certificate->series }} @endif
                </p>

                <table class="foot">
                    <tr>
                        <td>
                            <div class="sigline"></div>
                            <div class="sig-name">{{ $certificate->issuedBy?->name ?? 'Teacher' }}</div>
                            <div class="sig-role">Authorized Signatory</div>
                        </td>
                        <td>
                            <div class="qr">{!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(92)->margin(0)->generate($verifyUrl) !!}</div>
                            <div class="sig-role" style="margin-top:4px;">Scan to verify</div>
                        </td>
                        <td>
                            <div class="sigline"></div>
                            <div class="sig-name">Principal</div>
                            <div class="sig-role">Apex Brains Academy</div>
                        </td>
                    </tr>
                </table>

                <div class="certno">{{ $certificate->certificate_number }}</div>
            </div>
        </div>
    </div>

</body>
</html>
