<?php

declare(strict_types=1);

namespace ShopUnion\SDK\Platform;

use ShopUnion\SDK\Config;
use ShopUnion\SDK\Exception\SdkException;
use ShopUnion\SDK\Http\HttpClientInterface;

/**
 * 淘宝联盟开放 API：基于官方 Top SDK 实现
 * 物料搜索、链接转换、店铺搜索、商品详情、商品列表（权益物料精选）、生成淘口令、物料分类列表、物料精选商品列表
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
        if (isset($params['keyword']) && $params['keyword'] !== '' && $params['keyword'] !== null) {
            $apiParams['q'] = (string) $params['keyword'];
        }
        if (isset($params['sort']) && $params['sort'] !== '' && $params['sort'] !== null) {
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
     * 链接转换（长链转短链，使用 taobao.tbk.spread.get）
     * 支持单条或批量 URL，仅支持 uland.taobao.com、s.click.taobao.com、ai.taobao.com、temai.taobao.com 域名
     *
     * @param array<string, mixed> $params 需包含 url（字符串）或 urls（数组）
     * @return array<string, mixed> 含 results 列表，每项含 content（短链）、err_msg
     * @see https://open.taobao.com/api.htm?docId=27832&docType=2&scopeId=12340
     */
    public function linkConvert(array $params): array
    {
        $this->ensureTopSdk();

        $urls = [];
        if (isset($params['url']) && $params['url'] !== '' && $params['url'] !== null) {
            $urls[] = trim((string) $params['url']);
        }
        if (isset($params['urls']) && is_array($params['urls'])) {
            foreach ($params['urls'] as $u) {
                if ($u !== null && $u !== '') {
                    $urls[] = trim((string) $u);
                }
            }
        }
        if ($urls === []) {
            throw new SdkException('链接转换需要提供 url 或 urls（联盟长链，仅支持 uland/s.click/ai/temai.taobao.com）');
        }

        $requests = array_map(static function ($url) {
            return ['url' => $url];
        }, $urls);
        $apiParams = [
            'requests' => json_encode($requests),
        ];
        $req = new \GenericTopRequest('taobao.tbk.spread.get', $apiParams);
        return $this->execute($req);
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
            'adzone_id'   => $adzoneId,
            'page_no'     => (int) ($params['page_no'] ?? 1),
            'page_size'   => min((int) ($params['page_size'] ?? 20), 100),
            'material_id' => $params['material_id'] ?? '4093',
        ];
        if ($siteId !== null && $siteId !== '') {
            $apiParams['site_id'] = $siteId;
        }
        if (isset($params['keyword']) && $params['keyword'] !== '' && $params['keyword'] !== null) {
            $apiParams['keyword'] = (string) $params['keyword'];
        }

        $req = new \GenericTopRequest('taobao.tbk.dg.optimus.material', $apiParams);
        return $this->execute($req);
    }

    /**
     * 商品详情（升级版，淘宝客新商品ID）
     * API: taobao.tbk.item.info.upgrade.get
     *
     * @param array<string, mixed> $params 需包含 num_iids 或 item_id（多个逗号分隔，最多20个），可选 biz_scene_id、get_tlj_info、ip 等
     * @return array<string, mixed>
     * @see https://open.taobao.com/api.htm?docId=64763&docType=2&scopeId=16189
     */
    public function itemDetail(array $params): array
    {
        $this->ensureTopSdk();
        $itemId = $params['item_id'] ?? $params['num_iids'] ?? null;
        if (is_array($itemId)) {
            $itemId = implode(',', $itemId);
        }
        if ($itemId === null || trim((string) $itemId) === '') {
            throw new SdkException('商品详情需要提供 num_iids 或 item_id');
        }
        $itemId = trim((string) $itemId);

        $apiParams = [
            'item_id' => $itemId,
        ];
        if (isset($params['biz_scene_id'])) {
            $apiParams['biz_scene_id'] = $params['biz_scene_id'];
        }
        if (isset($params['get_tlj_info'])) {
            $apiParams['get_tlj_info'] = (int) $params['get_tlj_info'];
        }
        if (isset($params['ip']) && $params['ip'] !== '' && $params['ip'] !== null) {
            $apiParams['ip'] = (string) $params['ip'];
        }
        if (isset($params['manage_item_pub_id']) && $params['manage_item_pub_id'] !== '' && $params['manage_item_pub_id'] !== null) {
            $apiParams['manage_item_pub_id'] = (string) $params['manage_item_pub_id'];
        }
        if (isset($params['promotion_type'])) {
            $apiParams['promotion_type'] = (int) $params['promotion_type'];
        }
        if (isset($params['relation_id']) && $params['relation_id'] !== '' && $params['relation_id'] !== null) {
            $apiParams['relation_id'] = (string) $params['relation_id'];
        }

        $req = new \GenericTopRequest('taobao.tbk.item.info.upgrade.get', $apiParams);
        return $this->execute($req);
    }

    /**
     * 获取物料分类列表（物料 id 列表，用于物料搜索等场景）
     * API: taobao.tbk.optimus.tou.material.ids.get
     *
     * @param array<string, mixed> $params 可选 subject（默认1）、material_type（默认1）、page_no、page_size
     * @return array<string, mixed>
     * @see https://open.taobao.com/api.htm?docId=64333&docType=2&scopeId=27939
     */
    public function materialCategoryList(array $params = []): array
    {
        $this->ensureTopSdk();
        $materialQuery = [
            'subject'       => (int) ($params['subject'] ?? 1),
            'material_type' => (int) ($params['material_type'] ?? 1),
        ];
        if (isset($params['page_no'])) {
            $materialQuery['page_no'] = (int) $params['page_no'];
        }
        if (isset($params['page_size'])) {
            $materialQuery['page_size'] = (int) $params['page_size'];
        }
        $apiParams = [
            'material_query' => json_encode($materialQuery),
        ];
        $req = new \GenericTopRequest('taobao.tbk.optimus.tou.material.ids.get', $apiParams);
        return $this->execute($req);
    }

    /**
     * 根据物料 id 获取商品列表（物料精选，与 materialCategoryList 配合使用）
     * API: taobao.tbk.dg.material.recommend
     *
     * @param array<string, mixed> $params 需包含 material_id（来自 materialCategoryList），可选 page_no、page_size、item_id、favorites_id 等
     * @return array<string, mixed>
     * @see https://open.taobao.com/api.htm?docId=62201&docType=2&scopeId=27939
     */
    public function materialRecommendList(array $params): array
    {
        $this->ensureTopSdk();
        $materialId = $params['material_id'] ?? null;
        if ($materialId === null || (string) $materialId === '') {
            throw new SdkException('物料精选商品列表需要提供 material_id（可从 materialCategoryList 接口获取）');
        }
        $adzoneId = $this->resolveAdzoneId();

        $apiParams = [
            'adzone_id'   => $adzoneId,
            'material_id' => (string) $materialId,
            'page_no'     => (int) ($params['page_no'] ?? 1),
            'page_size'   => min((int) ($params['page_size'] ?? 20), 100),
        ];
        if (isset($params['item_id']) && $params['item_id'] !== '' && $params['item_id'] !== null) {
            $apiParams['item_id'] = (string) $params['item_id'];
        }
        if (isset($params['favorites_id']) && $params['favorites_id'] !== '' && $params['favorites_id'] !== null) {
            $apiParams['favorites_id'] = (string) $params['favorites_id'];
        }
        if (isset($params['promotion_type'])) {
            $apiParams['promotion_type'] = (int) $params['promotion_type'];
        }
        if (isset($params['relation_id']) && $params['relation_id'] !== '' && $params['relation_id'] !== null) {
            $apiParams['relation_id'] = (string) $params['relation_id'];
        }
        if (isset($params['special_id']) && $params['special_id'] !== '' && $params['special_id'] !== null) {
            $apiParams['special_id'] = (string) $params['special_id'];
        }

        $req = new \GenericTopRequest('taobao.tbk.dg.material.recommend', $apiParams);
        return $this->execute($req);
    }

    /**
     * 商品列表（权益物料精选，获取指定权益下的推荐商品等）
     * API: taobao.tbk.dg.optimus.promotion
     *
     * @param array<string, mixed> $params 可选 page_num, page_size（一次最多10）, promotion_id（默认 62191 天猫店铺券）
     * @return array<string, mixed> 含 result_list.map_data，含 promotion_list、recommend_item_list 等
     * @see https://open.taobao.com/api.htm?docId=52700&docType=2&scopeId=16518
     */
    public function promotionGoodsList(array $params = []): array
    {
        $this->ensureTopSdk();
        $adzoneId = $this->resolveAdzoneId();

        $apiParams = [
            'adzone_id'    => $adzoneId,
            'page_num'     => (int) ($params['page_num'] ?? 1),
            'page_size'    => min((int) ($params['page_size'] ?? 10), 10),
            'promotion_id' => (int) ($params['promotion_id'] ?? $this->config->getTaobaoPromotionId()),
        ];

        $req = new \GenericTopRequest('taobao.tbk.dg.optimus.promotion', $apiParams);
        return $this->execute($req);
    }

    /**
     * 生成淘口令（将联盟推广链接转为淘口令）
     * API: taobao.tbk.tpwd.create（公用接口，不需用户授权）
     *
     * @param array<string, mixed> $params 需包含 url（联盟官方渠道的淘客推广链接，建议 https 开头）
     * @return array<string, mixed> 含 data.password_simple、data.model
     * @see https://open.taobao.com/api.htm?docId=31127&docType=2&scopeId=11655
     */
    public function createTpwd(array $params): array
    {
        $this->ensureTopSdk();
        $url = $params['url'] ?? null;
        if ($url === null || trim((string) $url) === '') {
            throw new SdkException('生成淘口令需要提供 url（联盟官方渠道的淘客推广链接）');
        }
        $apiParams = [
            'url' => trim((string) $url),
        ];
        if (isset($params['text']) && $params['text'] !== '' && $params['text'] !== null) {
            $apiParams['text'] = (string) $params['text'];
        }
        if (isset($params['logo']) && $params['logo'] !== '' && $params['logo'] !== null) {
            $apiParams['logo'] = (string) $params['logo'];
        }
        if (isset($params['ext']) && $params['ext'] !== '' && $params['ext'] !== null) {
            $apiParams['ext'] = (string) $params['ext'];
        }
        if (isset($params['user_id']) && $params['user_id'] !== '' && $params['user_id'] !== null) {
            $apiParams['user_id'] = (string) $params['user_id'];
        }
        $req = new \GenericTopRequest('taobao.tbk.tpwd.create', $apiParams);
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
        return $this->execute($req, $this->normalizeSession());
    }

    /** @return array<string, mixed> 链接类接口公共参数 adzone_id[, site_id] */
    private function baseLinkParams(): array
    {
        $adzoneId = $this->resolveAdzoneId();
        $siteId = $this->resolveSiteId();
        $params = ['adzone_id' => $adzoneId];
        if ($siteId !== null && $siteId !== '') {
            $params['site_id'] = $siteId;
        }
        return $params;
    }

    private function normalizeSession(): ?string
    {
        $session = $this->config->getTaobaoSession();
        if ($session === null || trim((string) $session) === '') {
            return null;
        }
        return trim((string) $session);
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
     * @param \GenericTopRequest $request
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
