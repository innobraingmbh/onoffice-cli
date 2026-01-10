<?php

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
});
