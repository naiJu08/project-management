<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days_count',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days_count' => 'decimal:1',
        'approved_at' => 'datetime',
    ];

    protected $appends = [
        'duration_text',
        'is_pending',
        'is_approved',
        'is_rejected',
    ];

    /**
     * Get the user who requested the leave.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the leave type.
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Get the user who approved/rejected the leave.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get human-readable duration text.
     */
    public function durationText(): Attribute
    {
        return new Attribute(
            get: function () {
                if ($this->start_date->eq($this->end_date)) {
                    return $this->start_date->format('M d, Y');
                }
                return $this->start_date->format('M d') . ' - ' . $this->end_date->format('M d, Y');
            }
        );
    }

    /**
     * Check if leave request is pending.
     */
    public function isPending(): Attribute
    {
        return new Attribute(
            get: fn () => $this->status === 'pending'
        );
    }

    /**
     * Check if leave request is approved.
     */
    public function isApproved(): Attribute
    {
        return new Attribute(
            get: fn () => $this->status === 'approved'
        );
    }

    /**
     * Check if leave request is rejected.
     */
    public function isRejected(): Attribute
    {
        return new Attribute(
            get: fn () => $this->status === 'rejected'
        );
    }

    /**
     * Scope to get pending leave requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get approved leave requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get rejected leave requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope to get leave requests for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get leave requests within a date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($q2) use ($startDate, $endDate) {
                  $q2->where('start_date', '<=', $startDate)
                     ->where('end_date', '>=', $endDate);
              });
        });
    }

    /**
     * Approve the leave request.
     */
    public function approve($approverId, $notes = null)
    {
        $this->status = 'approved';
        $this->approved_by = $approverId;
        $this->approved_at = now();
        if ($notes) {
            $this->notes = $notes;
        }
        $this->save();

        // Update leave balance
        $this->updateLeaveBalance();
    }

    /**
     * Reject the leave request.
     */
    public function reject($approverId, $reason)
    {
        $this->status = 'rejected';
        $this->approved_by = $approverId;
        $this->approved_at = now();
        $this->rejection_reason = $reason;
        $this->save();
    }

    /**
     * Cancel the leave request.
     */
    public function cancel()
    {
        $wasApproved = $this->status === 'approved';
        
        $this->status = 'cancelled';
        $this->save();

        // Restore leave balance if it was approved
        if ($wasApproved) {
            $this->restoreLeaveBalance();
        }
    }

    /**
     * Update leave balance after approval.
     */
    protected function updateLeaveBalance()
    {
        $balance = LeaveBalance::firstOrCreate(
            [
                'user_id' => $this->user_id,
                'leave_type_id' => $this->leave_type_id,
                'year' => $this->start_date->year,
            ],
            [
                'total_days' => $this->leaveType->days_per_year,
                'used_days' => 0,
                'remaining_days' => $this->leaveType->days_per_year,
            ]
        );

        $balance->deductDays($this->days_count);
    }

    /**
     * Restore leave balance after cancellation.
     */
    protected function restoreLeaveBalance()
    {
        $balance = LeaveBalance::where('user_id', $this->user_id)
            ->where('leave_type_id', $this->leave_type_id)
            ->where('year', $this->start_date->year)
            ->first();

        if ($balance) {
            $balance->restoreDays($this->days_count);
        }
    }
}
