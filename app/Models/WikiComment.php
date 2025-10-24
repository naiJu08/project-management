<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WikiComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'wiki_page_id',
        'user_id',
        'parent_comment_id',
        'content',
    ];

    protected $with = ['user', 'replies'];

    public function wikiPage(): BelongsTo
    {
        return $this->belongsTo(WikiPage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(WikiComment::class, 'parent_comment_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(WikiComment::class, 'parent_comment_id')->orderBy('created_at');
    }

    public function isClientComment(): bool
    {
        return $this->user && $this->user->hasRole('Client');
    }

    public function canDelete(): bool
    {
        $user = auth()->user();
        
        // User can delete their own comments
        if ($user && $user->id === $this->user_id) {
            return true;
        }
        
        // Admins can delete any comment
        if ($user && $user->can('Delete wiki page')) {
            return true;
        }
        
        return false;
    }
}
