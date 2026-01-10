<?php

use Illuminate\Support\Facades\Artisan;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\FieldRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\FieldFactory;

beforeEach(function () {
    FieldRepository::fake(
        FieldRepository::response([
            FieldRepository::page(
                actionId: OnOfficeAction::Get,
                resourceType: OnOfficeResourceType::Fields,
                recordFactories: [
                    FieldFactory::make()
                        ->id('estate')
                        ->data([
                            'kaufpreis' => [
                                'type' => 'float',
                                'length' => null,
                                'permittedvalues' => null,
                                'default' => null,
                            ],
                            'mietpreis' => [
                                'type' => 'float',
                                'length' => null,
                                'permittedvalues' => null,
                                'default' => null,
                            ],
                            'objekttyp' => [
                                'type' => 'singleselect',
                                'length' => null,
                                'permittedvalues' => ['haus', 'wohnung', 'grundstueck'],
                                'default' => null,
                            ],
                            'ort' => [
                                'type' => 'varchar',
                                'length' => 50,
                                'permittedvalues' => null,
                                'default' => null,
                            ],
                            'wohnflaeche' => [
                                'type' => 'float',
                                'length' => null,
                                'permittedvalues' => null,
                                'default' => null,
                            ],
                        ]),
                ],
            ),
        ])
    );
});

describe('FieldsCommand', function () {
    it('rejects unsupported entity', function () {
        $this->artisan('onoffice:fields', ['entity' => 'invalid', '--json' => true])
            ->assertFailed()
            ->expectsOutputToContain('Fields are not available');
    });

    it('shows supported entities in error message', function () {
        $this->artisan('onoffice:fields', ['entity' => 'foo', '--json' => true])
            ->assertFailed()
            ->expectsOutputToContain('estate');
    });

    it('returns all fields in compact mode by default', function () {
        Artisan::call('onoffice:fields', ['entity' => 'estate', '--json' => true]);
        $output = Artisan::output();

        expect($output)->toContain('"data":');
        expect($output)->toContain('"name":');
        expect($output)->toContain('"type":');
        expect($output)->toContain('"count": 5');
    });

    it('returns full field details with --full flag', function () {
        Artisan::call('onoffice:fields', ['entity' => 'estate', '--full' => true, '--json' => true]);
        $output = Artisan::output();

        expect($output)->toContain('"permittedValues":');
        expect($output)->toContain('"length":');
    });

    it('filters fields by substring', function () {
        Artisan::call('onoffice:fields', ['entity' => 'estate', '--filter' => 'preis', '--json' => true]);
        $output = Artisan::output();

        expect($output)->toContain('"kaufpreis"');
        expect($output)->toContain('"mietpreis"');
        expect($output)->toContain('"count": 2');
    });

    it('filters fields by wildcard pattern', function () {
        Artisan::call('onoffice:fields', ['entity' => 'estate', '--filter' => '*flaeche', '--json' => true]);
        $output = Artisan::output();

        expect($output)->toContain('"wohnflaeche"');
        expect($output)->toContain('"count": 1');
    });

    it('returns empty result when filter matches nothing', function () {
        Artisan::call('onoffice:fields', ['entity' => 'estate', '--filter' => 'nonexistent', '--json' => true]);
        $output = Artisan::output();

        expect($output)->toContain('"count": 0');
    });

    it('returns single field with permitted values', function () {
        Artisan::call('onoffice:fields', ['entity' => 'estate', '--field' => 'objekttyp', '--json' => true]);
        $output = Artisan::output();

        expect($output)->toContain('"name": "objekttyp"');
        expect($output)->toContain('"type": "singleselect"');
        expect($output)->toContain('"haus"');
        expect($output)->toContain('"wohnung"');
    });

    it('returns error for non-existent field', function () {
        $this->artisan('onoffice:fields', ['entity' => 'estate', '--field' => 'nonexistent', '--json' => true])
            ->assertFailed()
            ->expectsOutputToContain('not found');
    });

    it('matches field name case-insensitively', function () {
        Artisan::call('onoffice:fields', ['entity' => 'estate', '--field' => 'KAUFPREIS', '--json' => true]);
        $output = Artisan::output();

        expect($output)->toContain('"name": "kaufpreis"');
    });
});
