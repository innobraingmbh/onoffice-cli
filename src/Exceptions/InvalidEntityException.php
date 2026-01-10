<?php

namespace InnoBrain\OnofficeCli\Exceptions;

class InvalidEntityException extends OnOfficeCliException
{
    public function __construct(string $entity, array $availableEntities)
    {
        $message = "Unknown entity '{$entity}'. Available: ".implode(', ', $availableEntities);
        parent::__construct($message, 400);
    }
}
