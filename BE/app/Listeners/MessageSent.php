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
        dispatch(new \App\Jobs\markAsRead($event->conversationID, $event->message->id));
        Broadcast(new MessageNotification( $event->message))->toOthers();
    }
}
