<?php

namespace EhsanNosair\Chativel\Tests;

use Filament\FilamentServiceProvider;
use Livewire\LivewireServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Tables\TablesServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Filament\Infolists\InfolistsServiceProvider;
use EhsanNosair\Chativel\ChativelServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'EhsanNosair\\Chativel\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            ChativelServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_chativel_table.php.stub';
        $migration->up();
        */
    }
}
