<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payslip_templates', function (Blueprint $table) {
            $table->json('template_json')->nullable()->after('html');
            $table->string('canvas_css')->nullable()->after('template_json');
        });

        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->json('template_json')->nullable()->after('html');
            $table->string('canvas_css')->nullable()->after('template_json');
        });
    }

    public function down(): void
    {
        Schema::table('payslip_templates', function (Blueprint $table) {
            $table->dropColumn(['template_json', 'canvas_css']);
        });

        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->dropColumn(['template_json', 'canvas_css']);
        });
    }
};
