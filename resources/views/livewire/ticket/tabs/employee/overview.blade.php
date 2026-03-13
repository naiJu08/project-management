{{-- Description Section --}}
<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📝 Description</h3>
    <div class="prose dark:prose-invert max-w-none">
        @if($ticket->content)
            {!! nl2br(e($ticket->content)) !!}
        @else
            <p class="text-gray-500 dark:text-gray-400">No description provided</p>
        @endif
    </div>
</div>

{{-- Ticket Details Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Type & Component --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🏷️ Type & Component</h3>
        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Type</label>
                <p class="text-gray-900 dark:text-white mt-1">{{ $ticket->type->name ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Component</label>
                <p class="text-gray-900 dark:text-white mt-1">{{ $ticket->component ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    {{-- Assignment --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">👥 Assignment</h3>
        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Owner</label>
                @if($ticket->owner)
                    <div class="flex items-center gap-2 mt-1">
                        <img src="{{ $ticket->owner->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($ticket->owner->name) }}" 
                             alt="{{ $ticket->owner->name }}" class="w-6 h-6 rounded-full">
                        <p class="text-gray-900 dark:text-white">{{ $ticket->owner->name }}</p>
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Unassigned</p>
                @endif
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Assigned To</label>
                @if($ticket->responsible)
                    <div class="flex items-center gap-2 mt-1">
                        <img src="{{ $ticket->responsible->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($ticket->responsible->name) }}" 
                             alt="{{ $ticket->responsible->name }}" class="w-6 h-6 rounded-full">
                        <p class="text-gray-900 dark:text-white">{{ $ticket->responsible->name }}</p>
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Unassigned</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Sprint & Epic --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🎯 Sprint & Epic</h3>
        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Sprint</label>
                <p class="text-gray-900 dark:text-white mt-1">
                    @if($ticket->sprint)
                        <a href="#" class="text-blue-600 hover:text-blue-700 dark:text-blue-400">{{ $ticket->sprint->name }}</a>
                    @else
                        <span class="text-gray-500">Not assigned</span>
                    @endif
                </p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Epic</label>
                <p class="text-gray-900 dark:text-white mt-1">
                    @if($ticket->epic)
                        <a href="#" class="text-blue-600 hover:text-blue-700 dark:text-blue-400">{{ $ticket->epic->name }}</a>
                    @else
                        <span class="text-gray-500">Not assigned</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Severity & Risk --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">⚠️ Severity & Risk</h3>
        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Severity</label>
                <p class="text-gray-900 dark:text-white mt-1">
                    @if($ticket->severity)
                        <span class="px-2 py-1 text-xs font-semibold rounded"
                              :class="@switch($ticket->severity)
                                  @case('critical') bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 @break
                                  @case('major') bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 @break
                                  @case('minor') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 @break
                                  @default bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200
                              @endswitch">
                            {{ ucfirst($ticket->severity) }}
                        </span>
                    @else
                        <span class="text-gray-500">Not set</span>
                    @endif
                </p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Risk Level</label>
                <p class="text-gray-900 dark:text-white mt-1">
                    @if($ticket->risk_level)
                        <span class="px-2 py-1 text-xs font-semibold rounded"
                              :class="@switch($ticket->risk_level)
                                  @case('critical') bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 @break
                                  @case('high') bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 @break
                                  @case('medium') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 @break
                                  @default bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                              @endswitch">
                            {{ ucfirst($ticket->risk_level) }}
                        </span>
                    @else
                        <span class="text-gray-500">Not assessed</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Status Update Form --}}
@if($showStatusForm && ($ticket->responsible_id === auth()->id() || $ticket->owner_id === auth()->id()))
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📊 Update Status</h3>
        <div class="space-y-4">
            @if($statusError)
                <div class="p-3 bg-red-100 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-sm text-red-800 dark:text-red-200">{{ $statusError }}</p>
                </div>
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">New Status</label>
                <select wire:model="newStatus" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    <option value="">Select a status...</option>
                    @foreach($availableStatuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button wire:click="updateStatus" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm font-medium">
                    ✓ Update
                </button>
                <button wire:click="$set('showStatusForm', false)" class="flex-1 px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-700 text-gray-900 dark:text-white rounded-lg transition-colors text-sm font-medium">
                    Cancel
                </button>
            </div>
        </div>
    </div>
@endif
