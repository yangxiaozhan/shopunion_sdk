<?php

declare(strict_types=1);

namespace ShopUnion\SDK\Tests;

use PHPUnit\Framework\TestCase;
use ShopUnion\SDK\Config;
use ShopUnion\SDK\ShopUnionClient;

/**
 * 集成测试：需要配置环境变量才执行真实 API 调用，否则自动跳过。
 *
 * 使用方式（在项目根目录）：
 *   TAOBAO_APP_KEY=xxx TAOBAO_APP_SECRET=xxx TAOBAO_PID=mm_1_2_3 \
 *   ./vendor/bin/phpunit tests/IntegrationTest.php
 *
 * 或先 export 再运行：
 *   export TAOBAO_APP_KEY=xxx TAOBAO_APP_SECRET=xxx TAOBAO_PID=mm_1_2_3
 *   ./vendor/bin/phpunit
 */
final class IntegrationTest extends TestCase
{
    private function skipIfNoTaobaoConfig(): void
    {
        if (!getenv('TAOBAO_APP_KEY') || !getenv('TAOBAO_APP_SECRET')) {
            $this->markTestSkipped('未设置 TAOBAO_APP_KEY / TAOBAO_APP_SECRET，跳过淘宝联盟集成测试');
        }
    }

    public function testTaobaoMaterialSearch(): void
    {
        $this->skipIfNoTaobaoConfig();
        $config = new Config([
            'taobao' => [
                'app_key'    => getenv('TAOBAO_APP_KEY'),
                'app_secret' => getenv('TAOBAO_APP_SECRET'),
                'pid'        => getenv('TAOBAO_PID') ?: null,
                'adzone_id'  => getenv('TAOBAO_ADZONE_ID') ?: null,
            ],
        ]);
        $client = new ShopUnionClient($config);
        $result = $client->taobao()->materialSearch([
            'keyword'   => '手机',
            'page_no'   => 1,
            'page_size' => 2,
        ]);
        $this->assertIsArray($result);
        // 成功时返回数组即可（具体字段以各平台文档为准）
    }
}
