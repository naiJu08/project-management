<div>
    {{-- AI Assistant Toggle Button --}}
    @if(!$open)
        <button
            wire:click="toggle"
            style="position: fixed !important; bottom: 24px !important; right: 24px !important; z-index: 9999 !important; background: linear-gradient(to right, #9333ea, #db2777) !important;"
            class="text-white rounded-full p-4 shadow-2xl transition-all duration-300 transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-white/60"
            onmouseover="this.style.background='linear-gradient(to right, #7e22ce, #be185d)'"
            onmouseout="this.style.background='linear-gradient(to right, #9333ea, #db2777)'"
            title="Open AI Assistant"
            aria-label="Open AI Assistant">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
        </button>
    @endif

    {{-- AI Assistant Panel --}}
    @if($open)
        {{-- Backdrop overlay (click to close) --}}
        @if(!$fullscreen)
            <div class="fixed inset-0 z-[9998] bg-black/40" wire:click="toggle"></div>
        @endif
        <div class="{{ $fullscreen
            ? 'fixed inset-0 z-[9999] w-screen h-screen rounded-none'
            : 'fixed top-0 right-0 z-[9999] h-screen w-full sm:w-[420px] md:w-[480px] lg:w-[520px] xl:w-[640px] sm:min-w-[360px] md:min-w-[440px] rounded-l-2xl' }} bg-white dark:bg-gray-800 shadow-2xl flex flex-col overflow-hidden border border-gray-200 dark:border-gray-700">
            
            {{-- Header --}}
            <div style="background: linear-gradient(to right, #9333ea, #db2777) !important;" class="p-4 flex justify-between items-center text-white">
                <div class="flex items-center">
                    <div class="p-2 bg-white/20 rounded-lg mr-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">AI Assistant</h3>
                        @if($selectedSection)
                            <p class="text-xs text-white/80">{{ $sections[$selectedSection]['name'] ?? '' }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button
                        wire:click="toggleFullscreen"
                        class="p-2 bg-white/20 hover:bg-white/30 rounded-lg transition"
                        title="{{ $fullscreen ? 'Exit Fullscreen' : 'Fullscreen' }}">
                        @if($fullscreen)
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-label="Exit Fullscreen">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 10V4h6M20 14v6h-6M4 14v6h6M20 10V4h-6" />
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-label="Fullscreen">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 8H4V4h4M20 8h-4V4h4M8 20H4v-4h4M20 20h-4v-4h4" />
                            </svg>
                        @endif
                    </button>
                    @if($selectedSection)
                        <button
                            wire:click="newConversation"
                            class="p-2 bg-white/20 hover:bg-white/30 rounded-lg transition"
                            title="New Conversation">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    @endif
                    <button
                        wire:click="toggle"
                        class="p-2 bg-white/20 hover:bg-white/30 rounded-lg transition"
                        title="Close">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-label="Close">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Content --}}
            <div class="flex-1 overflow-hidden flex flex-col">
                @if(!$selectedSection)
                    {{-- Section Selection --}}
                    <div class="p-4 overflow-y-auto">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Choose a section to get started:</h4>
                        @if($hasActiveConversation)
                            <div class="mb-3 p-3 border border-purple-200 dark:border-purple-800 rounded-lg bg-purple-50 dark:bg-purple-900/30">
                                <div class="flex items-center justify-between">
                                    <div class="text-xs text-purple-900 dark:text-purple-200">
                                        You have an active conversation. Would you like to resume it?
                                    </div>
                                    <div class="flex gap-2">
                                        <button wire:click="resumeLastConversation" class="px-3 py-1 text-xs bg-purple-600 text-white rounded-md hover:bg-purple-700">Resume</button>
                                        <button wire:click="newConversation" class="px-3 py-1 text-xs bg-white dark:bg-gray-800 border border-purple-300 dark:border-purple-700 text-purple-700 dark:text-purple-200 rounded-md hover:bg-purple-50 dark:hover:bg-gray-700">Start new</button>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="space-y-3">
                            @foreach($sections as $key => $section)
                                <button
                                    wire:click="selectSection('{{ $key }}')"
                                    class="w-full text-left p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 hover:from-purple-50 hover:to-pink-50 dark:hover:from-purple-900 dark:hover:to-pink-900 rounded-xl border-2 border-transparent hover:border-purple-300 transition-all duration-200 group">
                                    <div class="flex items-start">
                                        <div class="p-2 bg-purple-100 dark:bg-purple-800 rounded-lg mr-3 group-hover:scale-110 transition-transform">
                                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($key === 'management')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                @elseif($key === 'hr')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                                                @endif
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <h5 class="font-bold text-gray-900 dark:text-white mb-1">{{ $section['name'] }}</h5>
                                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ $section['description'] }}</p>
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @else
                    {{-- Chat Interface --}}
                    <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50 dark:bg-gray-900" id="messages-container-{{ $conversationId }}">
                        @forelse($messages as $message)
                            <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                                <div class="flex items-start max-w-[85%] {{ $message['role'] === 'user' ? 'flex-row-reverse' : '' }}">
                                    {{-- Avatar --}}
                                    <div class="flex-shrink-0 {{ $message['role'] === 'user' ? 'ml-2' : 'mr-2' }}">
                                        @if($message['role'] === 'user')
                                            <div class="w-8 h-8 rounded-full bg-purple-600 flex items-center justify-center text-white text-sm font-bold">
                                                {{ substr(auth()->user()->name, 0, 1) }}
                                            </div>
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Message --}}
                                    <div>
                                        <div class="px-3 py-2 rounded-2xl text-sm {{ $message['role'] === 'user' ? 'bg-purple-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow' }}">
                                            <p class="leading-relaxed whitespace-pre-wrap">{{ $message['content'] }}</p>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 {{ $message['role'] === 'user' ? 'text-right' : '' }}">
                                            {{ $message['time'] }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <p class="text-sm">Start chatting below</p>
                            </div>
                        @endforelse

                        @if($isProcessing)
                            <div class="flex justify-start">
                                <div class="flex items-start max-w-[85%]">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center mr-2">
                                        <svg class="w-5 h-5 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                        </svg>
                                    </div>
                                    <div class="px-3 py-2 rounded-2xl bg-white dark:bg-gray-800 shadow">
                                        <div class="flex space-x-1">
                                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Quick Actions --}}
                    @if($showQuickActions && !empty($quickActions))
                        <div class="px-4 py-2 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">Quick actions:</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($quickActions as $action)
                                    <button
                                        wire:click="executeQuickAction('{{ $action }}')"
                                        class="px-3 py-1 text-xs bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 rounded-full hover:bg-purple-200 dark:hover:bg-purple-800 transition">
                                        {{ $action }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Input/Footer --}}
                    <div class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-3">
                        <form wire:submit.prevent="send" class="flex items-stretch gap-2">
                            <button type="button" wire:click="toggle" class="px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm" aria-label="Close chat">
                                Close
                            </button>
                            <div class="flex-1">
                                <textarea
                                    wire:model.defer="input"
                                    rows="2"
                                    placeholder="Type your message... (Shift+Enter for new line)"
                                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-purple-500 focus:ring-purple-500 resize-none"
                                    @keydown.enter.prevent="if(!$event.shiftKey) { $wire.send(); }"
                                ></textarea>
                            </div>
                            <button type="submit" wire:loading.attr="disabled" @disabled($isProcessing || empty($input)) @class(['px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-2', 'bg-purple-600 hover:bg-purple-700 text-white' => true, 'opacity-60 cursor-not-allowed' => $isProcessing || empty($input)]) aria-label="Send message">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                <span>Send</span>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('livewire:load', function () {
            Livewire.hook('message.processed', (message, component) => {
                const container = document.querySelector('[id^="messages-container-"]');
                if (container) {
                    setTimeout(() => {
                        container.scrollTop = container.scrollHeight;
                    }, 100);
                }
            });
        });
    </script>
    @endpush
</div>
