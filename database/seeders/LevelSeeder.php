<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    public function run(): void
    {
        // 11-level structure approved 2026-06 client meeting:
        // Junior 1–4 (numbers 1–4) followed by Regular 1–7 (numbers 5–11).
        // Junior 4 students move directly to Regular 3 — see PromotionController.
        $levels = [
            ['number' => 1,  'title' => 'Junior 1',   'fee_per_month' => 800],
            ['number' => 2,  'title' => 'Junior 2',   'fee_per_month' => 900],
            ['number' => 3,  'title' => 'Junior 3',   'fee_per_month' => 1000],
            ['number' => 4,  'title' => 'Junior 4',   'fee_per_month' => 1100],
            ['number' => 5,  'title' => 'Regular 1',  'fee_per_month' => 1200],
            ['number' => 6,  'title' => 'Regular 2',  'fee_per_month' => 1300],
            ['number' => 7,  'title' => 'Regular 3',  'fee_per_month' => 1400],
            ['number' => 8,  'title' => 'Regular 4',  'fee_per_month' => 1500],
            ['number' => 9,  'title' => 'Regular 5',  'fee_per_month' => 1600],
            ['number' => 10, 'title' => 'Regular 6',  'fee_per_month' => 1700],
            ['number' => 11, 'title' => 'Regular 7',  'fee_per_month' => 1800],
        ];

        foreach ($levels as $data) {
            Level::firstOrCreate(['number' => $data['number']], [
                'title'         => $data['title'],
                'slug'          => 'level-' . $data['number'],
                'description'   => "Level {$data['number']}: {$data['title']}",
                'fee_per_month' => $data['fee_per_month'],
                'is_active'     => true,
                'sort_order'    => $data['number'],
            ]);
        }
    }
}
