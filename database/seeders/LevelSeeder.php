<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['number' => 1,  'title' => 'Beginner 1',     'fee_per_month' => 800],
            ['number' => 2,  'title' => 'Beginner 2',     'fee_per_month' => 900],
            ['number' => 3,  'title' => 'Elementary 1',   'fee_per_month' => 1000],
            ['number' => 4,  'title' => 'Elementary 2',   'fee_per_month' => 1000],
            ['number' => 5,  'title' => 'Pre-Junior 1',   'fee_per_month' => 1100],
            ['number' => 6,  'title' => 'Pre-Junior 2',   'fee_per_month' => 1100],
            ['number' => 7,  'title' => 'Junior 1',       'fee_per_month' => 1200],
            ['number' => 8,  'title' => 'Junior 2',       'fee_per_month' => 1200],
            ['number' => 9,  'title' => 'Intermediate 1', 'fee_per_month' => 1400],
            ['number' => 10, 'title' => 'Intermediate 2', 'fee_per_month' => 1400],
            ['number' => 11, 'title' => 'Senior 1',       'fee_per_month' => 1600],
            ['number' => 12, 'title' => 'Senior 2',       'fee_per_month' => 1600],
            ['number' => 13, 'title' => 'Expert 1',       'fee_per_month' => 1800],
            ['number' => 14, 'title' => 'Expert 2',       'fee_per_month' => 1800],
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
