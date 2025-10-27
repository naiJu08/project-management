<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'total_budget',
        'allocated_budget',
        'spent_budget',
        'notes',
    ];

    protected $casts = [
        'total_budget' => 'float',
        'allocated_budget' => 'float',
        'spent_budget' => 'float',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(BudgetExpense::class);
    }

    public function getRemainingBudgetAttribute()
    {
        return $this->total_budget - $this->spent_budget;
    }

    public function getBudgetUtilizationAttribute()
    {
        return $this->total_budget > 0 ? round(($this->spent_budget / $this->total_budget) * 100, 2) : 0;
    }

    public function getIsOverBudgetAttribute()
    {
        return $this->spent_budget > $this->total_budget;
    }
}
