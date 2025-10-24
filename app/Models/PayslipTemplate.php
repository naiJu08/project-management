<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayslipTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'html', 'placeholders', 'is_default', 'is_active'
    ];

    protected $casts = [
        'placeholders' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];
}
