<?php

declare(strict_types=1);

namespace Revolution\Threads\Traits;

use Illuminate\Container\Container;
use Revolution\Threads\Contracts\Factory;

trait WithThreads
{
    public function threads(): Factory
    {
        return Container::getInstance()
            ->make(Factory::class)
            ->token($this->tokenForThreads());
    }

    abstract protected function tokenForThreads(): string;
}
