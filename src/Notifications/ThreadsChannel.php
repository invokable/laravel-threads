<?php

declare(strict_types=1);

namespace Revolution\Threads\Notifications;

use Illuminate\Notifications\Notification;
use Revolution\Threads\Facades\Threads;

class ThreadsChannel
{
    public function send(mixed $notifiable, Notification $notification): void
    {
        /** @var ThreadsMessage $message */
        $message = $notification->toThreads($notifiable);

        if (! $message instanceof ThreadsMessage) {
            return; // @codeCoverageIgnore
        }

        $token = $notifiable->routeNotificationFor('threads', $notification);

        Threads::token($token);

        if (filled($message->video_url)) {
            $response = Threads::createVideo($message->video_url, $message->text);
        } elseif (filled($message->image_url)) {
            $response = Threads::createImage($message->image_url, $message->text);
        } else {
            $response = Threads::createText($message->text);
        }

        if ($response->failed()) {
            return;
        }

        Threads::publish($response->json('id', ''), $message->sleep);
    }
}
