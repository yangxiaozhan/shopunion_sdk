<?php

declare(strict_types=1);

namespace ShopUnion\SDK\Platform;

use ShopUnion\SDK\Config;
use ShopUnion\SDK\Exception\SdkException;
use ShopUnion\SDK\Http\HttpClientInterface;

/**
 * 淘宝联盟开放 API：基于官方 Top SDK 实现
 * 物料搜索、链接转换、店铺搜索、商品详情
 *
 * @see https://aff-open.taobao.com/ 淘宝联盟开放平台文档
 */
final class TaobaoClient
{
    /** @var Config */
    private $config;

    /** @var HttpClientInterface|null 保留用于兼容，实际请求由 Top SDK 发起 */
    private $http;

    /** @var \TopClient|null */
    private $topClient;

    public function __construct(Config $config, ?HttpClientInterface $http = null)
    {
        $this->config = $config;
        $this->http = $http;
    }

    /**
     * 物料搜索（全网淘客商品查询）
     * API: taobao.tbk.dg.material.optional.upgrade
     *
     * @param array<string, mixed> $params 如 keyword, page_no, page_size, sort, material_id 等
     * @return array<string, mixed>
     */
    public function materialSearch(array $params = []): array
    {
        $this->ensureTopSdk();
        $adzoneId = $this->resolveAdzoneId();
        $siteId = $this->resolveSiteId();

        $apiParams = [
            'adzone_id' => $adzoneId,
            'page_no' => (int) ($params['page_no'] ?? 1),
            'page_size' => min((int) ($params['page_size'] ?? 20), 100),
        ];
        if ($siteId !== null && $siteId !== '') {
            $apiParams['site_id'] = $siteId;
        }
        $materialId = $this->config->getTaobaoMaterialId();
        if ($materialId !== null) {
            $apiParams['material_id'] = $materialId;
        }
        if (!empty($params['keyword'])) {
            $apiParams['q'] = $params['keyword'];
        }
        if (!empty($params['sort'])) {
            $apiParams['sort'] = $params['sort'];
        }
        if (isset($params['has_coupon'])) {
            $apiParams['has_coupon'] = $params['has_coupon'];
        }
        if (isset($params['start_price'])) {
            $apiParams['start_price'] = $params['start_price'];
        }
        if (isset($params['end_price'])) {
            $apiParams['end_price'] = $params['end_price'];
        }

        $req = new \GenericTopRequest('taobao.tbk.dg.material.optional.upgrade', $apiParams);
        return $this->execute($req);
    }

    /**
     * 链接转换（商品/淘口令转高佣推广链接）
     * API: taobao.tbk.sc.tpwd.convert / taobao.tbk.dg.item.coupon.get
     *
     * @param array<string, mixed> $params 需包含 item_id 或 content（淘口令）或 url
     * @return array<string, mixed>
     */
    public function linkConvert(array $params): array
    {
        $this->ensureTopSdk();
        $adzoneId = $this->resolveAdzoneId();
        $siteId = $this->resolveSiteId();
        $session = $this->config->getTaobaoSession();
        $session = $session !== null && trim((string) $session) !== '' ? trim((string) $session) : null;

        $linkParams = ['adzone_id' => $adzoneId];
        if ($siteId !== null && $siteId !== '') {
            $linkParams['site_id'] = $siteId;
        }

        if (isset($params['content']) && !empty($params['content'])) {
            $req = new \GenericTopRequest('taobao.tbk.sc.tpwd.convert', array_merge($linkParams, [
                'content' => $params['content'],
            ]));
            return $this->execute($req, $session);
        }

        $itemId = $params['item_id'] ?? $params['num_iid'] ?? null;
        if ($itemId) {
            $req = new \GenericTopRequest('taobao.tbk.dg.item.coupon.get', array_merge($linkParams, [
                'item_id' => (string) $itemId,
            ]));
            return $this->execute($req, $session);
        }

        if (!empty($params['url'])) {
            $req = new \GenericTopRequest('taobao.tbk.sc.tpwd.convert', array_merge($linkParams, [
                'content' => $params['url'],
            ]));
            return $this->execute($req, $session);
        }

        throw new SdkException('链接转换需要提供 item_id、content(淘口令) 或 url 之一');
    }

    /**
     * 店铺搜索（联盟店铺物料）
     * API: taobao.tbk.dg.optimus.material
     *
     * @param array<string, mixed> $params 如 keyword, page_no, page_size, material_id
     * @return array<string, mixed>
     */
    public function shopSearch(array $params = []): array
    {
        $this->ensureTopSdk();
        $adzoneId = $this->resolveAdzoneId();
        $siteId = $this->resolveSiteId();

        $apiParams = [
            'adzone_id' => $adzoneId,
            'page_no' => (int) ($params['page_no'] ?? 1),
            'page_size' => min((int) ($params['page_size'] ?? 20), 100),
            'material_id' => $params['material_id'] ?? '4093',
        ];
        if ($siteId !== null && $siteId !== '') {
            $apiParams['site_id'] = $siteId;
        }
        if (!empty($params['keyword'])) {
            $apiParams['keyword'] = $params['keyword'];
        }

        $req = new \GenericTopRequest('taobao.tbk.dg.optimus.material', $apiParams);
        return $this->execute($req);
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
        $this->ensureTopSdk();
        $numIids = $params['num_iids'] ?? $params['item_id'] ?? null;
        if (is_array($numIids)) {
            $numIids = implode(',', $numIids);
        }
        if (empty($numIids)) {
            throw new SdkException('商品详情需要提供 num_iids 或 item_id');
        }

        $req = new \TbkItemInfoGetRequest();
        $req->setNumIids((string) $numIids);
        $req->setPlatform((int) ($params['platform'] ?? 2));

        return $this->execute($req);
    }

