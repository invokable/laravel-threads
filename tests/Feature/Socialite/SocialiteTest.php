<?php

declare(strict_types=1);

namespace Tests\Feature\Socialite;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
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
}
