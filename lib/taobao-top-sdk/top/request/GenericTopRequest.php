<?php
/**
 * 通用 TOP 请求：用于 SDK 未包含的 API
 */
class GenericTopRequest
{
    private $method;
    private $apiParas = [];

    public function __construct($method, array $params = [])
    {
        $this->method = $method;
        $this->apiParas = $params;
    }

    public function getApiMethodName()
    {
        return $this->method;
    }

    public function getApiParas()
    {
        return $this->apiParas;
    }

    public function check()
    {
        // 不强制校验，由服务端校验
    }

    public function putOtherTextParam($key, $value)
    {
        $this->apiParas[$key] = $value;
    }
}
