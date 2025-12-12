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
        Schema::create('hmo_deductions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // HMO plan name (e.g., "Maxicare Premium", "Medicare Plus")
            $table->decimal('min_salary', 10, 2)->nullable();
            $table->decimal('max_salary', 10, 2)->nullable();
            $table->boolean('is_percentage')->default(false);
            $table->enum('target_type', ['all', 'employees', 'departments'])->default('all');
            $table->json('applies_to')->nullable();
            $table->decimal('employee_share', 8, 2);
            $table->decimal('employer_share', 8, 2)->nullable();
            $table->string('deduction_frequency')->default('semi_monthly'); // semi_monthly or first_half_monthly
            $table->string('deduction_frequency_target_type')->default('all');
            $table->json('deduction_frequency_applies_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hmo_deductions');
    }
};
