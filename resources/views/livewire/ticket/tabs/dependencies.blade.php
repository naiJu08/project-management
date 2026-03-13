<div class="space-y-6">
    {{-- Blocked By --}}
    @if($blockedByTickets->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-red-200 dark:border-red-900 p-6">
            <h3 class="text-lg font-semibold text-red-800 dark:text-red-200 mb-4">🚫 Blocked By</h3>
            <div class="space-y-2">
                @foreach($blockedByTickets as $ticket)
                    <a href="#" class="block p-3 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 rounded-lg transition-colors">
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ $ticket->code }}: {{ $ticket->name }}</p>
                        <p class="text-xs text-red-700 dark:text-red-300 mt-1">Status: {{ $ticket->status->name }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Blocks --}}
    @if($blocksTickets->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-orange-200 dark:border-orange-900 p-6">
            <h3 class="text-lg font-semibold text-orange-800 dark:text-orange-200 mb-4">⚠️ Blocks</h3>
            <div class="space-y-2">
                @foreach($blocksTickets as $ticket)
                    <a href="#" class="block p-3 bg-orange-50 dark:bg-orange-900/20 hover:bg-orange-100 dark:hover:bg-orange-900/40 rounded-lg transition-colors">
                        <p class="text-sm font-medium text-orange-800 dark:text-orange-200">{{ $ticket->code }}: {{ $ticket->name }}</p>
                        <p class="text-xs text-orange-700 dark:text-orange-300 mt-1">Status: {{ $ticket->status->name }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- All Dependencies --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">🔗 All Dependencies</h3>
            <button wire:click="$set('showDependencyForm', !$showDependencyForm)" class="px-3 py-1 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                + Add
            </button>
        </div>

        @if($showDependencyForm)
            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg mb-4 space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Relationship Type</label>
                    <select wire:model="dependencyType" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                        <option value="blocks">Blocks</option>
                        <option value="blocked_by">Blocked By</option>
                        <option value="related_to">Related To</option>
                        <option value="duplicates">Duplicates</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Ticket</label>
                    <input type="number" wire:model="dependencyTicketId" placeholder="Ticket ID" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                </div>
                <div class="flex gap-2">
                    <button wire:click="addDependency" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm font-medium">
                        Add Dependency
                    </button>
                    <button wire:click="$set('showDependencyForm', false)" class="flex-1 px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-900 dark:text-white rounded-lg transition-colors text-sm font-medium">
                        Cancel
                    </button>
                </div>
            </div>
        @endif

        <div class="space-y-2">
            @forelse($ticket->dependencies as $dep)
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $dep->dependsOnTicket->code }}: {{ $dep->dependsOnTicket->name }}
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $dep->type)) }}</p>
                    </div>
                    <button wire:click="removeDependency({{ $dep->id }})" class="px-3 py-1 text-xs bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 text-red-700 dark:text-red-200 rounded transition-colors">
                        Remove
                    </button>
                </div>
            @empty
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">No dependencies</p>
            @endforelse
        </div>
    </div>

    {{-- Child Tickets --}}
    @if($childTickets->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">👶 Subtasks</h3>
            <div class="space-y-2">
                @foreach($childTickets as $child)
                    <a href="#" class="block p-3 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $child->code }}: {{ $child->name }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Status: {{ $child->status->name }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
