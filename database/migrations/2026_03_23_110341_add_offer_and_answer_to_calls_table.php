<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            if (!Schema::hasColumn('calls', 'offer')) {
                $table->longText('offer')->nullable()->after('status');
            }

            if (!Schema::hasColumn('calls', 'answer')) {
                $table->longText('answer')->nullable()->after('offer');
            }
        });
    }

    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            if (Schema::hasColumn('calls', 'offer')) {
                $table->dropColumn('offer');
            }

            if (Schema::hasColumn('calls', 'answer')) {
                $table->dropColumn('answer');
            }
        });
    }
};