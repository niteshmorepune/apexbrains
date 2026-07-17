<?php

namespace Database\Seeders\Concerns;

/**
 * Generates plausible arithmetic MCQ rows for a given (category, type) pair,
 * used by the Regular/Competition bank content seeders so every seeded
 * category/type combo has a usable demo question pool. "Type" names encode
 * digit-count and row-count (e.g. "2 Digit - 5 Rows", "3 Digit X 1 Digit",
 * "4 Digit / 1 Digit") which this parses via regex to shape the operands.
 */
trait GeneratesArithmeticQuestions
{
    /**
     * @return array{question_text:string,option_a:string,option_b:string,option_c:string,option_d:string,correct_answer:string,difficulty:string}
     */
    private function generateQuestion(string $categoryName, string $typeName, int $seed): array
    {
        mt_srand($seed);

        if ($categoryName === 'Multiplication' && preg_match('/(\d+)\s*Digit\s*[Xx×]\s*(\d+)\s*Digit/', $typeName, $m)) {
            $a = $this->randomOfLength((int) $m[1]);
            $b = $this->randomOfLength((int) $m[2]);
            $answer = $a * $b;
            $text = "{$a} × {$b} = ?";
            $difficulty = ((int) $m[1] + (int) $m[2]) >= 6 ? 'hard' : 'medium';

            return $this->buildMcq($text, $answer, $difficulty);
        }

        if ($categoryName === 'Division' && preg_match('/(\d+)(?:\s*&\s*\d+)?\s*Digit\s*\/\s*(\d+)\s*Digit/', $typeName, $m)) {
            $divisor = max(1, $this->randomOfLength((int) $m[2]));
            $quotient = $this->randomOfLength(max(1, (int) $m[1] - (int) $m[2]) ?: 1);
            $dividend = $divisor * $quotient;
            $text = "{$dividend} ÷ {$divisor} = ?";
            $difficulty = (int) $m[1] >= 4 ? 'hard' : 'medium';

            return $this->buildMcq($text, $quotient, $difficulty);
        }

        if ($categoryName === 'Decimals') {
            $rows = $this->extractRows($typeName) ?: 3;
            $parts = [];
            $sum = 0.0;
            for ($i = 0; $i < $rows; $i++) {
                $whole = mt_rand(1, 9);
                $decimal = mt_rand(1, 9);
                $value = (float) "{$whole}.{$decimal}";
                $parts[] = number_format($value, 1);
                $sum += $value;
            }
            $sum = round($sum, 1);
            $text = implode(' + ', $parts) . ' = ?';

            return $this->buildMcq($text, $sum, 'medium', decimals: true);
        }

        // Default: addition family (Without Partners, Small Partners,
        // Big & Small Partners, Grouping, Small & Big Partners).
        $digits = $this->extractDigits($typeName) ?: 1;
        $rows = $this->extractRows($typeName) ?: 3;

        $operands = [];
        $sum = 0;
        for ($i = 0; $i < max(2, $rows); $i++) {
            $n = $this->randomOfLength($digits);
            $operands[] = $n;
            $sum += $n;
        }

        $text = implode(' + ', $operands) . ' = ?';
        $difficulty = $rows >= 8 || $digits >= 3 ? 'hard' : ($rows >= 5 || $digits >= 2 ? 'medium' : 'easy');

        return $this->buildMcq($text, $sum, $difficulty);
    }

    private function randomOfLength(int $digits): int
    {
        $digits = max(1, $digits);
        $min = $digits === 1 ? 1 : (int) str_pad('1', $digits, '0');
        $max = (int) str_pad('', $digits, '9');

        return mt_rand($min, $max);
    }

    private function extractDigits(string $typeName): ?int
    {
        return preg_match('/(\d+)\s*Digit/', $typeName, $m) ? (int) $m[1] : null;
    }

    private function extractRows(string $typeName): ?int
    {
        return preg_match('/(\d+)\s*Rows?/', $typeName, $m) ? (int) $m[1] : null;
    }

    private function buildMcq(string $text, int|float $answer, string $difficulty, bool $decimals = false): array
    {
        $fmt = fn ($v) => $decimals ? number_format((float) $v, 1) : (string) $v;

        $wrongs = $decimals
            ? [$answer + 0.1, $answer - 0.1, $answer + 1.0]
            : [$answer + 1, $answer - 1, $answer + 2];

        $options = array_merge([$answer], $wrongs);
        shuffle($options);
        $letters = ['a', 'b', 'c', 'd'];
        $correctIndex = array_search($answer, $options, true);

        return [
            'question_text' => $text,
            'option_a' => $fmt($options[0]),
            'option_b' => $fmt($options[1]),
            'option_c' => $fmt($options[2]),
            'option_d' => $fmt($options[3]),
            'correct_answer' => $letters[$correctIndex],
            'difficulty' => $difficulty,
        ];
    }
}
