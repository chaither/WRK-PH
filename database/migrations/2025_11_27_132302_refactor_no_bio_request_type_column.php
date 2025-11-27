<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added DB facade import

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('no_bio_requests', function (Blueprint $table) {
            // 1. Add a new temporary column with the desired enum values
            $table->enum('new_type', ['morning_in', 'morning_out', 'afternoon_in', 'afternoon_out'])->after('type');
        });

        // 2. Copy and transform data from the old 'type' column to 'new_type'
        // Using DB::statement for direct SQL for better performance on large tables
        DB::statement("UPDATE no_bio_requests SET new_type = CASE type
            WHEN 'time_in' THEN 'morning_in'
            WHEN 'time_out' THEN 'afternoon_out'
            WHEN 'both' THEN 'morning_in' -- Default for existing 'both' entries
            ELSE 'morning_in' -- Fallback
        END");

        Schema::table('no_bio_requests', function (Blueprint $table) {
            // 3. Drop the old 'type' column
            $table->dropColumn('type');
        });

        Schema::table('no_bio_requests', function (Blueprint $table) {
            // 4. Rename the new temporary column to 'type'
            $table->renameColumn('new_type', 'type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('no_bio_requests', function (Blueprint $table) {
            // Revert by adding the old 'type' column back and dropping the new one
            $table->enum('type', ['time_in', 'time_out', 'both'])->after('date');
        });

        // Note: Reverting data is complex and not handled in this rollback.
        // It's assumed that a rollback after a data-transforming migration might involve manual data correction.
        // For simplicity, we are not attempting to map back 'morning_in' to 'time_in', etc. here.
        Schema::table('no_bio_requests', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('no_bio_requests', function (Blueprint $table) {
            $table->renameColumn('new_type', 'type');
        });
    }
};
