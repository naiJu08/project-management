<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BacklogItemComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'backlog_item_id',
        'user_id',
        'parent_comment_id',
        'content',
    ];

    protected $with = ['user', 'replies'];

    public function backlogItem(): BelongsTo
    {
        return $this->belongsTo(BacklogItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(BacklogItemComment::class, 'parent_comment_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(BacklogItemComment::class, 'parent_comment_id')->orderBy('created_at');
    }

    public function canDelete(): bool
    {
        $user = auth()->user();
        
        if ($user && $user->id === $this->user_id) {
            return true;
        }
        
        if ($user && $user->can('Delete backlog item')) {
            return true;
        }
        
        return false;
    }
}
