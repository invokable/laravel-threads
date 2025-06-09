Basic client
====

## Token

Tokens can be obtained on Socialite or through the Threads tester token generator tool.

### Token Types

There are two types of tokens in the Threads API:

- **Short-lived tokens**: Obtained through Socialite OAuth flow, valid for a limited time
- **Long-lived tokens**: Valid for 60-90 days, required for API usage

Short-lived tokens must be converted to long-lived tokens using `exchangeToken()`. Long-lived tokens can be refreshed to extend their validity, allowing continuous usage when properly maintained.

Threads tester tokens are generated as long-lived tokens by default, so Socialite is not required for single-user scenarios.

**Important**: Long-lived tokens require periodic refresh and cannot be stored permanently in `.env` files. Store them in a database or cache with proper refresh mechanisms.

## Response

The API results are returned as an `Illuminate\Http\Client\Response` object,
so you can use it freely just like you would with normal Laravel.

```php
/** @var \Illuminate\Http\Client\Response $response */
$response->json();
$response->collect();
$response['id'];
```

## Post to Threads

### Two-Phase Publishing Process

Threads API uses a two-phase process for content publishing:

1. **Create**: Upload content (text, image, video) to get a media container ID
2. **Publish**: Make the content publicly visible using the container ID

This two-phase approach enables advanced features like carousel posts with multiple media items.

### Text

```php
use Revolution\Threads\Facades\Threads;

Threads::token($token);

$id = Threads::createText('test')->json('id');
Threads::publish($id);
```

### Auto publish

The `Threads::publish()` step can be skipped if the `auto_publish_text` option is specified in the `options` array when creating text posts:

```php
use Revolution\Threads\Facades\Threads;

Threads::token($token);

Threads::createText('test', options: ['auto_publish_text' => true]);
```

**Note:** Auto-publishing is only available for Text posts.

Arbitrary additional parameters can be specified in the `options` array.

For additional information, see: https://developers.facebook.com/docs/threads/reference/publishing

### Reply Control

```php
use Revolution\Threads\Facades\Threads;
use Revolution\Threads\Enums\ReplyControl;

Threads::token($token);

$id = Threads::createText(text: 'test', options: ['reply_control' => ReplyControl::FOLLOW->value])->json('id');
Threads::publish($id);
```

### Image

```php
use Revolution\Threads\Facades\Threads;

Threads::token($token);

$id = Threads::createImage(url: 'https://.../cat.png', text: 'test')->json('id');
Threads::publish($id);
```

**Note**: Images and videos require publicly accessible URLs. Direct file uploads are not supported. For local files, use Laravel's Storage facade:

```php
use Illuminate\Support\Facades\Storage;

$id = Threads::createImage(url: Storage::url('cat.png'), text: 'test')->json('id');
Threads::publish($id);
```

### Video

```php
use Revolution\Threads\Facades\Threads;

Threads::token($token);

$id = Threads::createVideo(url: 'https://.../dog.mov', text: 'test')->json('id');
Threads::publish($id);
```

**Video Processing**: Videos require processing time before publishing. It's recommended to wait 30 seconds before publishing video content:

```php
use Illuminate\Support\Facades\Storage;

$id = Threads::createVideo(url: Storage::url('dog.mov'), text: 'test')->json('id');
Threads::publish($id, sleep: 30); // Wait 30 seconds before publishing
```

Alternative approaches:
- Use `Threads::status($id)` to check processing status
- Implement queue-based delayed publishing
- Handle processing in background jobs

### Carousel

Carousel posts allow combining multiple images and videos in a single post. The two-phase publishing process enables this functionality by creating individual media containers first, then combining them:

```php
use Revolution\Threads\Facades\Threads;
use Illuminate\Support\Facades\Storage;

Threads::token($token);

$id1 = Threads::createImage(url: Storage::url('cat1.png'), is_carousel: true)['id'];
$id2 = Threads::createImage(url: Storage::url('cat2.png'), is_carousel: true)['id'];
$id = Threads::createCarousel(children: [$id1, $id2], text: 'test')['id'];
Threads::publish($id);
```

**Implementation Note**: While using temporary variables may seem un-Laravel-like, this approach is necessary for carousel functionality and represents the most reliable pattern for multi-media posts.

