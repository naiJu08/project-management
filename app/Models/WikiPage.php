<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class WikiPage extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'project_id', 'title', 'content', 'parent_id', 
        'version', 'created_by', 'updated_by', 'order',
        'client_visible', 'client_visible_at'
    ];

    protected $casts = [
        'version' => 'integer',
        'order' => 'integer',
        'client_visible' => 'boolean',
        'client_visible_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(WikiPage::class, 'parent_id', 'id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(WikiPage::class, 'parent_id', 'id')->orderBy('order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(WikiComment::class)->whereNull('parent_comment_id')->orderBy('created_at');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(WikiComment::class)->orderBy('created_at');
    }

    public function signoffs(): HasMany
    {
        return $this->hasMany(WikiSignoff::class)->orderBy('signed_off_at', 'desc');
    }

    public function activeSignoffs(): HasMany
    {
        return $this->hasMany(WikiSignoff::class)
            ->where('is_outdated', false)
            ->where('version_signed', $this->version)
            ->orderBy('signed_off_at', 'desc');
    }

    public function isSignedOff(): bool
    {
        return $this->activeSignoffs()->exists();
    }

    public function getSignoffStatus(): string
    {
        if ($this->isSignedOff()) {
            return 'Signed-Off';
        }
        
        if ($this->signoffs()->where('is_outdated', true)->exists()) {
            return 'Outdated';
        }
        
        return 'Pending';
    }

    public function makeClientVisible(): void
    {
        $this->update([
            'client_visible' => true,
            'client_visible_at' => now(),
        ]);
    }

    public function hideFromClient(): void
    {
        $this->update([
            'client_visible' => false,
            'client_visible_at' => null,
        ]);
    }

    public function scopeClientVisible($query)
    {
        return $query->where('client_visible', true);
    }

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($wikiPage) {
            // If content or title changed, mark existing signoffs as outdated
            if ($wikiPage->isDirty(['content', 'title'])) {
                $wikiPage->signoffs()->where('is_outdated', false)->update(['is_outdated' => true]);
            }
        });

        static::deleting(function ($wikiPage) {
            // Delete all comments
            $wikiPage->allComments()->delete();
            
            // Delete all signoffs
            $wikiPage->signoffs()->delete();
            
            // Delete all child pages
            $wikiPage->children()->each(function ($child) {
                $child->delete();
            });
        });
    }
}
