<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class BacklogItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'parent_id',
        'type',
        'title',
        'description',
        'status',
        'priority',
        'assignee_id',
        'sprint_id',
        'estimated_hours',
        'start_date',
        'due_date',
        'order_index',
        'code',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'estimated_hours' => 'float',
        'start_date' => 'date',
        'due_date' => 'date',
        'order_index' => 'integer',
    ];

    protected $with = ['assignee'];

    // Type constants
    const TYPE_EPIC = 'Epic';
    const TYPE_FEATURE = 'Feature';
    const TYPE_USER_STORY = 'UserStory';
    const TYPE_TASK = 'Task';
    const TYPE_SUBTASK = 'Subtask';

    // Status constants
    const STATUS_TODO = 'To Do';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_DONE = 'Done';
    const STATUS_BLOCKED = 'Blocked';

    // Priority constants
    const PRIORITY_CRITICAL = 'Critical';
    const PRIORITY_HIGH = 'High';
    const PRIORITY_MEDIUM = 'Medium';
    const PRIORITY_LOW = 'Low';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (!$item->code) {
                $item->code = static::generateCode($item);
            }
            if (!$item->order_index) {
                $item->order_index = static::getNextOrderIndex($item->project_id, $item->parent_id);
            }
        });

        static::saving(function ($item) {
            if ($item->start_date && $item->due_date && $item->due_date < $item->start_date) {
                throw ValidationException::withMessages([
                    'due_date' => 'Due date cannot be before start date'
                ]);
            }
        });

        static::deleting(function ($item) {
            // Delete all children recursively
            $item->children()->each(function ($child) {
                $child->delete();
            });
            
            // Delete associated comments
            $item->comments()->delete();
            
            // Delete history records
            $item->history()->delete();
        });
    }

    // Relationships

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(BacklogItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(BacklogItem::class, 'parent_id')->orderBy('order_index');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BacklogItemComment::class)->whereNull('parent_comment_id')->orderBy('created_at');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(BacklogItemComment::class)->orderBy('created_at');
    }

    public function history(): HasMany
    {
        return $this->hasMany(BacklogItemHistory::class)->orderBy('created_at', 'desc');
    }
    
    public function histories(): HasMany
    {
        return $this->hasMany(BacklogItemHistory::class)->orderBy('created_at', 'desc');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'backlog_item_id');
    }

    // Helper Methods

    public static function generateCode(BacklogItem $item): string
    {
        $project = Project::find($item->project_id);
        $prefix = match($item->type) {
            self::TYPE_EPIC => 'EP',
            self::TYPE_FEATURE => 'FT',
            self::TYPE_USER_STORY => 'US',
            self::TYPE_TASK => 'TK',
            self::TYPE_SUBTASK => 'ST',
            default => 'BI',
        };
        
        // Find the highest number used for this type (including soft deleted)
        $codePrefix = ($project?->ticket_prefix ?? 'PRJ') . '-' . $prefix . '-';
        $maxCode = static::withTrashed()
            ->where('project_id', $item->project_id)
            ->where('type', $item->type)
            ->where('code', 'LIKE', $codePrefix . '%')
            ->orderByRaw('CAST(SUBSTRING(code, LENGTH(?) + 1) AS UNSIGNED) DESC', [$codePrefix])
            ->value('code');
        
        if ($maxCode) {
            // Extract the number from the code
            $number = (int) substr($maxCode, strlen($codePrefix));
            $nextNumber = $number + 1;
        } else {
            $nextNumber = 1;
        }
        
        // Ensure uniqueness (in case of race conditions)
        $attempts = 0;
        while ($attempts < 100) {
            $code = $codePrefix . $nextNumber;
            $exists = static::withTrashed()
                ->where('code', $code)
                ->exists();
            
            if (!$exists) {
                return $code;
            }
            
            $nextNumber++;
            $attempts++;
        }
        
        // Fallback: use timestamp-based unique code
        return $codePrefix . time();
    }

    public static function getNextOrderIndex($projectId, $parentId = null): int
    {
        $maxOrder = static::where('project_id', $projectId)
            ->where('parent_id', $parentId)
            ->max('order_index');
        
        return ($maxOrder ?? -1) + 1;
    }

    public function canHaveChildren(): bool
    {
        return $this->type !== self::TYPE_SUBTASK;
    }

    public function getAllowedChildTypes(): array
    {
        return match($this->type) {
            self::TYPE_EPIC => [self::TYPE_FEATURE],
            self::TYPE_FEATURE => [self::TYPE_USER_STORY],
            self::TYPE_USER_STORY => [self::TYPE_TASK],
            self::TYPE_TASK => [self::TYPE_SUBTASK],
            default => [],
        };
    }

    public function canBeParentOf(string $childType): bool
    {
        return in_array($childType, $this->getAllowedChildTypes());
    }

    public function getTypeIcon(): string
    {
        return match($this->type) {
            self::TYPE_EPIC => '🎯',
            self::TYPE_FEATURE => '🔷',
            self::TYPE_USER_STORY => '📖',
            self::TYPE_TASK => '✓',
            self::TYPE_SUBTASK => '▫',
            default => '•',
        };
    }

    public function getTypeColor(): string
    {
        return match($this->type) {
            self::TYPE_EPIC => 'purple',
            self::TYPE_FEATURE => 'blue',
            self::TYPE_USER_STORY => 'green',
            self::TYPE_TASK => 'orange',
            self::TYPE_SUBTASK => 'gray',
            default => 'gray',
        };
    }

    public function getPriorityColor(): string
    {
        return match($this->priority) {
            self::PRIORITY_CRITICAL => 'red',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_MEDIUM => 'yellow',
            self::PRIORITY_LOW => 'green',
            default => 'gray',
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_DONE => 'green',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_BLOCKED => 'red',
            default => 'gray',
        };
    }

    public function getDepth(): int
    {
        $depth = 0;
        $current = $this;
        
        while ($current->parent_id) {
            $depth++;
            $current = $current->parent;
        }
        
        return $depth;
    }

    public function getHierarchyPath(): array
    {
        $path = [$this];
        $current = $this;
        
        while ($current->parent_id) {
            $current = $current->parent;
            array_unshift($path, $current);
        }
        
        return $path;
    }

    public function getTotalEstimatedHours(): float
    {
        $total = $this->estimated_hours ?? 0;
        
        foreach ($this->children as $child) {
            $total += $child->getTotalEstimatedHours();
        }
        
        return $total;
    }

    public function getCompletionPercentage(): int
    {
        if ($this->children->isEmpty()) {
            return $this->status === self::STATUS_DONE ? 100 : 0;
        }
        
        $total = $this->children->count();
        $completed = $this->children->where('status', self::STATUS_DONE)->count();
        
        return $total > 0 ? (int) (($completed / $total) * 100) : 0;
    }

    public function moveToSprint(?int $sprintId): void
    {
        $this->update(['sprint_id' => $sprintId]);
        
        // Also move all children
        foreach ($this->children as $child) {
            $child->moveToSprint($sprintId);
        }
    }

    public function reorder(int $newOrderIndex, ?int $newParentId = null): void
    {
        $oldParentId = $this->parent_id;
        $oldOrderIndex = $this->order_index;
        
        // If parent changed, validate hierarchy rules
        if ($newParentId !== $oldParentId && $newParentId) {
            $newParent = static::find($newParentId);
            if (!$newParent->canBeParentOf($this->type)) {
                throw new \Exception("Cannot move {$this->type} under {$newParent->type}");
            }
        }
        
        // Update siblings' order
        if ($newParentId !== $oldParentId) {
            // Decrease order of items after old position
            static::where('project_id', $this->project_id)
                ->where('parent_id', $oldParentId)
                ->where('order_index', '>', $oldOrderIndex)
                ->decrement('order_index');
            
            // Increase order of items at/after new position
            static::where('project_id', $this->project_id)
                ->where('parent_id', $newParentId)
                ->where('order_index', '>=', $newOrderIndex)
                ->increment('order_index');
        } else {
            // Same parent, just reordering
            if ($newOrderIndex < $oldOrderIndex) {
                static::where('project_id', $this->project_id)
                    ->where('parent_id', $oldParentId)
                    ->whereBetween('order_index', [$newOrderIndex, $oldOrderIndex - 1])
                    ->increment('order_index');
            } else {
                static::where('project_id', $this->project_id)
                    ->where('parent_id', $oldParentId)
                    ->whereBetween('order_index', [$oldOrderIndex + 1, $newOrderIndex])
                    ->decrement('order_index');
            }
        }
        
        // Update this item
        $this->update([
            'parent_id' => $newParentId,
            'order_index' => $newOrderIndex,
        ]);
    }

    // Scopes

    public function scopeEpics($query)
    {
        return $query->where('type', self::TYPE_EPIC);
    }

    public function scopeFeatures($query)
    {
        return $query->where('type', self::TYPE_FEATURE);
    }

    public function scopeUserStories($query)
    {
        return $query->where('type', self::TYPE_USER_STORY);
    }

    public function scopeTasks($query)
    {
        return $query->where('type', self::TYPE_TASK);
    }

    public function scopeSubtasks($query)
    {
        return $query->where('type', self::TYPE_SUBTASK);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeInSprint($query, $sprintId)
    {
        return $query->where('sprint_id', $sprintId);
    }

    public function scopeInBacklog($query)
    {
        return $query->whereNull('sprint_id');
    }
}
