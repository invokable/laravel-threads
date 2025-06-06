<?php

namespace Revolution\Threads\Socialite;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class ThreadsProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [
        'threads_basic',
        'threads_content_publish',
        'threads_delete',
    ];

    protected string $endpoint = 'https://graph.threads.net/';

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ',';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state): string
    {
        $url = 'https://threads.net/oauth/authorize';

        return $this->buildAuthUrlFromBase($url, $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return $this->endpoint.'oauth/access_token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken(#[\SensitiveParameter] $token)
    {
        $response = $this->getHttpClient()
            ->get($this->endpoint.'v1.0/me', [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer '.$token,
                ],
                RequestOptions::QUERY => [
                    'fields' => 'id,username,name,threads_profile_picture_url,threads_biography',
                ],
            ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user): User
    {
        return (new User)->setRaw($user)->map([
            'id' => Arr::get($user, 'id'), // Threads user ID. This is returned by default.
            'nickname' => Arr::get($user, 'username'), // Handle or unique username on Threads.
            'name' => Arr::get($user, 'name'), // Display name of the user on Threads.
            'avatar' => Arr::get($user, 'threads_profile_picture_url'), // URL of the user's profile picture on Threads.
            'threads_biography' => Arr::get($user, 'threads_biography'), // Biography text on Threads profile.
        ]);
    }
}
