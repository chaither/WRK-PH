<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->time('lunch_break_start')->nullable()->after('is_night_shift');
            $table->time('lunch_break_end')->nullable()->after('lunch_break_start');
            $table->integer('lunch_break_duration')->default(60)->after('lunch_break_end')->comment('Duration in minutes');
            $table->boolean('is_lunch_paid')->default(false)->after('lunch_break_duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['lunch_break_start', 'lunch_break_end', 'lunch_break_duration', 'is_lunch_paid']);
        });
    }
};
