@props(['text' => '', 'size' => 44])
@php
    // Parse an arithmetic expression into right-aligned vertical operands,
    // the standard abacus column-sum layout. "9 + 8 − 3 = ?" becomes a
    // stacked column: 9 / +8 / −3 with an answer underline.
    $clean = preg_replace('/=\s*\?|\?/', '', (string) $text);
    $opMap = ['*' => '×', 'x' => '×', 'X' => '×', '/' => '÷', '-' => '−', '–' => '−'];
    preg_match_all('/([+\-−–×xX*÷\/])?\s*(\d+(?:\.\d+)?)/u', $clean, $matches, PREG_SET_ORDER);

    $terms = [];
    foreach ($matches as $i => $set) {
        $op = $set[1] ?? '';
        if ($op !== '' && isset($opMap[$op])) {
            $op = $opMap[$op];
        }
        if ($i === 0 && $op === '') {
            $op = '';        // first operand carries no sign
        } elseif ($op === '') {
            $op = '+';       // implicit addition between bare numbers
        }
        $terms[] = ['op' => $op, 'num' => $set[2]];
    }
@endphp

@if(count($terms) <= 1)
    {{-- Single value or non-parseable expression — show as written. --}}
    <p class="font-black text-gray-900 leading-tight whitespace-pre-line text-center" style="font-size: {{ $size }}px;">{{ $text }}</p>
@else
    <div {{ $attributes->merge(['class' => 'inline-block font-mono font-black text-gray-900 leading-tight']) }}
         style="font-size: {{ $size }}px;">
        <table style="border-collapse: collapse; margin-left: auto;">
            @foreach($terms as $t)
                <tr>
                    <td class="pr-4 text-right text-gray-500">{{ $t['op'] }}</td>
                    <td class="text-right tabular-nums">{{ $t['num'] }}</td>
                </tr>
            @endforeach
            <tr><td colspan="2" class="border-t-4 border-gray-800 pt-1"></td></tr>
        </table>
    </div>
@endif
