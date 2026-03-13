<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Enhanced fields
            $table->string('component')->nullable();
            $table->string('affected_version')->nullable();
            $table->string('fixed_version')->nullable();
            
            // Severity for bugs
            $table->enum('severity', ['critical', 'major', 'minor', 'trivial'])->nullable();
            
            // Scheduling
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('estimated_hours')->nullable();
            
            // Relationships
            $table->unsignedBigInteger('parent_ticket_id')->nullable();
            $table->foreign('parent_ticket_id')->references('id')->on('tickets')->onDelete('set null');
            
            // Additional tracking
            $table->integer('reopened_count')->default(0);
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->text('blocked_reason')->nullable();
            
            // SLA tracking
            $table->integer('sla_hours')->nullable();
            $table->timestamp('sla_due_at')->nullable();
            $table->enum('sla_status', ['on_track', 'at_risk', 'breached'])->default('on_track');
            
            // Risk assessment
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->nullable();
            $table->text('risk_description')->nullable();
            $table->text('mitigation_plan')->nullable();
            
            // Approval tracking
            $table->boolean('requires_approval')->default(false);
            $table->enum('approval_status', ['pending', 'approved', 'rejected', 'approved_with_comments'])->nullable();
            
            // Budget tracking
            $table->decimal('budget_allocated', 10, 2)->nullable();
            $table->decimal('budget_spent', 10, 2)->default(0);
            
            // Metrics
            $table->integer('comment_count')->default(0);
            $table->integer('attachment_count')->default(0);
            $table->integer('watcher_count')->default(0);
            
            // Indexes for performance
            $table->index('parent_ticket_id');
            $table->index('component');
            $table->index('risk_level');
            $table->index('sla_status');
            $table->index('approval_status');
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['parent_ticket_id']);
            $table->dropIndex(['parent_ticket_id']);
            $table->dropIndex(['component']);
            $table->dropIndex(['risk_level']);
            $table->dropIndex(['sla_status']);
            $table->dropIndex(['approval_status']);
            
            $table->dropColumn([
                'component', 'affected_version', 'fixed_version', 'severity',
                'start_date', 'due_date', 'estimated_hours', 'parent_ticket_id',
                'reopened_count', 'first_response_at', 'resolved_at', 'is_blocked',
                'blocked_reason', 'sla_hours', 'sla_due_at', 'sla_status',
                'risk_level', 'risk_description', 'mitigation_plan', 'requires_approval',
                'approval_status', 'budget_allocated', 'budget_spent', 'comment_count',
                'attachment_count', 'watcher_count'
            ]);
        });
    }
};
