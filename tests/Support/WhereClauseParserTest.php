<?php

use InnoBrain\OnofficeCli\Support\WhereClauseParser;

describe('WhereClauseParser', function () {
    it('parses equality operator', function () {
        $result = WhereClauseParser::parse('status=active');

        expect($result)->toBe([
            'field' => 'status',
            'operator' => '=',
            'value' => 'active',
        ]);
    });

    it('parses not equal operator', function () {
        $result = WhereClauseParser::parse('status!=deleted');

        expect($result)->toBe([
            'field' => 'status',
            'operator' => '!=',
            'value' => 'deleted',
        ]);
    });

    it('parses less than operator', function () {
        $result = WhereClauseParser::parse('price<500000');

        expect($result)->toBe([
            'field' => 'price',
            'operator' => '<',
            'value' => 500000,
        ]);
    });

    it('parses greater than operator', function () {
        $result = WhereClauseParser::parse('rooms>3');

        expect($result)->toBe([
            'field' => 'rooms',
            'operator' => '>',
            'value' => 3,
        ]);
    });

    it('parses less than or equal operator', function () {
        $result = WhereClauseParser::parse('price<=300000');

        expect($result)->toBe([
            'field' => 'price',
            'operator' => '<=',
            'value' => 300000,
        ]);
    });

    it('parses greater than or equal operator', function () {
        $result = WhereClauseParser::parse('rooms>=4');

        expect($result)->toBe([
            'field' => 'rooms',
            'operator' => '>=',
            'value' => 4,
        ]);
    });

    it('parses like operator', function () {
        $result = WhereClauseParser::parse('city like Berlin');

        expect($result)->toBe([
            'field' => 'city',
            'operator' => 'like',
            'value' => 'Berlin',
        ]);
    });

    it('parses not like operator', function () {
        $result = WhereClauseParser::parse('city not like Berlin');

        expect($result)->toBe([
            'field' => 'city',
            'operator' => 'not like',
            'value' => 'Berlin',
        ]);
    });

    it('casts numeric values to integers', function () {
        $result = WhereClauseParser::parse('rooms=4');

        expect($result['value'])->toBe(4);
        expect($result['value'])->toBeInt();
    });

    it('casts decimal values to floats', function () {
        $result = WhereClauseParser::parse('price=299.99');

        expect($result['value'])->toBe(299.99);
        expect($result['value'])->toBeFloat();
    });

    it('casts true boolean value', function () {
        $result = WhereClauseParser::parse('active=true');

        expect($result['value'])->toBeTrue();
    });

    it('casts false boolean value', function () {
        $result = WhereClauseParser::parse('active=false');

        expect($result['value'])->toBeFalse();
    });

    it('casts null value', function () {
        $result = WhereClauseParser::parse('owner=null');

        expect($result['value'])->toBeNull();
    });

    it('parses multiple clauses', function () {
        $result = WhereClauseParser::parseMany([
            'status=active',
            'price<500000',
            'rooms>=3',
        ]);

        expect($result)->toHaveCount(3);
        expect($result[0]['field'])->toBe('status');
        expect($result[1]['field'])->toBe('price');
        expect($result[2]['field'])->toBe('rooms');
    });

    it('throws exception for missing operator', function () {
        WhereClauseParser::parse('statusactive');
    })->throws(InvalidArgumentException::class, 'no valid operator found');

    it('throws exception for missing field', function () {
        WhereClauseParser::parse('=active');
    })->throws(InvalidArgumentException::class, 'field and value are required');

    it('throws exception for missing value', function () {
        WhereClauseParser::parse('status=');
    })->throws(InvalidArgumentException::class, 'field and value are required');

    it('trims whitespace from clause', function () {
        $result = WhereClauseParser::parse('  status = active  ');

        expect($result['field'])->toBe('status');
        expect($result['value'])->toBe('active');
    });
});
