<?php

namespace App\Events;

use App\Models\ServerConsolelog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServerConsolelogCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public ServerConsolelog $serverConsolelog)
    {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('consolelogs.'.$this->serverConsolelog->server_id);
    }

    /**
     * @return bool
     */
    public function broadcastWhen()
    {
        // Only broadcast if length is less than 10240 which is pusherJS limit
        return strlen($this->serverConsolelog) < 10240;
    }

    public function broadcastWith()
    {
        return ['data' => $this->serverConsolelog];
    }
}
