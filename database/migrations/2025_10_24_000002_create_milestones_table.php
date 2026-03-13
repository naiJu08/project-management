<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('target_date');
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->integer('version')->default(1);
            $table->text('release_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['project_id', 'target_date']);
            $table->index('status');
        });

        Schema::create('milestone_ticket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milestone_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['milestone_id', 'ticket_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('milestone_ticket');
        Schema::dropIfExists('milestones');
    }
};
