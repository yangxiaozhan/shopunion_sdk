<?php

declare(strict_types=1);

namespace ShopUnion\SDK\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final class GuzzleHttpClient implements HttpClientInterface
{
    /** @var Client */
    private $client;

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?? new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);
    }

    /**
     * @param array<string, mixed> $options
     * @return array{body: string, status: int, headers: array}
     */
    public function request(string $method, string $url, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $url, $options);
            return [
                'body' => (string) $response->getBody(),
                'status' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
            ];
        } catch (GuzzleException $e) {
            throw new \RuntimeException('HTTP request failed: ' . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
