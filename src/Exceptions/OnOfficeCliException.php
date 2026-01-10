<?php

namespace InnoBrain\OnofficeCli\Exceptions;

use Exception;
use Throwable;

class OnOfficeCliException extends Exception
{
    public function __construct(
        string $message,
        protected int $httpCode = 500,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $httpCode, $previous);
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
}
