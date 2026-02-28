# ShopUnion SDK

电商联盟统一 PHP SDK，用于对接 **淘宝联盟**、**多多进宝**、**京东联盟** 的开放 API。合作方通过 Composer 引入后，使用**自身或平台方提供的配置**即可调用物料搜索、链接转换、店铺搜索、商品详情、商品列表、生成淘口令、物料分类及物料精选等能力。

---

## 合作方如何调用本 SDK

### 一、环境要求

- PHP >= 7.4
- Composer
- 已开通对应联盟平台并取得 **app_key / app_secret（或 client_id / client_secret）** 及推广位等信息

### 二、安装

在合作方项目中执行：

```bash
composer require fcwh/shop_sdk
```

或在 `composer.json` 中增加依赖后执行 `composer update`：

```json
{
    "require": {
        "fcwh/shop_sdk": "^1.0"
    }
}
```

若使用公司私有 Packagist，需在 `composer.json` 中配置 `repositories` 指向本包仓库或私有源。

### 三、配置与创建客户端

**所有密钥、推广位等均由合作方自行传入**，SDK 不内置任何账号信息。合作方从各自业务配置（环境变量、配置中心、数据库等）中读取后，组装成 `Config` 再创建 `ShopUnionClient`。

```php
use ShopUnion\SDK\Config;
use ShopUnion\SDK\ShopUnionClient;

// 从合作方自己的配置源读取（示例为数组，实际可来自 .env、数据库等）
$options = [
    'taobao' => [
        'app_key'    => getenv('TAOBAO_APP_KEY') ?: '你的淘宝app_key',
        'app_secret' => getenv('TAOBAO_APP_SECRET') ?: '你的淘宝app_secret',
        'pid'        => getenv('TAOBAO_PID') ?: 'mm_xxx_xxx_xxx',  // 或 adzone_id
    ],
    'pinduoduo' => [
        'client_id'     => getenv('PDD_CLIENT_ID') ?: '你的拼多多client_id',
        'client_secret' => getenv('PDD_CLIENT_SECRET') ?: '你的拼多多client_secret',
        'pid'           => getenv('PDD_PID') ?: '你的推广位pid',
    ],
    'jd' => [
        'app_key'     => getenv('JD_APP_KEY') ?: '你的京东app_key',
        'app_secret'  => getenv('JD_APP_SECRET') ?: '你的京东app_secret',
        'union_id'    => getenv('JD_UNION_ID') ?: '联盟ID',
        'position_id' => getenv('JD_POSITION_ID') ?: '推广位ID',
    ],
];

$config = new Config($options);
$client = new ShopUnionClient($config);
```

**说明：**

- 只需配置将要使用的平台，未使用的平台可省略。
- 淘宝：必填 `app_key`、`app_secret`；转链/物料等需 `pid`（或 `adzone_id`）。
- 拼多多：必填 `client_id`、`client_secret`；转链需 `pid`。
- 京东：必填 `app_key`、`app_secret`；转链需 `union_id`、`position_id`。

### 四、调用方式约定

- **入口**：统一使用 `ShopUnionClient` 实例。
- **选平台**：通过 `$client->taobao()`、`$client->pinduoduo()`、`$client->jd()` 选择淘宝联盟、多多进宝、京东联盟。
- **选能力**：在对应平台对象上调用具体方法，传入 `array` 参数，返回值为接口原始结构的 `array`（由各联盟 API 文档定义）。

**通用调用形式：**

```php
$result = $client->平台()->方法名([
    '参数名' => '参数值',
    // ...
]);
```

合作方只需按「平台 → 方法名 → 参数」即可完成调用，无需关心签名、网关等底层细节。

### 五、各平台能力与调用示例

#### 1. 淘宝联盟 `$client->taobao()`

| 方法 | 说明 | 主要参数 |
|------|------|----------|
| `materialSearch` | 物料搜索（关键词商品） | keyword, page_no, page_size |
| `linkConvert` | 长链转短链 | url 或 urls（数组） |
| `shopSearch` | 店铺搜索 | keyword, page_no, page_size |
| `itemDetail` | 商品详情（升级版，最多20个） | num_iids 或 item_id |
| `promotionGoodsList` | 权益物料商品列表 | page_num, page_size, promotion_id(默认62191) |
| `createTpwd` | 生成淘口令 | url（推广链接） |
| `materialCategoryList` | 物料分类列表（获取物料 id） | subject(默认1), material_type(默认1) |
| `materialRecommendList` | 按物料 id 获取商品列表 | material_id（来自分类列表）, page_no, page_size |

```php
// 示例：关键词搜索
$result = $client->taobao()->materialSearch([
    'keyword'   => '手机',
    'page_no'   => 1,
    'page_size' => 20,
]);

// 示例：长链转短链
$result = $client->taobao()->linkConvert([
    'url' => 'https://s.click.taobao.com/xxx',
]);

// 示例：先取物料分类，再按物料 id 取商品列表
$categories = $client->taobao()->materialCategoryList(['subject' => 1, 'material_type' => 1]);
$goods      = $client->taobao()->materialRecommendList([
    'material_id' => 27939,
    'page_no'     => 1,
    'page_size'   => 20,
]);
```

