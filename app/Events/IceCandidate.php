<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class IceCandidate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $candidate;
    public $senderId;
    public $receiverId;

    public function __construct($candidate, $senderId, $receiverId)
    {
        $this->candidate = $candidate;
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
    }

    public function broadcastOn()
    {
        return new Channel('voice-call.' . $this->receiverId);
    }

    public function broadcastAs()
    {
        return 'IceCandidate';
    }

    public function broadcastWith()
    {
        return [
            'candidate' => $this->candidate,
            'senderId' => $this->senderId,
            'receiverId' => $this->receiverId
        ];
    }
}