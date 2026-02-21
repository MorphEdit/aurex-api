<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class HttpException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly string $errorCode,
        int $statusCode = 400
    ) {
        parent::__construct($message, $statusCode);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
