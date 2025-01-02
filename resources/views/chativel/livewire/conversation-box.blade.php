@php
    use Carbon\Carbon;
    use App\Facades\Chativel;
@endphp
<div class="h-full relative" x-data="{ imageUrl: '', imageId: '', imageSize: ''}">
    <div class="flex flex-col h-full">
        <x-filament::modal width="fit" id="image-view-modal">
            <img :src="imageUrl" alt="Preview" class="max-w-full" style="max-height: 80vh">
            <div class="flex justify-between items-center">
                <p x-text='imageSize' class="text-gray-400"></p>
                <x-filament::icon-button
                    icon="heroicon-m-arrow-down-tray"
                    color="primary"
                    label="Download Image"
                    @click="$wire.call('downloadFile', imageId)"
                />
            </div>
        </x-filament::modal>
        <!-- chat header -->
        <div class="px-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-start h-16 gap-3">
                <x-filament::icon-button
                    icon="heroicon-m-bars-3"
                    @click="isChatsbarOpen = !isChatsbarOpen"
                    class="xl:hidden" 
                    x-show="!isChatsbarOpen"
                />
                @if ($otherParticipant)
                    @php
                        $displayColumn = $otherParticipant->chatable?->displayColumn ?? $chatable[config('chativel.deafult_display_column')];
                    @endphp
                    <x-filament::avatar
                        src="https://ui-avatars.com/api/?name={{ $displayColumn }}"
                        alt="Profile" size="lg" 
                    />
                    <div>
                        <p class="text-sm">{{ ucfirst($displayColumn) }}</p>
                        <p class="text-xs text-gray-400">
                            @if ($lastSeen)
                                @if (Carbon::parse($lastSeen->last_seen)->setTimeZone(config('chativel.timezone', 'app.timezone'))->diffInSeconds(Carbon::now(config('chativel.timezone', 'app.timezone'))) <= 61)
                                    {{ __('Online') }}
                                @else
                                    {{ __('Last seen') }} {{ Carbon::parse($lastSeen->last_seen)->setTimeZone(config('chativel.timezone', 'app.timezone'))?->diffForHumans() }}
                                @endif
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- chat messages -->
        @if ($selectedConversation)
            <div id="messages-container" class="flex flex-col-reverse flex-1 py-3 px-4 overflow-y-auto chativel-scrollbar">

                @foreach ($conversationMessages as $index => $message)
                    
                    @php
                        $currentUser = auth()->user();
                        $nextMessage = $conversationMessages[$index + 1] ?? null;
                        $nextMessageDate = $nextMessage ? Carbon::parse($nextMessage->created_at)->setTimeZone(config('chativel.timezone', 'app.timezone'))->format('Y-m-d') : null;
                        $currentMessageDate = Carbon::parse($message->created_at)->setTimeZone(config('chativel.timezone', 'app.timezone'))->format('Y-m-d');
                        $fromCurrent = $message->sender_type == $currentUser->getMorphClass() && $message->sender_id == $currentUser->getKey();
                        $alignment = $fromCurrent ? 'end':'start';
                        $bgColor = $fromCurrent ? 'bg-primary-600 dark:bg-primary-500':'bg-gray-200 dark:bg-gray-800';
                        $textColor = $fromCurrent ? 'text-primary-300 dark:text-primary-200':'text-gray-500 dark:text-gray-600';
                        $createdAt = Carbon::parse($message->created_at)->setTimeZone(config('chativel.timezone', 'app.timezone'));
                        $attachmentColor = $fromCurrent ? 'background:rgb(50 50 50 / 30%)':'background:rgb(126 126 126 / 30%)';
                    @endphp

                    <div class="flex justify-{{ $alignment}} items-end mb-2 gap-2">
                        <div class="max-w-md p-2 rounded-xl {{ $bgColor }}">
                            @php
                                $images = $message->getMedia('image');
                                $files = $message->getMedia('file');
                            @endphp
                            <div class="flex flex-col gap-1 rounded-xl overflow-hidden" style="{{ count($images) > 0 ? 'width: 290px':'' }}">
                                @if (count($images) % 2 == 1)
                                    @php
                                        $firstImg = $images->shift();
                                    @endphp
                                    <img class="object-cover w-full cursor-pointer" style="aspect-ratio: 4/3" src="{{ $firstImg->getFullUrl() }}"
                                    @click="imageUrl = '{{$firstImg->getFullUrl()}}';imageId = '{{ $firstImg->id }}';$dispatch('open-modal', { id: 'image-view-modal' })">
                                @endif
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($images as $media)
                                        <img class="object-cover cursor-pointer" style="aspect-ratio: 4/3; width: calc(50% - 0.125rem)" src="{{ $media->getFullUrl() }}"
                                        @click="imageUrl = '{{$media->getFullUrl()}}';imageId = '{{ $media->id }}';imageSize = '{{$media->human_readable_size}}';$dispatch('open-modal', { id: 'image-view-modal' })">
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex flex-col gap-1 rounded-xl overflow-hidden" style="{{ count($images) > 0 ? 'width: 290px':'' }}">
                                @foreach ($files as $media)
                                    <div wire:click.prevent='downloadFile({{ $media->id }})' class="group flex items-center gap-2 cursor-pointer py-1">
                                        <div class="flex justify-center items-center p-2 text-white rounded-full" style="width: 2.5rem;height: 2.5rem;{{$attachmentColor}}">
                                            <x-filament::icon icon="heroicon-m-paper-clip" class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <p class="text-sm truncate overflow-hidden whitespace-nowrap {{ $textColor }}" style="width: 235px">{{ $media->file_name }}</p>
                                            <div class="flex gap-2 items-center">
                                                <p class="text-sm {{ $textColor }}">{{ $media->human_readable_size }}</p>
                                                <x-filament::icon icon="heroicon-m-arrow-down-tray" class="w-4 h-4 hidden group-hover:block" />
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if ($message->message)
                                <p class="text-sm">{{ $message->message }}</p>
                            @endif
                            <div class="flex items-center gap-1 {{ $textColor }}" >
                                @if ($fromCurrent)
                                    @if ($message->is_read)
                                        <div class="flex relative">
                                            <x-filament::icon icon="heroicon-m-check" style="width: 0.90rem;height: 0.90rem;" />
                                            <x-filament::icon icon="heroicon-m-check" class="absolute" style="width: 0.90rem;height: 0.90rem;inset-inline-end: 4px;" />
                                        </div>
                                    @else
                                        <div>
                                            <x-filament::icon icon="heroicon-m-check" style="width: 0.90rem;height: 0.90rem;" />
                                        </div>
                                    @endif
                                @endif
                                <p class="mt-1 text-xs {{ $textColor }} text-{{ $alignment }}">{{ $createdAt->format('g:i A') }}</p>
                            </div>
                        </div>
                    </div>

                    @if ($currentMessageDate !== $nextMessageDate)
                        <div class="flex justify-center my-4">
                            <x-filament::badge>
                                {{ $createdAt->format('F j, Y') }}
                            </x-filament::badge>
                        </div>
                    @endif

                @endforeach

                @if ($this->paginator->hasMorePages())
                    <div x-intersect="$wire.loadMoreMessages" class="h-4">
                        <x-filament::loading-indicator class="mx-auto h-5 w-5" />
                    </div>
                @endif
            </div>
        @else
            <div class="flex h-full items-center justify-center">
                <div>
                    <x-filament::badge color="gray">
                        {{ __('select a conversation to start messaging') }}
                    </x-filament::badge>
                </div>
            </div>
        @endif

        <!-- message input -->
        @if ($selectedConversation)
            <div x-data="{ message: @entangle('message'), attachments: @entangle('attachments') }"  
                class="flex gap-4 items-center justify-center py-3 px-4 border-t border-gray-200 dark:border-gray-700">

                <div class="flex items-end h-full">
                    <div class="flex">
                        <x-filament::icon-button
                            icon="heroicon-m-paper-clip"
                            style="margin-top: auto;margin-bottom: 0.01rem;margin-inline-end: 0.1rem"
                            @click="$dispatch('open-modal', { id: 'attachments-modal' })"
                        >
                            <x-slot name="badge">
                                {{ count($attachments) > 0 ? count($attachments):'' }}
                            </x-slot>
                        </x-filament::icon-button>
        
                        <x-filament::modal :close-button="false"  id="attachments-modal">
                            <x-slot name="heading">
                                {{ __('New message') }}
                            </x-slot>
                    
                            <div>
                                {!! $this->form->getComponent('attachments')->render() !!}
                            </div>

                            <div>
                                {!! $this->form->getComponent('message')->render() !!}
                            </div>

                            <div class="flex justify-end">
                                <x-filament::icon-button
                                    icon="heroicon-m-paper-airplane"
                                    wire:click.prevent="sendMessage"
                                    class="rtl:rotate-180"
                                    x-bind:disabled="!message.trim() && attachments.length === 0" 
                                />
                            </div>
                        </x-filament::modal>
                    </div>
                </div>
                
                <div class="w-full max-h-96">
                    {!! $this->form->getComponent('message')->render() !!}                    
                </div>

                <x-filament::icon-button
                    icon="heroicon-m-paper-airplane"
                    style="margin-top: auto;margin-bottom: 0.01rem"
                    wire:click.prevent="sendMessage"
                    class="rtl:rotate-180"
                    x-bind:disabled="!message.trim() && attachments.length === 0" 
                />

            </div>
        @endif
    </div>

    @if ($selectedConversation)
        @script
        <script>
            tt = Intl.DateTimeFormat().resolvedOptions().timeZone;
            console.log(tt);
            
            setInterval(() => {
                Livewire.dispatch('checkStatus');            
            }, 30000);        
        </script>
        @endscript
    @endif
</div>
