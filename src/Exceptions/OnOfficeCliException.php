<?php

namespace InnoBrain\OnofficeCli\Exceptions;

use Exception;

class OnOfficeCliException extends Exception
{
    protected int $httpCode;

    public function __construct(string $message, int $httpCode = 500, ?Exception $previous = null)
    {
        $this->httpCode = $httpCode;
        parent::__construct($message, $httpCode, $previous);
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
}
