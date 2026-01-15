<?php

namespace InnoBrain\OnofficeCli\Support;

use Innobrain\OnOfficeAdapter\Query\Builder;
use InvalidArgumentException;

class RepositoryFactory
{
    /**
     * @param  array<string, class-string>  $repositories
     */
    public function __construct(
        protected array $repositories = []
    ) {}

    /**
     * Get a query builder for the given entity.
     */
    public function query(string $entity): Builder
    {
        $repositoryClass = $this->getRepositoryClass($entity);

        return $repositoryClass::query();
    }

    /**
     * Get the repository class for the given entity.
     *
     * @return class-string
     */
    public function getRepositoryClass(string $entity): string
    {
        $normalized = strtolower(trim($entity));

        if (! isset($this->repositories[$normalized])) {
            throw new InvalidArgumentException(
                "Unknown entity '{$entity}'. Available entities: ".implode(', ', $this->getAvailableEntities())
            );
        }

        return $this->repositories[$normalized];
    }

    /**
     * Get a list of available entity names.
     *
     * @return array<string>
     */
    public function getAvailableEntities(): array
    {
        return array_keys($this->repositories);
    }

    /**
     * Check if an entity is supported.
     */
    public function isValidEntity(string $entity): bool
    {
        return isset($this->repositories[strtolower(trim($entity))]);
    }
}
