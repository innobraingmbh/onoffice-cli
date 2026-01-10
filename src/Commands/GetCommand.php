<?php

namespace InnoBrain\OnofficeCli\Commands;

use Exception;
use Illuminate\Console\Command;
use InnoBrain\OnofficeCli\Concerns\OutputsJson;
use InnoBrain\OnofficeCli\Support\RepositoryFactory;
use InvalidArgumentException;

class GetCommand extends Command
{
    use OutputsJson;

    public $signature = 'onoffice:get
        {entity : The entity type (estate, address, activity, etc.)}
        {id : The record ID to fetch}
        {--select=* : Fields to select (e.g., --select=Id --select=Ort)}
        {--json : Output results as JSON}';

    public $description = 'Get a single onOffice record by ID';

    public function handle(): int
    {
        $entity = $this->argument('entity');
        $id = $this->argument('id');

        if (! RepositoryFactory::isValidEntity($entity)) {
            return $this->outputError(
                "Unknown entity '{$entity}'. Available: ".implode(', ', RepositoryFactory::getAvailableEntities()),
                400
            );
        }

        if (! is_numeric($id)) {
            return $this->outputError("ID must be numeric, got '{$id}'", 400);
        }

        try {
            $query = RepositoryFactory::query($entity);

            // Apply select
            $select = $this->option('select');
            if (! empty($select)) {
                $query->select($select);
            }

            $record = $query->find((int) $id);

            if ($record === null) {
                return $this->outputError(
                    "Record not found: {$entity} #{$id}",
                    404
                );
            }

            return $this->outputSuccess($record, [
                'entity' => $entity,
            ]);

        } catch (InvalidArgumentException $e) {
            return $this->outputError($e->getMessage(), 400);
        } catch (Exception $e) {
            return $this->outputError($e->getMessage(), 500);
        }
    }
}
