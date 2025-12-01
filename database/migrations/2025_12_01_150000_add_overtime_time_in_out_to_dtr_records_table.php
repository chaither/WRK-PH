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
            $table->time('overtime_time_in')->nullable()->after('time_out_2');
            $table->time('overtime_time_out')->nullable()->after('overtime_time_in');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dtr_records', function (Blueprint $table) {
            $table->dropColumn(['overtime_time_in', 'overtime_time_out']);
        });
    }
};
