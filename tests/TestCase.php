<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Application;
use Laravel\Socialite\SocialiteServiceProvider;
use Revolution\Threads\ThreadsServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Load package service provider.
     *
     * @param  Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [
            ThreadsServiceProvider::class,
            SocialiteServiceProvider::class,
        ];
    }

    /**
     * Load package alias.
     *
     * @param  Application  $app
     */
    protected function getPackageAliases($app): array
    {
        return [
            //
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('services.threads',
            [
                'client_id' => 'test',
                'client_secret' => 'test',
                'redirect' => 'http://localhost',
            ],
        );
    }
}
