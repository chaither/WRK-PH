<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'unpaid' and 'paid' to the status enum
        DB::statement("ALTER TABLE `pay_periods` MODIFY `status` ENUM('draft','processing','completed','unpaid','paid') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE `pay_periods` MODIFY `status` ENUM('draft','processing','completed') NOT NULL DEFAULT 'draft'");
    }
};
