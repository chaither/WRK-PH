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
        Schema::table('dtr_records', function (Blueprint $table) {
            $table->dateTime('time_in')->nullable()->change();
            $table->dateTime('time_out')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dtr_records', function (Blueprint $table) {
            $table->time('time_in')->nullable()->change();
            $table->time('time_out')->nullable()->change();
        });
    }
};
