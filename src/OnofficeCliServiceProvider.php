<?php

namespace InnoBrain\OnofficeCli;

use InnoBrain\OnofficeCli\Commands\FieldsCommand;
use InnoBrain\OnofficeCli\Commands\GetCommand;
use InnoBrain\OnofficeCli\Commands\SearchCommand;
use InnoBrain\OnofficeCli\Support\RepositoryFactory;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class OnofficeCliServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('onoffice-cli')
            ->hasConfigFile()
            ->hasCommands([
                SearchCommand::class,
                GetCommand::class,
                FieldsCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(RepositoryFactory::class, function ($app) {
            return new RepositoryFactory(
                config('onoffice-cli.entities', [])
            );
        });
    }
}
