<?php

namespace InnoBrain\OnofficeCli\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \InnoBrain\OnofficeCli\OnofficeCli
 */
class OnofficeCli extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \InnoBrain\OnofficeCli\OnofficeCli::class;
    }
}
