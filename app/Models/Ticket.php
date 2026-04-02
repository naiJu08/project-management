<?php

namespace App\Models;

use App\Notifications\TicketCreated;
use App\Notifications\TicketStatusUpdated;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Ticket extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name', 'content', 'owner_id', 'responsible_id','responsible_ids',
        'status_id', 'project_id', 'code', 'order', 'type_id',
        'priority_id', 'estimation', 'epic_id', 'sprint_id', 'backlog_item_id',
        'description', 'component', 'affected_version', 'fixed_version', 'severity',
        'start_date', 'due_date', 'estimated_hours', 'parent_ticket_id',
        'reopened_count', 'first_response_at', 'resolved_at', 'is_blocked',
        'blocked_reason', 'sla_hours', 'sla_due_at', 'sla_status',
        'risk_level', 'risk_description', 'mitigation_plan', 'requires_approval',
        'approval_status', 'budget_allocated', 'budget_spent'
    ];

    protected $casts = [
        'start_date' => 'datetime:Y-m-d',
        'due_date' => 'datetime:Y-m-d',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'sla_due_at' => 'datetime',
        'estimated_hours' => 'float',
        'budget_allocated' => 'float',
        'budget_spent' => 'float',
        'is_blocked' => 'boolean',
        'requires_approval' => 'boolean',
        'responsible_ids' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function (Ticket $item) {
            $project = Project::where('id', $item->project_id)->first();
            $count = Ticket::where('project_id', $project->id)->count();
            $order = $project->tickets?->last()?->order ?? -1;
            $item->code = $project->ticket_prefix . '-' . ($count + 1);
            $item->order = $order + 1;
            
            // Set epic_id from sprint if sprint exists and has epic
            if ($item->sprint_id) {
                $sprint = Sprint::find($item->sprint_id);
                if ($sprint && $sprint->epic_id) {
                    $item->epic_id = $sprint->epic_id;
                }
            }
        });

        static::created(function (Ticket $item) {
            foreach ($item->watchers as $user) {
                $user->notify(new TicketCreated($item));
            }
        });

        static::updating(function (Ticket $item) {
            $old = Ticket::where('id', $item->id)->first();

            // Ticket activity based on status
            $oldStatus = $old->status_id;
            if ($oldStatus != $item->status_id) {
                TicketActivity::create([
                    'ticket_id' => $item->id,
                    'old_status_id' => $oldStatus,
                    'new_status_id' => $item->status_id,
                    'user_id' => auth()->user()->id
                ]);
                foreach ($item->watchers as $user) {
                    $user->notify(new TicketStatusUpdated($item));
                }
            }

            // Ticket sprint update
            $oldSprint = $old->sprint_id;
            if ($oldSprint && !$item->sprint_id) {
                $item->epic_id = null;
            } elseif ($item->sprint_id && $item->sprint->epic_id) {
                $item->epic_id = $item->sprint->epic_id;
            }
        });

        static::deleting(function (Ticket $item) {
            // Delete all related data when ticket is deleted
            $item->activities()->delete();
            $item->comments()->delete();
            $item->relations()->delete();
            $item->hours()->delete();
            $item->subscribers()->detach();
            
            // Delete ticket relations where this ticket is the relation
            TicketRelation::where('relation_id', $item->id)->delete();
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id', 'id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TicketStatus::class, 'status_id', 'id')->withTrashed();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id')->withTrashed();
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TicketType::class, 'type_id', 'id')->withTrashed();
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(TicketPriority::class, 'priority_id', 'id')->withTrashed();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TicketActivity::class, 'ticket_id', 'id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class, 'ticket_id', 'id');
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_subscribers', 'ticket_id', 'user_id');
    }

    public function relations(): HasMany
    {
        return $this->hasMany(TicketRelation::class, 'ticket_id', 'id');
    }

    public function hours(): HasMany
    {
        return $this->hasMany(TicketHour::class, 'ticket_id', 'id');
    }

    public function epic(): BelongsTo
    {
        return $this->belongsTo(Epic::class, 'epic_id', 'id');
    }

    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class, 'sprint_id', 'id');
    }

    public function sprints(): BelongsTo
    {
        return $this->belongsTo(Sprint::class, 'sprint_id', 'id');
    }

    public function watchers(): Attribute
    {
        return new Attribute(
            get: function () {
                $users = $this->project->users;
                $users->push($this->owner);
                if ($this->responsible_ids) {
                    $assignedUsers = \App\Models\User::whereIn('id', $this->responsible_ids)->get();
                    foreach ($assignedUsers as $user) {
                        $users->push($user);
                    }
                }
                return $users->unique('id');
            }
        );
    }

    public function totalLoggedHours(): Attribute
    {
        return new Attribute(
            get: function () {
                $seconds = $this->hours->sum('value') * 3600;
                return CarbonInterval::seconds($seconds)->cascade()->forHumans();
            }
        );
    }

    public function totalLoggedSeconds(): Attribute
    {
        return new Attribute(
            get: function () {
                return $this->hours->sum('value') * 3600;
            }
        );
    }

    public function totalLoggedInHours(): Attribute
    {
        return new Attribute(
            get: function () {
                return $this->hours->sum('value');
            }
        );
    }

    public function estimationForHumans(): Attribute
    {
        return new Attribute(
            get: function () {
                return CarbonInterval::seconds($this->estimationInSeconds)->cascade()->forHumans();
            }
        );
    }

    public function estimationInSeconds(): Attribute
    {
        return new Attribute(
            get: function () {
                if (!$this->estimation) {
                    return null;
                }
                return $this->estimation * 3600;
            }
        );
    }

    public function estimationProgress(): Attribute
    {
        return new Attribute(
            get: function () {
                return (($this->totalLoggedSeconds ?? 0) / ($this->estimationInSeconds ?? 1)) * 100;
            }
        );
    }

    public function completudePercentage(): Attribute
    {
        return new Attribute(
            get: fn() => $this->estimationProgress
        );
    }

    public function backlogItem(): BelongsTo
    {
        return $this->belongsTo(BacklogItem::class, 'backlog_item_id', 'id');
    }

    // Enhanced ticket relationships
    public function parentTicket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'parent_ticket_id', 'id');
    }

    public function childTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'parent_ticket_id', 'id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(TicketApproval::class, 'ticket_id', 'id');
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(TicketDependency::class, 'ticket_id', 'id');
    }

    public function dependentTickets(): HasMany
    {
        return $this->hasMany(TicketDependency::class, 'depends_on_ticket_id', 'id');
    }

    // Enhanced ticket methods
    public function getTotalLoggedHours(): float
    {
        return $this->hours->sum('value');
    }

    public function getRemainingHours(): ?float
    {
        if (!$this->estimated_hours) {
            return null;
        }
        return max(0, $this->estimated_hours - $this->getTotalLoggedHours());
    }

    public function getProgressPercentage(): float
    {
        if (!$this->estimated_hours || $this->estimated_hours == 0) {
            return 0;
        }
        return min(100, ($this->getTotalLoggedHours() / $this->estimated_hours) * 100);
    }

    public function isOverBudget(): bool
    {
        if (!$this->budget_allocated) {
            return false;
        }
        return $this->budget_spent >= $this->budget_allocated;
    }

    public function getRemainingBudget(): ?float
    {
        if (!$this->budget_allocated) {
            return null;
        }
        return max(0, $this->budget_allocated - $this->budget_spent);
    }

    public function isBlocked(): bool
    {
        return $this->is_blocked || $this->dependencies()
            ->where('type', 'blocked_by')
            ->whereHas('dependsOnTicket', fn($q) => $q->whereNotIn('status_id', [3, 4])) // Not Done/Closed
            ->exists();
    }

    public function getBlockingReasons(): array
    {
        $reasons = [];
        if ($this->is_blocked) {
            $reasons[] = $this->blocked_reason ?? 'Manually blocked';
        }
        
        $blockedBy = $this->dependencies()
            ->where('type', 'blocked_by')
            ->with('dependsOnTicket')
            ->get();
        
        foreach ($blockedBy as $dep) {
            $reasons[] = "Blocked by {$dep->dependsOnTicket->code}";
        }
        
        return $reasons;
    }

    public function isSLAAtRisk(): bool
    {
        if (!$this->sla_due_at) {
            return false;
        }
        return now()->diffInHours($this->sla_due_at) <= 2;
    }

    public function isSLABreached(): bool
    {
        if (!$this->sla_due_at) {
            return false;
        }
        return now()->isAfter($this->sla_due_at);
    }

    public function updateSLAStatus(): void
    {
        if ($this->isSLABreached()) {
            $this->sla_status = 'breached';
        } elseif ($this->isSLAAtRisk()) {
            $this->sla_status = 'at_risk';
        } else {
            $this->sla_status = 'on_track';
        }
        $this->save();
    }

    public function requiresApproval(): bool
    {
        return $this->requires_approval && $this->approval_status !== 'approved';
    }

    public function getPendingApprovals(): int
    {
        return $this->approvals()->where('status', 'pending')->count();
    }

    public function getAllApprovalsCompleted(): bool
    {
        $total = $this->approvals()->count();
        if ($total === 0) {
            return true;
        }
        $approved = $this->approvals()->whereIn('status', ['approved', 'approved_with_comments'])->count();
        return $approved === $total;
    }

    public function getActivitylogOptions(): LogOptionsAttribute
    {
        return new Attribute(
            get: fn() => $this->estimationProgress
        );
    }
}
