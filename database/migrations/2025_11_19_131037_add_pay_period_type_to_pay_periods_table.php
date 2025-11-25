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
        Schema::table('pay_periods', function (Blueprint $table) {
            $table->enum('pay_period_type', ['daily', 'weekly', 'bi-weekly', 'semi-monthly', 'monthly'])->default('semi-monthly')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pay_periods', function (Blueprint $table) {
            $table->dropColumn('pay_period_type');
        });
    }
};
