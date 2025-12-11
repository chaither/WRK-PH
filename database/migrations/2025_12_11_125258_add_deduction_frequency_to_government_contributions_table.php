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
        Schema::table('government_contributions', function (Blueprint $table) {
            $table->string('deduction_frequency')->default('semi_monthly')->after('applies_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('government_contributions', function (Blueprint $table) {
            $table->dropColumn('deduction_frequency');
        });
    }
};
