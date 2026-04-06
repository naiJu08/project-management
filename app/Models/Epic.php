<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Epic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'project_id', 'starts_at', 'ends_at',
        'parent_id'
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (Epic $epic) {
            if ($epic->starts_at && $epic->ends_at && $epic->ends_at < $epic->starts_at) {
                throw ValidationException::withMessages([
                    'ends_at' => 'End date cannot be before start date'
                ]);
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'epic_id', 'id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Epic::class, 'parent_id', 'id');
    }

    public function sprint(): HasOne
    {
        return $this->hasOne(Sprint::class, 'epic_id', 'id');
    }
}
