<?php

declare(strict_types=1);

namespace ShopUnion\SDK\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * 调试用 HTTP 客户端：记录完整请求和响应（供 tools 使用）
 */
final class DebugHttpClient implements HttpClientInterface
{
    /** @var array{method?: string, url?: string, headers?: array, body?: string} */
    public $lastRequest = [];

    /** @var array{status?: int, headers?: array, body?: string} */
    public $lastResponse = [];

    /**
     * @param array<string, mixed> $options
     * @return array{body: string, status: int, headers: array}
     */
    public function request(string $method, string $url, array $options = []): array
    {
        $this->lastRequest = [];
        $this->lastResponse = [];

        $stack = HandlerStack::create();
        $stack->push(Middleware::mapRequest(function (RequestInterface $req) {
            $this->lastRequest = [
                'method' => $req->getMethod(),
                'url' => (string) $req->getUri(),
                'headers' => array_map(fn ($v) => implode(', ', $v), $req->getHeaders()),
                'body' => (string) $req->getBody(),
            ];
            return $req;
        }));
        $stack->push(Middleware::mapResponse(function (ResponseInterface $res) {
            $this->lastResponse = [
                'status' => $res->getStatusCode(),
                'headers' => array_map(fn ($v) => implode(', ', $v), $res->getHeaders()),
                'body' => (string) $res->getBody(),
            ];
            return $res;
        }));

        $client = new Client([
            'handler' => $stack,
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);

        try {
            $response = $client->request($method, $url, $options);
            return [
                'body' => (string) $response->getBody(),
                'status' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
            ];
        } catch (GuzzleException $e) {
            if ($e instanceof RequestException && $e->hasResponse()) {
                $r = $e->getResponse();
                $this->lastResponse = [
                    'status' => $r->getStatusCode(),
                    'headers' => array_map(fn ($v) => implode(', ', $v), $r->getHeaders()),
                    'body' => (string) $r->getBody(),
                ];
            }
            throw new \RuntimeException('HTTP request failed: ' . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
