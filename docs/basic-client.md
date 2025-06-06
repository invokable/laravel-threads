Basic client
====

## Token

Tokens can be obtained on Socialite or through the Threads tester token generator tool.

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

### Video

```php
use Revolution\Threads\Facades\Threads;

Threads::token($token);

$id = Threads::createVideo(url: 'https://.../dog.mov', text: 'test')->json('id');
Threads::publish($id);
```

### Carousel

```php
use Revolution\Threads\Facades\Threads;

Threads::token($token);

$id1 = Threads::createImage(url: 'https://.../cat1.png', is_carousel: true)['id'];
$id2 = Threads::createImage(url: 'https://.../cat2.png', is_carousel: true)['id'];
$id = Threads::createCarousel(children: [$id1, $id2], text: 'test')['id'];
Threads::publish($id);
```

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

## Get single post

```php
use Revolution\Threads\Facades\Threads;

$post = Threads::token($token)->single(id: $id)->json();
//[
//    'id' => '',
//    'text' => 'Hello World',
//    '...' => '...',
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

## Macroable

If you need other methods you can add any method using the macro feature.

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
