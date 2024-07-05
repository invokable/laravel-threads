<?php
declare(strict_types=1);

namespace Revolution\Threads\Contracts;

interface Factory
{
    public function token(#[\SensitiveParameter] string $token): static;

    public function baseUrl(string $base_url): static;

    public function profiles(?array $fields = null): array;

    public function posts(int $limit = 25, ?array $fields = null, ?string $before = null, ?string $after = null, ?string $since = null, ?string $until = null): array;

    public function single(string $id, ?array $fields = null): array;

    public function publish(string $id, int $sleep = 0): array;

    public function createText(string $text): string;

    public function createImage(string $url, ?string $text = null, bool $is_carousel = false): string;

    public function createVideo(string $url, ?string $text = null, bool $is_carousel = false): string;

    public function createCarousel(array $children, ?string $text = null): string;

    public function status(string $id, ?array $fields = null): array;

    public function quota(?array $fields = null): array;

    public function exchangeToken(#[\SensitiveParameter] string $short, #[\SensitiveParameter] string $secret): array;

    public function refreshToken(): array;
}
