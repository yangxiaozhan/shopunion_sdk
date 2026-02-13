<?php

declare(strict_types=1);

namespace ShopUnion\SDK\Http;

interface HttpClientInterface
{
    /**
     * @param array<string, mixed> $options
     * @return array{body: string, status: int, headers: array}
     */
    public function request(string $method, string $url, array $options = []): array;
}
