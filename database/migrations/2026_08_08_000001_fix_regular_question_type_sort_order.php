<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ORDER = [
        '1 Digit - 5 Rows' => 0,
        '1 Digit - 8 Rows' => 1,
        '1 Digit - 10 Rows' => 2,
        '2 Digit - 3 Rows' => 3,
        '2 Digit - 5 Rows' => 4,
        '2 Digit - 7 Rows' => 5,
        '2 Digit - 10 Rows' => 6,
        '3 Digit - 3 Rows' => 7,
        '3 Digit - 4 Rows' => 8,
    ];

    public function up(): void
    {
        foreach (self::ORDER as $name => $order) {
            DB::table('regular_question_types')
                ->where('name', $name)
                ->update(['sort_order' => $order]);
        }
    }

    public function down(): void
    {
        // Historical seed order is not recoverable; no-op.
    }
};
