<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class TestBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $message;

    public function __construct($userId, $message)
    {
        $this->userId = $userId;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new Channel('voice-call.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'TestBroadcast';
    }
}
