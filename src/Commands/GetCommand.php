<?php

namespace InnoBrain\OnofficeCli\Commands;

use InnoBrain\OnofficeCli\Exceptions\InvalidEntityException;
use InnoBrain\OnofficeCli\Exceptions\RecordNotFoundException;
use InnoBrain\OnofficeCli\Exceptions\ValidationException;
use InnoBrain\OnofficeCli\Support\RepositoryFactory;

class GetCommand extends OnOfficeCommand
{
    protected $signature = 'onoffice:get
        {entity : The entity type (estate, address, activity, etc.)}
        {id : The record ID to fetch}
        {--select=* : Fields to select (e.g., --select=Id --select=Ort)}
        {--apiClaim= : API claim for the request}
        {--json : Output results as JSON}';

    protected $description = 'Get a single onOffice record by ID';

    protected function executeCommand(): int
    {
        $entity = $this->argument('entity');
        $id = $this->argument('id');

        if (! RepositoryFactory::isValidEntity($entity)) {
            throw new InvalidEntityException($entity, RepositoryFactory::getAvailableEntities());
        }

        if (! is_numeric($id)) {
            throw new ValidationException("ID must be numeric, got '{$id}'");
        }

        $query = RepositoryFactory::query($entity);
        $this->applyApiClaim($query);

        $select = $this->option('select');
        if (filled($select)) {
            $query->select($select);
        }

        $record = $query->find((int) $id);

        if ($record === null) {
            throw new RecordNotFoundException($entity, $id);
        }

        return $this->outputSuccess($record, [
            'entity' => $entity,
        ]);
    }
}
