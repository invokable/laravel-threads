<?php

declare(strict_types=1);

namespace Revolution\Threads;

use Illuminate\Support\ServiceProvider;
use Revolution\Threads\Contracts\Factory;

class ThreadsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Factory::class, ThreadsClient::class);
    }

    public function boot(): void
    {
        //
    }
}
