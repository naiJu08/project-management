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
        // Handle duplicate department names by appending a number
        $duplicates = DB::table('departments')
            ->select('name', DB::raw('COUNT(*) as count'))
            ->groupBy('name')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            $departments = DB::table('departments')
                ->where('name', $duplicate->name)
                ->orderBy('id')
                ->get();

            $counter = 1;
            foreach ($departments as $department) {
                if ($counter > 1) {
                    DB::table('departments')
                        ->where('id', $department->id)
                        ->update(['name' => $department->name . ' ' . $counter]);
                }
                $counter++;
            }
        }

        // Now add the unique constraint
        Schema::table('departments', function (Blueprint $table) {
            $table->unique('name');
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
            $table->dropUnique(['name']);
        });
    }
};
