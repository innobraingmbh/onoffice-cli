<?php

namespace InnoBrain\OnofficeCli\Exceptions;

class RecordNotFoundException extends OnOfficeCliException
{
    public function __construct(string $entity, int|string $id)
    {
        parent::__construct("Record not found: {$entity} #{$id}", 404);
    }
}
