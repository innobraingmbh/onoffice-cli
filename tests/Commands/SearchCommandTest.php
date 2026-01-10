<?php

use Illuminate\Support\Facades\Artisan;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;

describe('SearchCommand', function () {
    it('rejects invalid entity', function () {
        $this->artisan('onoffice:search', ['entity' => 'invalid', '--json' => true])
            ->assertFailed()
            ->expectsOutputToContain('Unknown entity');
    });

    it('lists available entities in error message', function () {
        $this->artisan('onoffice:search', ['entity' => 'foo', '--json' => true])
            ->assertFailed()
            ->expectsOutputToContain('estate');
    });

    it('returns search results as JSON', function () {
        EstateRepository::fake(
            EstateRepository::response([
                EstateRepository::page(
                    actionId: OnOfficeAction::Read,
                    resourceType: OnOfficeResourceType::Estate,
                    recordFactories: [
                        EstateFactory::make()
                            ->id(123)
                            ->data([
                                'kaufpreis' => 250000,
                                'ort' => 'Berlin',
                                'zimmer' => 3,
                            ]),
                        EstateFactory::make()
                            ->id(456)
                            ->data([
                                'kaufpreis' => 350000,
                                'ort' => 'Hamburg',
                                'zimmer' => 4,
                            ]),
                    ],
                ),
            ])
        );

        Artisan::call('onoffice:search', ['entity' => 'estate', '--json' => true]);
        $output = Artisan::output();

        expect($output)->toContain('"data":');
        expect($output)->toContain('"total": 2');
        expect($output)->toContain('"entity": "estate"');
    });

    it('applies select option to query', function () {
        EstateRepository::fake(
            EstateRepository::response([
                EstateRepository::page(
                    actionId: OnOfficeAction::Read,
                    resourceType: OnOfficeResourceType::Estate,
                    recordFactories: [
                        EstateFactory::make()
                            ->id(123)
                            ->data([
                                'kaufpreis' => 250000,
                            ]),
                    ],
                ),
            ])
        );

        Artisan::call('onoffice:search', [
            'entity' => 'estate',
            '--select' => ['kaufpreis'],
            '--json' => true,
        ]);
        $output = Artisan::output();

        expect($output)->toContain('"data":');
        expect($output)->toContain('250000');
    });

    it('returns empty results gracefully', function () {
        EstateRepository::fake(
            EstateRepository::response([
                EstateRepository::page(
                    actionId: OnOfficeAction::Read,
                    resourceType: OnOfficeResourceType::Estate,
                    recordFactories: [],
                ),
            ])
        );

        Artisan::call('onoffice:search', ['entity' => 'estate', '--json' => true]);
        $output = Artisan::output();

        expect($output)->toContain('"total": 0');
    });

    it('includes limit and offset in meta', function () {
        EstateRepository::fake(
            EstateRepository::response([
                EstateRepository::page(
                    actionId: OnOfficeAction::Read,
                    resourceType: OnOfficeResourceType::Estate,
                    recordFactories: [],
                ),
            ])
        );

        Artisan::call('onoffice:search', [
            'entity' => 'estate',
            '--limit' => '10',
            '--offset' => '5',
            '--json' => true,
        ]);
        $output = Artisan::output();

        expect($output)->toContain('"limit": 10');
        expect($output)->toContain('"offset": 5');
    });

    it('rejects negative limit', function () {
        $this->artisan('onoffice:search', [
            'entity' => 'estate',
            '--limit' => '-1',
            '--json' => true,
        ])
            ->assertFailed()
            ->expectsOutputToContain('Limit must be a positive integer');
    });

    it('rejects negative offset', function () {
        $this->artisan('onoffice:search', [
            'entity' => 'estate',
            '--offset' => '-1',
            '--json' => true,
        ])
            ->assertFailed()
            ->expectsOutputToContain('Offset must be a non-negative integer');
    });
});
