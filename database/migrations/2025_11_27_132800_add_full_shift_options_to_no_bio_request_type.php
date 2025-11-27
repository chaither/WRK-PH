<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added this import for DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('no_bio_requests', function (Blueprint $table) {
            // 1. Add a new temporary column with all desired enum values
            $table->enum('new_type', ['morning_in', 'morning_out', 'afternoon_in', 'afternoon_out', 'all_morning', 'all_afternoon', 'whole_day'])->after('type');
        });

        // 2. Copy data from the existing 'type' column to 'new_type'
        // We need to map existing values to themselves for the new column
        DB::statement("UPDATE no_bio_requests SET new_type = type");

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
            // Revert by adding the old enum values back (without the new full shift options)
            $table->enum('type', ['morning_in', 'morning_out', 'afternoon_in', 'afternoon_out'])->change();
        });
    }
};
