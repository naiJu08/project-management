@props(['selectedCount' => 0, 'totalCount' => 0])

<div x-data="{ showActions: @js($selectedCount > 0) }" x-show="showActions" class="sticky bottom-0 left-0 right-0 z-40 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between gap-4">
            {{-- Selection Info --}}
            <div class="flex items-center gap-4">
                <div class="text-sm font-medium text-gray-900 dark:text-white">
                    <span class="font-bold text-blue-600 dark:text-blue-400">{{ $selectedCount }}</span>
                    <span class="text-gray-600 dark:text-gray-400">of {{ $totalCount }} selected</span>
                </div>

                {{-- Select All Toggle --}}
                <button @click="$dispatch('toggle-select-all')" class="text-xs px-3 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded transition-colors">
                    Select All
                </button>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2">
                {{-- Status Update --}}
                <select @change="$dispatch('bulk-update-status', { status: $event.target.value })" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">Change Status...</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="closed">Closed</option>
                </select>

                {{-- Priority Update --}}
                <select @change="$dispatch('bulk-update-priority', { priority: $event.target.value })" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">Change Priority...</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>

                {{-- Assignee Update --}}
                <select @change="$dispatch('bulk-update-assignee', { assignee: $event.target.value })" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">Assign to...</option>
                    <slot name="assignees" />
                </select>

                {{-- Delete Button --}}
                <button @click="if(confirm('Delete ' + {{ $selectedCount }} + ' items?')) $dispatch('bulk-delete')" class="px-3 py-2 text-sm bg-red-100 dark:bg-red-900/20 hover:bg-red-200 dark:hover:bg-red-900/40 text-red-700 dark:text-red-400 rounded-lg transition-colors">
                    Delete
                </button>

                {{-- Export Button --}}
                <button @click="$dispatch('bulk-export')" class="px-3 py-2 text-sm bg-green-100 dark:bg-green-900/20 hover:bg-green-200 dark:hover:bg-green-900/40 text-green-700 dark:text-green-400 rounded-lg transition-colors">
                    Export
                </button>

                {{-- Close Button --}}
                <button @click="showActions = false; $dispatch('clear-selection')" class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Bulk Actions Checkbox Column --}}
<div x-data="{ allSelected: false }" class="hidden">
    {{-- Template for bulk select checkbox --}}
    <input type="checkbox" class="bulk-select-checkbox w-4 h-4 text-blue-600 rounded border-gray-300 dark:border-gray-600 focus:ring-blue-500" />
</div>
