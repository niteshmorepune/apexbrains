<?php

namespace Database\Seeders;

use App\Models\QuestionBank;
use Illuminate\Database\Seeder;

class PracticeQuestionsSeeder extends Seeder
{
    /**
     * Seeds the approved Question Bank with abacus-style MCQs. Practice Papers
     * are built from this pool by CompetitionPracticePapersSeeder.
     */
    public function run(): void
    {
        // Idempotent: skip if an approved bank already exists.
        if (QuestionBank::where('status', 'approved')->exists()) {
            return;
        }

        foreach ($this->generateQuestions() as $q) {
            QuestionBank::firstOrCreate(['question_text' => $q['question_text']], $q);
        }
    }

    private function generateQuestions(): array
    {
        $questions = [];
        $categories = ['competition', 'level_practice', 'class_practice'];

        // Single-digit additions
        for ($a = 1; $a <= 9; $a++) {
            for ($b = 1; $b <= 9; $b++) {
                $ans = $a + $b;
                $questions[] = $this->mcq(
                    "{$a} + {$b} = ?",
                    (string)$ans,
                    $ans + 1,
                    $ans - 1,
                    $ans + 2,
                    'easy',
                    $categories[0]
                );
            }
        }

        // Two-digit additions
        foreach ([[12,34],[23,45],[56,37],[41,29],[63,18],[77,14],[85,12],[39,42],[67,23],[48,35]] as [$a,$b]) {
            $ans = $a + $b;
            $questions[] = $this->mcq("{$a} + {$b} = ?", (string)$ans, $ans+1, $ans-1, $ans+10, 'easy', $categories[1]);
        }

        // Subtractions
        foreach ([[50,23],[75,38],[100,47],[63,29],[84,56],[91,34],[72,45],[88,61],[55,28],[99,43]] as [$a,$b]) {
            $ans = $a - $b;
            $questions[] = $this->mcq("{$a} − {$b} = ?", (string)$ans, $ans+1, $ans-1, $ans+5, 'easy', $categories[2]);
        }

        // Multiplications
        foreach ([[3,7],[4,8],[5,6],[6,9],[7,8],[4,9],[3,8],[5,7],[6,6],[8,8]] as [$a,$b]) {
            $ans = $a * $b;
            $questions[] = $this->mcq("{$a} × {$b} = ?", (string)$ans, $ans+1, $ans-1, $ans+$b, 'medium', $categories[0]);
        }

        // Larger multiplications
        foreach ([[12,11],[15,13],[18,12],[21,11],[14,14],[13,17],[16,15],[19,12],[22,13],[25,14]] as [$a,$b]) {
            $ans = $a * $b;
            $questions[] = $this->mcq("{$a} × {$b} = ?", (string)$ans, $ans+2, $ans-2, $ans+10, 'medium', $categories[1]);
        }

        // Three-digit additions
        foreach ([[123,456],[234,321],[456,231],[178,293],[345,267],[412,389],[567,234],[890,113],[721,189],[645,278]] as [$a,$b]) {
            $ans = $a + $b;
            $questions[] = $this->mcq("{$a} + {$b} = ?", (string)$ans, $ans+1, $ans-1, $ans+10, 'medium', $categories[2]);
        }

        // Divisions
        foreach ([[56,7],[72,8],[63,9],[48,6],[35,5],[81,9],[64,8],[45,5],[54,6],[96,8]] as [$a,$b]) {
            $ans = $a / $b;
            $questions[] = $this->mcq("{$a} ÷ {$b} = ?", (string)$ans, $ans+1, $ans-1, $ans+2, 'medium', $categories[0]);
        }

        // Hard: mixed operations
        foreach ([
            ['(15 + 23) × 2 = ?', 76, 77, 75, 80],
            ['(100 − 37) + 24 = ?', 87, 88, 86, 90],
            ['(8 × 9) − 15 = ?', 57, 58, 56, 60],
            ['(144 ÷ 12) + 8 = ?', 20, 21, 19, 24],
            ['(25 × 4) − 37 = ?', 63, 64, 62, 70],
            ['(56 + 78) − 29 = ?', 105, 106, 104, 110],
            ['(9 × 12) + 14 = ?', 122, 123, 121, 130],
            ['(200 − 75) + 38 = ?', 163, 164, 162, 170],
            ['(7 × 13) − 18 = ?', 73, 74, 72, 80],
            ['(84 ÷ 4) × 3 = ?', 63, 64, 62, 70],
        ] as [$q, $correct, $w1, $w2, $w3]) {
            $questions[] = $this->mcq($q, (string)$correct, $w1, $w2, $w3, 'hard', $categories[0]);
        }

        // Abacus bead counting
        foreach ([
            ['Abacus: 3 beads in units, 2 in tens = ?', 23, 32, 203, 230],
            ['Abacus: 5 beads in units, 1 in tens = ?', 15, 51, 105, 510],
            ['Abacus: 4 beads in hundreds, 3 in tens, 2 in units = ?', 432, 234, 342, 423],
            ['Abacus: 7 beads in units, 0 in tens, 1 in hundreds = ?', 107, 170, 710, 701],
            ['Abacus: 9 beads in units, 9 in tens = ?', 99, 9, 909, 990],
        ] as [$q, $correct, $w1, $w2, $w3]) {
            $questions[] = $this->mcq($q, (string)$correct, $w1, $w2, $w3, 'hard', $categories[1]);
        }

        return $questions;
    }

    private function mcq(string $text, string $correct, $w1, $w2, $w3, string $diff, string $cat): array
    {
        // Shuffle options so correct answer isn't always 'a'
        $opts   = [(string)$correct, (string)$w1, (string)$w2, (string)$w3];
        shuffle($opts);
        $letter = array_search((string)$correct, $opts);
        $map    = ['a', 'b', 'c', 'd'];

        return [
            'question_text'     => $text,
            'type'              => 'mcq',
            'option_a'          => $opts[0],
            'option_b'          => $opts[1],
            'option_c'          => $opts[2],
            'option_d'          => $opts[3],
            'correct_answer'    => $map[$letter],
            'difficulty'        => $diff,
            'status'            => 'approved',
            'question_category' => $cat,
        ];
    }
}
