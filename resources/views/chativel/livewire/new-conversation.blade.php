<x-filament::modal :close-button="false" id="new-conversation-modal">
    <x-slot name="heading">
        {{ __('New Conversation') }} 
        <x-filament::loading-indicator class="mx-auto h-5 w-5" wire:loading wire:target="goToConversation" />
    </x-slot>
    
    <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
        <x-filament::input
            type="text"
            placeholder="{{ __('Search...') }}"
            wire:model.live.debounce.500ms="searchForNewConversation"
            autofocus
        />
    </x-filament::input.wrapper>

    <x-filament::loading-indicator class="mx-auto h-5 w-5" wire:loading wire:target="searchForNewConversation" />
    @if ($searchForNewConversation)
        @if ($chatables->isNotEmpty())
            <div class="flex flex-col gap-2 overflow-y-auto chativel-scrollbar" style="max-height: 300px">
                @foreach ($chatables as $chatable)
                    <div wire:click.prevent="goToConversation('{{ addslashes(get_class($chatable)) }}', {{ $chatable->id }})" class="cursor-pointer px-2 xl-px-3 py-2 mt-0.5 rounded-xl hover:bg-gray-100 focus-visible:bg-gray-100 dark:hover:bg-white/5 dark:focus-visible:bg-white/5">
                        <div class="flex items-center justify-start gap-2">
                            @php
                                $displayColumn = $chatable->displayColumn ?? $chatable[config('chativel.deafult_display_column')];
                            @endphp
                            <x-filament::avatar
                                src="https://ui-avatars.com/api/?name={{ urlencode($displayColumn) }}"
                                alt="Profile" size="lg" 
                            />
                            <span>{{ $displayColumn }}</span>
                        </div>
                    </div>                
                @endforeach
            </div>
        @else
            <p wire:loading.remove wire:target="searchForNewConversation" class="text-center text-gray-500">{{ __('no matching results found') }}</p>
        @endif
    @endif
</x-filament::modal>
