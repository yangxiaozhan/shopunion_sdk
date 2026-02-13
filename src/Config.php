<?php

declare(strict_types=1);

namespace ShopUnion\SDK;

/**
 * SDK 配置：支持外部传入，用于淘宝联盟、多多进宝、京东联盟
 */
final class Config
{
    /** @var array<string, mixed> */
    private $taobao = [];

    /** @var array<string, mixed> */
    private $pinduoduo = [];

    /** @var array<string, mixed> */
    private $jd = [];

    public function __construct(array $options = [])
    {
        $this->taobao = $options['taobao'] ?? [];
        $this->pinduoduo = $options['pinduoduo'] ?? [];
        $this->jd = $options['jd'] ?? [];
    }

    /** 淘宝联盟：app_key (必填) */
    public function getTaobaoAppKey(): ?string
    {
        return $this->taobao['app_key'] ?? null;
    }

    /** 淘宝联盟：app_secret (必填) */
    public function getTaobaoAppSecret(): ?string
    {
        return $this->taobao['app_secret'] ?? null;
    }

    /** 淘宝联盟：推广位 pid，转链等需要 */
    public function getTaobaoPid(): ?string
    {
        return $this->taobao['pid'] ?? null;
    }

    /** 淘宝联盟：adzone_id，与 pid 二选一或由 pid 解析 */
    public function getTaobaoAdzoneId(): ?string
    {
        return $this->taobao['adzone_id'] ?? null;
    }

    /** 淘宝联盟：session（部分接口需要会员授权） */
    public function getTaobaoSession(): ?string
    {
        return $this->taobao['session'] ?? null;
    }

    /** 淘宝联盟网关 */
    public function getTaobaoGateway(): string
    {
        return $this->taobao['gateway'] ?? 'https://eco.taobao.com/router/rest';
    }

    /** 拼多多：client_id (必填) */
    public function getPinduoduoClientId(): ?string
    {
        return $this->pinduoduo['client_id'] ?? null;
    }

    /** 拼多多：client_secret (必填) */
    public function getPinduoduoClientSecret(): ?string
    {
        return $this->pinduoduo['client_secret'] ?? null;
    }

    /** 拼多多：推广位 pid，转链需要 */
    public function getPinduoduoPid(): ?string
    {
        return $this->pinduoduo['pid'] ?? null;
    }

    /** 拼多多：access_token（部分接口需要） */
    public function getPinduoduoAccessToken(): ?string
    {
        return $this->pinduoduo['access_token'] ?? null;
    }

    /** 拼多多网关 */
    public function getPinduoduoGateway(): string
    {
        return $this->pinduoduo['gateway'] ?? 'https://gw-api.pinduoduo.com/router';
    }

    /** 京东：app_key (必填) */
    public function getJdAppKey(): ?string
    {
        return $this->jd['app_key'] ?? null;
    }

    /** 京东：app_secret (必填) */
    public function getJdAppSecret(): ?string
    {
        return $this->jd['app_secret'] ?? null;
    }

    /** 京东：联盟 ID unionId，转链需要 */
    public function getJdUnionId(): ?string
    {
        return $this->jd['union_id'] ?? null;
    }

    /** 京东：推广位 positionId */
    public function getJdPositionId(): ?string
    {
        return $this->jd['position_id'] ?? null;
    }

    /** 京东网关 */
    public function getJdGateway(): string
    {
        return $this->jd['gateway'] ?? 'https://api.jd.com/routerjson';
    }

    /** 是否已配置淘宝 */
    public function hasTaobao(): bool
    {
        return $this->getTaobaoAppKey() !== null && $this->getTaobaoAppSecret() !== null;
    }

    /** 是否已配置拼多多 */
    public function hasPinduoduo(): bool
    {
        return $this->getPinduoduoClientId() !== null && $this->getPinduoduoClientSecret() !== null;
    }

    /** 是否已配置京东 */
    public function hasJd(): bool
    {
        return $this->getJdAppKey() !== null && $this->getJdAppSecret() !== null;
    }
}
