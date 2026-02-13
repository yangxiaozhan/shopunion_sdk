<?php

declare(strict_types=1);

namespace ShopUnion\SDK\Platform;

use ShopUnion\SDK\Config;
use ShopUnion\SDK\Exception\SdkException;
use ShopUnion\SDK\Http\HttpClientInterface;

/**
 * 京东联盟开放 API：物料搜索、链接转换、店铺搜索、商品详情
 */
final class JdClient
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
     * 物料搜索（关键词商品查询）
     * API: jd.union.open.goods.query 或 jd.union.open.goods.search
     *
     * @param array<string, mixed> $params keyword, pageIndex, pageSize, sortName, hasCoupon 等
     * @return array<string, mixed>
     */
    public function materialSearch(array $params = []): array
    {
        $req = [
            'keyword' => $params['keyword'] ?? '',
            'pageIndex' => (int) ($params['page_index'] ?? $params['pageIndex'] ?? 1),
            'pageSize' => min((int) ($params['page_size'] ?? $params['pageSize'] ?? 20), 100),
        ];
        if (isset($params['sort_name'])) {
            $req['sortName'] = $params['sort_name'];
        }
        if (isset($params['sort'])) {
            $req['sort'] = $params['sort'];
        }
        if (isset($params['has_coupon'])) {
            $req['hasCoupon'] = (bool) $params['has_coupon'];
        }
        if (!empty($params['cid1']) || !empty($params['cid2']) || !empty($params['cid3'])) {
            if (!empty($params['cid1'])) {
                $req['cid1'] = (int) $params['cid1'];
            }
            if (!empty($params['cid2'])) {
                $req['cid2'] = (int) $params['cid2'];
            }
            if (!empty($params['cid3'])) {
                $req['cid3'] = (int) $params['cid3'];
            }
        }
        return $this->call('jd.union.open.goods.query', ['goodsReqDTO' => $req]);
    }

    /**
     * 链接转换（获取推广链接）
     * API: jd.union.open.promotion.common.get
     *
     * @param array<string, mixed> $params 需包含 material_id（商品链接或 SKU 链接），可选 union_id, position_id
     * @return array<string, mixed>
     */
    public function linkConvert(array $params): array
    {
        $unionId = $params['union_id'] ?? $this->config->getJdUnionId();
        $positionId = $params['position_id'] ?? $this->config->getJdPositionId();
        if (empty($params['material_id']) && empty($params['materialId'])) {
            throw new SdkException('京东转链需要提供 material_id（推广物料 URL 或商品 ID）');
        }
        $req = [
            'materialId' => $params['material_id'] ?? $params['materialId'],
            'unionId' => $unionId,
            'positionId' => $positionId,
            'autoSearch' => $params['auto_search'] ?? true,
        ];
        return $this->call('jd.union.open.promotion.common.get', ['promotionCodeReq' => $req]);
    }

    /**
     * 店铺搜索（京东联盟以商品搜索为主，店铺维度可通过 keyword 或接口说明实现）
     * 此处提供商品搜索的别名，或后续可接店铺活动/店铺商品接口
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function shopSearch(array $params = []): array
    {
        return $this->materialSearch($params);
    }

    /**
     * 商品详情（根据 SKU 查询）
     * API: jd.union.open.goods.query 或 详情接口
     *
     * @param array<string, mixed> $params 需包含 sku_ids（多个逗号分隔）
     * @return array<string, mixed>
     */
    public function itemDetail(array $params): array
    {
        $skuIds = $params['sku_ids'] ?? $params['skuIds'] ?? $params['sku_id'] ?? null;
        if (is_array($skuIds)) {
            $skuIds = implode(',', $skuIds);
        }
        if (empty($skuIds)) {
            throw new SdkException('京东商品详情需要提供 sku_ids');
        }
        $req = ['skuIds' => $skuIds];
        return $this->call('jd.union.open.goods.query', ['goodsReqDTO' => $req]);
    }

    /**
     * 统一调用京东开放平台（JSON 格式）
     *
     * @param array<string, mixed> $apiParams 业务参数，如 ['goodsReqDTO' => [...]]
     * @return array<string, mixed>
     */
    public function call(string $method, array $apiParams = []): array
    {
        $appKey = $this->config->getJdAppKey();
        $appSecret = $this->config->getJdAppSecret();
        if ($appKey === null || $appSecret === null) {
            throw new SdkException('京东联盟未配置 app_key / app_secret');
        }
        $request = [
            'method' => $method,
            'app_key' => $appKey,
            'timestamp' => date('Y-m-d H:i:s'),
            'format' => 'json',
            'v' => '1.0',
            'param_json' => json_encode($apiParams),
        ];
        $request['sign'] = $this->sign($appSecret, $request);
        $response = $this->http->request('POST', $this->config->getJdGateway(), [
            'form_params' => $request,
        ]);
        $data = json_decode($response['body'], true);
        if (!is_array($data)) {
            throw new SdkException('京东 API 返回非 JSON: ' . substr($response['body'], 0, 200));
        }
        $key = str_replace('.', '_', $method) . '_response';
        $res = $data[$key] ?? $data;
        $code = $res['code'] ?? $res['errorCode'] ?? 0;
        $msg = $res['message'] ?? $res['errorMessage'] ?? '';
        if ($code !== 0 && $code !== '0') {
            throw new SdkException((string) $msg, (int) $code, null, (string) $code, $data);
        }
        return $res;
    }

    private function sign(string $appSecret, array $params): string
    {
        ksort($params);
        $str = $appSecret;
        foreach ($params as $k => $v) {
            if ($k === 'sign' || $v === '' || $v === null) {
                continue;
            }
            $str .= $k . $v;
        }
        $str .= $appSecret;
        return strtoupper(md5($str));
    }
}
