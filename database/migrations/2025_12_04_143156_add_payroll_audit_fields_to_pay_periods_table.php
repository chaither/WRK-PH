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
            $table->foreignId('generated_by_user_id')->nullable()->constrained('users');
            $table->foreignId('regenerated_by_user_id')->nullable()->constrained('users');
            $table->foreignId('marked_paid_by_user_id')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pay_periods', function (Blueprint $table) {
            $table->dropConstrainedForeignId('generated_by_user_id');
            $table->dropConstrainedForeignId('regenerated_by_user_id');
            $table->dropConstrainedForeignId('marked_paid_by_user_id');
        });
    }
};
