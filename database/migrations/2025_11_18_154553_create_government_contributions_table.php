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
        Schema::create('government_contributions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['sss', 'philhealth', 'pagibig']);
            $table->decimal('min_salary', 10, 2)->nullable();
            $table->decimal('max_salary', 10, 2)->nullable();
            $table->decimal('employee_share', 8, 2);
            $table->decimal('employer_share', 8, 2)->nullable(); // For future use, if needed
            $table->unique(['type', 'min_salary', 'max_salary']); // Ensure unique contribution ranges
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('government_contributions');
    }
};
