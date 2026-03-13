<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ticket_dependencies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('depends_on_ticket_id');
            $table->enum('type', ['blocks', 'blocked_by', 'related_to', 'duplicates', 'duplicated_by']);
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('depends_on_ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            
            $table->unique(['ticket_id', 'depends_on_ticket_id', 'type']);
            $table->index('type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ticket_dependencies');
    }
};
