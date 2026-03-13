<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Temporary mapping tables for data migration
        // These help track the relationship between old and new systems
        
        Schema::create('ticket_backlog_mapping', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('backlog_item_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['ticket_id', 'backlog_item_id']);
        });

        Schema::create('epic_backlog_mapping', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('epic_id');
            $table->foreignId('backlog_item_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['epic_id', 'backlog_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('epic_backlog_mapping');
        Schema::dropIfExists('ticket_backlog_mapping');
    }
};