### Polls

```php
use Revolution\Threads\Facades\Threads;

Threads::token($token);

$polls = [
    'option_a' => 'first option',
    'option_b' => 'second option',
    'option_c' => 'third option', // Optional
    'option_d' => 'fourth option', // Optional
];

$id = Threads::createText('test', options: ['poll_attachment' => $polls])->json('id');
Threads::publish($id);
```

## Get my posts

```php
use Revolution\Threads\Facades\Threads;

$posts = Threads::token($token)->posts(limit: 30)->json();
//[
//    'data' => [
//
//    ],
//    'paging' => [
//
//    ],
//]
```

### Working with Post Data

API responses are returned as raw arrays without additional processing, allowing flexible usage with Laravel Collections:

```php
collect($posts['data'] ?? [])->each(function (array $post) {
    $text = $post['text'] ?? ''; // Handle missing fields
    // or use Arr::get($post, 'text') for safer access
});
```

**Important**: Post data structure varies by content type. Image-only posts may not include a `text` field, and other fields may be missing depending on the post type. Always check for field existence or use safe access methods.

**Pagination**: Default limit is 25 posts, maximum is 100. Use the `limit` parameter to control the number of results returned.

## Get posts from specified user

```php
use Revolution\Threads\Facades\Threads;

$posts = Threads::token($token)->posts(user: '1234567', limit: 30)->json();
```

## Get a single post

```php
use Revolution\Threads\Facades\Threads;

$post = Threads::token($token)->single(id: $id)->json();
//[
//    'id' => '',
//    'text' => 'Hello World',
//    '...' => '...',
//]
```

## Repost

```php
use Revolution\Threads\Facades\Threads;

$repost = Threads::token($token)->repost(id: $id)->json();
//[
//    'id' => '',
//]
```

## Delete post

```php
use Revolution\Threads\Facades\Threads;

$result = Threads::token($token)->delete(id: $id)->json();
//[
//    'success' => true,
//    'deleted_id' => '1234567890',
//]
```

## Keyword Search

Search for public Threads media with specific keywords using the keyword search API.

### Basic Search (TOP results)

```php
use Revolution\Threads\Facades\Threads;

$results = Threads::token($token)->search(q: 'laravel')->json();
//[
//    'data' => [
//        // Array of matching posts
//    ],
//    'paging' => [
//        // Pagination information
//    ],
//]
```

### Search with Type

You can specify the search type to get either top results (default) or recent results:

```php
use Revolution\Threads\Facades\Threads;
use Revolution\Threads\Enums\SearchType;

// Search for top results (default)
$topResults = Threads::token($token)->search(q: 'laravel', type: SearchType::TOP->name)->json();

// Search for recent results
$recentResults = Threads::token($token)->search(q: 'laravel', type: SearchType::RECENT->name)->json();
```

**Note:** The keyword search API requires the `threads_keyword_search` permission scope, which is included by default in the Socialite provider.

## Token Management

### Refreshing Long-lived Tokens

Long-lived tokens should be refreshed periodically to maintain access. This can be done on each use or via scheduled tasks:

```php
use Revolution\Threads\Facades\Threads;

$newToken = Threads::token($user->threads_token)->refreshToken()['access_token'] ?? null;
if ($newToken) {
    $user->update(['threads_token' => $newToken]);
}
```

### Token Exchange (Socialite Integration)

Convert short-lived tokens from Socialite to long-lived tokens:

```php
use Revolution\Threads\Facades\Threads;

$longToken = Threads::exchangeToken($shortToken, config('services.threads.client_secret'))['access_token'];
```

## Macroable

This package only implements a few of the most commonly used methods.  
If you need other methods, you can add any method using the macro feature.

```php
namespace App\Providers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Revolution\Threads\Facades\Threads;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Threads::macro('userInsights', function (?array $metric = null): Response {
            $metric ??= [
                'likes',
                'replies',
                'reposts',
                'quotes',
                'followers_count',
            ];

            return $this->http()
                ->get('me/threads_insights', [
                    'metric' => Arr::join($metric, ','),
                ]);
        });
    }
}
```

```php
use Revolution\Threads\Facades\Threads;

$insights = Threads::token($token)->userInsights()->json();
```
