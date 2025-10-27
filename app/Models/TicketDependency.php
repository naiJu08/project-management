<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketDependency extends Model
{
    protected $fillable = [
        'ticket_id',
        'depends_on_ticket_id',
        'type',
        'description'
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function dependsOnTicket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'depends_on_ticket_id');
    }

    public function isBlockedBy(): bool
    {
        return $this->type === 'blocked_by';
    }

    public function blocks(): bool
    {
        return $this->type === 'blocks';
    }

    public function isDuplicate(): bool
    {
        return in_array($this->type, ['duplicates', 'duplicated_by']);
    }

    public function isRelated(): bool
    {
        return $this->type === 'related_to';
    }
}
