<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
// Remove: use Illuminate\Queue\SerializesModels;

class CallOffer implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets; // Removed SerializesModels

    public $offer;
    public $callerId;
    public $callerName;
    public $receiverId;

    public function __construct($offer, $callerId, $callerName, $receiverId)
    {
        $this->offer = $offer;
        $this->callerId = $callerId;
        $this->callerName = $callerName;
        $this->receiverId = $receiverId;
    }

    public function broadcastOn()
    {
        return new Channel('voice-call.' . $this->receiverId);
    }

    public function broadcastAs()
    {
        return 'CallOffer';
    }

    public function broadcastWith()
    {
        return [
            'offer' => $this->offer,
            'callerId' => $this->callerId,
            'callerName' => $this->callerName,
            'receiverId' => $this->receiverId
        ];
    }
}