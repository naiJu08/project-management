<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First, truncate existing department names that are too long
        DB::table('departments')
            ->whereRaw('LENGTH(name) > 40')
            ->update(['name' => DB::raw('LEFT(name, 40)')]);

        Schema::table('departments', function (Blueprint $table) {
            $table->string('name', 40)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('name')->change();
        });
    }
};
