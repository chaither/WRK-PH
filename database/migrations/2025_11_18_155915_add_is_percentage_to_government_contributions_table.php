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
            $table->boolean('is_percentage')->default(false)->after('max_salary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('government_contributions', function (Blueprint $table) {
            $table->dropColumn('is_percentage');
        });
    }
};
