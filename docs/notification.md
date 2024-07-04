Laravel Notifications
====

## Notification class
```php
use Illuminate\Notifications\Notification;
use Revolution\Threads\Notifications\ThreadsChannel;
use Revolution\Threads\Notifications\ThreadsMessage;

class TestNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return [
            ThreadsChannel::class
        ];
    }

    public function toThreads(object $notifiable): ThreadsMessage
    {
        return ThreadsMessage::create(text: 'test');
    }
}
```

### With Image
The url must be a public url.

```php
    public function toThreads(object $notifiable): ThreadsMessage
    {
        return ThreadsMessage::create(text: 'test')->withImage(url: 'https://.../cat.png');
    }
```

### With Video
You can only notify one of the following: Text only, Text with image, or Text with video.

```php
    public function toThreads(object $notifiable): ThreadsMessage
    {
        return ThreadsMessage::create(text: 'test')->withVideo(url: 'https://.../dog.mov');
    }
```

## On-Demand Notifications
```php
use Illuminate\Support\Facades\Notification;

Notification::route('threads', $token)
            ->notify(new TestNotification());
```

## User Notifications
```php
use Illuminate\Notifications\Notifiable;

class User
{
    use Notifiable;

    public function routeNotificationForThreads($notification): string
    {
        return $this->threads_token;
    }
}
```

```php
$user->notify(new TestNotification());
```
