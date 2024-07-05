<?php

declare(strict_types=1);

namespace Revolution\Threads\Facades;

use Illuminate\Support\Facades\Facade;
use Revolution\Threads\Contracts\Factory;
use Revolution\Threads\ThreadsClient;

/**
 * @method static static token(string $token)
 * @method static static baseUrl(string $base_url)
 * @method static static apiVersion(string $api_version)
 * @method static array profiles(string $user = 'me', ?array $fields = null)
 * @method static array posts(string $user = 'me', int $limit = 25, ?array $fields = null, ?string $before = null, ?string $after = null, ?string $since = null, ?string $until = null)
 * @method static array single(string $id, ?array $fields = null)
 * @method static array publish(string $id, int $sleep = 0)
 * @method static string createText(string $text)
 * @method static string createImage(string $url, ?string $text = null, bool $is_carousel = false)
 * @method static string createVideo(string $url, ?string $text = null, bool $is_carousel = false)
 * @method static string createCarousel(array $children, ?string $text = null)
 * @method static array status(string $id, ?array $fields = null)
 * @method static array quota(string $user = 'me', ?array $fields = null)
 * @method static array exchangeToken(string $short, string $secret)
 * @method static array refreshToken()
 * @method static void macro(string $name, object|callable $macro)
 * @method static static|mixed when(\Closure|mixed|null $value = null, callable|null $callback = null, callable|null $default = null)
 * @method static static|mixed unless(\Closure|mixed|null $value = null, callable|null $callback = null, callable|null $default = null)
 *
 * @see ThreadsClient
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
