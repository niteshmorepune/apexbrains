<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1f2937; font-size: 12px; margin: 0; }
        .head { border-bottom: 2px solid #1A73E8; padding-bottom: 10px; margin-bottom: 16px; }
        .title { font-size: 20px; font-weight: bold; color: #111827; margin: 0; }
        .meta { color: #6b7280; font-size: 11px; margin-top: 4px; }
        .tag { display: inline-block; background: #dcfce7; color: #15803d; font-size: 10px;
               font-weight: bold; padding: 2px 8px; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1f2937; color: #fff; text-align: left; padding: 7px 9px; font-size: 11px; }
        td { padding: 7px 9px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        td.num { width: 34px; text-align: center; color: #6b7280; }
        td.ans { width: 130px; font-weight: bold; color: #15803d; }
        tr:nth-child(even) td { background: #f8fafc; }
    </style>
</head>
<body>
    <div class="head">
        <p class="title">{{ $paper->title }}</p>
        <p class="meta">
            Answer Key &middot; Level {{ $paper->level?->number ?? '—' }}
            &middot; {{ $questions->count() }} questions
            &middot; Generated {{ now()->format('d M Y, H:i') }}
        </p>
        <p style="margin-top:6px;"><span class="tag">PRACTICE TEST</span></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:34px; text-align:center;">#</th>
                <th>Question</th>
                <th style="width:130px;">Correct Answer</th>
            </tr>
        </thead>
        <tbody>
            @foreach($questions as $i => $pq)
                @php
                    $q = $pq->question;
                    $letter = strtolower($q->correct_answer);
                    $value = $q->{'option_' . $letter} ?? null;
                @endphp
                <tr>
                    <td class="num">{{ $i + 1 }}</td>
                    <td>{{ $q->question_text }}</td>
                    <td class="ans">{{ strtoupper($q->correct_answer) }}@if($value)) {{ $value }}@endif</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
