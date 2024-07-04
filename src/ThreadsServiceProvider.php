<?php

declare(strict_types=1);

namespace Revolution\Threads;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use Revolution\Threads\Contracts\Factory;
use Revolution\Threads\Socialite\ThreadsProvider;

class ThreadsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Factory::class, ThreadsClient::class);
    }

    public function boot(): void
    {
        Socialite::extend('threads', fn ($app) => Socialite::buildProvider(ThreadsProvider::class, Config::get('services.threads')));
    }
}
