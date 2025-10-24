<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->json('breaks')->nullable()->after('total_hours'); // Store break periods
            $table->decimal('break_duration', 5, 2)->default(0)->after('breaks'); // Total break time in hours
            $table->decimal('work_hours', 5, 2)->nullable()->after('break_duration'); // Actual work hours (total - breaks)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn(['breaks', 'break_duration', 'work_hours']);
        });
    }
};
