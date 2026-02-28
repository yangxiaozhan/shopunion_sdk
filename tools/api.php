<?php

declare(strict_types=1);

/**
 * 调试工具 API：接收前端请求，调用 ShopUnion SDK 并返回结果
 *
 * 用法：POST application/json
 * {
 *   "platform": "taobao|pinduoduo|jd",
 *   "method": "materialSearch|linkConvert|shopSearch|itemDetail",
 *   "params": { ... },
 *   "config": { ... }  // 可选，覆盖默认配置
 * }
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => '请使用 POST 请求']);
    exit;
}

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (!is_file($autoload)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => '请先运行 composer install']);
    exit;
}

require $autoload;

// 加载 .env 到环境变量（若存在）
$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        $pos = strpos($line, '=');
        if ($pos !== false) {
            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1), " \t\n\r\0\x0B'\"");
            if ($key !== '') {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

$input = json_decode((string) file_get_contents('php://input'), true) ?: [];
$platform = $input['platform'] ?? '';
$method = $input['method'] ?? '';
$params = $input['params'] ?? [];
$configOverride = $input['config'] ?? [];

$baseConfig = [];
$configFile = __DIR__ . '/config.local.php';
if (is_file($configFile)) {
    $baseConfig = (array) require $configFile;
} else {
    $baseConfig = (array) require __DIR__ . '/config.sample.php';
}

foreach (['taobao', 'pinduoduo', 'jd'] as $p) {
    if (!empty($configOverride[$p])) {
        $baseConfig[$p] = array_merge($baseConfig[$p] ?? [], $configOverride[$p]);
    }
}

// 去除凭证首尾空格/换行，避免 Invalid signature（常见于 .env 或复制粘贴）
$trimKeys = ['app_key', 'app_secret', 'client_id', 'client_secret', 'session', 'pid', 'adzone_id', 'site_id', 'union_id', 'position_id'];
foreach (['taobao', 'pinduoduo', 'jd'] as $p) {
    foreach ($trimKeys as $k) {
        if (isset($baseConfig[$p][$k]) && is_string($baseConfig[$p][$k])) {
            $baseConfig[$p][$k] = trim($baseConfig[$p][$k]);
        }
    }
}

$validPlatforms = ['taobao', 'pinduoduo', 'jd'];
$validMethods = ['materialSearch', 'linkConvert', 'shopSearch', 'itemDetail'];

if (!in_array($platform, $validPlatforms, true)) {
    echo json_encode(['success' => false, 'error' => 'platform 需为 taobao|pinduoduo|jd']);
    exit;
}
if (!in_array($method, $validMethods, true)) {
    echo json_encode(['success' => false, 'error' => 'method 需为 materialSearch|linkConvert|shopSearch|itemDetail']);
    exit;
}

$debugHttp = new \ShopUnion\SDK\Http\DebugHttpClient();

try {
    $config = new \ShopUnion\SDK\Config($baseConfig);
    $client = new \ShopUnion\SDK\ShopUnionClient($config, $debugHttp);

    $platformClient = match ($platform) {
        'taobao' => $client->taobao(),
        'pinduoduo' => $client->pinduoduo(),
        'jd' => $client->jd(),
    };

    $result = $platformClient->$method($params);

    $out = [
        'success' => true,
        'data' => $result,
        'debug' => [
            'request' => $debugHttp->lastRequest,
            'response' => $debugHttp->lastResponse,
        ],
    ];
    echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    http_response_code(500);
    $payload = [
        'success' => false,
        'error' => $e->getMessage(),
        'code' => $e->getCode(),
        'debug' => [
            'request' => $debugHttp->lastRequest,
            'response' => $debugHttp->lastResponse,
        ],
    ];
    if ($e instanceof \ShopUnion\SDK\Exception\SdkException && $e->getRawResponse() !== null) {
        $payload['raw'] = $e->getRawResponse();
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
