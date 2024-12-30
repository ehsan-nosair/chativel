<?php

namespace EhsanNosair\Chativel;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use EhsanNosair\Chativel\Commands\ChativelCommand;

class ChativelServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('chativel')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_chativel_table')
            ->hasCommand(ChativelCommand::class);
    }
}
