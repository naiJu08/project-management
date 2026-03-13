<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ticket_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('approver_id');
            $table->enum('type', ['code_review', 'qa_signoff', 'product_approval', 'client_approval', 'management_approval']);
            $table->enum('status', ['pending', 'approved', 'rejected', 'approved_with_comments'])->default('pending');
            $table->text('comments')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['ticket_id', 'type']);
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ticket_approvals');
    }
};
