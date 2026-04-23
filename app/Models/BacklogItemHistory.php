<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BacklogItemHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'backlog_item_id',
        'user_id',
        'field',
        'old_value',
        'new_value',
        'action',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    public function backlogItem(): BelongsTo
    {
        return $this->belongsTo(BacklogItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedChange(): string
    {
        return match($this->action) {
            'created' => "Created {$this->backlogItem->type}",
            'updated' => "Updated {$this->field} from '{$this->getOldValueDisplay()}' to '{$this->getNewValueDisplay()}'",
            'moved' => "Moved item in hierarchy",
            'deleted' => "Deleted {$this->backlogItem->type}",
            default => $this->action,
        };
    }
    
    public function getOldValueDisplay(): string
    {
        if (is_array($this->old_value)) {
            return implode(', ', array_values($this->old_value));
        }
        return (string) $this->old_value;
    }
    
    public function getNewValueDisplay(): string
    {
        if (is_array($this->new_value)) {
            return implode(', ', array_values($this->new_value));
        }
        return (string) $this->new_value;
    }
    
    public function getFieldNameAttribute(): string
    {
        return $this->field ?? '';
    }
}
