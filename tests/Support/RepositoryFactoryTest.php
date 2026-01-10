<?php

use InnoBrain\OnofficeCli\Support\RepositoryFactory;

describe('RepositoryFactory', function () {
    it('returns available entities', function () {
        $entities = RepositoryFactory::getAvailableEntities();

        expect($entities)->toContain('estate');
        expect($entities)->toContain('address');
        expect($entities)->toContain('activity');
    });

    it('validates known entities', function () {
        expect(RepositoryFactory::isValidEntity('estate'))->toBeTrue();
        expect(RepositoryFactory::isValidEntity('address'))->toBeTrue();
        expect(RepositoryFactory::isValidEntity('activity'))->toBeTrue();
    });

    it('rejects unknown entities', function () {
        expect(RepositoryFactory::isValidEntity('unknown'))->toBeFalse();
        expect(RepositoryFactory::isValidEntity('foo'))->toBeFalse();
    });

    it('is case insensitive', function () {
        expect(RepositoryFactory::isValidEntity('ESTATE'))->toBeTrue();
        expect(RepositoryFactory::isValidEntity('Estate'))->toBeTrue();
        expect(RepositoryFactory::isValidEntity('eStAtE'))->toBeTrue();
    });

    it('trims whitespace', function () {
        expect(RepositoryFactory::isValidEntity('  estate  '))->toBeTrue();
    });

    it('throws exception for unknown entity when getting repository class', function () {
        RepositoryFactory::getRepositoryClass('unknown');
    })->throws(InvalidArgumentException::class, "Unknown entity 'unknown'");

    it('returns correct repository class for estate', function () {
        $class = RepositoryFactory::getRepositoryClass('estate');

        expect($class)->toBe(\Innobrain\OnOfficeAdapter\Facades\EstateRepository::class);
    });

    it('returns correct repository class for address', function () {
        $class = RepositoryFactory::getRepositoryClass('address');

        expect($class)->toBe(\Innobrain\OnOfficeAdapter\Facades\AddressRepository::class);
    });
});
