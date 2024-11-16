<?php

declare(strict_types=1);

namespace Revolution\Threads\Facades;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Threads\Contracts\Factory;
use Revolution\Threads\Enums\ReplyControl;
use Revolution\Threads\ThreadsClient;

/**
 * @method static Factory token(string $token)
 * @method static Factory baseUrl(string $base_url)
 * @method static Factory apiVersion(string $api_version)
 * @method static Response profiles(string $user = 'me', ?array $fields = null)
 * @method static Response posts(string $user = 'me', int $limit = 25, ?array $fields = null, ?string $before = null, ?string $after = null, ?string $since = null, ?string $until = null)
 * @method static Response single(string $id, ?array $fields = null)
 * @method static Response publish(string $id, int $sleep = 0)
 * @method static Response createText(string $text, ?ReplyControl $reply_control = null, ?string $reply_to_id = null)
 * @method static Response createImage(string $url, ?string $text = null, bool $is_carousel = false, ?ReplyControl $reply_control = null, ?string $reply_to_id = null)
 * @method static Response createVideo(string $url, ?string $text = null, bool $is_carousel = false, ?ReplyControl $reply_control = null, ?string $reply_to_id = null)
 * @method static Response createCarousel(array $children, ?string $text = null, ?ReplyControl $reply_control = null, ?string $reply_to_id = null)
 * @method static Response status(string $id, ?array $fields = null)
 * @method static Response quota(string $user = 'me', ?array $fields = null)
 * @method static Response exchangeToken(string $short, string $secret)
 * @method static Response refreshToken()
 *
 * @mixin ThreadsClient
 * @mixin Macroable
 * @mixin Conditionable
 */
class Threads extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return Factory::class;
    }
}
