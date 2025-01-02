<?php

namespace EhsanNosair\Chativel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ChativelCommand extends Command
{
    public $signature = 'chativel:install';

    public $description = 'Install ChatiVel';

    public function handle(): int
    {
        $this->info('Installing ChatiVel...');
        $this->publishAssets();
        $this->runMigrations();
        $this->comment('All done');

        return self::SUCCESS;
    }

    protected function publishAssets()
    {
        $this->info('Publishing assets...');

        // Publish migrations
        Artisan::call('vendor:publish', [
            '--provider' => 'Spatie\MediaLibrary\MediaLibraryServiceProvider',
            '--tag' => 'medialibrary-migrations',
        ]);
        Artisan::call('vendor:publish', [
            '--provider' => 'EhsanNosair\Chativel\ChativelServiceProvider',
            '--tag' => 'chativel-migrations',
        ]);

        // Publish configuration
        Artisan::call('vendor:publish', [
            '--provider' => 'EhsanNosair\Chativel\ChativelServiceProvider',
            '--tag' => 'chativel-config',
        ]);

        $this->info('Assets published.');
    }

    protected function runMigrations()
    {
        $this->info('Running migrations...');
        Artisan::call('migrate');
        $this->info('Migrations completed.');
    }

}
