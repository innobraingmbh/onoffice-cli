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

            if (filled($this->option('select'))) {
                $query->select($this->option('select'));
            }

            foreach (WhereClauseParser::parseMany($this->option('where') ?? []) as $clause) {
                $query->where($clause['field'], $clause['operator'], $clause['value']);
            }

            if ($orderBy = $this->option('orderBy')) {
                $query->orderBy($orderBy);
            }

            if ($orderByDesc = $this->option('orderByDesc')) {
                $query->orderByDesc($orderByDesc);
            }

            if ($limit = $this->option('limit')) {
                $query->limit((int) $limit);
            }

            if ($offset = $this->option('offset')) {
                $query->offset((int) $offset);
            }

            $results = $query->get();

            return $this->outputSuccess($results, [
                'total' => $results->count(),
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
