<?php

declare(strict_types=1);

namespace Tests\Feature\Socialite;

use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;
use Revolution\Threads\Socialite\ThreadsProvider;
use Tests\TestCase;

class SocialiteTest extends TestCase
{
    public function test_instance()
    {
        $provider = Socialite::driver('threads');

        $this->assertInstanceOf(ThreadsProvider::class, $provider);
    }

    public function test_redirect()
    {
        Socialite::fake('threads');

        $response = Socialite::driver('threads')->redirect();

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function test_user()
    {
        Socialite::fake('threads', (new User)->map([
            'id' => '123456789012345678',
            'nickname' => 'testuser',
            'name' => 'Test User',
            'avatar' => 'https://scontent.threads.net/v/t1.0-1/profile.jpg',
        ])->setToken('access_token_123'));

        $user = Socialite::driver('threads')->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('123456789012345678', $user->getId());
        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals('testuser', $user->getNickname());
        $this->assertEquals('https://scontent.threads.net/v/t1.0-1/profile.jpg', $user->getAvatar());
        $this->assertEquals('access_token_123', $user->token);
    }

    public function test_user_with_null_avatar()
    {
        Socialite::fake('threads', (new User)->map([
            'id' => '111222333444555666',
            'nickname' => 'testuser3',
            'name' => 'Test User 3',
            'avatar' => null,
        ]));

        $user = Socialite::driver('threads')->user();

        $this->assertNull($user->getAvatar());
    }

    public function test_scopes_configuration()
    {
        $provider = Socialite::driver('threads');

        $this->assertEquals(['threads_basic', 'threads_content_publish', 'threads_delete', 'threads_keyword_search'], $provider->getScopes());
    }

    public function test_provider_with_custom_scopes()
    {
        $provider = Socialite::driver('threads');
        $provider->scopes(['threads_manage_insights']);

        $this->assertEquals(['threads_basic', 'threads_content_publish', 'threads_delete', 'threads_keyword_search', 'threads_manage_insights'], $provider->getScopes());
    }
}
