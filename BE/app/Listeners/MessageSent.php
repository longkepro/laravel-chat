<?php

namespace App\Listeners;

use App\Events\MessageNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;

class MessageSent implements ShouldQueue
{
    /**
     * Create the event listener.
     */

    public function handle(object $event): void
    {
        Broadcast(new MessageNotification( $event->message))->toOthers();
    }
}
