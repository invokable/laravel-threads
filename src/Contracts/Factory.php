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
     * @throws RequestException
     * @throws ConnectionException
     */
    public function profiles(?array $fields = null): array;

    /**
     * My posts.
     *
     * @return array{data: array, paging: array}
     * @throws RequestException
     * @throws ConnectionException
     */
    public function posts(int $limit = 25, ?array $fields = null, ?string $before = null, ?string $after = null, ?string $since = null, ?string $until = null): array;

    /**
     * Get Single Threads Media.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function single(string $id, ?array $fields = null): array;

    /**
     * Publish Threads Media Container.
     *
     * @return array{id: string}
     * @throws RequestException
     * @throws ConnectionException
     */
    public function publish(string $id, int $sleep = 0): array;

    /**
     * Create Text Container.
     *
     * @return string Threads Media Container ID
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createText(string $text): string;

    /**
     * Create Image Container.
     *
     * @return string Threads Media Container ID
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createImage(string $url, ?string $text = null, bool $is_carousel = false): string;

    /**
     * Create Video Container.
     *
     * @return string Threads Media Container ID
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createVideo(string $url, ?string $text = null, bool $is_carousel = false): string;

    /**
     * Create Carousel Container.
     *
     * @param  array  $children Container IDs
     * @return string Threads Media Container ID
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createCarousel(array $children, ?string $text = null): string;

    /**
     * Publishing status.
     *
     * @return array{status: string}
     * @throws RequestException
     * @throws ConnectionException
     */
    public function status(string $id, ?array $fields = null): array;

    /**
     * Publishing Quota Limit.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function quota(?array $fields = null): array;

    /**
     * Exchange short-lived token to long-lived token.
     *
     * @return array{access_token: string}
     * @throws RequestException
     * @throws ConnectionException
     */
    public function exchangeToken(#[\SensitiveParameter] string $short, #[\SensitiveParameter] string $secret): array;

    /**
     * Refresh long-lived token.
     *
     * @return array{access_token: string}
     * @throws RequestException
     * @throws ConnectionException
     */
    public function refreshToken(): array;
}
