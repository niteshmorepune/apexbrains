@php
    use Illuminate\Support\Facades\Storage;

    // Brand logo as a base64 data URI (dompdf can't fetch URLs).
    $logoData = null;
    $logoPath = $appSettings['logo_path'] ?? null;
    if ($logoPath && Storage::disk('public')->exists($logoPath)) {
        $logoData = 'data:image/png;base64,' . base64_encode(Storage::disk('public')->get($logoPath));
    } elseif (file_exists(public_path('images/apex-logo.png'))) {
        $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/apex-logo.png')));
    }

    $franchise = $student->franchise;

    $passRate = $attempts->count()
        ? round($attempts->where('is_passed', true)->count() / $attempts->count() * 100)
        : 0;
    $avgScore = $attempts->count() ? (float) $attempts->avg('percentage') : 0;

    $metrics = collect($radarData['labels'] ?? [])
        ->map(fn ($label, $i) => ['label' => $label, 'value' => (float) ($radarData['values'][$i] ?? 0)]);

    $currentNum = $student->currentLevel?->number ?? 0;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1f2937; font-size: 11px; margin: 0; }
        .head { width: 100%; border-bottom: 2px solid #1A73E8; padding-bottom: 10px; margin-bottom: 16px; }
        .head td { vertical-align: middle; }
        .brand { font-size: 18px; font-weight: bold; color: #1A2332; }
        .iso { color: #9ca3af; font-size: 9px; }
        .rtitle { color: #1A73E8; font-size: 13px; font-weight: bold; text-align: right; }
        .rsub { color: #9ca3af; font-size: 9px; text-align: right; margin-top: 2px; }

        .section { margin-bottom: 16px; }
        .section-title { font-size: 11px; font-weight: bold; color: #1A73E8; text-transform: uppercase; margin-bottom: 6px; padding-bottom: 4px; border-bottom: 1px solid #E8EDF2; }

        table.kv { width: 100%; border-collapse: collapse; }
        table.kv td { padding: 4px 0; font-size: 10px; }
        table.kv .lbl { color: #9ca3af; width: 20%; }
        table.kv .val { color: #111827; font-weight: bold; width: 30%; }

        table.kpi-row { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin-bottom: 4px; }
        table.kpi-row td { border: 1px solid #D0D7E2; border-radius: 8px; padding: 8px 10px; width: 25%; }
        .kpi-label { font-size: 8px; color: #888; text-transform: uppercase; margin-bottom: 3px; }
        .kpi-value { font-size: 17px; font-weight: bold; color: #1A73E8; }

        table.bars { width: 100%; border-collapse: collapse; }
        table.bars td { padding: 5px 0; font-size: 10px; vertical-align: middle; }
        table.bars .m-label { width: 22%; color: #374151; }
        table.bars .m-track { width: 63%; }
        table.bars .m-value { width: 15%; text-align: right; font-weight: bold; color: #1A2332; }
        .track { width: 100%; background: #EEF1F6; border-radius: 4px; }
        .fill { height: 8px; border-radius: 4px; background: #1A73E8; }

        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #1A2332; color: #fff; text-align: left; padding: 6px 8px; font-size: 9px; }
        table.data td { padding: 6px 8px; border-bottom: 1px solid #E8EDF2; font-size: 9.5px; }
        table.data tr:nth-child(even) td { background: #F7F9FC; }
        .right { text-align: right; }
        .center { text-align: center; }
        .pass { color: #16A34A; font-weight: bold; }
        .fail { color: #DC2626; font-weight: bold; }

        table.checklist { width: 100%; border-collapse: collapse; }
        table.checklist td { padding: 4px 8px; font-size: 9.5px; border-bottom: 1px solid #F1F3F6; }
        .chk-icon { width: 16px; text-align: center; font-weight: bold; }
        .chk-done .chk-icon { color: #16A34A; }
        .chk-current { background: #EAF2FE; }
        .chk-current .chk-icon { color: #1A73E8; }
        .chk-upcoming td { color: #9CA3AF; }
        .chk-status { text-align: right; font-size: 8.5px; }

        .footer { margin-top: 18px; padding-top: 8px; border-top: 1px solid #E8EDF2; font-size: 8.5px; color: #9CA3AF; text-align: center; }
        .empty { color: #9CA3AF; font-size: 9.5px; text-align: center; padding: 14px 0; }
    </style>
</head>
<body>
    <table class="head">
        <tr>
            <td style="width:54px;">
                @if($logoData)
                    <img src="{{ $logoData }}" style="height:40px;" alt="logo">
                @endif
            </td>
            <td>
                <div class="brand">{{ $appSettings['app_name'] ?? 'Apex Brains' }}</div>
                <div class="iso">
                    @if($franchise){{ $franchise->name }}@if($franchise->city), {{ $franchise->city }}@endif @endif
                </div>
            </td>
            <td style="width:180px;">
                <div class="rtitle">Student Progress Report</div>
                <div class="rsub">Generated {{ now()->format('d M Y, H:i') }}</div>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Student Information</div>
        <table class="kv">
            <tr>
                <td class="lbl">Name</td><td class="val">{{ $student->full_name }}</td>
                <td class="lbl">Student ID</td><td class="val">{{ $student->student_code }}</td>
            </tr>
            <tr>
                <td class="lbl">Type</td><td class="val">{{ ucfirst($student->student_type) }}</td>
                <td class="lbl">Level</td><td class="val">{{ $student->currentLevel?->title ?? '—' }}</td>
            </tr>
            <tr>
                <td class="lbl">Date of Birth</td><td class="val">{{ $student->date_of_birth?->format('d M Y') ?? '—' }}</td>
                <td class="lbl">Gender</td><td class="val">{{ $student->gender ? ucfirst($student->gender) : '—' }}</td>
            </tr>
            <tr>
                <td class="lbl">Enrolled On</td><td class="val">{{ $student->enrollment_date?->format('d M Y') ?? '—' }}</td>
                <td class="lbl">Parent / Guardian</td><td class="val">{{ $student->primaryParent?->name ?? '—' }}@if($student->primaryParent?->phone) &middot; {{ $student->primaryParent->phone }} @endif</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Performance Summary</div>
        <table class="kpi-row">
            <tr>
                <td>
                    <div class="kpi-label">Total Exams</div>
                    <div class="kpi-value" style="color:#1A2332;">{{ $attempts->count() }}</div>
                </td>
                <td>
                    <div class="kpi-label">Average Score</div>
                    <div class="kpi-value">{{ $attempts->count() ? number_format($avgScore, 1) . '%' : '—' }}</div>
                </td>
                <td>
                    <div class="kpi-label">Pass Rate</div>
                    <div class="kpi-value" style="color:#16A34A;">{{ $attempts->count() ? $passRate . '%' : '—' }}</div>
                </td>
                <td>
                    <div class="kpi-label">Exams Passed</div>
                    <div class="kpi-value" style="color:#1A2332;">{{ $attempts->where('is_passed', true)->count() }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Performance Breakdown</div>
        @if($attempts->count() > 0)
            <table class="bars">
                @foreach($metrics as $m)
                    <tr>
                        <td class="m-label">{{ $m['label'] }}</td>
                        <td class="m-track">
                            <div class="track"><div class="fill" style="width:{{ max(2, min(100, round($m['value']))) }}%;"></div></div>
                        </td>
                        <td class="m-value">{{ round($m['value']) }}%</td>
                    </tr>
                @endforeach
            </table>
        @else
            <div class="empty">No exam data available yet.</div>
        @endif
    </div>

    @if($student->student_type === 'internal')
        <div class="section">
            <div class="section-title">Topic Checklist — Curriculum Progress</div>
            <table class="checklist">
                @foreach($levels as $lvl)
                    @php $state = $lvl->number < $currentNum ? 'done' : ($lvl->number === $currentNum ? 'current' : 'upcoming'); @endphp
                    <tr class="chk-{{ $state }}">
                        <td class="chk-icon">{{ $state === 'done' ? '✓' : ($state === 'current' ? '●' : '○') }}</td>
                        <td>{{ $lvl->title }}</td>
                        <td class="chk-status">
                            @if($state === 'done') Completed
                            @elseif($state === 'current') In Progress
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
            @if($student->currentLevel && filled($student->currentLevel->learning_objectives))
                <div style="margin-top:10px; padding-top:8px; border-top:1px solid #F1F3F6;">
                    <div style="font-size:9.5px; font-weight:bold; color:#374151; margin-bottom:4px;">
                        Focus areas — {{ $student->currentLevel->title }}
                    </div>
                    <table style="width:100%;">
                        @foreach($student->currentLevel->learning_objectives as $obj)
                            <tr><td style="font-size:9px; color:#4B5563; padding:1px 0;">&bull; {{ $obj }}</td></tr>
                        @endforeach
                    </table>
                </div>
            @endif
        </div>
    @endif

    <div class="section">
        <div class="section-title">All Exam Attempts</div>
        @if($attempts->count() > 0)
            <table class="data">
                <thead>
                    <tr>
                        <th>Exam</th>
                        <th>Date</th>
                        <th class="right">Score</th>
                        <th class="center">Result</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attempts as $attempt)
                        <tr>
                            <td>{{ $attempt->exam?->title ?? 'Exam #' . $attempt->exam_id }}</td>
                            <td>{{ $attempt->submitted_at?->format('d M Y') }}</td>
                            <td class="right">{{ number_format($attempt->percentage, 1) }}%</td>
                            <td class="center {{ $attempt->is_passed ? 'pass' : 'fail' }}">{{ $attempt->is_passed ? 'Passed' : 'Failed' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">No exam attempts yet.</div>
        @endif
    </div>

    <div class="footer">This is a computer-generated report &bull; {{ $appSettings['app_name'] ?? 'Apex Brains' }} Academy Management System</div>
</body>
</html>
