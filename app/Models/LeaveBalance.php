<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'year',
        'total_days',
        'used_days',
        'remaining_days',
    ];

    protected $casts = [
        'year' => 'integer',
        'total_days' => 'decimal:1',
        'used_days' => 'decimal:1',
        'remaining_days' => 'decimal:1',
    ];

    /**
     * Get the user that owns this leave balance.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the leave type for this balance.
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Update the remaining days after a leave request.
     */
    public function updateBalance()
    {
        $this->remaining_days = $this->total_days - $this->used_days;
        $this->save();
    }

    /**
     * Check if user has sufficient balance for requested days.
     */
    public function hasSufficientBalance(float $requestedDays): bool
    {
        return $this->remaining_days >= $requestedDays;
    }

    /**
     * Deduct days from balance.
     */
    public function deductDays(float $days)
    {
        $this->used_days += $days;
        $this->updateBalance();
    }

    /**
     * Restore days to balance (e.g., when leave is cancelled).
     */
    public function restoreDays(float $days)
    {
        $this->used_days = max(0, $this->used_days - $days);
        $this->updateBalance();
    }

    /**
     * Scope to get balances for current year.
     */
    public function scopeCurrentYear($query)
    {
        return $query->where('year', now()->year);
    }

    /**
     * Scope to get balances for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
