<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->decimal('total_budget', 12, 2);
            $table->decimal('allocated_budget', 12, 2)->default(0);
            $table->decimal('spent_budget', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('project_id');
        });

        Schema::create('budget_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('category');
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['budget_id', 'expense_date']);
            $table->index('status');
        });

        Schema::create('resource_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('hourly_rate', 10, 2);
            $table->enum('rate_type', ['hourly', 'daily', 'monthly'])->default('hourly');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['project_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('resource_costs');
        Schema::dropIfExists('budget_expenses');
        Schema::dropIfExists('budgets');
    }
};
