<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\RequestException;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Sleep;
use Revolution\Threads\Notifications\ThreadsChannel;
use Revolution\Threads\Notifications\ThreadsMessage;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    public function test_notification_text()
    {
        Http::fakeSequence()
            ->push(['id' => 'id'])
            ->whenEmpty(Http::response());

        Notification::route('threads', 'token')
            ->notify(new TestNotification(text: 'test'));

        Http::assertSentCount(2);

        $recorded = Http::recorded();

        $this->assertSame('test', $recorded[0][0]['text']);
        $this->assertSame('id', $recorded[1][0]['creation_id']);
    }

    public function test_notification_image()
    {
        Http::fakeSequence()
            ->push(['id' => 'id'])
            ->whenEmpty(Http::response());

        Notification::route('threads', 'token')
            ->notify(new TestImageNotification(text: 'test'));

        Http::assertSentCount(2);

        $recorded = Http::recorded();

        $this->assertSame('test', $recorded[0][0]['text']);
        $this->assertSame('id', $recorded[1][0]['creation_id']);
    }

    public function test_notification_video()
    {
        Http::fakeSequence()
            ->push(['id' => 'id'])
            ->whenEmpty(Http::response());

        Sleep::fake();

        Notification::route('threads', 'token')
            ->notify(new TestVideoNotification(text: 'test'));

        Http::assertSentCount(2);
        Sleep::assertSleptTimes(1);

        $recorded = Http::recorded();

        $this->assertSame('test', $recorded[0][0]['text']);
        $this->assertSame('id', $recorded[1][0]['creation_id']);
    }

    public function test_notification_fake()
    {
        Notification::fake();

        Notification::route('threads', 'token')
            ->notify(new TestNotification(text: 'test'));

        Notification::assertSentOnDemand(TestNotification::class);
    }

    public function test_message()
    {
        $m = new ThreadsMessage(text: 'test');
        $m2 = ThreadsMessage::create(text: 'test');

        $this->assertIsArray($m->toArray());
        $this->assertSame('test', $m->toArray()['text']);
        $this->assertSame('test', $m2->toArray()['text']);
    }

    public function test_message_image()
    {
        $m = ThreadsMessage::create(text: 'test')->withImage('url');

        $this->assertSame('url', $m->image_url);
    }

    public function test_message_video()
    {
        $m = ThreadsMessage::create(text: 'test')->withVideo('url');

        $this->assertSame('url', $m->video_url);
    }

    public function test_user_notify()
    {
        Http::fake(fn () => Http::response(['id' => 'id']));

        $user = new TestUser;

        $user->notify(new TestNotification(text: 'test'));

        Http::assertSentCount(2);
    }
}

class TestNotification extends \Illuminate\Notifications\Notification
{
    public function __construct(
        protected string $text,
    ) {}

    public function via(object $notifiable): array
    {
        return [ThreadsChannel::class];
    }

    public function toThreads(object $notifiable): ThreadsMessage
    {
        return ThreadsMessage::create(text: $this->text);
    }
}

class TestImageNotification extends \Illuminate\Notifications\Notification
{
    public function __construct(
        protected string $text,
    ) {}

    public function via(object $notifiable): array
    {
        return [ThreadsChannel::class];
    }

    public function toThreads(object $notifiable): ThreadsMessage
    {
        return ThreadsMessage::create(text: $this->text)->withImage('url');
    }
}

class TestVideoNotification extends \Illuminate\Notifications\Notification
{
    public function __construct(
        protected string $text,
    ) {}

    public function via(object $notifiable): array
    {
        return [ThreadsChannel::class];
    }

    public function toThreads(object $notifiable): ThreadsMessage
    {
        return ThreadsMessage::create(text: $this->text)->withVideo('url');
    }
}

class TestUser extends Model
{
    use Notifiable;

    public function routeNotificationForThreads($notification): string
    {
        return 'token';
    }
}
