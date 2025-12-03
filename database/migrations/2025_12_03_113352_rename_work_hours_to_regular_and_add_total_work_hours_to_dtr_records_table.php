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
            $table->renameColumn('work_hours', 'regular_work_hours');
            $table->float('total_work_hours')->after('overtime_hours')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dtr_records', function (Blueprint $table) {
            $table->renameColumn('regular_work_hours', 'work_hours');
            $table->dropColumn('total_work_hours');
        });
    }
};
