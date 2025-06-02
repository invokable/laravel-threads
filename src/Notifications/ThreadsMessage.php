<?php

declare(strict_types=1);

namespace Revolution\Threads\Notifications;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;

final class ThreadsMessage implements Arrayable
{
    use Conditionable;
    use Macroable;

    public ?string $image_url = null;

    public ?string $video_url = null;

    public int $sleep = 0;

    public function __construct(
        public readonly string $text,
    ) {}

    public static function create(string $text): self
    {
        return new self(text: $text);
    }

    public function withImage(string $url): self
    {
        $this->image_url = $url;

        return $this;
    }

    public function withVideo(string $url, int $sleep = 30): self
    {
        $this->video_url = $url;
        $this->sleep = $sleep;

        return $this;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
