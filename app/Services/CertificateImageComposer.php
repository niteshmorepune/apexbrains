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

        // Only the first character is forced to uppercase — the rest of the
        // name is displayed exactly as stored (so "ARJUN PATIL" is untouched
        // and "madhuri test test" becomes "Madhuri test test").
        $studentName   = ucfirst($certificate->student?->full_name ?? '');
        $franchiseName = $certificate->franchise?->name ?? '';
        $levelName     = $certificate->level?->title ?? '';
        $placeVal      = $certificate->place ?: ($certificate->franchise?->city ?? '');
        $dateVal       = optional($certificate->issued_at)->format('d F Y');
        // Participation/Competition certs show the actual competition date
        // (not the issuance date) — scoped to this variable only so it
        // doesn't change Level Up/Champion/Winner's existing date behavior.
        $competitionDateVal = optional($certificate->competition?->start_date ?? $certificate->issued_at)->format('d F Y');

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
        //
        // 2026-08-17 ROUND 4 exact-design replacement: client supplied all 4
        // templates fresh (blank fill-in versions, new logo+signature already
        // baked into the artwork by the client) — every field below was
        // recalibrated from scratch via pixel-scanning + gridded-overlay
        // crops, native canvas is now level-up.png 1024×1536, participation/
        // champion/winner.png 1054×1492 (all portrait — see paperConfigFor()
        // in Certificate.php, updated to match the new portrait aspect ratio).
        return match ($type) {
            // Level Up / Participation: each line is centered as a single
            // unit (static words + the fill-in value together) — the whole
            // line is masked and redrawn as one composed string per render,
            // so a short name and a long name both land dead-center with
            // natural spacing.
            Certificate::TYPE_PARTICIPATION, Certificate::TYPE_COMPETITION => [
                ['box' => [60, 818, 994, 910], 'text' => 'Master / Miss ' . $studentName, 'font' => 'italic', 'size' => 36, 'color' => $navyItalic, 'align' => 'center', 'baseline' => 865],
                ['box' => [60, 880, 994, 975], 'text' => 'studying at ' . $franchiseName . ' Center for', 'font' => 'italic', 'size' => 28, 'color' => $navyItalic, 'align' => 'center', 'baseline' => 938],
                ['box' => [60, 960, 994, 1055], 'text' => 'participating in the ' . $levelName . ' Online Competition', 'font' => 'italic', 'size' => 28, 'color' => $navyItalic, 'align' => 'center', 'baseline' => 1018],
                // Date/Place values only — "Date :"/"Place :" labels stay baked into the artwork.
                ['box' => [215, 1204, 690, 1240], 'pad' => 8, 'text' => $competitionDateVal, 'font' => 'italic', 'size' => 26, 'color' => $navyItalic, 'align' => 'left', 'baseline' => 1230],
                // Place sits on its own baked "Place :" line further down than
                // Date's — pixel-measured against the artwork's own label
                // baseline (~1293), not the old Date-relative guess (~1270)
                // that made the value float above its label.
                ['box' => [215, 1265, 690, 1310], 'pad' => 8, 'text' => $placeVal, 'font' => 'italic', 'size' => 26, 'color' => $navyItalic, 'align' => 'left', 'baseline' => 1293],
            ],
            Certificate::TYPE_LEVEL_UP, Certificate::TYPE_LEVEL_COMPLETION => [
                ['box' => [60, 745, 964, 815], 'text' => 'Master / Miss ' . $studentName, 'font' => 'italic', 'size' => 38, 'color' => $navyItalic, 'align' => 'center', 'baseline' => 800],
                ['box' => [60, 838, 964, 903], 'text' => 'has completed ' . $levelName . ' level successfully', 'font' => 'italic', 'size' => 30, 'color' => $navyItalic, 'align' => 'center', 'baseline' => 888],
                ['box' => [60, 985, 964, 1049], 'text' => 'at ' . $franchiseName . ' center.', 'font' => 'italic', 'size' => 28, 'color' => $navyItalic, 'align' => 'center', 'baseline' => 1033],
                ['box' => [190, 1148, 700, 1198], 'pad' => 10, 'text' => $dateVal,  'font' => 'italic', 'size' => 26, 'color' => $navyItalic, 'align' => 'left', 'baseline' => 1183],
                // Pixel-measured against the artwork's own "Place :" label
                // baseline (~1249) — the old baseline (1240) rendered the
                // value visibly above the label line. Right edge trimmed to
                // 683 (was 700) — "Managing Director" starts at x≈692 on this
                // artwork and the old wider box was masking its left stroke.
                ['box' => [190, 1210, 683, 1268], 'pad' => 10, 'text' => $placeVal, 'font' => 'italic', 'size' => 26, 'color' => $navyItalic, 'align' => 'left', 'baseline' => 1249],
            ],
            // Champion / Winner: the static prefixes ("Master / Miss",
            // "studying at", "has been awarded", "for") stay baked into the
            // artwork untouched — only the value is masked+drawn, left-aligned
            // right after where the prefix ends. Unlike Level Up/Participation,
            // recentering the whole line isn't needed here (matches how the
            // artwork itself is laid out — confirmed via render-and-compare).
            Certificate::TYPE_CHAMPION => [
                ['box' => [555, 755, 994, 815], 'pad' => 6, 'baseline' => 800, 'text' => $studentName,   'font' => 'bold', 'size' => 34, 'color' => $navyBold, 'align' => 'left'],
                ['box' => [480, 835, 994, 895], 'pad' => 6, 'baseline' => 880, 'text' => $franchiseName, 'font' => 'bold', 'size' => 30, 'color' => $navyBold, 'align' => 'left'],
                [
                    'box' => [520, 915, 700, 975], 'baseline' => 960, 'text' => 'Champion ' . ($certificate->rank ?: ''), 'font' => 'bold', 'size' => 30, 'color' => $gold, 'align' => 'left',
                    'trailing' => ['box' => [700, 915, 860, 975], 'text' => 'Trophy', 'font' => 'italic', 'size' => 28, 'color' => $navyItalic, 'gap' => 10, 'baseline' => 960],
                ],
                ['box' => [455, 995, 994, 1055], 'pad' => 28, 'baseline' => 1040, 'text' => $levelName, 'font' => 'bold', 'size' => 28, 'color' => $navyBold, 'align' => 'left'],
                ['box' => [200, 1200, 690, 1245], 'pad' => 8, 'baseline' => 1228, 'text' => $placeVal, 'font' => 'regular', 'size' => 28, 'color' => $navyBold, 'align' => 'left'],
                ['box' => [200, 1240, 690, 1300], 'pad' => 8, 'baseline' => 1285, 'text' => $dateVal,  'font' => 'regular', 'size' => 28, 'color' => $navyBold, 'align' => 'left'],
            ],
            Certificate::TYPE_WINNER => [
                ['box' => [510, 725, 994, 785], 'pad' => 6, 'baseline' => 768, 'text' => $studentName,   'font' => 'bold', 'size' => 34, 'color' => $navyBold, 'align' => 'left'],
                ['box' => [475, 805, 994, 865], 'pad' => 6, 'baseline' => 848, 'text' => $franchiseName, 'font' => 'bold', 'size' => 30, 'color' => $navyBold, 'align' => 'left'],
                // "WINNER" (unlike Champion's rank number) never varies per student, so it stays baked into the artwork — no field for it here.
                ['box' => [440, 965, 994, 1025], 'pad' => 28, 'baseline' => 1008, 'text' => $levelName, 'font' => 'bold', 'size' => 28, 'color' => $navyBold, 'align' => 'left'],
                ['box' => [200, 1170, 690, 1215], 'pad' => 8, 'baseline' => 1198, 'text' => $placeVal, 'font' => 'regular', 'size' => 28, 'color' => $navyBold, 'align' => 'left'],
                ['box' => [200, 1210, 690, 1270], 'pad' => 8, 'baseline' => 1255, 'text' => $dateVal,  'font' => 'regular', 'size' => 28, 'color' => $navyBold, 'align' => 'left'],
            ],
            default => [],
        };
    }

    /**
     * Target rectangle (native artwork pixel space) the Managing Director
     * signature is drawn into — the same position previously occupied by the
     * signature baked into each background PNG (pixel-measured against the
     * 2026-08-17 artwork before it was replaced with a blank-signature
     * version). Contain-fit + centered, so the signature's own aspect ratio
     * is preserved rather than stretched.
     */
    protected function signatureBoxFor(string $type): array
    {
        return match ($type) {
            Certificate::TYPE_LEVEL_UP, Certificate::TYPE_LEVEL_COMPLETION => [700, 1090, 880, 1215],
            Certificate::TYPE_PARTICIPATION, Certificate::TYPE_COMPETITION => [730, 1175, 905, 1288],
            Certificate::TYPE_CHAMPION => [745, 1160, 915, 1278],
            Certificate::TYPE_WINNER => [760, 1105, 920, 1240],
            default => [730, 1175, 905, 1288],
        };
    }

    /**
     * Composite the vendored signature image (a transparent PNG, pre-cropped
     * to its own ink bounding box) onto the certificate, contain-fit within
     * signatureBoxFor()'s rectangle.
     */
    protected function compositeSignature($im, Certificate $certificate): void
    {
        $sigPath = public_path('images/certificates/signature.png');
        $sig = @imagecreatefrompng($sigPath);
        if (! $sig) {
            return;
        }

        [$x0, $y0, $x1, $y1] = $this->signatureBoxFor($certificate->type);
        $boxW = $x1 - $x0;
        $boxH = $y1 - $y0;

        $sigW = imagesx($sig);
        $sigH = imagesy($sig);
        $scale = min($boxW / $sigW, $boxH / $sigH);
        $destW = max(1, (int) round($sigW * $scale));
        $destH = max(1, (int) round($sigH * $scale));
        $destX = (int) round($x0 + ($boxW - $destW) / 2);
        $destY = (int) round($y0 + ($boxH - $destH) / 2);

        $resized = imagecreatetruecolor($destW, $destH);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefill($resized, 0, 0, $transparent);
        imagecopyresampled($resized, $sig, 0, 0, 0, 0, $destW, $destH, $sigW, $sigH);

        imagealphablending($im, true);
        imagecopy($im, $resized, $destX, $destY, 0, 0, $destW, $destH);

        imagedestroy($resized);
        imagedestroy($sig);
    }

    protected function maskColorFor(string $type): array
    {
        return match ($type) {
            Certificate::TYPE_CHAMPION => [246, 244, 243],
            Certificate::TYPE_WINNER => [250, 250, 250],
            Certificate::TYPE_PARTICIPATION, Certificate::TYPE_COMPETITION => [251, 251, 250],
            Certificate::TYPE_LEVEL_UP, Certificate::TYPE_LEVEL_COMPLETION => [252, 252, 252],
            default => [249, 247, 245],
        };
    }

    /**
     * All 4 flow templates use a single decorative script font (Vivaldi,
     * vendored into resources/fonts/certificates/ since no bold/italic
     * variant exists for it — every 'font' role maps to the same file).
     */
    protected function fontPath(string $font): string
    {
        return resource_path('fonts/certificates/Vivaldi.ttf');
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
            // PDF still generates even if the artwork file is missing. All 4
            // templates are portrait as of the 2026-08-17 redesign.
            $isLevelUp = in_array($certificate->type, [Certificate::TYPE_LEVEL_UP, Certificate::TYPE_LEVEL_COMPLETION], true);
            $im = imagecreatetruecolor($isLevelUp ? 1024 : 1054, $isLevelUp ? 1536 : 1492);
            $white = imagecolorallocate($im, 255, 255, 255);
            imagefill($im, 0, 0, $white);
        }

        imagesavealpha($im, true);

        foreach ($this->fieldsFor($certificate) as $field) {
            [$x0, $y0, $x1, $y1] = $field['box'];
            [$mr, $mg, $mb] = $this->maskColorFor($certificate->type);
            $maskColor = imagecolorallocate($im, $mr, $mg, $mb);

            // 'maskBox' (if present) is the rectangle actually erased — narrower
            // than 'box' when 'box' has to stay wide/symmetric for center-alignment
            // math but the artwork has something to the right (e.g. the signature)
            // that a full-width erase would wipe out. 'box' still drives text
            // positioning/centering/shrink-to-fit either way.
            [$mx0, $my0, $mx1, $my1] = $field['maskBox'] ?? $field['box'];
            imagefilledrectangle($im, $mx0, $my0, $mx1, $my1, $maskColor);

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

        $this->compositeSignature($im, $certificate);

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
