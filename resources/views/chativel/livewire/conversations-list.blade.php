<div class="h-full">
    <div class="flex flex-col h-full">

        <!-- Converstations header -->
        <div class="px-4 ">
            <div class="flex items-center justify-between h-16 gap-2">
                <p class="text-lg font-bold">{{__('Conversations')}}</p>
                
                <div class="flex gap-3">

                    <x-filament::icon-button
                        @click="$dispatch('open-modal', { id: 'new-conversation-modal' })"
                        icon="heroicon-m-pencil-square"
                    />
                    
                    <x-filament::icon-button
                        icon="heroicon-m-x-mark"
                        @click="isChatsbarOpen = !isChatsbarOpen"
                        class="xl:hidden" 
                    />
                </div>
            </div>
        </div>
    
        <!-- Conversations list -->
        <div class="chativel-scrollbar overflow-y-auto h-full">
            <div class="flex flex-col gap-2 p-2 h-full">
                @forelse ($conversations as $conversation)
                    <a wire:click.prevent="goToConversation({{ $conversation->id }})" class="cursor-pointer px-3 py-3 mt-0.5 rounded-xl hover:bg-gray-100 focus-visible:bg-gray-100 dark:hover:bg-white/5 dark:focus-visible:bg-white/5
                        {{ $selectedConversation?->id == $conversation->id ? 'bg-gray-100 dark:bg-white/5':'' }}
                    ">
                        <div class="flex items-center justify-start gap-3">

                            @if (!$conversation->is_group)
                                <x-filament::avatar
                                    src="{{ $conversation->avatar }}"
                                    alt="Profile" size="lg" 
                                />
                            @else
                                <div class="rounded-full h-10 w-10 flex items-center justify-center" style="background: #f0e9e9;aspect-ratio: 1">
                                    <x-filament::icon icon="heroicon-m-users" class="w-6 h-6" style="color: rgb(115, 115, 115)" />
                                </div>
                            @endif


                            <div class="w-full">
                                <div class="flex justify-between">
                                    <p class="text-sm">{{ ucfirst($conversation->display_name) }}</p>
                                    <p class="text-xs text-gray-400">{{ $conversation->formatted_last_action_date }}</p>
                                </div>
                                <p class="text-xs text-gray-400 truncate overflow-hidden whitespace-nowrap" style="width: 14rem">{{ $conversation->last_message }}</p>
                            </div>
                        </div>
                    </a>   
                @empty
                    <div class="flex flex-col items-center justify-center h-full">
                        <div class="p-3 mb-4 bg-gray-100 rounded-full dark:bg-gray-500/20">
                            <x-filament::icon icon="heroicon-m-x-mark" class="w-6 h-6 text-gray-500 dark:text-gray-400" />
                        </div>
                        <div x-data class="text-center">
                            <p class="text-base text-gray-600 dark:text-gray-400">
                                {{ __('no conversations yet') }}
                            </p>
                            <x-filament::link
                                @click="$dispatch('open-modal', { id: 'new-conversation-modal' })"
                                tag="button"
                            >
                                {{ strtolower(__('New Conversation')) }}
                            </x-filament::link>
                        </div>
                    </div>
                @endforelse

                @if ($this->paginator->hasMorePages())
                    <div x-intersect="$wire.loadMoreConversations" class="h-5 ">
                        <x-filament::loading-indicator class="mx-auto h-5 w-5" />
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
