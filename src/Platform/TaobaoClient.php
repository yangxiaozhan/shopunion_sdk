<?php

declare(strict_types=1);

namespace ShopUnion\SDK\Platform;

use ShopUnion\SDK\Config;
use ShopUnion\SDK\Exception\SdkException;
use ShopUnion\SDK\Http\HttpClientInterface;

/**
 * 淘宝联盟开放 API：物料搜索、链接转换、店铺搜索、商品详情
 */
final class TaobaoClient
{
    private const VERSION = '2.0';
    private const SIGN_METHOD = 'md5';

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
     * 物料搜索（全网淘客商品查询）
     * API: taobao.tbk.dg.material.optional
     *
     * @param array<string, mixed> $params 如 keyword, page_no, page_size, sort 等
     * @return array<string, mixed>
     */
    public function materialSearch(array $params = []): array
    {
        $apiParams = array_merge([
            'adzone_id' => $this->config->getTaobaoAdzoneId() ?? $this->parseAdzoneIdFromPid(),
            'page_no' => $params['page_no'] ?? 1,
            'page_size' => $params['page_size'] ?? 20,
        ], $params);
        unset($apiParams['page_no'], $apiParams['page_size']);
        $apiParams['page_no'] = $params['page_no'] ?? 1;
        $apiParams['page_size'] = min(($params['page_size'] ?? 20), 100);
        if (!empty($params['keyword'])) {
            $apiParams['q'] = $params['keyword'];
        }
        return $this->call('taobao.tbk.dg.material.optional', $apiParams);
    }

    /**
     * 链接转换（商品/淘口令转高佣推广链接）
     * 支持：商品 URL、淘口令、商品 ID
     * API: taobao.tbk.sc.tpwd.convert（淘口令） / taobao.tbk.dg.item.coupon.get（商品+券）等
     *
     * @param array<string, mixed> $params 需包含 item_id 或 content（淘口令）或 url
     * @return array<string, mixed>
     */
    public function linkConvert(array $params): array
    {
        $adzoneId = $this->config->getTaobaoAdzoneId() ?? $this->parseAdzoneIdFromPid();
        if (isset($params['content']) && !empty($params['content'])) {
            return $this->call('taobao.tbk.sc.tpwd.convert', [
                'adzone_id' => $adzoneId,
                'content' => $params['content'],
                'session' => $this->config->getTaobaoSession(),
            ]);
        }
        $itemId = $params['item_id'] ?? $params['num_iid'] ?? null;
        if ($itemId) {
            return $this->call('taobao.tbk.dg.item.coupon.get', [
                'adzone_id' => $adzoneId,
                'item_id' => $itemId,
                'session' => $this->config->getTaobaoSession(),
            ]);
        }
        if (!empty($params['url'])) {
            return $this->call('taobao.tbk.sc.tpwd.convert', [
                'adzone_id' => $adzoneId,
                'content' => $params['url'],
                'session' => $this->config->getTaobaoSession(),
            ]);
        }
        throw new SdkException('链接转换需要提供 item_id、content(淘口令) 或 url 之一');
    }

    /**
     * 店铺搜索（联盟店铺物料）
     * API: taobao.tbk.dg.optimus.material（可做精选）或 店铺物料接口
     *
     * @param array<string, mixed> $params 如 keyword, page_no, page_size
     * @return array<string, mixed>
     */
    public function shopSearch(array $params = []): array
    {
        $apiParams = [
            'adzone_id' => $this->config->getTaobaoAdzoneId() ?? $this->parseAdzoneIdFromPid(),
            'page_no' => $params['page_no'] ?? 1,
            'page_size' => min(($params['page_size'] ?? 20), 100),
            'material_id' => $params['material_id'] ?? '4093', // 可选店铺相关物料
        ];
        if (!empty($params['keyword'])) {
            $apiParams['keyword'] = $params['keyword'];
        }
        return $this->call('taobao.tbk.dg.optimus.material', $apiParams);
    }

    /**
     * 商品详情
     * API: taobao.tbk.item.info.get
     *
     * @param array<string, mixed> $params 需包含 num_iids（多个用逗号）或 item_id
     * @return array<string, mixed>
     */
    public function itemDetail(array $params): array
    {
        $numIids = $params['num_iids'] ?? $params['item_id'] ?? null;
        if (is_array($numIids)) {
            $numIids = implode(',', $numIids);
        }
        if (empty($numIids)) {
            throw new SdkException('商品详情需要提供 num_iids 或 item_id');
        }
        return $this->call('taobao.tbk.item.info.get', [
            'num_iids' => $numIids,
            'platform' => $params['platform'] ?? 2,
        ]);
    }

    /**
     * 统一调用淘宝开放平台
     *
     * @param array<string, mixed> $apiParams
     * @return array<string, mixed>
     */
    public function call(string $method, array $apiParams = []): array
    {
        $appKey = $this->config->getTaobaoAppKey();
        $appSecret = $this->config->getTaobaoAppSecret();
        if ($appKey === null || $appSecret === null) {
            throw new SdkException('淘宝联盟未配置 app_key / app_secret');
        }
        $public = [
            'method' => $method,
            'app_key' => $appKey,
            'timestamp' => date('Y-m-d H:i:s'),
            'v' => self::VERSION,
            'sign_method' => self::SIGN_METHOD,
            'format' => 'json',
        ];
        if (($session = $this->config->getTaobaoSession()) !== null) {
            $public['session'] = $session;
        }
        $sign = $this->sign($appSecret, $public, $apiParams);
        $public['sign'] = $sign;
        $body = array_merge($public, $apiParams);
        $response = $this->http->request('POST', $this->config->getTaobaoGateway(), [
            'form_params' => $body,
        ]);
        $data = json_decode($response['body'], true);
        if (!is_array($data)) {
            throw new SdkException('淘宝 API 返回非 JSON: ' . substr($response['body'], 0, 200));
        }
        $error = $data['error_response'] ?? null;
        if ($error !== null) {
            throw new SdkException(
                $error['sub_msg'] ?? $error['msg'] ?? 'Unknown error',
                (int) ($error['code'] ?? 0),
                null,
                (string) ($error['sub_code'] ?? ''),
                $data
            );
        }
        $key = str_replace('.', '_', $method) . '_response';
        return $data[$key] ?? $data;
    }

    private function sign(string $appSecret, array $public, array $apiParams): string
    {
        $all = array_merge($public, $apiParams);
        ksort($all);
        $str = $appSecret;
        foreach ($all as $k => $v) {
            if ($k === 'sign' || $v === '' || $v === null) {
                continue;
            }
            $str .= $k . $v;
        }
        $str .= $appSecret;
        return strtoupper(md5($str));
    }

    private function parseAdzoneIdFromPid(): ?string
    {
        $pid = $this->config->getTaobaoPid();
        if ($pid === null) {
            return null;
        }
        // pid 格式: mm_123_456_789 最后一段为 adzone_id
        $parts = explode('_', $pid);
        return end($parts) ?: null;
    }
}
