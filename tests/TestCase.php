<?php

namespace InnoBrain\OnofficeCli\Tests;

use InnoBrain\OnofficeCli\OnofficeCliServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            OnofficeCliServiceProvider::class,
        ];
    }
}
