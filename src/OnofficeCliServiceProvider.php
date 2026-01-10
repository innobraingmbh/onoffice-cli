<?php

namespace InnoBrain\OnofficeCli;

use InnoBrain\OnofficeCli\Commands\OnofficeCliCommand;
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
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_onoffice_cli_table')
            ->hasCommand(OnofficeCliCommand::class);
    }
}
