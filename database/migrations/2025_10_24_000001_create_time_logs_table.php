<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('time_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('hours', 8, 2);
            $table->text('description')->nullable();
            $table->boolean('is_billable')->default(true);
            $table->date('logged_date');
            $table->enum('category', ['development', 'testing', 'documentation', 'meeting', 'other'])->default('development');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['project_id', 'logged_date']);
            $table->index(['user_id', 'logged_date']);
            $table->index(['ticket_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('time_logs');
    }
};
