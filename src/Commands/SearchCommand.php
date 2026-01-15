<?php

namespace InnoBrain\OnofficeCli\Commands;

use InnoBrain\OnofficeCli\Exceptions\InvalidEntityException;
use InnoBrain\OnofficeCli\Exceptions\ValidationException;
use InnoBrain\OnofficeCli\Support\RepositoryFactory;
use InnoBrain\OnofficeCli\Support\WhereClauseParser;

class SearchCommand extends OnOfficeCommand
{
    protected $signature = 'onoffice:search
        {entity : The entity to search (estate, address, activity, etc.)}
        {--where=* : Filter conditions (e.g., --where="status=1" --where="price<500000")}
        {--select=* : Fields to select (e.g., --select=Id --select=Ort)}
        {--limit= : Maximum number of results}
        {--offset= : Number of results to skip}
        {--orderBy= : Field to order by (ascending)}
        {--orderByDesc= : Field to order by (descending)}
        {--apiClaim= : API claim for the request}
        {--json : Output results as JSON}';

    protected $description = 'Search onOffice records with filters and pagination';

    protected function executeCommand(): int
    {
        $entity = $this->argument('entity');

        if (! RepositoryFactory::isValidEntity($entity)) {
            throw new InvalidEntityException($entity, RepositoryFactory::getAvailableEntities());
        }

        $query = RepositoryFactory::query($entity);
        $this->applyApiClaim($query);

        $select = $this->option('select');
        if (filled($select)) {
            $query->select($select);
        }

        foreach (WhereClauseParser::parseMany($this->option('where')) as $clause) {
            $query->where($clause['field'], $clause['operator'], $clause['value']);
        }

        if ($orderBy = $this->option('orderBy')) {
            $query->orderBy($orderBy);
        }

        if ($orderByDesc = $this->option('orderByDesc')) {
            $query->orderByDesc($orderByDesc);
        }

        $limit = $this->getValidatedLimit();
        $offset = $this->getValidatedOffset();

        if ($limit !== null) {
            $query->limit($limit);
        }

        if ($offset > 0) {
            $query->offset($offset);
        }

        $results = $query->get();

        return $this->outputSuccess($results, [
            'total' => $results->count(),
            'limit' => $limit,
            'offset' => $offset,
            'entity' => $entity,
        ]);
    }

    private function getValidatedLimit(): ?int
    {
        $limit = $this->option('limit');

        if ($limit === null) {
            return null;
        }

        $limit = (int) $limit;

        if ($limit < 1) {
            throw new ValidationException('Limit must be a positive integer');
        }

        return $limit;
    }

    private function getValidatedOffset(): int
    {
        $offset = $this->option('offset');

        if ($offset === null) {
            return 0;
        }

        $offset = (int) $offset;

        if ($offset < 0) {
            throw new ValidationException('Offset must be a non-negative integer');
        }

        return $offset;
    }
}
