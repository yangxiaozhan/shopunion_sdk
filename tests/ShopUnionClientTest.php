<?php

declare(strict_types=1);

namespace ShopUnion\SDK\Tests;

use PHPUnit\Framework\TestCase;
use ShopUnion\SDK\Config;
use ShopUnion\SDK\ShopUnionClient;

final class ShopUnionClientTest extends TestCase
{
    public function testClientCanBeCreatedWithConfig(): void
    {
        $config = new Config([
            'taobao' => [
                'app_key' => 'test_key',
                'app_secret' => 'test_secret',
                'pid' => 'mm_1_2_3',
            ],
        ]);
        $client = new ShopUnionClient($config);
        $this->assertTrue($config->hasTaobao());
        $this->assertSame($config, $client->getConfig());
        $this->assertNotNull($client->taobao());
    }

    public function testConfigHasPinduoduoAndJd(): void
    {
        $config = new Config([
            'pinduoduo' => ['client_id' => 'c', 'client_secret' => 's'],
            'jd' => ['app_key' => 'k', 'app_secret' => 's'],
        ]);
        $this->assertTrue($config->hasPinduoduo());
        $this->assertTrue($config->hasJd());
    }
}
