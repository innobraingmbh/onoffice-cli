<?php

use Illuminate\Support\Facades\Artisan;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;

describe('GetCommand', function () {
    it('rejects invalid entity', function () {
        $this->artisan('onoffice:get', ['entity' => 'invalid', 'id' => '123', '--json' => true])
            ->assertFailed()
            ->expectsOutputToContain('Unknown entity');
    });

    it('rejects non-numeric id', function () {
        $this->artisan('onoffice:get', ['entity' => 'estate', 'id' => 'abc', '--json' => true])
            ->assertFailed()
            ->expectsOutputToContain('ID must be numeric');
    });

    it('returns single record as JSON', function () {
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
                    ],
                ),
            ])
        );

        Artisan::call('onoffice:get', ['entity' => 'estate', 'id' => '123', '--json' => true]);
        $output = Artisan::output();

        expect($output)->toContain('"data":');
        expect($output)->toContain('"id": 123');
        expect($output)->toContain('"entity": "estate"');
        expect($output)->toContain('250000');
        expect($output)->toContain('Berlin');
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

        Artisan::call('onoffice:get', [
            'entity' => 'estate',
            'id' => '123',
            '--select' => ['kaufpreis'],
            '--json' => true,
        ]);
        $output = Artisan::output();

        expect($output)->toContain('"data":');
        expect($output)->toContain('250000');
    });

    it('returns 404 when record not found', function () {
        EstateRepository::fake(
            EstateRepository::response([
                EstateRepository::page(
                    actionId: OnOfficeAction::Read,
                    resourceType: OnOfficeResourceType::Estate,
                    recordFactories: [],
                ),
            ])
        );

        $this->artisan('onoffice:get', ['entity' => 'estate', 'id' => '999', '--json' => true])
            ->assertFailed()
            ->expectsOutputToContain('not found');
    });
});
