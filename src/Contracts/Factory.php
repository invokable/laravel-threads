<?php
declare(strict_types=1);

namespace Revolution\Threads\Contracts;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

interface Factory
{
    public function token(#[\SensitiveParameter] string $token): static;

    public function baseUrl(string $base_url): static;

    public function apiVersion(string $api_version): static;

    /**
     * My profiles.
     *
     * @return array{id: string, username: string, threads_profile_picture_url: string, threads_biography: string}
     */
    public function profiles(string $user = 'me', ?array $fields = null): array;

    /**
     * My posts.
     *
     * @return array{data: array, paging: array}
     */
    public function posts(string $user = 'me', int $limit = 25, ?array $fields = null, ?string $before = null, ?string $after = null, ?string $since = null, ?string $until = null): array;

    /**
     * Get Single Threads Media.
     */
    public function single(string $id, ?array $fields = null): array;

    /**
     * Publish Threads Media Container.
     *
     * @return array{id: string}
     */
    public function publish(string $id, int $sleep = 0): array;

    /**
     * Create Text Container.
     *
     * @return string Threads Media Container ID
     */
    public function createText(string $text): string;

    /**
     * Create Image Container.
     *
     * @return string Threads Media Container ID
     */
    public function createImage(string $url, ?string $text = null, bool $is_carousel = false): string;

    /**
     * Create Video Container.
     *
     * @return string Threads Media Container ID
     */
    public function createVideo(string $url, ?string $text = null, bool $is_carousel = false): string;

    /**
     * Create Carousel Container.
     *
     * @param  array  $children Container IDs
     * @return string Threads Media Container ID
     */
    public function createCarousel(array $children, ?string $text = null): string;

    /**
     * Publishing status.
     *
     * @return array{status: string}
     */
    public function status(string $id, ?array $fields = null): array;

    /**
     * Publishing Quota Limit.
     */
    public function quota(string $user = 'me', ?array $fields = null): array;

    /**
     * Exchange short-lived token to long-lived token.
     *
     * @return array{access_token: string}
     */
    public function exchangeToken(#[\SensitiveParameter] string $short, #[\SensitiveParameter] string $secret): array;

    /**
     * Refresh long-lived token.
     *
     * @return array{access_token: string}
     */
    public function refreshToken(): array;
}
