ThreadsClient
====

Basic client.

## Token
Tokens can be obtained on Socialite or through the Threads tester token generator tool.

## Post to Threads

### Text
```php
use Revolution\Threads\Facades\Threads;

Threads::token($token);

$id = Threads::createText('test');
Threads::publish($id)
```

### Image
```php
use Revolution\Threads\Facades\Threads;

Threads::token($token);

$id = Threads::createImage(url: 'https://.../cat.png', text: 'test');
Threads::publish($id)
```

### Video
```php
use Revolution\Threads\Facades\Threads;

Threads::token($token);

$id = Threads::createVideo(url: 'https://.../dog.mov', text: 'test');
Threads::publish($id)
```

### Carousel
```php
use Revolution\Threads\Facades\Threads;

Threads::token($token);

$id1 = Threads::createImage(url: 'https://.../cat1.png', is_carousel: true);
$id2 = Threads::createImage(url: 'https://.../cat2.png', is_carousel: true);
$id = Threads::createCarousel(children: [$id1, $id2], text: 'test');
Threads::publish($id)
```

## Get my posts

```php
use Revolution\Threads\Facades\Threads;

$posts = Threads::token($token)->posts(limit: 30);
```

## Get single post

```php
use Revolution\Threads\Facades\Threads;

$post = Threads::token($token)->single(id: $id);
```
