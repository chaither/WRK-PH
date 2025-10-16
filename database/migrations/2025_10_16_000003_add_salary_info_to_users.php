<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->enum('pay_period', ['semi-monthly', 'monthly'])->default('semi-monthly');
            $table->decimal('daily_rate', 10, 2)->default(0);
            $table->decimal('hourly_rate', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['basic_salary', 'pay_period', 'daily_rate', 'hourly_rate']);
        });
    }
};