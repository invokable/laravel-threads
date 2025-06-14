<?php

declare(strict_types=1);

namespace Revolution\Threads;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Threads\Contracts\Factory;
use Revolution\Threads\Enums\MediaType;
use Revolution\Threads\Enums\SearchType;

use function Illuminate\Support\enum_value;

class ThreadsClient implements Factory
{
    use Conditionable;
    use Macroable;

    protected string $token = '';

    protected string $base_url = 'https://graph.threads.net/';

    protected string $api_version = 'v1.0';

    protected const POST_DEFAULT_FIELDS = [
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
        'alt_text',
        'link_attachment_url',
        'has_replies',
        'is_reply',
        'is_reply_owned_by_me',
        'root_post',
        'replied_to',
        'hide_status',
        'reply_audience',
        'quoted_post',
        'reposted_post',
        'gif_url',
        'poll_attachment',
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

    public function profiles(string $user = 'me', ?array $fields = null): Response
    {
        $fields ??= [
            'id',
            'username',
            'threads_profile_picture_url',
            'threads_biography',
        ];

        return $this->http()
            ->get($user, [
                'fields' => Arr::join($fields, ','),
            ]);
    }

    public function posts(string $user = 'me', int $limit = 25, ?array $fields = null, ?string $before = null, ?string $after = null, ?string $since = null, ?string $until = null): Response
    {
        $fields ??= self::POST_DEFAULT_FIELDS;

        return $this->http()
            ->get($user.'/threads', [
                'fields' => Arr::join($fields, ','),
                'limit' => $limit,
                'before' => $before,
                'after' => $after,
                'since' => $since,
                'until' => $until,
            ]);
    }

    public function single(string $id, ?array $fields = null): Response
    {
        $fields ??= self::POST_DEFAULT_FIELDS;

        return $this->http()
            ->get($id, [
                'fields' => Arr::join($fields, ','),
            ]);
    }

    public function replies(string $user = 'me', int $limit = 25, ?array $fields = null, ?string $before = null, ?string $after = null, ?string $since = null, ?string $until = null): Response
    {
        $fields ??= self::POST_DEFAULT_FIELDS;

        return $this->http()
            ->get($user.'/replies', [
                'fields' => Arr::join($fields, ','),
                'limit' => $limit,
                'before' => $before,
                'after' => $after,
                'since' => $since,
                'until' => $until,
            ]);
    }

    public function publish(string $id, int $sleep = 0): Response
    {
        if ($sleep > 0) {
            Sleep::sleep($sleep);
        }

        return $this->http()
            ->post('me/threads_publish', [
                'creation_id' => $id,
            ]);
    }

    public function createText(string $text, ?array $options = []): Response
    {
        return $this->http()
            ->post('me/threads', [
                'media_type' => MediaType::TEXT->name,
                'text' => $text,
            ] + $options);
    }

    public function createImage(string $url, ?string $text = null, bool $is_carousel = false, ?array $options = []): Response
    {
        return $this->http()
            ->post('me/threads', [
                'media_type' => MediaType::IMAGE->name,
                'image_url' => $url,
                'text' => $text,
                'is_carousel_item' => $is_carousel,
            ] + $options);
    }

    public function createVideo(string $url, ?string $text = null, bool $is_carousel = false, ?array $options = []): Response
    {
        return $this->http()
            ->post('me/threads', [
                'media_type' => MediaType::VIDEO->name,
                'video_url' => $url,
                'text' => $text,
                'is_carousel_item' => $is_carousel,
            ] + $options);
    }

    public function createCarousel(array $children, ?string $text = null, ?array $options = []): Response
    {
        return $this->http()
            ->post('me/threads', [
                'media_type' => MediaType::CAROUSEL->name,
                'children' => Arr::join($children, ','),
                'text' => $text,
            ] + $options);
    }

    public function repost(string $id): Response
    {
        return $this->http()
            ->post($id.'/repost');
    }

    public function delete(string $id): Response
    {
        return $this->http()
            ->delete($id);
    }

    public function search(string $q, string|SearchType $type = SearchType::TOP, ?array $fields = null): Response
    {
        $fields ??= self::POST_DEFAULT_FIELDS;

        return $this->http()
            ->get('keyword_search', [
                'q' => $q,
                'search_type' => enum_value($type),
                'fields' => Arr::join($fields, ','),
            ]);
    }

    public function status(string $id, ?array $fields = null): Response
    {
        $fields ??= [
            'status',
            'error_message',
        ];

        return $this->http()
            ->get($id, [
                'fields' => Arr::join($fields, ','),
            ]);
    }

    public function quota(string $user = 'me', ?array $fields = null): Response
    {
        $fields ??= [
            'quota_usage',
            'config',
        ];

        return $this->http()
            ->get($user.'/threads_publishing_limit', [
                'fields' => Arr::join($fields, ','),
            ]);
    }

    public function exchangeToken(#[\SensitiveParameter] string $short, #[\SensitiveParameter] string $secret): Response
    {
        return Http::baseUrl($this->base_url)
            ->get('access_token', [
                'grant_type' => 'th_exchange_token',
                'client_secret' => $secret,
                'access_token' => $short,
            ]);
    }

    public function refreshToken(): Response
    {
        return Http::baseUrl($this->base_url)
            ->get('refresh_access_token', [
                'grant_type' => 'th_refresh_token',
                'access_token' => $this->token,
            ]);
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl($this->base_url.$this->api_version.'/')
            ->withToken($this->token);
    }
}
