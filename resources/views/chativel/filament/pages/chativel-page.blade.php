<x-filament-panels::page>
    <div x-data="{ isChatsbarOpen: true }" class="flex CHATIVEL"
    x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('chativel-styles', package: 'ehsan-nosair/chativel'))]">
        
        <div class="relative w-full overflow-hidden">
            <div class="absolute z-30 w-full h-full rounded-xl bg-black opacity-35 xl:hidden" 
                @click="isChatsbarOpen = false"
                x-show="isChatsbarOpen"
            ></div>
            <div class="overflow-hidden flex bg-white shadow-sm rounded-xl ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10" style="height: calc(100vh - 8rem)">
                <div class="absolute xl:relative inset-y-0 ltr:left-0 rtl:right-0 w-80 xl:w-1/3 max-w-full z-30  transform transition-transform duration-300 bg-white dark:bg-gray-900 rounded-xl ltr:rounded-l-xl xl:ltr:rounded-r-none xl:rtl:rounded-l-none ltr:border-r rtl:border-l  border-gray-200 dark:border-gray-700"
                    x-show="isChatsbarOpen"
                    x-transition:enter="transform transition duration-300"
                    x-transition:enter-start="rtl:translate-x-full ltr:-translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition duration-300"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="rtl:translate-x-full ltr:-translate-x-full"
                    >
                    <livewire:chativel-conversations-list :selectedConversation="$selectedConversation" lazy />
                </div>
                <div class="w-full xl:w-2/3 h-full" :class="!isChatsbarOpen ? 'xl:w-full' : ''" >
                    <livewire:chativel-conversation-box :selectedConversation="$selectedConversation" :otherParticipant="$otherParticipant" />
                </div>
            </div>
        </div>
    
        <livewire:chativel-new-conversation>
    </div>
    
    @script
    <script>
        setInterval(() => {
            Livewire.dispatch('boradcastStatus');            
        }, 60000);        
    </script>
    @endscript
</x-filament-panels::page>