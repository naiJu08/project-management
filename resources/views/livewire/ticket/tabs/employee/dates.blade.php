{{-- Dates & Deadlines --}}
<div class="space-y-6">
    {{-- Action Buttons --}}
    <div class="flex gap-2">
        <button wire:click="editDates" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm font-medium">
            ✏️ Edit Dates
        </button>
        <button wire:click="openMasterEdit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors text-sm font-medium">
            ⚙️ Master Edit
        </button>
    </div>

    {{-- Date Edit Form --}}
    @if($showDateEdit)
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-200 mb-4">Edit Dates</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                    <input type="date" wire:model="editStartDate" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                    <input type="date" wire:model="editDueDate" min="{{ now()->format('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                </div>
                <div class="flex gap-2">
                    <button wire:click="saveDates" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm font-medium">
                        ✓ Save
                    </button>
                    <button wire:click="$set('showDateEdit', false)" class="flex-1 px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-700 text-gray-900 dark:text-white rounded-lg transition-colors text-sm font-medium">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Master Edit Form --}}
    @if($showMasterEdit)
        <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-purple-900 dark:text-purple-200 mb-4">Master Edit</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                    <input type="text" wire:model="masterEditData.name" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden flex flex-col" style="max-height: 400px;">
                        <div wire:ignore class="trix-wrapper flex-1 overflow-y-auto" style="min-height: 200px;">
                            <trix-editor input="master-content" class="trix-content"></trix-editor>
                            <input id="master-content" type="hidden" wire:model.defer="masterEditData.content">
                        </div>
                        <div class="border-t border-gray-300 dark:border-gray-600 px-3 py-2 bg-gray-50 dark:bg-gray-700 flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                            <span>Scroll to expand • Drag corner to resize</span>
                            <div class="flex gap-1">
                                <button type="button" onclick="document.querySelector('.trix-wrapper').style.minHeight = (parseInt(document.querySelector('.trix-wrapper').style.minHeight) + 50) + 'px'" class="px-2 py-1 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded text-xs">+</button>
                                <button type="button" onclick="if(parseInt(document.querySelector('.trix-wrapper').style.minHeight) > 100) document.querySelector('.trix-wrapper').style.minHeight = (parseInt(document.querySelector('.trix-wrapper').style.minHeight) - 50) + 'px'" class="px-2 py-1 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded text-xs">−</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <script>
                    document.addEventListener('livewire:load', function () {
                        function initMasterTrixEditor() {
                            const trixEditor = document.querySelector('trix-editor');
                            const hiddenInput = document.getElementById('master-content');
                            
                            if (trixEditor && hiddenInput) {
                                if (@this.masterEditData?.content) {
                                    trixEditor.editor.loadHTML(@this.masterEditData.content);
                                }
                                
                                trixEditor.addEventListener('trix-change', function() {
                                    @this.set('masterEditData.content', trixEditor.editor.getDocument().toString());
                                });
                            }
                        }
                        
                        @this.on('masterEditOpened', () => {
                            setTimeout(initMasterTrixEditor, 100);
                        });
                    });
                </script>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                        <input type="date" wire:model="masterEditData.start_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                        <input type="date" wire:model="masterEditData.due_date" min="{{ now()->format('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estimated Hours</label>
                        <input type="number" wire:model="masterEditData.estimated_hours" step="0.5" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                        @error('masterEditData.estimated_hours')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select wire:model="masterEditData.status_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            @foreach($availableStatuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button wire:click="saveMasterEdit" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm font-medium">
                        ✓ Save All
                    </button>
                    <button wire:click="$set('showMasterEdit', false)" class="flex-1 px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-700 text-gray-900 dark:text-white rounded-lg transition-colors text-sm font-medium">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Timeline Overview --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">📅 Timeline Overview</h3>
        
        <div class="space-y-6">
            {{-- Start Date --}}
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-blue-100 dark:bg-blue-900">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Start Date</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                        @if($ticket->start_date)
                            {{ $ticket->start_date->format('M d, Y') }}
                            <span class="text-xs text-gray-500">({{ $ticket->start_date->isToday() ? 'Today' : $ticket->start_date->diffForHumans() }})</span>
                        @else
                            <span class="text-gray-500">Not set</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Due Date --}}
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    @php
                        $isOverdue = $ticket->due_date && now()->isAfter($ticket->due_date);
                    @endphp
                    <div class="flex items-center justify-center h-12 w-12 rounded-lg"
                         :class="{{ $isOverdue ? "'bg-red-100 dark:bg-red-900'" : "'bg-green-100 dark:bg-green-900'" }}">
                        <svg class="h-6 w-6"
                             :class="{{ $isOverdue ? "'text-red-600 dark:text-red-400'" : "'text-green-600 dark:text-green-400'" }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Due Date</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                        @if($ticket->due_date)
                            {{ $ticket->due_date->format('M d, Y') }}
                            <span class="text-xs"
                                  :class="{{ $isOverdue ? "'text-red-600 dark:text-red-400'" : "'text-gray-500'" }}">
                                @if($isOverdue)
                                    (Overdue by {{ now()->diffInDays($ticket->due_date) }} days)
                                @else
                                    ({{ $ticket->due_date->diffForHumans(['parts' => 2]) }})
                                @endif
                            </span>
                        @else
                            <span class="text-gray-500">Not set</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Time Tracking --}}
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-purple-100 dark:bg-purple-900">
                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Time Estimate</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                        @if($ticket->estimated_hours)
                            {{ $ticket->estimated_hours }} hours
                        @else
                            <span class="text-gray-500">Not estimated</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- SLA Status --}}
            @if($ticket->sla_due_at)
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-yellow-100 dark:bg-yellow-900">
                            <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">SLA Due</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                            {{ $ticket->sla_due_at->format('M d, Y H:i') }}
                            <span class="text-xs"
                                  :class="@switch($ticket->sla_status)
                                      @case('on_track') text-green-600 dark:text-green-400 @break
                                      @case('at_risk') text-yellow-600 dark:text-yellow-400 @break
                                      @case('breached') text-red-600 dark:text-red-400 @break
                                      @default text-gray-500
                                  @endswitch">
                                ({{ ucfirst($ticket->sla_status ?? 'unknown') }})
                            </span>
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Resolved & First Response --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @if($ticket->first_response_at)
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">💬 First Response</h3>
                <p class="text-gray-900 dark:text-white">{{ $ticket->first_response_at->format('M d, Y H:i') }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $ticket->first_response_at->diffForHumans() }}</p>
            </div>
        @endif

        @if($ticket->resolved_at)
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">✓ Resolved</h3>
                <p class="text-gray-900 dark:text-white">{{ $ticket->resolved_at->format('M d, Y H:i') }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $ticket->resolved_at->diffForHumans() }}</p>
            </div>
        @endif
    </div>

    {{-- Blocked Status --}}
    @if($ticket->is_blocked)
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
            <div class="flex items-start gap-3">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2M6.343 3.665c.886-.887 2.318-.887 3.203 0l9.759 9.759c.886.886.886 2.318 0 3.203l-9.759 9.759c-.886.886-2.317.886-3.203 0L3.14 16.168c-.886-.886-.886-2.317 0-3.203L6.343 3.665z"></path>
                </svg>
                <div class="flex-1">
                    <h4 class="text-sm font-semibold text-red-800 dark:text-red-200">🚫 Ticket is Blocked</h4>
                    <p class="text-sm text-red-700 dark:text-red-300 mt-1">{{ $ticket->blocked_reason ?? 'No reason provided' }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Reopened Count --}}
    @if($ticket->reopened_count > 0)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                <span class="font-semibold">⚠️ Reopened {{ $ticket->reopened_count }} time{{ $ticket->reopened_count > 1 ? 's' : '' }}</span>
            </p>
        </div>
    @endif
</div>
