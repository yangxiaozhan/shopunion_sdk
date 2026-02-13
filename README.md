# ShopUnion SDK

电商联盟统一 PHP SDK，用于对接 **淘宝联盟**、**多多进宝**、**京东联盟** 的开放 API，支持物料搜索、链接转换、店铺搜索与商品详情。配置通过外部传入，适合公司内部 Composer 引用并发布到 Packagist（或私有 Packagist）。

## 要求

- PHP >= 7.4
- GuzzleHTTP ^7.0

## 安装

```bash
composer require fcwh/shop_sdk
```

或放入 `composer.json`：

```json
{
    "require": {
        "fcwh/shop_sdk": "^1.0"
    }
}
```

## 配置

使用 `ShopUnion\SDK\Config` 传入各平台配置，按需只配置要使用的平台即可。

```php
use ShopUnion\SDK\Config;
use ShopUnion\SDK\ShopUnionClient;

$config = new Config([
    // 淘宝联盟（阿里百川/淘宝开放平台）
    'taobao' => [
        'app_key'     => '你的app_key',
        'app_secret'  => '你的app_secret',
        'pid'         => 'mm_123_456_789',  // 推广位，转链必填；或单独填 adzone_id
        'adzone_id'   => null,             // 可选，不填则从 pid 最后一段解析
        'session'     => null,             // 部分接口需要会员授权 session
        'gateway'     => 'https://eco.taobao.com/router/rest',
    ],
    // 多多进宝（拼多多开放平台）
    'pinduoduo' => [
        'client_id'     => '你的client_id',
        'client_secret'  => '你的client_secret',
        'pid'            => '你的推广位pid',  // 转链必填
        'access_token'   => null,
        'gateway'        => 'https://gw-api.pinduoduo.com/router',
    ],
    // 京东联盟
    'jd' => [
        'app_key'     => '你的app_key',
        'app_secret'  => '你的app_secret',
        'union_id'    => '联盟ID',          // 转链必填
        'position_id' => '推广位ID',
        'gateway'     => 'https://api.jd.com/routerjson',
    ],
]);

$client = new ShopUnionClient($config);
```

## 使用方式

### 淘宝联盟

```php
// 物料搜索
$result = $client->taobao()->materialSearch([
    'keyword'   => '手机',
    'page_no'   => 1,
    'page_size' => 20,
]);

// 链接转换（商品 ID / 淘口令 / URL）
$result = $client->taobao()->linkConvert([
    'item_id' => '612345678901',  // 或 'content' => '淘口令', 或 'url' => 'https://...'
]);

// 店铺搜索（精选物料）
$result = $client->taobao()->shopSearch([
    'keyword'   => '旗舰店',
    'page_no'   => 1,
    'page_size' => 20,
]);

// 商品详情
$result = $client->taobao()->itemDetail([
    'num_iids' => '612345678901,612345678902',  // 或 'item_id' => '612345678901'
]);
```

### 多多进宝

```php
// 物料搜索
$result = $client->pinduoduo()->materialSearch([
    'keyword'   => '手机',
    'page'      => 1,
    'page_size' => 20,
    'sort_type' => 0,
    'with_coupon' => true,
]);

// 链接转换
$result = $client->pinduoduo()->linkConvert([
    'goods_sign_list' => ['xxx'],  // 或 goods_id_list
    'pid'             => null,    // 不传则用 config 中的 pid
]);

// 店铺搜索
$result = $client->pinduoduo()->shopSearch([
    'keyword'   => '旗舰店',
    'page'      => 1,
    'page_size' => 20,
]);

// 商品详情
$result = $client->pinduoduo()->itemDetail([
    'goods_sign_list' => ['xxx'],  // 或 goods_id_list
]);
```

### 京东联盟

```php
// 物料搜索
$result = $client->jd()->materialSearch([
    'keyword'    => '手机',
    'page_index' => 1,
    'page_size'  => 20,
    'has_coupon' => true,
]);

// 链接转换
$result = $client->jd()->linkConvert([
    'material_id' => 'https://item.jd.com/100012345678.html',  // 或商品链接
    'union_id'    => null,  // 不传则用 config
    'position_id' => null,
]);

// 店铺搜索（京东以商品搜索为主，当前实现为商品搜索）
$result = $client->jd()->shopSearch(['keyword' => '手机', 'page_index' => 1, 'page_size' => 20]);

// 商品详情
$result = $client->jd()->itemDetail([
    'sku_ids' => '100012345678,100012345679',  // 或 sku_ids 数组
]);
```

## 异常处理

接口调用失败会抛出 `ShopUnion\SDK\Exception\SdkException`，可获取平台错误码与原始响应：

```php
use ShopUnion\SDK\Exception\SdkException;

try {
    $result = $client->taobao()->materialSearch(['keyword' => '手机']);
} catch (SdkException $e) {
    echo $e->getMessage();
    echo $e->getCodeFromApi();   // 平台 sub_code / error_code
    print_r($e->getRawResponse()); // 原始返回
}
```

## 自定义 HTTP 客户端

可注入实现 `ShopUnion\SDK\Http\HttpClientInterface` 的客户端（如自定义超时、代理）：

```php
$http = new YourHttpClient();
$client = new ShopUnionClient($config, $http);
```

## 发布到 Packagist

1. 在 [packagist.org](https://packagist.org) 注册并提交仓库地址。
2. 打 tag 发布版本：`git tag v1.0.0 && git push origin v1.0.0`。
3. 公司内部可使用私有 Packagist 或 Composer 私有源，在项目 `composer.json` 中配置 `repositories` 指向本仓库。

## 许可证

MIT
