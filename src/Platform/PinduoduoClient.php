<?php

declare(strict_types=1);

namespace ShopUnion\SDK\Platform;

use ShopUnion\SDK\Config;
use ShopUnion\SDK\Exception\SdkException;
use ShopUnion\SDK\Http\HttpClientInterface;

/**
 * 多多进宝开放 API：物料搜索、链接转换、店铺搜索、商品详情
 */
final class PinduoduoClient
{
    /** @var Config */
    private $config;

    /** @var HttpClientInterface */
    private $http;

    public function __construct(Config $config, HttpClientInterface $http)
    {
        $this->config = $config;
        $this->http = $http;
    }

    /**
     * 物料搜索（商品搜索）
     * API: pdd.ddk.goods.search
     *
     * @param array<string, mixed> $params keyword, page, page_size, sort_type, with_coupon 等
     * @return array<string, mixed>
     */
    public function materialSearch(array $params = []): array
    {
        $request = [
            'keyword' => $params['keyword'] ?? '',
            'page' => (int) ($params['page'] ?? 1),
            'page_size' => min((int) ($params['page_size'] ?? 20), 100),
        ];
        if (isset($params['sort_type'])) {
            $request['sort_type'] = (int) $params['sort_type'];
        }
        if (isset($params['with_coupon'])) {
            $request['with_coupon'] = (bool) $params['with_coupon'];
        }
        if (!empty($params['cat_id'])) {
            $request['cat_id'] = $params['cat_id'];
        }
        return $this->call('pdd.ddk.goods.search', $request);
    }

    /**
     * 链接转换（生成推广链接）
     * API: pdd.ddk.goods.promotion.url.generate
     *
     * @param array<string, mixed> $params 需包含 goods_id_list 或 goods_sign_list，可选 pid
     * @return array<string, mixed>
     */
    public function linkConvert(array $params): array
    {
        $pid = $params['pid'] ?? $this->config->getPinduoduoPid();
        if (empty($pid)) {
            throw new SdkException('拼多多转链需要配置 pid 或传入 pid');
        }
        $request = [
            'p_id' => $pid,
            'generate_we_app' => $params['generate_we_app'] ?? false,
        ];
        if (!empty($params['goods_sign_list'])) {
            $request['goods_sign_list'] = is_array($params['goods_sign_list'])
                ? $params['goods_sign_list'] : [$params['goods_sign_list']];
        } elseif (!empty($params['goods_id_list'])) {
            $request['goods_id_list'] = is_array($params['goods_id_list'])
                ? array_map('strval', $params['goods_id_list']) : [ (string) $params['goods_id_list'] ];
        } else {
            throw new SdkException('链接转换需要提供 goods_sign_list 或 goods_id_list');
        }
        return $this->call('pdd.ddk.goods.promotion.url.generate', $request);
    }

    /**
     * 店铺搜索（店铺列表）
     * API: pdd.ddk.mall.list
     *
     * @param array<string, mixed> $params page, page_size, keyword 等
     * @return array<string, mixed>
     */
    public function shopSearch(array $params = []): array
    {
        $request = [
            'page' => (int) ($params['page'] ?? 1),
            'page_size' => min((int) ($params['page_size'] ?? 20), 100),
        ];
        if (!empty($params['keyword'])) {
            $request['keyword'] = $params['keyword'];
        }
        return $this->call('pdd.ddk.mall.list', $request);
    }

    /**
     * 商品详情
     * API: pdd.ddk.goods.detail
     *
     * @param array<string, mixed> $params 需包含 goods_sign_list 或 goods_id_list
     * @return array<string, mixed>
     */
    public function itemDetail(array $params): array
    {
        $request = [];
        if (!empty($params['goods_sign_list'])) {
            $request['goods_sign_list'] = is_array($params['goods_sign_list'])
                ? $params['goods_sign_list'] : [$params['goods_sign_list']];
        } elseif (!empty($params['goods_id_list'])) {
            $request['goods_id_list'] = is_array($params['goods_id_list'])
                ? array_map('strval', $params['goods_id_list']) : [ (string) $params['goods_id_list'] ];
        } else {
            throw new SdkException('商品详情需要提供 goods_sign_list 或 goods_id_list');
        }
        return $this->call('pdd.ddk.goods.detail', $request);
    }

    /**
     * 统一调用拼多多开放平台
     *
     * @param array<string, mixed> $apiParams
     * @return array<string, mixed>
     */
    public function call(string $type, array $apiParams = []): array
    {
        $clientId = $this->config->getPinduoduoClientId();
        $clientSecret = $this->config->getPinduoduoClientSecret();
        if ($clientId === null || $clientSecret === null) {
            throw new SdkException('多多进宝未配置 client_id / client_secret');
        }
        $timestamp = (string) time();
        $params = [
            'type' => $type,
            'client_id' => $clientId,
            'timestamp' => $timestamp,
            'data_type' => 'JSON',
        ];
        $params = array_merge($params, $apiParams);
        $params['sign'] = $this->sign($clientSecret, $params);
        $response = $this->http->request('POST', $this->config->getPinduoduoGateway(), [
            'form_params' => $params,
        ]);
        $data = json_decode($response['body'], true);
        if (!is_array($data)) {
            throw new SdkException('拼多多 API 返回非 JSON: ' . substr($response['body'], 0, 200));
        }
        $error = $data['error_response'] ?? null;
        if ($error !== null) {
            throw new SdkException(
                $error['error_msg'] ?? 'Unknown error',
                (int) ($error['error_code'] ?? 0),
                null,
                (string) ($error['error_code'] ?? ''),
                $data
            );
        }
        $key = str_replace('.', '_', $type) . '_response';
        return $data[$key] ?? $data;
    }

    private function sign(string $clientSecret, array $params): string
    {
        ksort($params);
        $str = $clientSecret;
        foreach ($params as $k => $v) {
            if ($k === 'sign' || $v === '' || $v === null) {
                continue;
            }
            if (is_bool($v)) {
                $v = $v ? 'true' : 'false';
            } elseif (is_array($v)) {
                $v = json_encode($v);
            } else {
                $v = (string) $v;
            }
            $str .= $k . $v;
        }
        $str .= $clientSecret;
        return strtoupper(md5($str));
    }
}