#### 2. 多多进宝 `$client->pinduoduo()`

| 方法 | 说明 | 主要参数 |
|------|------|----------|
| `materialSearch` | 物料搜索 | keyword, page, page_size |
| `linkConvert` | 链接转换 | goods_sign_list 或 goods_id_list, pid(可选) |
| `shopSearch` | 店铺搜索 | keyword, page, page_size |
| `itemDetail` | 商品详情 | goods_sign_list 或 goods_id_list |

```php
$result = $client->pinduoduo()->materialSearch([
    'keyword'   => '手机',
    'page'      => 1,
    'page_size' => 20,
]);
$result = $client->pinduoduo()->linkConvert([
    'goods_sign_list' => ['xxx'],
]);
```

#### 3. 京东联盟 `$client->jd()`

| 方法 | 说明 | 主要参数 |
|------|------|----------|
| `materialSearch` | 物料搜索 | keyword, page_index, page_size |
| `linkConvert` | 链接转换 | material_id（商品链接等） |
| `shopSearch` | 店铺/商品搜索 | 同 materialSearch |
| `itemDetail` | 商品详情 | sku_ids |

```php
$result = $client->jd()->materialSearch([
    'keyword'    => '手机',
    'page_index' => 1,
    'page_size'  => 20,
]);
$result = $client->jd()->linkConvert([
    'material_id' => 'https://item.jd.com/100012345678.html',
]);
```

### 六、返回值与异常

- **成功**：各方法返回 `array`，结构以对应联盟 API 文档为准（如 `result_list`、`data`、`results` 等），合作方按文档解析即可。
- **失败**：SDK 会抛出 `ShopUnion\SDK\Exception\SdkException`。合作方应捕获该异常，并根据需要记录日志、返回错误码或提示信息。

```php
use ShopUnion\SDK\Exception\SdkException;

try {
    $result = $client->taobao()->materialSearch(['keyword' => '手机']);
    // 使用 $result 做业务逻辑
} catch (SdkException $e) {
    // 错误信息
    $message = $e->getMessage();
    // 平台错误码（若有）
    $codeFromApi = $e->getCodeFromApi();
    // 原始响应（便于排查）
    $raw = $e->getRawResponse();
    // 合作方在此处理：打日志、返回统一错误格式等
}
```

### 七、推荐业务流程示例（淘宝）

合作方常见流程：获取物料/商品 → 取详情 → 转链 → 生成淘口令用于传播。

```php
// 1）获取物料分类下的商品列表
$list = $client->taobao()->materialRecommendList([
    'material_id' => 27939,
    'page_no'     => 1,
    'page_size'   => 20,
]);

// 2）根据商品 id 查详情（示例：取列表中第一个商品 id）
// $itemId = $list['result_list'][0]['item_id'] ?? null;

// 3）长链转短链（合作方自己的推广长链）
$short = $client->taobao()->linkConvert([
    'url' => 'https://s.click.taobao.com/xxx',
]);

// 4）生成淘口令用于分享
$tpwd = $client->taobao()->createTpwd([
    'url' => $short['results'][0]['content'] ?? 'https://s.click.taobao.com/xxx',
]);
// 淘口令文案：$tpwd['data']['model'] 或 $tpwd['data']['password_simple']
```

合作方按自身业务选择其中步骤或调整顺序即可。

---

## 配置项说明（可选阅读）

| 平台 | 配置键 | 必填 | 说明 |
|------|--------|------|------|
| 淘宝 | app_key, app_secret | 是 | 开放平台应用凭证 |
| 淘宝 | pid 或 adzone_id | 转链/物料必填 | 推广位，pid 格式 mm_账户_媒体_推广位 |
| 淘宝 | session | 部分接口 | 会员授权 session |
| 拼多多 | client_id, client_secret | 是 | 开放平台应用凭证 |
| 拼多多 | pid | 转链必填 | 推广位 |
| 京东 | app_key, app_secret | 是 | 开放平台应用凭证 |
| 京东 | union_id, position_id | 转链必填 | 联盟 ID、推广位 ID |

---

## 高级用法

**自定义 HTTP 客户端**（如超时、代理）：实现 `ShopUnion\SDK\Http\HttpClientInterface` 后注入：

```php
$client = new ShopUnionClient($config, $yourHttpClient);
```

**直接调平台原始 API**（淘宝）：`$client->taobao()->call('taobao.xxx.xxx', ['param' => 'value']);`

---

## 发布与版本

- 本包可发布至 [packagist.org](https://packagist.org) 或公司私有 Packagist。
- 版本发布：打 tag（如 `v1.0.0`）并推送到仓库后，合作方通过 `composer update fcwh/shop_sdk` 获取新版本。

## 许可证

MIT
