<?php

namespace InnoBrain\OnofficeCli;

use InnoBrain\OnofficeCli\Commands\FieldsCommand;
use InnoBrain\OnofficeCli\Commands\GetCommand;
use InnoBrain\OnofficeCli\Commands\SearchCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class OnofficeCliServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('onoffice-cli')
            ->hasCommand(SearchCommand::class)
            ->hasCommand(GetCommand::class)
            ->hasCommand(FieldsCommand::class);
    }
}
