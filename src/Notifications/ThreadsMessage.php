<?php

declare(strict_types=1);

namespace Revolution\Threads\Notifications;

use Illuminate\Contracts\Support\Arrayable;

class ThreadsMessage implements Arrayable
{
    public ?string $image_url = null;
    public ?string $video_url = null;
    public int $sleep = 0;

    public function __construct(
        public readonly string $text,
    ) {
    }

    public static function create(string $text): static
    {
        return new static(text: $text);
    }

    public function withImage(string $url): static
    {
        $this->image_url = $url;

        return $this;
    }

    public function withVideo(string $url, int $sleep = 30): static
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
