<?php

namespace EhsanNosair\Chativel;

use Livewire\Livewire;
use Filament\Support\Assets\Css;
use Spatie\LaravelPackageTools\Package;
use Filament\Support\Facades\FilamentAsset;
use EhsanNosair\Chativel\Commands\ChativelCommand;
use EhsanNosair\Chativel\Livewire\ConversationBox;
use EhsanNosair\Chativel\Livewire\NewConversation;
use EhsanNosair\Chativel\Livewire\ConversationsList;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ChativelServiceProvider extends PackageServiceProvider
{
    public static string $name = 'chativel';

    public static string $viewNamespace = 'chativel';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name(static::$name)
            ->hasAssets()
            ->hasConfigFile()
            ->hasViews(static::$viewNamespace)
            ->hasMigrations($this->getMigrations())
            ->hasTranslations()
            ->hasRoute('api')
            ->hasCommand(ChativelCommand::class);
    }

    public function packageBooted(): void
    {
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        Livewire::component('chativel-conversation-box', ConversationBox::class);
        Livewire::component('chativel-conversations-list', ConversationsList::class);
        Livewire::component('chativel-new-conversation', NewConversation::class);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'ehsan-nosair/chativel';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            Css::make('chativel-styles', __DIR__ . '/../resources/css/chativel.css')->loadedOnRequest(),
        ];
    }
    
    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            '00001_create_conversations_table',
            '00002_create_conversation_participants_table',
            '00003_create_messages_table',
            '00004_create_message_statuses_table',
            '00005_create_chatable_statuses_table',
        ];
    }
}
