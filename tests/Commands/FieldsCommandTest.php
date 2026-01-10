<?php

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
});
