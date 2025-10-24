<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'ai_generation_status')) {
                $table->string('ai_generation_status')->nullable()->after('type'); // running|success|failed|null
            }
            if (!Schema::hasColumn('projects', 'ai_last_run_at')) {
                $table->timestamp('ai_last_run_at')->nullable()->after('ai_generation_status');
            }
            if (!Schema::hasColumn('projects', 'ai_last_message')) {
                $table->text('ai_last_message')->nullable()->after('ai_last_run_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'ai_last_message')) {
                $table->dropColumn('ai_last_message');
            }
            if (Schema::hasColumn('projects', 'ai_last_run_at')) {
                $table->dropColumn('ai_last_run_at');
            }
            if (Schema::hasColumn('projects', 'ai_generation_status')) {
                $table->dropColumn('ai_generation_status');
            }
        });
    }
};
