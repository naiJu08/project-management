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
            'updated' => "Updated {$this->field} from '{$this->old_value}' to '{$this->new_value}'",
            'moved' => "Moved to {$this->new_value}",
            'deleted' => "Deleted {$this->backlogItem->type}",
            default => $this->action,
        };
    }
}
