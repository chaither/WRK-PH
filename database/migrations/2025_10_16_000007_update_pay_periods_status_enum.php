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
        Schema::table('pay_periods', function (Blueprint $table) {
            $table->string('status_new')->default('draft');
        });

        DB::statement('UPDATE pay_periods SET status_new = status;');

        Schema::table('pay_periods', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('pay_periods', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pay_periods', function (Blueprint $table) {
            $table->string('status_new')->default('draft');
        });

        DB::statement('UPDATE pay_periods SET status_new = status;');

        Schema::table('pay_periods', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('pay_periods', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });
    }
};
