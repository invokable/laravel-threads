<?php

declare(strict_types=1);

namespace Revolution\Threads\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Threads\Contracts\Factory;
use Revolution\Threads\ThreadsClient;

/**
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
