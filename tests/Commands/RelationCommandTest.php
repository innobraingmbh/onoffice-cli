<?php

use Illuminate\Support\Facades\Artisan;
use Innobrain\OnOfficeAdapter\Facades\RelationRepository;

describe('SearchCommand with relation entity', function () {
    it('fails because relationType is required', function () {
        // RelationBuilder requires relationType to be set before get() is called.
        // The SearchCommand doesn't provide this, so it fails with an uninitialized property error.
        // This is expected behavior - relations need special parameters (relationType, parentIds, childIds)
        // that aren't available through the generic search interface.
        RelationRepository::fake([]);

        Artisan::call('onoffice:search', ['entity' => 'relation', '--json' => true]);
    })->throws(Error::class, 'relationType must not be accessed before initialization');

    it('fails with where clauses because relationType is still required', function () {
        RelationRepository::fake([]);

        // Even with filters, the underlying RelationBuilder still requires relationType
        Artisan::call('onoffice:search', [
            'entity' => 'relation',
            '--where' => ['status=1'],
            '--json' => true,
        ]);
    })->throws(Error::class, 'relationType must not be accessed before initialization');
});

describe('GetCommand with relation entity', function () {
    it('fails because find is not implemented for relations', function () {
        RelationRepository::fake([]);

        $this->artisan('onoffice:get', ['entity' => 'relation', 'id' => '123', '--json' => true])
            ->assertFailed()
            ->expectsOutputToContain('Not implemented');
    });
});
