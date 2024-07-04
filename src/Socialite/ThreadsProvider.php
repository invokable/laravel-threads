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
    ];

    /**
     * @var string
     */
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
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()
            ->get($this->endpoint.'v1.0/me', [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer '.$token,
                ],
                RequestOptions::QUERY => [
                    'fields' => 'id,username,threads_profile_picture_url,threads_biography',
                ],
            ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user): User
    {
        return (new User())->setRaw($user)->map([
            'id' => Arr::get($user, 'id'),
            'nickname' => Arr::get($user, 'username'),
            'name' => Arr::get($user, 'username'),
            'avatar' => Arr::get($user, 'threads_profile_picture_url'),
        ]);
    }
}
