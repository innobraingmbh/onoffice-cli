<?php

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
});
