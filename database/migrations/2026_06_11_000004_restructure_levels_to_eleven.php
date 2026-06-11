<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Restructures the level ladder to the 11-level scheme approved in the 2026-06
 * client meeting: Junior 1–4 (numbers 1–4) + Regular 1–7 (numbers 5–11).
 *
 * NON-DESTRUCTIVE data migration — preserves all students, exams, attempts,
 * payments, certificates, etc.:
 *   1. Rows for numbers 1–11 are relabelled IN PLACE (ids unchanged), so every
 *      foreign key that points at a level stays valid.
 *   2. Any record sitting on an obsolete level (old numbers 12–14) is reassigned
 *      to the new top level (number 11, "Regular 7") before that level is removed.
 *   3. Obsolete level rows are then deleted; their book pivot rows cascade away.
 *
 * NOTE: because the old 14-level names have no canonical mapping to the new 11,
 * levels are remapped POSITIONALLY by number (old number N keeps id/number N,
 * new label) and anything above 11 is clamped to Regular 7. Adjust seeded label
 * mapping here if the organisation later specifies an explicit crosswalk.
 */
return new class extends Migration
{
    /** Tables/columns that reference levels and must be preserved (not nulled). */
    private array $levelRefs = [
        ['students', 'current_level_id'],
        ['batches', 'level_id'],
        ['question_banks', 'level_id'],
        ['student_levels', 'level_id'],
        ['certificates', 'level_id'],
        ['fees', 'level_id'],
        ['resource_files', 'level_id'],
        ['practice_sessions', 'level_id'],
        ['class_practice_sessions', 'level_id'],
        ['exams', 'level_id'],
        ['competition_practice_papers', 'level_id'],
        ['competition_question_papers', 'level_id'],
    ];

    private array $levels = [
        ['number' => 1,  'title' => 'Junior 1',  'fee' => 800],
        ['number' => 2,  'title' => 'Junior 2',  'fee' => 900],
        ['number' => 3,  'title' => 'Junior 3',  'fee' => 1000],
        ['number' => 4,  'title' => 'Junior 4',  'fee' => 1100],
        ['number' => 5,  'title' => 'Regular 1', 'fee' => 1200],
        ['number' => 6,  'title' => 'Regular 2', 'fee' => 1300],
        ['number' => 7,  'title' => 'Regular 3', 'fee' => 1400],
        ['number' => 8,  'title' => 'Regular 4', 'fee' => 1500],
        ['number' => 9,  'title' => 'Regular 5', 'fee' => 1600],
        ['number' => 10, 'title' => 'Regular 6', 'fee' => 1700],
        ['number' => 11, 'title' => 'Regular 7', 'fee' => 1800],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('levels')) {
            return;
        }

        DB::transaction(function () {
            // 1. Upsert the 11 new level definitions by number (relabel in place).
            foreach ($this->levels as $def) {
                $payload = [
                    'title'         => $def['title'],
                    'slug'          => 'level-' . $def['number'],
                    'description'   => 'Level ' . $def['number'] . ': ' . $def['title'],
                    'fee_per_month' => $def['fee'],
                    'is_active'     => true,
                    'sort_order'    => $def['number'],
                    'updated_at'    => now(),
                ];

                if (DB::table('levels')->where('number', $def['number'])->exists()) {
                    DB::table('levels')->where('number', $def['number'])->update($payload);
                } else {
                    DB::table('levels')->insert($payload + [
                        'number'     => $def['number'],
                        'created_at' => now(),
                    ]);
                }
            }

            // 2. Reassign anything on obsolete levels (number > 11) to Regular 7.
            $obsoleteIds = DB::table('levels')->where('number', '>', 11)->pluck('id')->all();
            if (! empty($obsoleteIds)) {
                $topId = DB::table('levels')->where('number', 11)->value('id');

                foreach ($this->levelRefs as [$table, $column]) {
                    if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                        DB::table($table)
                            ->whereIn($column, $obsoleteIds)
                            ->update([$column => $topId]);
                    }
                }

                // 3. Delete obsolete levels (level_resource_files cascade-delete).
                DB::table('levels')->whereIn('id', $obsoleteIds)->delete();
            }
        });
    }

    public function down(): void
    {
        // One-way data migration: the old 14-level names/assignments cannot be
        // reconstructed once collapsed into 11. Intentionally a no-op.
    }
};
