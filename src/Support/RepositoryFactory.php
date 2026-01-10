<?php

namespace InnoBrain\OnofficeCli\Support;

use Innobrain\OnOfficeAdapter\Facades\ActivityRepository;
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\FieldRepository;
use Innobrain\OnOfficeAdapter\Facades\FileRepository;
use Innobrain\OnOfficeAdapter\Facades\FilterRepository;
use Innobrain\OnOfficeAdapter\Facades\LogRepository;
use Innobrain\OnOfficeAdapter\Facades\MacroRepository;
use Innobrain\OnOfficeAdapter\Facades\MarketplaceRepository;
use Innobrain\OnOfficeAdapter\Facades\RelationRepository;
use Innobrain\OnOfficeAdapter\Facades\SearchCriteriaRepository;
use Innobrain\OnOfficeAdapter\Facades\SettingRepository;
use Innobrain\OnOfficeAdapter\Query\Builder;
use InvalidArgumentException;

class RepositoryFactory
{
    /**
     * @var array<string, class-string>
     */
    protected static array $repositories = [
        'estate' => EstateRepository::class,
        'address' => AddressRepository::class,
        'activity' => ActivityRepository::class,
        'field' => FieldRepository::class,
        'file' => FileRepository::class,
        'filter' => FilterRepository::class,
        'log' => LogRepository::class,
        'macro' => MacroRepository::class,
        'marketplace' => MarketplaceRepository::class,
        'relation' => RelationRepository::class,
        'searchcriteria' => SearchCriteriaRepository::class,
        'setting' => SettingRepository::class,
    ];

    /**
     * Get a query builder for the given entity.
     */
    public static function query(string $entity): Builder
    {
        $repositoryClass = self::getRepositoryClass($entity);

        return $repositoryClass::query();
    }

    /**
     * Get the repository class for the given entity.
     *
     * @return class-string
     */
    public static function getRepositoryClass(string $entity): string
    {
        $normalized = strtolower(trim($entity));

        if (! isset(self::$repositories[$normalized])) {
            throw new InvalidArgumentException(
                "Unknown entity '{$entity}'. Available entities: ".implode(', ', self::getAvailableEntities())
            );
        }

        return self::$repositories[$normalized];
    }

    /**
     * Get a list of available entity names.
     *
     * @return array<string>
     */
    public static function getAvailableEntities(): array
    {
        return array_keys(self::$repositories);
    }

    /**
     * Check if an entity is supported.
     */
    public static function isValidEntity(string $entity): bool
    {
        return isset(self::$repositories[strtolower(trim($entity))]);
    }
}