    /**
     * 通用调用（任意 Top API，供扩展用）
     *
     * @param array<string, mixed> $apiParams
     * @return array<string, mixed>
     */
    public function call(string $method, array $apiParams = []): array
    {
        $this->ensureTopSdk();
        $req = new \GenericTopRequest($method, $apiParams);
        $session = $this->config->getTaobaoSession();
        $session = $session !== null && trim((string) $session) !== '' ? trim((string) $session) : null;
        return $this->execute($req, $session);
    }

    private function getTopClient(): \TopClient
    {
        if ($this->topClient !== null) {
            return $this->topClient;
        }

        $appKey = $this->config->getTaobaoAppKey();
        $appSecret = $this->config->getTaobaoAppSecret();
        if ($appKey === null || $appSecret === null) {
            throw new SdkException('淘宝联盟未配置 app_key / app_secret');
        }

        $appKey = trim((string) $appKey);
        $appSecret = trim((string) $appSecret);

        $this->topClient = new \TopClient($appKey, $appSecret);
        $this->topClient->gatewayUrl = $this->config->getTaobaoGateway();
        $this->topClient->format = 'json';
        $this->topClient->checkRequest = false;
        $this->topClient->connectTimeout = $this->config->getTaobaoConnectTimeout();
        $this->topClient->readTimeout = $this->config->getTaobaoReadTimeout();

        return $this->topClient;
    }

    /**
     * @param \TbkDgMaterialOptionalUpgradeRequest|\TbkItemInfoGetRequest|\GenericTopRequest $request
     * @return array<string, mixed>
     */
    private function execute($request, ?string $session = null): array
    {
        $client = $this->getTopClient();
        $resp = $client->execute($request, $session);

        if ($resp instanceof \ResultSet) {
            throw new SdkException($resp->msg ?? 'Top SDK 执行异常', (int) ($resp->code ?? 0), null, null, null);
        }

        // 错误响应：{ code, sub_code, sub_msg, msg }
        if (isset($resp->code)) {
            $msg = $resp->sub_msg ?? $resp->msg ?? 'Unknown error';
            $subCode = $resp->sub_code ?? '';
            $hint = '';
            if (strpos((string) $msg, 'pid') !== false || strpos((string) $msg, 'adzoneId') !== false) {
                $hint = ' 请登录 https://pub.alimama.com 推广管理-推广位管理 确认 PID/媒体ID/推广位ID 与 app_key 已正确备案关联。';
            } elseif (strpos((string) $msg, '超时') !== false || strpos((string) $subCode, 'timeout') !== false) {
                $hint = ' 可尝试在配置中增加 read_timeout/connect_timeout（秒）或检查网络环境。';
            }
            throw new SdkException((string) $msg . $hint, (int) $resp->code, null, (string) $subCode, $this->objectToArray($resp));
        }

        return $this->objectToArray($resp);
    }

    /**
     * @param object $obj
     * @return array<string, mixed>
     */
    private function objectToArray($obj): array
    {
        $arr = [];
        foreach ((array) $obj as $k => $v) {
            if (is_object($v)) {
                $arr[$k] = $this->objectToArray($v);
            } elseif (is_array($v)) {
                $arr[$k] = array_map(function ($item) {
                    return is_object($item) ? $this->objectToArray($item) : $item;
                }, $v);
            } else {
                $arr[$k] = $v;
            }
        }
        return $arr;
    }

    private function ensureTopSdk(): void
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        if (!defined('TOP_SDK_WORK_DIR')) {
            define('TOP_SDK_WORK_DIR', sys_get_temp_dir() . '/taobao_sdk/');
        }
        $sdkPath = dirname(__DIR__, 2) . '/lib/taobao-top-sdk/TopSdk.php';
        if (!is_file($sdkPath)) {
            throw new SdkException('Top SDK 未找到: ' . $sdkPath);
        }
        require_once $sdkPath;
        $loaded = true;
    }

    /**
     * PID 格式: mm_账户ID_媒体ID_推广位ID，解析为 [site_id, adzone_id]
     * 第二段=媒体ID(site_id)，第三段=推广位ID(adzone_id)
     */
    private function parsePid(): array
    {
        $str = $this->config->getTaobaoAdzoneId() ?? $this->config->getTaobaoPid();
        if ($str !== null && preg_match('/^mm_\d+_\d+_\d+$/', trim((string) $str))) {
            $parts = explode('_', trim((string) $str));
            return [$parts[2], $parts[3]]; // [媒体ID, 推广位ID]
        }
        return [null, null];
    }

    private function resolveAdzoneId(): ?string
    {
        $adzoneId = $this->config->getTaobaoAdzoneId();
        if ($adzoneId !== null && $adzoneId !== '') {
            $adzoneId = trim((string) $adzoneId);
            if (preg_match('/^mm_/', $adzoneId)) {
                $parts = explode('_', $adzoneId);
                return count($parts) >= 4 ? (string) end($parts) : null;
            }
            return $adzoneId;
        }
        [, $adzoneId] = $this->parsePid();
        return $adzoneId;
    }

    private function resolveSiteId(): ?string
    {
        $siteId = $this->config->getTaobaoSiteId();
        if ($siteId !== null && $siteId !== '') {
            return trim((string) $siteId);
        }
        [$siteId] = $this->parsePid();
        return $siteId;
    }
}
