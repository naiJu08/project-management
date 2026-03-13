<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'certificate_template_id', 'type', 'issued_on', 'issued_by', 'data', 'html_snapshot', 'pdf_path'
    ];

    protected $casts = [
        'issued_on' => 'date',
        'data' => 'array',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function template(): BelongsTo { return $this->belongsTo(CertificateTemplate::class, 'certificate_template_id'); }
}
