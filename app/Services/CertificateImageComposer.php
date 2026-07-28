<?php

namespace App\Services;

use App\Models\Certificate;

/**
 * Renders the 4 exact-design certificates (Certificate_Generation_Flows) by
 * compositing the dynamic values directly onto the bundled artwork with GD,
 * then handing dompdf a single flattened <img>. dompdf's CSS support for many
 * overlapping absolutely-positioned elements is unreliable (it silently drops
 * some of them on denser pages) — GD compositing sidesteps that entirely.
 */
class CertificateImageComposer
{
    /** left/top/width/height in the artwork's native pixel space. */
    protected function fieldsFor(Certificate $certificate): array
    {
        $type = $certificate->type;

        $studentName   = $certificate->student?->full_name ?? '';
        $franchiseName = $certificate->franchise?->name ?? '';
        $levelName     = $certificate->level?->title ?? '';
        $placeVal      = $certificate->place ?: ($certificate->franchise?->city ?? '');
        $dateVal       = optional($certificate->issued_at)->format('d F Y');

        $navyItalic = [28, 46, 99];
        $navyBold   = [18, 24, 47];
        $gold       = [200, 134, 15];

        // 'box' is the mask rectangle erased before drawing (must fully cover
        // the artwork's fill-in blank/underline or placeholder text with no
        // remainder). 'pad' is an additional left-inset applied only to where
        // the text itself starts — calibrated per field (via pixel-scanning the
        // artwork's own baked placeholder/underline) so the value starts right
        // where the original design's placeholder did, no further. Fields that
        // are followed by more static artwork text on the same line (e.g. "…
        // level successfully", "… center.", "… Trophy") carry a 'trailing' spec:
        // the old baked words are masked out too and redrawn immediately after
        // the dynamic value ends (+ 'gap' px), so spacing stays natural — a
        // single space — no matter how long or short the value is.
        return match ($type) {
            Certificate::TYPE_PARTICIPATION, Certificate::TYPE_COMPETITION => [
                ['box' => [448, 635, 1442, 692], 'pad' => 8, 'text' => $studentName,   'font' => 'italic', 'size' => 32, 'color' => $navyItalic, 'align' => 'left'],
                [
                    'box' => [400, 695, 1304, 750], 'pad' => 8, 'text' => $franchiseName, 'font' => 'italic', 'size' => 30, 'color' => $navyItalic, 'align' => 'left',
                    'trailing' => ['box' => [1311, 695, 1435, 750], 'text' => 'Centre for', 'font' => 'italic', 'size' => 30, 'color' => $navyItalic, 'gap' => 14],
                ],
                [
                    'box' => [501, 753, 1209, 808], 'pad' => 8, 'text' => $levelName,     'font' => 'italic', 'size' => 30, 'color' => $navyItalic, 'align' => 'left',
                    'trailing' => ['box' => [1215, 753, 1434, 808], 'text' => 'Level Competition', 'font' => 'italic', 'size' => 30, 'color' => $navyItalic, 'gap' => 14],
                ],
                ['box' => [337, 903, 679, 955],  'pad' => 8, 'text' => $dateVal,       'font' => 'italic', 'size' => 28, 'color' => $navyItalic, 'align' => 'left'],
                ['box' => [337, 963, 679, 1015], 'pad' => 9, 'text' => $placeVal,      'font' => 'italic', 'size' => 28, 'color' => $navyItalic, 'align' => 'left'],
            ],
            Certificate::TYPE_LEVEL_UP, Certificate::TYPE_LEVEL_COMPLETION => [
                ['box' => [433, 635, 1427, 694], 'pad' => 9, 'text' => $studentName,   'font' => 'italic', 'size' => 32, 'color' => $navyItalic, 'align' => 'left'],
                [
                    'box' => [433, 703, 1197, 760], 'pad' => 9, 'text' => $levelName,     'font' => 'italic', 'size' => 30, 'color' => $navyItalic, 'align' => 'left',
                    'trailing' => ['box' => [1207, 703, 1422, 760], 'text' => 'level successfully', 'font' => 'italic', 'size' => 30, 'color' => $navyItalic, 'gap' => 14],
                ],
                [
                    'box' => [899, 751, 1339, 818], 'pad' => 9, 'text' => $franchiseName, 'font' => 'italic', 'size' => 30, 'color' => $navyItalic, 'align' => 'left',
                    'trailing' => ['box' => [1343, 751, 1422, 818], 'text' => 'center.', 'font' => 'italic', 'size' => 30, 'color' => $navyItalic, 'gap' => 14],
                ],
                ['box' => [315, 873, 551, 922],  'pad' => 9,  'text' => $dateVal,       'font' => 'italic', 'size' => 28, 'color' => $navyItalic, 'align' => 'left'],
                ['box' => [316, 926, 550, 975],  'pad' => 15, 'text' => $placeVal,      'font' => 'italic', 'size' => 28, 'color' => $navyItalic, 'align' => 'left'],
            ],
            Certificate::TYPE_CHAMPION => [
                ['box' => [742, 1000, 1520, 1120], 'pad' => 10, 'baseline' => 1085, 'text' => $studentName,   'font' => 'bold', 'size' => 34, 'color' => $navyBold, 'align' => 'left'],
                ['box' => [725, 1108, 1520, 1221], 'pad' => 9,  'baseline' => 1183, 'text' => $franchiseName, 'font' => 'bold', 'size' => 34, 'color' => $navyBold, 'align' => 'left'],
                [
                    'box' => [779, 1250, 1075, 1334], 'pad' => 11, 'baseline' => 1305, 'text' => 'Champion ' . ($certificate->rank ?: ''), 'font' => 'bold', 'size' => 32, 'color' => $gold, 'align' => 'left',
                    'trailing' => ['box' => [1089, 1250, 1233, 1334], 'text' => 'Trophy', 'font' => 'italic', 'size' => 30, 'color' => $navyItalic, 'gap' => 14, 'baseline' => 1305],
                ],
                ['box' => [679, 1372, 1520, 1448], 'pad' => 7, 'baseline' => 1419, 'text' => $levelName,     'font' => 'bold', 'size' => 32, 'color' => $navyBold, 'align' => 'left'],
                ['box' => [285, 1722, 645, 1790],  'text' => $placeVal,      'font' => 'regular', 'size' => 28, 'color' => $navyBold, 'align' => 'center'],
                ['box' => [285, 1842, 645, 1895],  'text' => $dateVal,       'font' => 'regular', 'size' => 28, 'color' => $navyBold, 'align' => 'center'],
            ],
            Certificate::TYPE_WINNER => [
                ['box' => [742, 1000, 1520, 1120], 'pad' => 10, 'baseline' => 1085, 'text' => $studentName,   'font' => 'bold', 'size' => 34, 'color' => $navyBold, 'align' => 'left'],
                ['box' => [725, 1108, 1520, 1221], 'pad' => 9,  'baseline' => 1183, 'text' => $franchiseName, 'font' => 'bold', 'size' => 34, 'color' => $navyBold, 'align' => 'left'],
                ['box' => [679, 1372, 1520, 1448], 'pad' => 7, 'baseline' => 1419, 'text' => $levelName,     'font' => 'bold', 'size' => 32, 'color' => $navyBold, 'align' => 'left'],
                ['box' => [285, 1722, 645, 1790],  'text' => $placeVal,      'font' => 'regular', 'size' => 28, 'color' => $navyBold, 'align' => 'center'],
                ['box' => [285, 1842, 645, 1895],  'text' => $dateVal,       'font' => 'regular', 'size' => 28, 'color' => $navyBold, 'align' => 'center'],
            ],
            default => [],
        };
    }

