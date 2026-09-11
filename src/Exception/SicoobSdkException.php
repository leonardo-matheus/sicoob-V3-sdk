<?php

namespace Logics\SicoobSdk\Exception;

use Exception;

class SicoobSdkException extends Exception
{
}

class AuthenticationException extends SicoobSdkException
{
}

class ValidationException extends SicoobSdkException
{
}

class ApiException extends SicoobSdkException
{
    private int $statusCode;

    public function __construct(string $message, int $statusCode, int $code = 0)
    {
        parent::__construct($message, $code);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public static function fromResponse(int $statusCode, string $body): self
    {
        $decoded = json_decode($body);
        $message = $decoded->mensagem ?? "Erro na API Sicoob (HTTP {$statusCode})";

        return new self($message, $statusCode);
    }
}
