<x-filament::page>
    <div class="space-y-6">
        @if(!$selectedSection)
            {{-- Section Selection --}}
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    Welcome to AI Assistant
                </h2>
                <p class="text-gray-600 dark:text-gray-400">
                    Choose a section to get started. I'll help you accomplish tasks through natural conversation.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($sections as $key => $section)
                    <button
                        wire:click="selectSection('{{ $key }}')"
                        class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 p-8 text-left border-2 border-transparent hover:border-primary-500 transform hover:-translate-y-1">
                        
                        <div class="flex items-center mb-4">
                            <div class="p-3 bg-primary-100 dark:bg-primary-900 rounded-lg mr-4">
                                <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    @if($key === 'management')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    @elseif($key === 'hr')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                                    @endif
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $section['name'] }}
                            </h3>
                        </div>

                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            {{ $section['description'] }}
                        </p>

                        <div class="space-y-2">
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Capabilities:</p>
                            <ul class="space-y-1">
                                @foreach($section['capabilities'] as $capability)
                                    <li class="flex items-start text-sm text-gray-600 dark:text-gray-400">
                                        <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $capability }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="mt-6 flex items-center text-primary-600 dark:text-primary-400 font-semibold group-hover:translate-x-2 transition-transform">
                            Get Started
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </div>
                    </button>
                @endforeach
            </div>
        @else
            {{-- Chat Interface --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-primary-600 to-primary-700 p-4 flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="p-2 bg-white/20 rounded-lg mr-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">
                                {{ $sections[$selectedSection]['name'] }} Assistant
                            </h3>
                            <p class="text-sm text-white/80">
                                {{ $sections[$selectedSection]['description'] }}
                            </p>
                        </div>
                    </div>
                    <button
                        wire:click="newConversation"
                        class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg transition flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        New Chat
                    </button>
                </div>

                {{-- Messages --}}
                <div class="h-[600px] overflow-y-auto p-6 space-y-4 bg-gray-50 dark:bg-gray-900" id="messages-container">
                    @forelse($messages as $message)
                        <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                            <div class="flex items-start max-w-[80%] {{ $message['role'] === 'user' ? 'flex-row-reverse' : '' }}">
                                {{-- Avatar --}}
                                <div class="flex-shrink-0 {{ $message['role'] === 'user' ? 'ml-3' : 'mr-3' }}">
                                    @if($message['role'] === 'user')
                                        <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center text-white font-bold">
                                            {{ substr(auth()->user()->name, 0, 1) }}
                                        </div>
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                {{-- Message Content --}}
                                <div>
                                    <div class="px-4 py-3 rounded-2xl {{ $message['role'] === 'user' ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow' }}">
                                        <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $message['content'] }}</p>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 {{ $message['role'] === 'user' ? 'text-right' : '' }}">
                                        {{ $message['time'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 dark:text-gray-400 py-12">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <p>Start a conversation by typing a message below</p>
                        </div>
                    @endforelse

                    @if($isProcessing)
                        <div class="flex justify-start">
                            <div class="flex items-start max-w-[80%]">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center mr-3">
                                    <svg class="w-6 h-6 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                </div>
                                <div class="px-4 py-3 rounded-2xl bg-white dark:bg-gray-800 shadow">
                                    <div class="flex space-x-2">
                                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Input --}}
                <div class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4">
                    <form wire:submit.prevent="sendMessage" class="flex items-end space-x-3">
                        <div class="flex-1">
                            <textarea
                                wire:model.defer="userInput"
                                rows="2"
                                placeholder="Type your message here... (e.g., 'Create a new project called Website Redesign')"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 resize-none"
                                @keydown.enter.prevent="if(!$event.shiftKey) { $wire.sendMessage(); }"
                                :disabled="$wire.isProcessing"
                            ></textarea>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Press Enter to send, Shift+Enter for new line
                            </p>
                        </div>
                        <button
                            type="submit"
                            :disabled="$wire.isProcessing || !$wire.userInput"
                            class="px-6 py-3 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-400 text-white rounded-lg font-semibold transition flex items-center">
                            @if($isProcessing)
                                <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            @else
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                Send
                            @endif
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        // Auto-scroll to bottom when new messages arrive
        document.addEventListener('livewire:load', function () {
            Livewire.hook('message.processed', (message, component) => {
                const container = document.getElementById('messages-container');
                if (container) {
                    setTimeout(() => {
                        container.scrollTop = container.scrollHeight;
                    }, 100);
                }
            });
        });
    </script>
    @endpush
</x-filament::page>
