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
        Schema::create('payroll_schedules', function (Blueprint $table) {
            $table->id();
            $table->enum('pay_period_type', ['semi-monthly', 'monthly'])->unique();
            $table->json('generation_days'); // Store an array of days (e.g., [15, 30] or ['last_day'])
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_schedules');
    }
};
