# Socialite driver for Threads

To use Socialite, please refer to the README and create an "App" in Meta for Developers.

## Configuration

### config/services.php

```php
    'threads' => [
        // Threads App ID
        'client_id' => env('THREADS_CLIENT_ID'),
        // Threads App Secret
        'client_secret' => env('THREADS_CLIENT_SECRET'),
        'redirect' => env('THREADS_REDIRECT_URL', '/threads/callback'),
    ],
```

### .env
```php
THREADS_CLIENT_ID=
THREADS_CLIENT_SECRET=
THREADS_REDIRECT_URL=/threads/callback
```

## Usage

### routes/web.php
```php
use App\Http\Controllers\SocialiteController;

Route::get('threads/login', [SocialiteController::class, 'login']);
Route::get('threads/callback', [SocialiteController::class, 'callback']);
```

### Controller

```php
<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Revolution\Threads\Facades\Threads;

class SocialiteController extends Controller
{
    public function login()
    {
        return Socialite::driver('threads')->redirect();
    }

    public function callback(Request $request)
    {
        if ($request->missing('code')) {
            dd($request);
        }

        /**
        * @var \Laravel\Socialite\Two\User
        */
        $user = Socialite::driver('threads')->user();

        // Exchange short-lived token to long-lived token
        $long_token = Threads::exchangeToken($user->token, config('services.threads.client_secret'))->json('access_token', '');

        $loginUser = User::updateOrCreate([
            'id' => $user->id,
        ], [
            'name' => $user->nickname,
            'avatar' => $user->avatar,
            'threads_token' => $long_token,
        ]);

        auth()->login($loginUser, true);

        return redirect()->route('home');
    }
}
```

### Add Scopes

```php
    public function login()
    {
        return Socialite::driver('threads')
                        ->scopes(['threads_manage_replies', 'threads_read_replies', 'threads_manage_insights'])
                        ->redirect();
    }
```
