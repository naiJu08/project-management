<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Milestone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'target_date',
        'status',
        'version',
        'release_notes',
    ];

    protected $casts = [
        'target_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'milestone_ticket');
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('target_date', '>=', now()->startOfDay())->orderBy('target_date');
    }

    public function scopeOverdue($query)
    {
        return $query->where('target_date', '<', now()->startOfDay())
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled');
    }

    public function getCompletionPercentageAttribute()
    {
        $tickets = $this->tickets;
        if ($tickets->isEmpty()) {
            return 0;
        }

        $completed = $tickets->where('status.name', 'Done')->count();
        return round(($completed / $tickets->count()) * 100);
    }

    public function getDaysUntilTargetAttribute()
    {
        return $this->target_date->diffInDays(now()->startOfDay());
    }

    public function getIsOverdueAttribute()
    {
        return $this->target_date->lt(now()->startOfDay()) && $this->status !== 'completed' && $this->status !== 'cancelled';
    }
}
