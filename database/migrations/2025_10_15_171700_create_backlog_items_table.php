<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backlog_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('backlog_items')->cascadeOnDelete();
            $table->enum('type', ['Epic', 'Feature', 'UserStory', 'Task', 'Subtask']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('To Do');
            $table->string('priority')->default('Medium');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sprint_id')->nullable()->constrained('sprints')->nullOnDelete();
            $table->float('estimated_hours')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('order_index')->default(0);
            $table->string('code')->nullable()->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['project_id', 'type']);
            $table->index(['project_id', 'parent_id']);
            $table->index(['project_id', 'sprint_id']);
            $table->index('order_index');
        });

        // Add relationship to existing tickets table for backward compatibility
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('backlog_item_id')->nullable()->after('epic_id')->constrained('backlog_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['backlog_item_id']);
            $table->dropColumn('backlog_item_id');
        });
        
        Schema::dropIfExists('backlog_items');
    }
};
