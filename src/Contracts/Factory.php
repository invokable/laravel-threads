<?php
declare(strict_types=1);

namespace Revolution\Threads\Contracts;

use Illuminate\Http\Client\Response;
use Revolution\Threads\Enums\ReplyControl;

interface Factory
{
    public function token(#[\SensitiveParameter] string $token): static;

    public function baseUrl(string $base_url): static;

    public function apiVersion(string $api_version): static;

    /**
     * My profiles.
     *
     * @return Response{id: string, username: string, threads_profile_picture_url: string, threads_biography: string}
     */
    public function profiles(string $user = 'me', ?array $fields = null): Response;

    /**
     * My posts.
     *
     * @return Response{data: array<array-key, string>, paging: array}
     */
    public function posts(string $user = 'me', int $limit = 25, ?array $fields = null, ?string $before = null, ?string $after = null, ?string $since = null, ?string $until = null): Response;

    /**
     * Get Single Threads Media.
     *
     * @return Response<array-key, string>
     */
    public function single(string $id, ?array $fields = null): Response;

    /**
     * @return Response{data: array<array-key, string>, paging: array}
     */
    public function replies(string $user = 'me', int $limit = 25, ?array $fields = null, ?string $before = null, ?string $after = null, ?string $since = null, ?string $until = null): Response;

    /**
     * Publish Threads Media Container.
     *
     * @return Response{id: string}
     */
    public function publish(string $id, int $sleep = 0): Response;

    /**
     * Create Text Container.
     *
     * @return Response{id: string} Threads Media Container ID
     */
    public function createText(string $text, ?ReplyControl $reply_control = null, ?string $reply_to_id = null, ?string $link_attachment = null, ?string $quote_post_id = null, ?array $options = []): Response;

    /**
     * Create Image Container.
     *
     * @return Response{id: string} Threads Media Container ID
     */
    public function createImage(string $url, ?string $text = null, bool $is_carousel = false, ?ReplyControl $reply_control = null, ?string $reply_to_id = null, ?string $alt_text = null, ?string $quote_post_id = null, ?array $options = []): Response;

    /**
     * Create Video Container.
     *
     * @return Response{id: string} Threads Media Container ID
     */
    public function createVideo(string $url, ?string $text = null, bool $is_carousel = false, ?ReplyControl $reply_control = null, ?string $reply_to_id = null, ?string $alt_text = null, ?string $quote_post_id = null, ?array $options = []): Response;

    /**
     * Create Carousel Container.
     *
     * @param  array  $children  Container IDs
     * @return Response{id: string} Threads Media Container ID
     */
    public function createCarousel(array $children, ?string $text = null, ?ReplyControl $reply_control = null, ?string $reply_to_id = null, ?string $quote_post_id = null, ?array $options = []): Response;

    /**
     * Repost.
     *
     * @return Response{id: string}  Threads Repost ID
     */
    public function repost(string $id): Response;

    /**
     * Publishing status.
     *
     * @return Response{status: string, id: string, error_message?: string}
     */
    public function status(string $id, ?array $fields = null): Response;

    /**
     * Publishing Quota Limit.
     *
     * @return Response{data: array}
     */
    public function quota(string $user = 'me', ?array $fields = null): Response;

    /**
     * Exchange short-lived token to long-lived token.
     *
     * @return Response{access_token: string}
     */
    public function exchangeToken(#[\SensitiveParameter] string $short, #[\SensitiveParameter] string $secret): Response;

    /**
     * Refresh long-lived token.
     *
     * @return Response{access_token: string}
     */
    public function refreshToken(): Response;
}
