<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
// Remove: use Illuminate\Queue\SerializesModels;

class CallAnswer implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets; // Removed SerializesModels

    public $answer;
    public $callerId;
    public $receiverId;

    public function __construct($answer, $callerId, $receiverId)
    {
        $this->answer = $answer;
        $this->callerId = $callerId;
        $this->receiverId = $receiverId;
    }

    public function broadcastOn()
    {
        return new Channel('voice-call.' . $this->receiverId);
    }

    public function broadcastAs()
    {
        return 'CallAnswer';
    }

    public function broadcastWith()
    {
        return [
            'answer' => $this->answer,
            'callerId' => $this->callerId,
            'receiverId' => $this->receiverId
        ];
    }
}