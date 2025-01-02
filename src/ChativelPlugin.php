<?php

namespace EhsanNosair\Chativel;

use EhsanNosair\Chativel\Pages\ChativelPage;
use Filament\Contracts\Plugin;
use Filament\Panel;

class ChativelPlugin implements Plugin
{
    public function getId(): string
    {
        return 'chativel';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            ChativelPage::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
