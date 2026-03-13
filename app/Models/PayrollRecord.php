<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','period_start','period_end','basic','allowances','deductions','gross','net','generated_at','payslip_template_id','html_snapshot','pdf_path'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'allowances' => 'array',
        'deductions' => 'array',
        'gross' => 'decimal:2',
        'net' => 'decimal:2',
        'generated_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function template(): BelongsTo { return $this->belongsTo(PayslipTemplate::class, 'payslip_template_id'); }
}
