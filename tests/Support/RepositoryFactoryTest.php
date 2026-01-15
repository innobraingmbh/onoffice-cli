<?php

use InnoBrain\OnofficeCli\Support\RepositoryFactory;

describe('RepositoryFactory', function () {
    it('returns available entities', function () {
        $factory = app(RepositoryFactory::class);
        $entities = $factory->getAvailableEntities();

        expect($entities)->toContain('estate');
        expect($entities)->toContain('address');
        expect($entities)->toContain('activity');
        expect($entities)->toContain('link');
        expect($entities)->toContain('lastseen');
    });

    it('validates known entities', function () {
        $factory = app(RepositoryFactory::class);

        expect($factory->isValidEntity('estate'))->toBeTrue();
        expect($factory->isValidEntity('address'))->toBeTrue();
        expect($factory->isValidEntity('activity'))->toBeTrue();
        expect($factory->isValidEntity('link'))->toBeTrue();
        expect($factory->isValidEntity('lastseen'))->toBeTrue();
    });

    it('rejects unknown entities', function () {
        $factory = app(RepositoryFactory::class);

        expect($factory->isValidEntity('unknown'))->toBeFalse();
        expect($factory->isValidEntity('foo'))->toBeFalse();
    });

    it('is case insensitive', function () {
        $factory = app(RepositoryFactory::class);

        expect($factory->isValidEntity('ESTATE'))->toBeTrue();
        expect($factory->isValidEntity('Estate'))->toBeTrue();
        expect($factory->isValidEntity('eStAtE'))->toBeTrue();
    });

    it('trims whitespace', function () {
        $factory = app(RepositoryFactory::class);

        expect($factory->isValidEntity('  estate  '))->toBeTrue();
    });

    it('throws exception for unknown entity when getting repository class', function () {
        $factory = app(RepositoryFactory::class);
        $factory->getRepositoryClass('unknown');
    })->throws(InvalidArgumentException::class, "Unknown entity 'unknown'");

    it('returns correct repository class for estate', function () {
        $factory = app(RepositoryFactory::class);
        $class = $factory->getRepositoryClass('estate');

        expect($class)->toBe(\Innobrain\OnOfficeAdapter\Facades\EstateRepository::class);
    });

    it('returns correct repository class for address', function () {
        $factory = app(RepositoryFactory::class);
        $class = $factory->getRepositoryClass('address');

        expect($class)->toBe(\Innobrain\OnOfficeAdapter\Facades\AddressRepository::class);
    });

    it('returns correct repository class for relation', function () {
        $factory = app(RepositoryFactory::class);
        $class = $factory->getRepositoryClass('relation');

        expect($class)->toBe(\Innobrain\OnOfficeAdapter\Facades\RelationRepository::class);
    });

    it('returns correct repository class for link', function () {
        $factory = app(RepositoryFactory::class);
        $class = $factory->getRepositoryClass('link');

        expect($class)->toBe(\Innobrain\OnOfficeAdapter\Facades\LinkRepository::class);
    });

    it('returns correct repository class for lastseen', function () {
        $factory = app(RepositoryFactory::class);
        $class = $factory->getRepositoryClass('lastseen');

        expect($class)->toBe(\Innobrain\OnOfficeAdapter\Facades\LastSeenRepository::class);
    });

    it('validates relation entity', function () {
        $factory = app(RepositoryFactory::class);

        expect($factory->isValidEntity('relation'))->toBeTrue();
    });
});
