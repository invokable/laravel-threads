<?php

declare(strict_types=1);

namespace Tests\Feature\Client;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Revolution\Threads\Enums\ReplyControl;
use Revolution\Threads\Facades\Threads;
use Revolution\Threads\ThreadsClient;
use Revolution\Threads\Traits\WithThreads;
use Tests\TestCase;

class ClientTest extends TestCase
{
    public function test_client()
    {
        $client = new ThreadsClient();
        $client->token('token')->when(true, function (ThreadsClient $client) {
            return $client->baseUrl('url')->apiVersion('v1.0');
        });

        $this->assertInstanceOf(ThreadsClient::class, $client);
    }

    public function test_profiles()
    {
        Http::fakeSequence()
            ->push([])
            ->whenEmpty(Http::response());

        $profiles = Threads::token('token')->profiles()->json();

        $this->assertIsArray($profiles);
    }

    public function test_posts()
    {
        Http::fakeSequence()
            ->push([])
            ->whenEmpty(Http::response());

        $posts = Threads::token('token')->posts(limit: 1)->json();

        $this->assertIsArray($posts);
    }

    public function test_single()
    {
        Http::fakeSequence()
            ->push([])
            ->whenEmpty(Http::response());

        $post = Threads::token('token')->single(id: 'id')->json();

        $this->assertIsArray($post);
    }

    public function test_create_text()
    {
        Http::fakeSequence()
            ->push(['id' => 'test'])
            ->whenEmpty(Http::response());

        $id = Threads::token('token')
            ->createText(text: 'test', reply_control: ReplyControl::EVERYONE, reply_to_id: '1', options: [])
            ->json('id');

        $this->assertSame('test', $id);
    }

    public function test_create_image()
    {
        Http::fakeSequence()
            ->push(['id' => 'test'])
            ->whenEmpty(Http::response());

        $id = Threads::token('token')
            ->createImage(url: 'url', text: 'test', reply_control: ReplyControl::MENTIONED, reply_to_id: '1')
            ->json('id');

        $this->assertSame('test', $id);
    }

    public function test_create_video()
    {
        Http::fakeSequence()
            ->push(['id' => 'test'])
            ->whenEmpty(Http::response());

        $id = Threads::token('token')
            ->createVideo(url: 'url', text: 'test', reply_control: ReplyControl::MENTIONED, reply_to_id: '1')
            ->json('id');

        $this->assertSame('test', $id);
    }

    public function test_create_carousel()
    {
        Http::fakeSequence()
            ->push(['id' => 'test'])
            ->whenEmpty(Http::response());

        $id = Threads::token('token')
            ->createCarousel(children: [], reply_control: ReplyControl::FOLLOW, reply_to_id: '1')
            ->json('id');

        $this->assertSame('test', $id);
    }

    public function test_status()
    {
        Http::fakeSequence()
            ->push([])
            ->whenEmpty(Http::response());

        $res = Threads::token('token')
            ->status(id: 'id')
            ->json();

        $this->assertIsArray($res);
    }

    public function test_quota()
    {
        Http::fakeSequence()
            ->push([])
            ->whenEmpty(Http::response());

        $res = Threads::token('token')
            ->quota()
            ->json();

        $this->assertIsArray($res);
    }

    public function test_exchange_token()
    {
        Http::fakeSequence()
            ->push([])
            ->whenEmpty(Http::response());

        $res = Threads::exchangeToken('short', 'secret')
            ->json();

        $this->assertIsArray($res);
    }

    public function test_refresh_token()
    {
        Http::fakeSequence()
            ->push([])
            ->whenEmpty(Http::response());

        $res = Threads::token('token')
            ->refreshToken()
            ->json();

        $this->assertIsArray($res);
    }

    public function test_user_trait()
    {
        Http::fakeSequence()
            ->push([])
            ->whenEmpty(Http::response());

        $res = (new TestUser)->threads()->profiles()
            ->json();

        $this->assertIsArray($res);
    }
}

class TestUser extends Model
{
    use WithThreads;

    public function tokenForThreads(): string
    {
        return 'token';
    }
}
