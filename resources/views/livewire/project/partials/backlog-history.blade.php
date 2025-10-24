{{-- Backlog Item History Timeline --}}
<div class="space-y-4">
    @forelse($this->history as $entry)
        <div class="flex space-x-3">
            {{-- Timeline Line --}}
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full {{ $entry->action === 'created' ? 'bg-green-500' : ($entry->action === 'deleted' ? 'bg-red-500' : 'bg-blue-500') }} flex items-center justify-center text-white flex-shrink-0">
                    @if($entry->action === 'created')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    @elseif($entry->action === 'updated')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    @elseif($entry->action === 'moved')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    @elseif($entry->action === 'deleted')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @endif
                </div>
                
                @if(!$loop->last)
                    <div class="w-0.5 flex-1 bg-gray-200 dark:bg-gray-700 mt-2"></div>
                @endif
            </div>
            
            {{-- History Entry Content --}}
            <div class="flex-1 pb-8">
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ $entry->user ? $entry->user->name : 'System' }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ ucfirst($entry->action) }}
                            </span>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $entry->created_at->format('M d, Y H:i') }}
                        </span>
                    </div>
                    
                    {{-- Changes --}}
                    @if($entry->field_name && $entry->old_value !== null)
                        <div class="text-sm space-y-1">
                            <div class="text-gray-700 dark:text-gray-300">
                                Changed <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $entry->field_name)) }}</span>
                            </div>
                            
                            <div class="flex items-center space-x-2 text-xs">
                                <div class="px-2 py-1 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded">
                                    <span class="line-through">{{ $entry->old_value ?: 'empty' }}</span>
                                </div>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                                <div class="px-2 py-1 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded">
                                    {{ $entry->new_value ?: 'empty' }}
                                </div>
                            </div>
                        </div>
                    @elseif($entry->action === 'created')
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Created this {{ strtolower($entry->backlogItem->type) }}
                        </div>
                    @elseif($entry->action === 'moved')
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Moved item in hierarchy
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No history yet</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Changes to this item will appear here.</p>
        </div>
    @endforelse
</div>
