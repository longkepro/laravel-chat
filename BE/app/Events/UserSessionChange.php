<?php

namespace App\Events;
use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserSessionChange implements ShouldBroadcast{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $type;

    /**
     * Create a new event instance.
     */
    public function __construct($message, $type)
    {
        //
        $this->message = $message;
        $this->type = $type;
        
        Log::info( 'UserSessionChange event constructed at ' . now()->toIso8601String());
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        //Log::debug("{$this->message}, {$this->type}");

         return [new Channel('notifications')];
    }
    public function broadcastAs()
    {
        return 'UserSessionChange';
    }
}
