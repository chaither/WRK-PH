<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pay_periods', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'processing', 'completed'])->default('draft');
            $table->timestamps();
        });

        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('pay_period_id')->constrained()->onDelete('cascade');
            $table->date('pay_period_start')->nullable();
            $table->date('pay_period_end')->nullable();
            $table->decimal('gross_pay', 10, 2)->nullable();
            $table->decimal('overtime_pay', 10, 2)->default(0);
            $table->decimal('late_deductions', 10, 2)->default(0);
            $table->decimal('absences_deductions', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0.00);
            $table->decimal('net_pay', 10, 2);
            $table->integer('total_hours_worked');
            $table->integer('overtime_hours')->default(0);
            $table->integer('late_minutes')->default(0);
            $table->integer('absent_days')->default(0);
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('pay_periods');
    }
};