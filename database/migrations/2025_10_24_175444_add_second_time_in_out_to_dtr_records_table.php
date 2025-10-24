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
        Schema::table('dtr_records', function (Blueprint $table) {
            $table->dateTime('time_in_2')->nullable()->after('time_out');
            $table->dateTime('time_out_2')->nullable()->after('time_in_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dtr_records', function (Blueprint $table) {
            $table->dropColumn(['time_in_2', 'time_out_2']);
        });
    }
};
