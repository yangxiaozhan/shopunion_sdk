<?php

declare(strict_types=1);

namespace ShopUnion\SDK;

use ShopUnion\SDK\Http\GuzzleHttpClient;
use ShopUnion\SDK\Http\HttpClientInterface;
use ShopUnion\SDK\Platform\JdClient;
use ShopUnion\SDK\Platform\PinduoduoClient;
use ShopUnion\SDK\Platform\TaobaoClient;

/**
 * 电商联盟统一 SDK 入口
 *
 * 支持淘宝联盟、多多进宝、京东联盟的：
 * - 物料搜索 materialSearch
 * - 链接转换 linkConvert
 * - 店铺搜索 shopSearch
 * - 商品详情 itemDetail
 *
 * 使用外部传入的 Config 配置各平台 app_key / app_secret 等。
 *
 * 示例：
 *   $config = new Config([
 *     'taobao' => ['app_key' => 'xx', 'app_secret' => 'xx', 'pid' => 'mm_xx_xx_xx'],
 *     'pinduoduo' => ['client_id' => 'xx', 'client_secret' => 'xx', 'pid' => 'xx'],
 *     'jd' => ['app_key' => 'xx', 'app_secret' => 'xx', 'union_id' => 'xx', 'position_id' => 'xx'],
 *   ]);
 *   $client = new ShopUnionClient($config);
 *   $client->taobao()->materialSearch(['keyword' => '手机']);
 *   $client->pinduoduo()->linkConvert(['goods_sign_list' => ['xxx']]);
 *   $client->jd()->itemDetail(['sku_ids' => '100012345678']);
 */
final class ShopUnionClient
{
    /** @var Config */
    private $config;

    /** @var HttpClientInterface */
    private $http;

    /** @var TaobaoClient|null */
    private $taobaoClient;

    /** @var PinduoduoClient|null */
    private $pinduoduoClient;

    /** @var JdClient|null */
    private $jdClient;

    public function __construct(Config $config, ?HttpClientInterface $http = null)
    {
        $this->config = $config;
        $this->http = $http ?? new GuzzleHttpClient();
    }

    /** 淘宝联盟客户端（需在 Config 中配置 taobao.app_key / app_secret） */
    public function taobao(): TaobaoClient
    {
        if ($this->taobaoClient === null) {
            $this->taobaoClient = new TaobaoClient($this->config, $this->http);
        }
        return $this->taobaoClient;
    }

    /** 多多进宝客户端（需在 Config 中配置 pinduoduo.client_id / client_secret） */
    public function pinduoduo(): PinduoduoClient
    {
        if ($this->pinduoduoClient === null) {
            $this->pinduoduoClient = new PinduoduoClient($this->config, $this->http);
        }
        return $this->pinduoduoClient;
    }

    /** 京东联盟客户端（需在 Config 中配置 jd.app_key / app_secret） */
    public function jd(): JdClient
    {
        if ($this->jdClient === null) {
            $this->jdClient = new JdClient($this->config, $this->http);
        }
        return $this->jdClient;
    }

    /** 当前配置 */
    public function getConfig(): Config
    {
        return $this->config;
    }
}
