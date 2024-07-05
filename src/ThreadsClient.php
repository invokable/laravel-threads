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

    protected string $base_url = 'https://graph.threads.net/';

    protected string $api_version = 'v1.0';

    protected array $post_default_fields = [
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

    public function token(#[\SensitiveParameter] string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function baseUrl(string $base_url): static
    {
        $this->base_url = $base_url;

        return $this;
    }

    public function apiVersion(string $api_version): static
    {
        $this->api_version = $api_version;

        return $this;
    }

    public function profiles(string $user = 'me', ?array $fields = null): array
    {
        $fields ??= [
            'id',
            'username',
            'threads_profile_picture_url',
            'threads_biography',
        ];

        $response = $this->http()
            ->get($user, [
                'fields' => Arr::join($fields, ','),
            ]);

        return $response->json() ?? [];
    }

    public function posts(string $user = 'me', int $limit = 25, ?array $fields = null, ?string $before = null, ?string $after = null, ?string $since = null, ?string $until = null): array
    {
        $fields ??= $this->post_default_fields;

        $response = $this->http()
            ->get($user.'/threads', [
                'fields' => Arr::join($fields, ','),
                'limit' => $limit,
                'before' => $before,
                'after' => $after,
                'since' => $since,
                'until' => $until,
            ]);

        return $response->json() ?? [];
    }

    public function single(string $id, ?array $fields = null): array
    {
        $fields ??= $this->post_default_fields;

        $response = $this->http()
            ->get($id, [
                'fields' => Arr::join($fields, ','),
            ]);

        return $response->json() ?? [];
    }

    public function publish(string $id, int $sleep = 0): array
    {
        if ($sleep > 0) {
            Sleep::sleep($sleep);
        }

        $response = $this->http()
            ->post('me/threads_publish', [
                'creation_id' => $id,
            ]);

        return $response->json() ?? [];
    }

    public function createText(string $text): string
    {
        $response = $this->http()
            ->post('me/threads', [
                'media_type' => MediaType::TEXT->name,
                'text' => $text,
            ]);

        return $response->json('id', '');
    }

    public function createImage(string $url, ?string $text = null, bool $is_carousel = false): string
    {
        $response = $this->http()
            ->post('me/threads', [
                'media_type' => MediaType::IMAGE->name,
                'image_url' => $url,
                'text' => $text,
                'is_carousel_item' => $is_carousel,
            ]);

        return $response->json('id', '');
    }

    public function createVideo(string $url, ?string $text = null, bool $is_carousel = false): string
    {
        $response = $this->http()
            ->post('me/threads', [
                'media_type' => MediaType::VIDEO->name,
                'video_url' => $url,
                'text' => $text,
                'is_carousel_item' => $is_carousel,
            ]);

        return $response->json('id', '');
    }

    public function createCarousel(array $children, ?string $text = null): string
    {
        $response = $this->http()
            ->post('me/threads', [
                'media_type' => MediaType::CAROUSEL->name,
                'children' => Arr::join($children, ','),
                'text' => $text,
            ]);

        return $response->json('id', '');
    }

    public function status(string $id, ?array $fields = null): array
    {
        $fields ??= [
            'status',
            'error_message',
        ];

        $response = $this->http()
            ->get($id, [
                'fields' => Arr::join($fields, ','),
            ]);

        return $response->json() ?? [];
    }

    public function quota(string $user = 'me', ?array $fields = null): array
    {
        $fields ??= [
            'quota_usage',
            'config',
        ];

        $response = $this->http()
            ->get($user.'/threads_publishing_limit', [
                'fields' => Arr::join($fields, ','),
            ]);

        return $response->json() ?? [];
    }

    public function exchangeToken(#[\SensitiveParameter] string $short, #[\SensitiveParameter] string $secret): array
    {
        $response = Http::baseUrl($this->base_url)
            ->get('access_token', [
                'grant_type' => 'th_exchange_token',
                'client_secret' => $secret,
                'access_token' => $short,
            ]);

        return $response->json() ?? [];
    }

    public function refreshToken(): array
    {
        $response = Http::baseUrl($this->base_url)
            ->get('refresh_access_token', [
                'grant_type' => 'th_refresh_token',
                'access_token' => $this->token,
            ]);

        return $response->json() ?? [];
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl($this->base_url.$this->api_version.'/')
            ->withToken($this->token);
    }
}
