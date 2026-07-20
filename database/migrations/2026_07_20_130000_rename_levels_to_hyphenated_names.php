<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Standardizes the 11 level names to the hyphenated form used consistently
 * across the app (Junior-1..4, Regular-1..7), replacing the space-separated
 * form ("Junior 1") seeded by the 2026-06-11 restructure. In-place update by
 * `number` — ids/relationships are untouched.
 */
return new class extends Migration
{
    private array $names = [
        1  => 'Junior-1',
        2  => 'Junior-2',
        3  => 'Junior-3',
        4  => 'Junior-4',
        5  => 'Regular-1',
        6  => 'Regular-2',
        7  => 'Regular-3',
        8  => 'Regular-4',
        9  => 'Regular-5',
        10 => 'Regular-6',
        11 => 'Regular-7',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('levels')) {
            return;
        }

        foreach ($this->names as $number => $title) {
            DB::table('levels')->where('number', $number)->update([
                'title'       => $title,
                'description' => $title . ' — Abacus Mental Math curriculum level.',
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        $old = [
            1 => 'Junior 1', 2 => 'Junior 2', 3 => 'Junior 3', 4 => 'Junior 4',
            5 => 'Regular 1', 6 => 'Regular 2', 7 => 'Regular 3', 8 => 'Regular 4',
            9 => 'Regular 5', 10 => 'Regular 6', 11 => 'Regular 7',
        ];

        foreach ($old as $number => $title) {
            DB::table('levels')->where('number', $number)->update([
                'title'       => $title,
                'description' => 'Level ' . $number . ': ' . $title,
                'updated_at'  => now(),
            ]);
        }
    }
};
