<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketApproval extends Model
{
    protected $fillable = [
        'ticket_id',
        'approver_id',
        'type',
        'status',
        'comments',
        'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function approve(string $comments = null): void
    {
        $this->update([
            'status' => 'approved',
            'comments' => $comments,
            'approved_at' => now()
        ]);
    }

    public function reject(string $comments = null): void
    {
        $this->update([
            'status' => 'rejected',
            'comments' => $comments,
            'approved_at' => now()
        ]);
    }

    public function approveWithComments(string $comments): void
    {
        $this->update([
            'status' => 'approved_with_comments',
            'comments' => $comments,
            'approved_at' => now()
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return in_array($this->status, ['approved', 'approved_with_comments']);
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
