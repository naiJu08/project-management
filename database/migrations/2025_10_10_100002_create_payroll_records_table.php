<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('basic', 12, 2)->default(0);
            $table->json('allowances')->nullable();
            $table->json('deductions')->nullable();
            $table->decimal('gross', 12, 2)->default(0);
            $table->decimal('net', 12, 2)->default(0);
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('payslip_template_id')->nullable()->constrained('payslip_templates')->nullOnDelete();
            $table->longText('html_snapshot')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
    }
};
