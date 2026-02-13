<?php

declare(strict_types=1);

namespace ShopUnion\SDK\Exception;

use Throwable;

class SdkException extends \Exception implements Throwable
{
    /** @var string|null 平台返回的错误码 */
    private $codeFromApi;

    /** @var mixed 原始响应 */
    private $rawResponse;

    /**
     * @param mixed $rawResponse
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        ?string $codeFromApi = null,
        $rawResponse = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->codeFromApi = $codeFromApi;
        $this->rawResponse = $rawResponse;
    }

    public function getCodeFromApi(): ?string
    {
        return $this->codeFromApi;
    }

    public function getRawResponse()
    {
        return $this->rawResponse;
    }
}