    protected function maskColorFor(string $type): array
    {
        return match ($type) {
            Certificate::TYPE_CHAMPION => [250, 249, 246],
            Certificate::TYPE_WINNER => [250, 250, 250],
            default => [248, 244, 241],
        };
    }

    protected function fontPath(string $font): string
    {
        $base = base_path('vendor/dompdf/dompdf/lib/fonts/');

        return match ($font) {
            'bold' => $base . 'DejaVuSerif-Bold.ttf',
            'italic' => $base . 'DejaVuSerif-Italic.ttf',
            default => $base . 'DejaVuSerif.ttf',
        };
    }

    /**
     * Composite the certificate's dynamic fields onto its background artwork.
     * Returns raw PNG binary. Background artwork is rendered at 2x the size
     * used elsewhere (native PDF-page resolution) for crisper text.
     */
    public function compose(Certificate $certificate): string
    {
        $bgPath = public_path('images/certificates/' . Certificate::backgroundFileFor($certificate->type));

        $im = @imagecreatefrompng($bgPath);
        if (! $im) {
            // Fall back to a blank canvas matching the expected size so the
            // PDF still generates even if the artwork file is missing.
            $isPortrait = in_array($certificate->type, [Certificate::TYPE_CHAMPION, Certificate::TYPE_WINNER], true);
            $im = imagecreatetruecolor($isPortrait ? 1581 : 1685, $isPortrait ? 2238 : 1191);
            $white = imagecolorallocate($im, 255, 255, 255);
            imagefill($im, 0, 0, $white);
        }

        imagesavealpha($im, true);

        foreach ($this->fieldsFor($certificate) as $field) {
            [$x0, $y0, $x1, $y1] = $field['box'];
            [$mr, $mg, $mb] = $this->maskColorFor($certificate->type);
            $maskColor = imagecolorallocate($im, $mr, $mg, $mb);
            imagefilledrectangle($im, $x0, $y0, $x1, $y1, $maskColor);

            $text = trim((string) $field['text']);
            if ($text === '') {
                continue;
            }

            [$r, $g, $b] = $field['color'];
            $textColor = imagecolorallocate($im, $r, $g, $b);
            $fontFile  = $this->fontPath($field['font']);
            $size      = $field['size'];
            $pad       = $field['pad'] ?? 0;
            $textX0    = $x0 + $pad;

            $bbox = imagettfbbox($size, 0, $fontFile, $text);
            $textWidth = abs($bbox[2] - $bbox[0]);

            $boxWidth = $x1 - $textX0;
            $startX = match ($field['align']) {
                'center' => $x0 + max(0, (($x1 - $x0) - $textWidth) / 2),
                default => $textX0,
            };

            // Shrink to fit rather than overflow the box for unusually long values.
            if ($textWidth > $boxWidth && $textWidth > 0) {
                $size = max(14, (int) floor($size * $boxWidth / $textWidth));
                $bbox = imagettfbbox($size, 0, $fontFile, $text);
                $textWidth = abs($bbox[2] - $bbox[0]);
                $startX = match ($field['align']) {
                    'center' => $x0 + max(0, (($x1 - $x0) - $textWidth) / 2),
                    default => $textX0,
                };
            }

            $baselineY = $field['baseline'] ?? ($y0 + (($y1 - $y0) * 0.78));

            imagettftext($im, $size, 0, (int) $startX, (int) $baselineY, $textColor, $fontFile, $text);

            if (! empty($field['trailing'])) {
                $trailing = $field['trailing'];
                [$tx0, $ty0, $tx1, $ty1] = $trailing['box'];
                imagefilledrectangle($im, $tx0, $ty0, $tx1, $ty1, $maskColor);

                [$tr, $tg, $tb] = $trailing['color'];
                $trailingColor = imagecolorallocate($im, $tr, $tg, $tb);
                $trailingFont  = $this->fontPath($trailing['font']);
                $trailingX     = $startX + $textWidth + ($trailing['gap'] ?? 14);
                $trailingBaselineY = $trailing['baseline'] ?? $baselineY;

                imagettftext($im, $trailing['size'], 0, (int) $trailingX, (int) $trailingBaselineY, $trailingColor, $trailingFont, $trailing['text']);
            }
        }

        ob_start();
        imagepng($im);
        $bytes = ob_get_clean();
        imagedestroy($im);

        return $bytes;
    }

    public function composeDataUri(Certificate $certificate): string
    {
        return 'data:image/png;base64,' . base64_encode($this->compose($certificate));
    }
}
