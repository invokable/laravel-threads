<?php

declare(strict_types=1);

namespace Revolution\Threads;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Threads\Contracts\Factory;
use Revolution\Threads\Enums\MediaType;

class ThreadsClient implements Factory
{
    use Macroable;
    use Conditionable;

    protected string $token = '';

    protected string $base_url = 'https://graph.threads.net/v1.0/';

    public function token(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function baseUrl(string $base_url): static
    {
        $this->base_url = $base_url;

        return $this;
    }

    /**
     * My profiles.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function profiles(?array $fields = null): array
    {
        $fields ??= [
            'id',
            'username',
            'threads_profile_picture_url',
            'threads_biography',
        ];

        $response = $this->http()
            ->get('me', [
                'fields' => Arr::join($fields, ','),
            ])->throw();

        return $response->json() ?? [];
    }

    /**
     * My posts.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function posts(int $limit = 25, ?array $fields = null, ?string $before = null, ?string $after = null, ?string $since = null, ?string $until = null): array
    {
        $fields ??= [
            'id',
            'media_product_type',
            'media_type',
            'media_url',
            'permalink',
            'owner',
            'username',
            'text',
            'timestamp',
            'shortcode',
            'thumbnail_url',
            'children',
            'is_quote_post',
        ];

        $response = $this->http()
            ->get('me/threads', [
                'fields' => Arr::join($fields, ','),
                'limit' => $limit,
                'before' => $before,
                'after' => $after,
                'since' => $since,
                'until' => $until,
            ])->throw();

        return $response->json() ?? [];
    }

    /**
     * Get Single Threads Media.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function single(string $id, ?array $fields = null): array
    {
        $fields ??= [
            'id',
            'media_product_type',
            'media_type',
            'media_url',
            'permalink',
            'owner',
            'username',
            'text',
            'timestamp',
            'shortcode',
            'thumbnail_url',
            'children',
            'is_quote_post',
        ];

        $response = $this->http()
            ->get($id, [
                'fields' => Arr::join($fields, ','),
            ])->throw();

        return $response->json() ?? [];
    }

    /**
     * Publish Threads Media Container.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function publish(string $creation_id, int $sleep = 0): array
    {
        if ($sleep > 0) {
            Sleep::sleep($sleep);
        }

        $response = $this->http()
            ->post('me/threads_publish', [
                'creation_id' => $creation_id,
            ])->throw();

        return $response->json() ?? [];
    }

    /**
     * Create Text Container.
     *
     * @return string Threads Media Container ID
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createText(string $text): string
    {
        $response = $this->http()
            ->post('me/threads', [
                'media_type' => MediaType::TEXT->name,
                'text' => $text,
            ])->throw();

        return $response->json('id', '');
    }

    /**
     * Create Image Container.
     *
     * @return string Threads Media Container ID
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createImage(string $image_url, ?string $text = null, bool $is_carousel = false): string
    {
        $response = $this->http()
            ->post('me/threads', [
                'media_type' => MediaType::IMAGE->name,
                'image_url' => $image_url,
                'text' => $text,
                'is_carousel_item' => $is_carousel,
            ])->throw();

        return $response->json('id', '');
    }

    /**
     * Create Video Container.
     *
     * @return string Threads Media Container ID
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createVideo(string $video_url, ?string $text = null, bool $is_carousel = false): string
    {
        $response = $this->http()
            ->post('me/threads', [
                'media_type' => MediaType::VIDEO->name,
                'video_url' => $video_url,
                'text' => $text,
                'is_carousel_item' => $is_carousel,
            ])->throw();

        return $response->json('id', '');
    }

    /**
     * Create Carousel Container.
     *
     * @param  array  $children Container IDs
     * @return string Threads Media Container ID
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createCarousel(array $children, ?string $text = null): string
    {
        $response = $this->http()
            ->post('me/threads', [
                'media_type' => MediaType::CAROUSEL->name,
                'children' => Arr::join($children, ','),
                'text' => $text,
            ])->throw();

        return $response->json('id', '');
    }

    /**
     * Publishing status.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function status(string $creation_id, ?array $fields = null): array
    {
        $fields ??= [
            'status',
            'error_message',
        ];

        $response = $this->http()
            ->get($creation_id, [
                'fields' => Arr::join($fields, ','),
            ])->throw();

        return $response->json() ?? [];
    }

    /**
     * Publishing Quota Limit.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function quota(?array $fields = null): array
    {
        $fields ??= [
            'quota_usage',
            'config',
        ];

        $response = $this->http()
            ->get('me/threads_publishing_limit', [
                'fields' => Arr::join($fields, ','),
            ])->throw();

        return $response->json() ?? [];
    }

    /**
     * @throws RequestException
     */
    public function refreshToken(): array
    {
        $response = Http::get('https://graph.threads.net/refresh_access_token', [
            'grant_type' => 'th_refresh_token',
            'access_token' => $this->token,
        ])->throw();

        return $response->json() ?? [];
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl($this->base_url)
            ->withToken($this->token);
    }
}
