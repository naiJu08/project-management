<div class="fixed right-0 top-0 md:top-0 bottom-0 z-40 w-full sm:w-96 transform transition-transform duration-200"
     style="pointer-events: none;">
    <div class="h-full ml-auto shadow-xl border bg-white rounded-l-xl flex flex-col"
         style="width: 100%; max-width: 24rem; pointer-events: auto; {{ $open ? '' : 'transform: translateX(100%);' }}">
        <div class="px-3 py-2 border-b flex items-center justify-between bg-white rounded-tl-xl">
            <div class="font-semibold text-gray-700">AI Assistant</div>
            <div class="flex items-center gap-2 text-sm">
                <label class="flex items-center gap-1">
                    <input type="checkbox" wire:model="autoGenerateTasks" class="rounded border-gray-300">
                    <span class="text-gray-600">Auto-generate tasks</span>
                </label>
                <button wire:click="toggle" type="button" class="p-1 rounded hover:bg-gray-100" title="Close">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="px-3 py-2 border-b bg-gray-50">
            <label class="text-xs text-gray-500">Additional AI context (optional)</label>
            <textarea wire:model.defer="aiContext" rows="2" class="w-full mt-1 rounded border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Deadlines, team size, tech stack, etc."></textarea>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-3 bg-white" id="ai-assistant-messages">
            @foreach($messages as $msg)
                <div class="{{ $msg['role'] === 'user' ? 'text-right' : 'text-left' }}">
                    <div class="inline-block max-w-[90%] text-sm rounded-lg px-3 py-2 {{ $msg['role'] === 'user' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-800' }}">
                        {{ $msg['text'] }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="border-t p-3 bg-white rounded-bl-xl">
            <form wire:submit.prevent="send" class="flex gap-2">
                <textarea wire:model.defer="input" rows="2" class="flex-1 rounded border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Describe the project you want to create..." @keydown.enter.prevent="if(!event.shiftKey){ $wire.send(); }"></textarea>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700">Send</button>
            </form>
            <p class="text-xs text-gray-500 mt-1">Enter to send, Shift+Enter for newline</p>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', () => {
            const box = document.getElementById('ai-assistant-messages');
            const scroll = () => { if (box) box.scrollTop = box.scrollHeight; };
            scroll();
            Livewire.hook('message.processed', scroll);
        });
    </script>
</div>
