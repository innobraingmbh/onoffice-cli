<?php

namespace InnoBrain\OnofficeCli\Exceptions;

class ValidationException extends OnOfficeCliException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 400);
    }
}
