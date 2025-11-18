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
            $table->enum('target_type', ['all', 'employees', 'departments'])->default('all')->after('is_percentage');
            $table->json('applies_to')->nullable()->after('target_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('government_contributions', function (Blueprint $table) {
            $table->dropColumn(['target_type', 'applies_to']);
        });
    }
};
