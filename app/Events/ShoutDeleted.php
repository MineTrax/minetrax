<?php

namespace App\Events;

use App\Models\Shout;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ShoutDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected Shout $shout;
    public function __construct(Shout $shout)
    {
        $this->shout = $shout;
    }

    public function broadcastOn()
    {
        return new Channel('shouts');
    }

    public function broadcastWith()
    {
        return ['data' => $this->shout->load('user:id,name,username,profile_photo_path')];
    }
}
