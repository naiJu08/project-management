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

    public function getProcessedContentAttribute()
    {
        $content = $this->content;
        
        if (!$content) {
            return $content;
        }
        
        // Process media attachments in content
        // Replace Trix editor attachment placeholders with actual media URLs
        $content = preg_replace_callback('/data-trix-attachment="([^"]+)"/', function ($matches) {
            try {
                $attachmentData = json_decode(html_entity_decode($matches[1]), true);
                
                if (isset($attachmentData['url']) && str_contains($attachmentData['url'], 'blob:')) {
                    // This is a blob URL, need to find the corresponding media
                    $media = $this->getMedia()->first(function ($item) use ($attachmentData) {
                        return $item->file_name === ($attachmentData['filename'] ?? null) || 
                               $item->name === ($attachmentData['name'] ?? null);
                    });
                    
                    if ($media) {
                        $attachmentData['url'] = $media->getUrl();
                        return 'data-trix-attachment="' . htmlspecialchars(json_encode($attachmentData)) . '"';
                    }
                }
            } catch (\Exception $e) {
                // If processing fails, return original
                return $matches[0];
            }
            
            return $matches[0];
        }, $content);
        
        // Also process any remaining image references that might be stored as media
        $content = preg_replace_callback('/src="([^"]*)"/', function ($matches) {
            $src = $matches[1];
            
            // Skip if it's already a full URL or data URL
            if (str_starts_with($src, 'http') || str_starts_with($src, 'data:')) {
                return $matches[0];
            }
            
            // Try to find matching media for any image src
            $media = $this->getMedia()->first(function ($item) use ($src) {
                return str_contains($src, $item->file_name) || 
                       str_contains($src, $item->id) ||
                       str_contains($src, $item->name);
            });
            
            if ($media) {
                return 'src="' . $media->getUrl() . '"';
            }
            
            return $matches[0];
        }, $content);
        
        // Process figure elements that contain images
        $content = preg_replace_callback('/<figure[^>]*>.*?<\/figure>/s', function ($matches) {
            $figure = $matches[0];
            
            // Extract any image src from the figure
            if (preg_match('/src="([^"]*)"/', $figure, $imgMatches)) {
                $src = $imgMatches[1];
                
                // Skip if it's already a full URL or data URL
                if (!str_starts_with($src, 'http') && !str_starts_with($src, 'data:')) {
                    // Try to find matching media
                    $media = $this->getMedia()->first(function ($item) use ($src) {
                        return str_contains($src, $item->file_name) || 
                               str_contains($src, $item->id) ||
                               str_contains($src, $item->name);
                    });
                    
                    if ($media) {
                        $figure = str_replace($src, $media->getUrl(), $figure);
                    }
                }
            }
            
            // Remove any gray borders from figure elements
            $figure = preg_replace('/style="[^"]*border[^"]*"/', '', $figure);
            $figure = preg_replace('/class="([^"]*)attachment([^"]*)"/', 'class="$1$2"', $figure);
            
            return $figure;
        }, $content);
        
        return $content;
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
