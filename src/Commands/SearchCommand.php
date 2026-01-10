<?php

namespace InnoBrain\OnofficeCli\Commands;

use Exception;
use Illuminate\Console\Command;
use InnoBrain\OnofficeCli\Concerns\OutputsJson;
use InnoBrain\OnofficeCli\Support\RepositoryFactory;
use InnoBrain\OnofficeCli\Support\WhereClauseParser;
use InvalidArgumentException;

class SearchCommand extends Command
{
    use OutputsJson;

    public $signature = 'onoffice:search
        {entity : The entity to search (estate, address, activity, etc.)}
        {--where=* : Filter conditions (e.g., --where="status=1" --where="price<500000")}
        {--select=* : Fields to select (e.g., --select=Id --select=Ort)}
        {--limit= : Maximum number of results}
        {--offset= : Number of results to skip}
        {--orderBy= : Field to order by (ascending)}
        {--orderByDesc= : Field to order by (descending)}
        {--json : Output results as JSON}';

    public $description = 'Search onOffice records with filters and pagination';

    public function handle(): int
    {
        $entity = $this->argument('entity');

        if (! RepositoryFactory::isValidEntity($entity)) {
            return $this->outputError(
                "Unknown entity '{$entity}'. Available: ".implode(', ', RepositoryFactory::getAvailableEntities()),
                400
            );
        }

        try {
            $query = RepositoryFactory::query($entity);

            // Apply select
            $select = $this->option('select');
            if (! empty($select)) {
                $query->select($select);
            }

            // Apply where clauses
            $whereClauses = $this->option('where');
            if (! empty($whereClauses)) {
                $parsed = WhereClauseParser::parseMany($whereClauses);
                foreach ($parsed as $clause) {
                    $query->where($clause['field'], $clause['operator'], $clause['value']);
                }
            }

            // Apply ordering
            if ($orderBy = $this->option('orderBy')) {
                $query->orderBy($orderBy);
            }
            if ($orderByDesc = $this->option('orderByDesc')) {
                $query->orderByDesc($orderByDesc);
            }

            // Apply pagination
            if ($limit = $this->option('limit')) {
                $query->limit((int) $limit);
            }
            if ($offset = $this->option('offset')) {
                $query->offset((int) $offset);
            }

            // Execute query
            $results = $query->get();

            // Get result count (use collection count as fallback)
            $total = $results->count();

            return $this->outputSuccess($results, [
                'total' => $total,
                'limit' => $this->option('limit') ? (int) $this->option('limit') : null,
                'offset' => $this->option('offset') ? (int) $this->option('offset') : 0,
                'entity' => $entity,
            ]);

        } catch (InvalidArgumentException $e) {
            return $this->outputError($e->getMessage(), 400);
        } catch (Exception $e) {
            return $this->outputError($e->getMessage(), 500);
        }
    }
}
