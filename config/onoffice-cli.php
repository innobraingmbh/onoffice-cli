<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supported Entities
    |--------------------------------------------------------------------------
    |
    | The entities that can be queried via the CLI commands. Each entity maps
    | to a repository class from the laravel-onoffice-adapter package.
    |
    */

    'entities' => [
        'estate' => Innobrain\OnOfficeAdapter\Facades\EstateRepository::class,
        'address' => Innobrain\OnOfficeAdapter\Facades\AddressRepository::class,
        'activity' => Innobrain\OnOfficeAdapter\Facades\ActivityRepository::class,
        'field' => Innobrain\OnOfficeAdapter\Facades\FieldRepository::class,
        'file' => Innobrain\OnOfficeAdapter\Facades\FileRepository::class,
        'filter' => Innobrain\OnOfficeAdapter\Facades\FilterRepository::class,
        'lastseen' => Innobrain\OnOfficeAdapter\Facades\LastSeenRepository::class,
        'link' => Innobrain\OnOfficeAdapter\Facades\LinkRepository::class,
        'log' => Innobrain\OnOfficeAdapter\Facades\LogRepository::class,
        'macro' => Innobrain\OnOfficeAdapter\Facades\MacroRepository::class,
        'marketplace' => Innobrain\OnOfficeAdapter\Facades\MarketplaceRepository::class,
        'relation' => Innobrain\OnOfficeAdapter\Facades\RelationRepository::class,
        'searchcriteria' => Innobrain\OnOfficeAdapter\Facades\SearchCriteriaRepository::class,
        'setting' => Innobrain\OnOfficeAdapter\Facades\SettingRepository::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Field Modules
    |--------------------------------------------------------------------------
    |
    | Entities that support field lookups via the fields command. Maps entity
    | names to their corresponding onOffice module identifiers.
    |
    */

    'field_modules' => [
        'estate' => 'estate',
        'address' => 'address',
        'activity' => 'agentslog',
        'searchcriteria' => 'searchcriteria',
    ],
];
