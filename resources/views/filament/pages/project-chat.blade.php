<x-filament::page>
    <div class="space-y-4">
        {{-- Project Selector --}}
        <div class="bg-white rounded-lg shadow p-4 ai-card">
            {{ $this->form }}
        </div>

        @if($selectedProjectId)
            {{-- Chat Container --}}
            <div class="bg-white rounded-lg shadow ai-card" style="height: calc(100vh - 300px); display: flex; flex-direction: column;">
                
                {{-- Messages Area --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messages-container" wire:poll.5s="refreshMessages">
                    @forelse($messages as $msg)
                        <div class="flex {{ $msg['is_own'] ? 'justify-end' : 'justify-start' }}">
                            <div class="flex {{ $msg['is_own'] ? 'flex-row-reverse' : 'flex-row' }} items-start space-x-2 max-w-2xl">
                                {{-- Avatar --}}
                                <div class="flex-shrink-0">
                                    <img src="{{ $msg['user_avatar'] }}" alt="{{ $msg['user_name'] }}" 
                                         class="w-10 h-10 rounded-full">
                                </div>

                                {{-- Message Bubble --}}
                                <div class="{{ $msg['is_own'] ? 'mr-2' : 'ml-2' }}">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <span class="text-sm font-semibold text-gray-700">{{ $msg['user_name'] }}</span>
                                        <span class="text-xs text-gray-500" title="{{ $msg['created_at_full'] }}">
                                            {{ $msg['created_at'] }}
                                        </span>
                                        @if($msg['is_edited'])
                                            <span class="text-xs text-gray-400 italic">(edited)</span>
                                        @endif
                                    </div>

                                    {{-- Reply Reference --}}
                                    @if($msg['reply_to'])
                                        <div class="bg-gray-100 border-l-4 border-gray-400 p-2 mb-2 rounded text-sm">
                                            <div class="font-semibold text-gray-600">{{ $msg['reply_to']['user_name'] }}</div>
                                            <div class="text-gray-500 truncate">{{ Str::limit($msg['reply_to']['message'], 60) }}</div>
                                        </div>
                                    @endif

                                    {{-- Message Content --}}
                                    <div class="rounded-lg px-4 py-2 {{ $msg['is_own'] ? 'bg-primary-500 text-white' : 'bg-gray-200 text-gray-800' }}">
                                        <p class="text-sm whitespace-pre-wrap break-words">{{ $msg['message'] }}</p>
                                    </div>

                                    {{-- Actions --}}
                                    @if($msg['is_own'])
                                        <div class="flex space-x-2 mt-1">
                                            <button 
                                                onclick="if(confirm('Are you sure you want to delete this message?')) { 
                                                    @this.call('deleteMessage', {{ $msg['id'] }}) 
                                                }"
                                                class="text-xs text-red-600 hover:text-red-800"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    @else
                                        <div class="mt-1">
                                            <button wire:click="setReplyTo({{ $msg['id'] }})" 
                                                    class="text-xs text-primary-600 hover:text-primary-800">
                                                Reply
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex items-center justify-center h-full">
                            <div class="text-center text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <p class="mt-2 text-sm">No messages yet. Start the conversation!</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Reply Preview --}}
                @if($replyToId)
                    @php
                        $replyMsg = collect($messages)->firstWhere('id', $replyToId);
                    @endphp
                    @if($replyMsg)
                        <div class="border-t bg-gray-50 px-4 py-2 flex items-center justify-between ai-surface rounded-b-lg">
                            <div class="flex-1">
                                <div class="text-xs text-gray-500">Replying to {{ $replyMsg['user_name'] }}</div>
                                <div class="text-sm text-gray-700 truncate">{{ Str::limit($replyMsg['message'], 80) }}</div>
                            </div>
                            <button wire:click="cancelReply" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif
                @endif

                {{-- Message Input --}}
                <div class="border-t p-4 ai-surface rounded-b-lg">
                    <form wire:submit.prevent="sendMessage" class="flex space-x-2">
                        <textarea 
                            wire:model.defer="message" 
                            rows="2"
                            class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                            placeholder="Type your message..."
                            @keydown.enter.prevent="if(!event.shiftKey) { $wire.sendMessage(); }"
                        ></textarea>
                        <button 
                            type="submit"
                            class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 self-end"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </form>
                    <p class="text-xs text-gray-500 mt-2">Press Enter to send, Shift+Enter for new line</p>
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-8 text-center ai-card">
                <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <p class="mt-4 text-gray-500">Select a project to start chatting</p>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        // Auto-scroll to bottom when new messages arrive
        window.addEventListener('message-sent', () => {
            const container = document.getElementById('messages-container');
            if (container) {
                setTimeout(() => {
                    container.scrollTop = container.scrollHeight;
                }, 100);
            }
        });

        // Scroll to bottom on page load
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });

        // Scroll to bottom after Livewire updates
        Livewire.hook('message.processed', () => {
            const container = document.getElementById('messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });
    </script>
    @endpush
</x-filament::page>
