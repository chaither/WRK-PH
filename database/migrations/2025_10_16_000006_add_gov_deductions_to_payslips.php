<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->decimal('sss', 10, 2)->default(0)->after('absences_deductions');
            $table->decimal('gsis', 10, 2)->default(0)->after('sss');
            $table->decimal('philhealth', 10, 2)->default(0)->after('gsis');
            $table->decimal('other_deductions', 10, 2)->nullable()->default(0)->after('philhealth');
        });
    }

    public function down(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->dropColumn(['sss', 'gsis', 'philhealth', 'other_deductions']);
        });
    }
};
