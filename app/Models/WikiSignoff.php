<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WikiSignoff extends Model
{
    use HasFactory;

    protected $fillable = [
        'wiki_page_id',
        'client_id',
        'signed_off_by',
        'signed_off_at',
        'version_signed',
        'remarks',
        'is_outdated',
    ];

    protected $casts = [
        'signed_off_at' => 'datetime',
        'version_signed' => 'integer',
        'is_outdated' => 'boolean',
    ];

    public function wikiPage(): BelongsTo
    {
        return $this->belongsTo(WikiPage::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function signedOffBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_off_by');
    }

    public function markAsOutdated(): void
    {
        $this->update(['is_outdated' => true]);
    }

    public function isValid(): bool
    {
        return !$this->is_outdated && 
               $this->version_signed === $this->wikiPage->version;
    }
}
