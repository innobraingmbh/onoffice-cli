<?php

namespace InnoBrain\OnofficeCli\Exceptions;

class InvalidEntityException extends OnOfficeCliException
{
    /**
     * @param  array<string>  $availableEntities
     */
    public function __construct(string $entity, array $availableEntities)
    {
        parent::__construct(
            "Unknown entity '{$entity}'. Available: ".implode(', ', $availableEntities),
            400
        );
    }
}
