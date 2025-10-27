{{-- Relationships Management --}}
<div class="space-y-6">
    {{-- Add Relationship Form --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">🔗 Manage Relationships</h3>
            @if(!$showRelationForm)
                <button wire:click="$set('showRelationForm', true)" class="px-3 py-1 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    + Add Relationship
                </button>
            @endif
        </div>

        @if($showRelationForm)
            <div class="space-y-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Relationship Type</label>
                    <select wire:model="relationType" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                        <option value="related_to">Related To</option>
                        <option value="duplicates">Duplicates</option>
                        <option value="blocks">Blocks</option>
                        <option value="blocked_by">Blocked By</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search or Select Ticket</label>
                    <input type="text" wire:model.live="searchTicket" placeholder="Search by code or name (leave empty to see all)..." 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    
                    @if($ticketSearchResults->count() > 0)
                        <div class="mt-2 max-h-64 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800">
                            @foreach($ticketSearchResults as $result)
                                <button wire:click="selectTicketForRelation({{ $result->id }})" class="w-full text-left px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 border-b border-gray-200 dark:border-gray-700 last:border-0 transition-colors">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $result->code }}: {{ $result->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $result->status->name }}</p>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">No tickets found</p>
                    @endif
                </div>
                
                @if($relationTicketId)
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <p class="text-sm text-blue-800 dark:text-blue-200">Selected: <span class="font-semibold">{{ \App\Models\Ticket::find($relationTicketId)?->code }}</span></p>
                    </div>
                @endif
                
                <div class="flex gap-2">
                    <button wire:click="addRelation" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm font-medium {{ !$relationTicketId ? 'opacity-50 cursor-not-allowed' : '' }}" {{ !$relationTicketId ? 'disabled' : '' }}>
                        ✓ Add
                    </button>
                    <button wire:click="$set('showRelationForm', false)" class="flex-1 px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-700 text-gray-900 dark:text-white rounded-lg transition-colors text-sm font-medium">
                        Cancel
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- Related Tickets --}}
    @if($ticket->relations->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📌 Related Tickets</h3>
            
            <div class="space-y-2">
                @foreach($ticket->relations as $relation)
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg group hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                    {{ $relation->relation->code }}
                                </span>
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase">
                                    {{ str_replace('_', ' ', $relation->type) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-900 dark:text-white mt-1">{{ $relation->relation->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                <span class="px-2 py-1 rounded text-xs font-semibold"
                                      :class="@switch($relation->relation->status->name)
                                          @case('Open') bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 @break
                                          @case('In Progress') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 @break
                                          @case('Done') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 @break
                                          @default bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200
                                      @endswitch">
                                    {{ $relation->relation->status->name }}
                                </span>
                            </p>
                        </div>
                        <button wire:click="removeRelation({{ $relation->id }})" class="p-2 text-red-600 hover:text-red-700 dark:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 p-6 text-center">
            <p class="text-gray-500 dark:text-gray-400">No relationships yet</p>
        </div>
    @endif

    {{-- Blocked By Section --}}
    @php
        $blockedByRelations = $ticket->relations()->where('type', 'blocked_by')->with('relation')->get();
    @endphp
    @if($blockedByRelations->count() > 0)
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-red-800 dark:text-red-200 mb-4">🔴 Blocked By</h3>
            <div class="space-y-2">
                @foreach($blockedByRelations as $relation)
                    <a href="#" class="block p-3 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 rounded-lg transition-colors">
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ $relation->relation->code }}: {{ $relation->relation->name }}</p>
                        <p class="text-xs text-red-700 dark:text-red-300 mt-1">Status: {{ $relation->relation->status->name }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Blocks Section --}}
    @php
        $blocksRelations = $ticket->relations()->where('type', 'blocks')->with('relation')->get();
    @endphp
    @if($blocksRelations->count() > 0)
        <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-orange-800 dark:text-orange-200 mb-4">🟠 Blocks</h3>
            <div class="space-y-2">
                @foreach($blocksRelations as $relation)
                    <a href="#" class="block p-3 bg-orange-100 dark:bg-orange-900/30 hover:bg-orange-200 dark:hover:bg-orange-900/50 rounded-lg transition-colors">
                        <p class="text-sm font-medium text-orange-800 dark:text-orange-200">{{ $relation->relation->code }}: {{ $relation->relation->name }}</p>
                        <p class="text-xs text-orange-700 dark:text-orange-300 mt-1">Status: {{ $relation->relation->status->name }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Duplicates Section --}}
    @php
        $duplicateRelations = $ticket->relations()->where('type', 'duplicates')->with('relation')->get();
    @endphp
    @if($duplicateRelations->count() > 0)
        <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-purple-800 dark:text-purple-200 mb-4">🟣 Duplicates</h3>
            <div class="space-y-2">
                @foreach($duplicateRelations as $relation)
                    <a href="#" class="block p-3 bg-purple-100 dark:bg-purple-900/30 hover:bg-purple-200 dark:hover:bg-purple-900/50 rounded-lg transition-colors">
                        <p class="text-sm font-medium text-purple-800 dark:text-purple-200">{{ $relation->relation->code }}: {{ $relation->relation->name }}</p>
                        <p class="text-xs text-purple-700 dark:text-purple-300 mt-1">Status: {{ $relation->relation->status->name }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
