<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::table('projects', function ($table) {
                $table->dropUnique('projects_ticket_prefix_unique');
            });
        } catch (\Exception $e) {
            // Index might already be dropped
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try {
            Schema::table('projects', function ($table) {
                $table->string('ticket_prefix')->unique()->change();
            });
        } catch (\Exception $e) {
            // Already exists or cannot recreate
        }
    }
};