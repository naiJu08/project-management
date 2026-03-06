<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasColumn('tickets', 'responsible_ids')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->json('responsible_ids')->nullable()->after('owner_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasColumn('tickets', 'responsible_ids')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('responsible_ids');
            });
        }
    }
};