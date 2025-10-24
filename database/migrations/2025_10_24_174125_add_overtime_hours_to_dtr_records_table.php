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
            $table->float('overtime_hours')->default(0)->after('work_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dtr_records', function (Blueprint $table) {
            $table->dropColumn('overtime_hours');
        });
    }
};
