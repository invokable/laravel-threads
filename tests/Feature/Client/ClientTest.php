<?php

declare(strict_types=1);

namespace Tests\Feature\Client;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
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

        $profiles = Threads::token('token')->profiles();

        $this->assertIsArray($profiles);
    }

    public function test_posts()
    {
        Http::fakeSequence()
            ->push([])
            ->whenEmpty(Http::response());

        $posts = Threads::token('token')->posts(limit: 1);

        $this->assertIsArray($posts);
    }

    public function test_single()
    {
        Http::fakeSequence()
            ->push([])
            ->whenEmpty(Http::response());

        $post = Threads::token('token')->single(id: 'id');

        $this->assertIsArray($post);
    }

    public function test_create_text()
    {
        Http::fakeSequence()
            ->push(['id' => 'test'])
            ->whenEmpty(Http::response());

        $id = Threads::token('token')
            ->createText(text: 'test');

        $this->assertSame('test', $id);
    }

    public function test_create_carousel()
    {
        Http::fakeSequence()
            ->push(['id' => 'test'])
            ->whenEmpty(Http::response());

        $id = Threads::token('token')
            ->createCarousel(children: []);

        $this->assertSame('test', $id);
    }

    public function test_status()
    {
        Http::fakeSequence()
            ->push([])
            ->whenEmpty(Http::response());

        $res = Threads::token('token')
            ->status(id: 'id');

        $this->assertIsArray($res);
    }

    public function test_quota()
    {
        Http::fakeSequence()
            ->push([])
            ->whenEmpty(Http::response());

        $res = Threads::token('token')
            ->quota();

        $this->assertIsArray($res);
    }

    public function test_exchange_token()
    {
        Http::fakeSequence()
            ->push([])
            ->whenEmpty(Http::response());

        $res = Threads::exchangeToken('short', 'secret');

        $this->assertIsArray($res);
    }

    public function test_refresh_token()
    {
        Http::fakeSequence()
            ->push([])
            ->whenEmpty(Http::response());

        $res = Threads::token('token')
            ->refreshToken();

        $this->assertIsArray($res);
    }

    public function test_user_trait()
    {
        Http::fakeSequence()
            ->push([])
            ->whenEmpty(Http::response());

        $res = (new TestUser)->threads()->profiles();

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
