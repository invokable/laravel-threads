<?php

declare(strict_types=1);

namespace Tests\Feature\Socialite;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;
use Mockery as m;
use Revolution\Threads\Socialite\ThreadsProvider;
use Tests\TestCase;

class SocialiteTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }

    public function test_instance()
    {
        $provider = Socialite::driver('threads');

        $this->assertInstanceOf(ThreadsProvider::class, $provider);
    }

    public function test_redirect()
    {
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock('Illuminate\Contracts\Session\Session'));
        $session->shouldReceive('put')->once();

        $provider = new ThreadsProvider($request, 'client_id', 'client_secret', 'redirect');
        $response = $provider->redirect();

        $this->assertStringStartsWith('https://threads.net/', $response->getTargetUrl());
    }

    public function test_redirect_generates_correct_url()
    {
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('put')->once();

        $provider = new ThreadsProvider($request, 'client_id', 'client_secret', 'redirect');
        $response = $provider->redirect();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $url = $response->getTargetUrl();
        $this->assertStringStartsWith('https://threads.net/oauth/authorize', $url);
        $this->assertStringContainsString('client_id=client_id', $url);
        $this->assertStringContainsString('redirect_uri=redirect', $url);
        $this->assertStringContainsString('scope=threads_basic%2Cthreads_content_publish', $url);
        $this->assertStringContainsString('response_type=code', $url);
    }

    public function test_redirect_with_custom_scopes()
    {
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('put')->once();

        $provider = new ThreadsProvider($request, 'client_id', 'client_secret', 'redirect');
        $provider->scopes(['threads_basic', 'threads_content_publish', 'threads_manage_insights']);
        $response = $provider->redirect();

        $url = $response->getTargetUrl();
        $this->assertStringContainsString('scope=threads_basic%2Cthreads_content_publish%2Cthreads_manage_insights', $url);
    }

    public function test_get_auth_url_returns_correct_url()
    {
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('put')->once();

        $provider = new ThreadsProvider($request, 'client_id', 'client_secret', 'redirect');

        $url = $provider->redirect()->getTargetUrl();
        $this->assertStringStartsWith('https://threads.net/oauth/authorize', $url);
    }

    public function test_get_token_url_returns_correct_url()
    {
        $request = Request::create('foo');
        $provider = new ThreadsProvider($request, 'client_id', 'client_secret', 'redirect');

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('getTokenUrl');
        $method->setAccessible(true);

        $tokenUrl = $method->invoke($provider);
        $this->assertEquals('https://graph.threads.net/oauth/access_token', $tokenUrl);
    }

    public function test_user_retrieval_with_complete_data()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('A', 40), 'code' => 'code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('pull')->once()->with('state')->andReturn(str_repeat('A', 40));

        $provider = new ThreadsProvider($request, 'client_id', 'client_secret', 'redirect_uri');

        $tokenResponse = new Response(200, [], json_encode([
            'access_token' => 'access_token_123',
            'token_type' => 'bearer',
            'expires_in' => 3600,
        ]));

        $userResponse = new Response(200, [], json_encode([
            'id' => '123456789012345678',
            'username' => 'testuser',
            'name' => 'Test User',
            'threads_profile_picture_url' => 'https://scontent.threads.net/v/t1.0-1/profile.jpg',
            'threads_biography' => 'This is a test biography',
        ]));

        $mock = new MockHandler([$tokenResponse, $userResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $provider->setHttpClient($client);

        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('123456789012345678', $user->getId());
        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals('testuser', $user->getNickname());
        $this->assertEquals('https://scontent.threads.net/v/t1.0-1/profile.jpg', $user->getAvatar());
        $this->assertEquals('access_token_123', $user->token);
        $this->assertEquals(3600, $user->expiresIn);
        $this->assertEquals('This is a test biography', $user->getRaw()['threads_biography']);
    }

    public function test_user_retrieval_with_missing_optional_fields()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('B', 40), 'code' => 'code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('pull')->once()->with('state')->andReturn(str_repeat('B', 40));

        $provider = new ThreadsProvider($request, 'client_id', 'client_secret', 'redirect_uri');

        $tokenResponse = new Response(200, [], json_encode([
            'access_token' => 'access_token_456',
            'token_type' => 'bearer',
            'expires_in' => 7200,
        ]));

        $userResponse = new Response(200, [], json_encode([
            'id' => '987654321098765432',
            'username' => 'testuser2',
            'name' => 'testuser2',
        ]));

        $mock = new MockHandler([$tokenResponse, $userResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $provider->setHttpClient($client);

        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('987654321098765432', $user->getId());
        $this->assertEquals('testuser2', $user->getName());
        $this->assertEquals('testuser2', $user->getNickname());
        $this->assertNull($user->getAvatar());
        $this->assertEquals('access_token_456', $user->token);
        $this->assertEquals(7200, $user->expiresIn);
    }

    public function test_user_retrieval_with_null_avatar()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('C', 40), 'code' => 'code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('pull')->once()->with('state')->andReturn(str_repeat('C', 40));

        $provider = new ThreadsProvider($request, 'client_id', 'client_secret', 'redirect_uri');

        $tokenResponse = new Response(200, [], json_encode([
            'access_token' => 'access_token_789',
            'token_type' => 'bearer',
            'expires_in' => 1800,
        ]));

        $userResponse = new Response(200, [], json_encode([
            'id' => '111222333444555666',
            'username' => 'testuser3',
            'name' => 'Test User 3',
            'threads_profile_picture_url' => null,
            'threads_biography' => 'Biography with null avatar',
        ]));

        $mock = new MockHandler([$tokenResponse, $userResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $provider->setHttpClient($client);

        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('111222333444555666', $user->getId());
        $this->assertEquals('Test User 3', $user->getName());
        $this->assertEquals('testuser3', $user->getNickname());
        $this->assertNull($user->getAvatar());
        $this->assertEquals('Biography with null avatar', $user->getRaw()['threads_biography']);
    }

    public function test_scopes_configuration()
    {
        $request = Request::create('foo');
        $provider = new ThreadsProvider($request, 'client_id', 'client_secret', 'redirect');

        $this->assertEquals(['threads_basic', 'threads_content_publish'], $provider->getScopes());
    }

    public function test_provider_with_custom_scopes()
    {
        $request = Request::create('foo');
        $provider = new ThreadsProvider($request, 'client_id', 'client_secret', 'redirect');

        $provider->scopes(['threads_basic', 'threads_content_publish', 'threads_manage_insights']);

        $this->assertEquals(['threads_basic', 'threads_content_publish', 'threads_manage_insights'], $provider->getScopes());
    }

    public function test_user_profile_request_uses_bearer_token()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('D', 40), 'code' => 'code']);
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('pull')->once()->with('state')->andReturn(str_repeat('D', 40));

        $provider = new ThreadsProvider($request, 'client_id', 'client_secret', 'redirect_uri');

        $tokenResponse = new Response(200, [], json_encode([
            'access_token' => 'bearer_token_test',
            'token_type' => 'bearer',
            'expires_in' => 3600,
        ]));

        $userResponse = new Response(200, [], json_encode([
            'id' => 'test_user_id_123',
            'username' => 'bearertest',
            'name' => 'Bearer Test User',
            'threads_profile_picture_url' => 'https://scontent.threads.net/v/t1.0-1/bearer.jpg',
            'threads_biography' => 'Bearer token test biography',
        ]));

        $mock = new MockHandler([$tokenResponse, $userResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $provider->setHttpClient($client);

        $user = $provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test_user_id_123', $user->getId());
        $this->assertEquals('Bearer Test User', $user->getName());
        $this->assertEquals('bearertest', $user->getNickname());
        $this->assertEquals('bearer_token_test', $user->token);
    }

    public function test_scope_separator_is_comma()
    {
        $request = Request::create('foo');
        $request->setLaravelSession($session = m::mock(Session::class));
        $session->expects('put')->once();

        $provider = new ThreadsProvider($request, 'client_id', 'client_secret', 'redirect');
        $provider->scopes(['threads_basic', 'threads_content_publish', 'threads_manage_insights']);
        $response = $provider->redirect();

        $url = $response->getTargetUrl();
        $this->assertStringContainsString('scope=threads_basic%2Cthreads_content_publish%2Cthreads_manage_insights', $url);
    }

    public function test_get_user_by_token_method()
    {
        $request = Request::create('foo');
        $provider = new ThreadsProvider($request, 'client_id', 'client_secret', 'redirect');

        $userResponse = new Response(200, [], json_encode([
            'id' => 'direct_token_test',
            'username' => 'directtest',
            'name' => 'Direct Token Test',
            'threads_profile_picture_url' => 'https://scontent.threads.net/v/t1.0-1/direct.jpg',
            'threads_biography' => 'Direct token test biography',
        ]));

        $mock = new MockHandler([$userResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $provider->setHttpClient($client);

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('getUserByToken');
        $method->setAccessible(true);

        $userData = $method->invoke($provider, 'test_token');

        $this->assertEquals('direct_token_test', $userData['id']);
        $this->assertEquals('directtest', $userData['username']);
        $this->assertEquals('Direct Token Test', $userData['name']);
        $this->assertEquals('https://scontent.threads.net/v/t1.0-1/direct.jpg', $userData['threads_profile_picture_url']);
        $this->assertEquals('Direct token test biography', $userData['threads_biography']);
    }

    public function test_map_user_to_object_method()
    {
        $request = Request::create('foo');
        $provider = new ThreadsProvider($request, 'client_id', 'client_secret', 'redirect');

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('mapUserToObject');
        $method->setAccessible(true);

        $userData = [
            'id' => 'map_test_id',
            'username' => 'maptest',
            'name' => 'Map Test User',
            'threads_profile_picture_url' => 'https://scontent.threads.net/v/t1.0-1/map.jpg',
            'threads_biography' => 'Map test biography',
        ];

        $user = $method->invoke($provider, $userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('map_test_id', $user->getId());
        $this->assertEquals('Map Test User', $user->getName());
        $this->assertEquals('maptest', $user->getNickname());
        $this->assertEquals('https://scontent.threads.net/v/t1.0-1/map.jpg', $user->getAvatar());
        $this->assertEquals('Map test biography', $user->getRaw()['threads_biography']);
    }

    public function test_user_profile_request_includes_correct_fields()
    {
        $request = Request::create('foo');
        $provider = new ThreadsProvider($request, 'client_id', 'client_secret', 'redirect');

        $userResponse = new Response(200, [], json_encode([
            'id' => 'fields_test_id',
            'username' => 'fieldstest',
            'name' => 'Fields Test User',
            'threads_profile_picture_url' => 'https://scontent.threads.net/v/t1.0-1/fields.jpg',
            'threads_biography' => 'Fields test biography',
        ]));

        $mock = new MockHandler([$userResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $provider->setHttpClient($client);

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('getUserByToken');
        $method->setAccessible(true);

        $userData = $method->invoke($provider, 'test_token');

        $this->assertArrayHasKey('id', $userData);
        $this->assertArrayHasKey('username', $userData);
        $this->assertArrayHasKey('name', $userData);
        $this->assertArrayHasKey('threads_profile_picture_url', $userData);
        $this->assertArrayHasKey('threads_biography', $userData);
    }

    public function test_endpoint_configuration()
    {
        $request = Request::create('foo');
        $provider = new ThreadsProvider($request, 'client_id', 'client_secret', 'redirect');

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('endpoint');
        $property->setAccessible(true);

        $endpoint = $property->getValue($provider);
        $this->assertEquals('https://graph.threads.net/', $endpoint);
    }
}
