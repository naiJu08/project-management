{{-- Time Tracking Summary --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Estimated</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $ticket->estimated_hours ?? '—' }}h</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Logged</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ round($totalLoggedHours, 2) }}h</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Remaining</p>
        <p class="text-2xl font-bold {{ $remainingHours !== null && $remainingHours < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
            {{ $remainingHours !== null ? round($remainingHours, 2) : '—' }}h
        </p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Progress</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ round($progressPercentage) }}%</p>
    </div>
</div>

{{-- Progress Bar --}}
@if($ticket->estimated_hours)
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Progress</h3>
            <span class="text-sm text-gray-600 dark:text-gray-400">{{ round($progressPercentage) }}%</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
            <div class="bg-blue-600 dark:bg-blue-500 h-3 rounded-full transition-all" style="width: {{ min(100, $progressPercentage) }}%"></div>
        </div>
    </div>
@endif

{{-- Realtime Tracking --}}
<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">⏱️ Realtime Tracking</h3>
        <div class="flex gap-2">
            @if(!$isTracking)
                <button wire:click="startTracking" class="px-3 py-1 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                    ▶ Start
                </button>
            @else
                <button wire:click="stopTracking" class="px-3 py-1 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                    ⏹ Stop
                </button>
            @endif
        </div>
    </div>
    
    @if($isTracking || $trackingSeconds > 0)
        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-center" wire:poll.1000ms="pollTracking">
            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 font-mono" id="tracking-display">
                {{ sprintf('%02d:%02d:%02d', intdiv($trackingSeconds, 3600), intdiv($trackingSeconds % 3600, 60), $trackingSeconds % 60) }}
            </p>
            <p class="text-sm text-blue-700 dark:text-blue-300 mt-2" id="tracking-hours">
                {{ number_format($trackingSeconds / 3600, 2) }} hours
            </p>
        </div>
    @endif
</div>


{{-- Log Time Form --}}
<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">📝 Log Time</h3>
        @if(!$showTimeForm)
            <button wire:click="$set('showTimeForm', true)" class="px-3 py-1 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                + Add Time
            </button>
        @endif
    </div>

    @if($showTimeForm)
        <div class="space-y-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hours</label>
                    <input type="number" wire:model="hoursToLog" step="1" min="0" max="24" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                           placeholder="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Minutes</label>
                    <input type="number" wire:model="minutesToLog" step="1" min="0" max="59" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                           placeholder="0">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description (Optional)</label>
                <textarea wire:model="timeDescription" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                          placeholder="What did you work on?"></textarea>
            </div>
            <div class="flex gap-2">
                <button wire:click="@if($editingTimeId) updateTime @else logTime @endif" 
                        class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm font-medium">
                    @if($editingTimeId) ✓ Update @else ✓ Log Time @endif
                </button>
                <button wire:click="resetTimeForm" 
                        class="flex-1 px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-700 text-gray-900 dark:text-white rounded-lg transition-colors text-sm font-medium">
                    Cancel
                </button>
            </div>
        </div>
    @endif
</div>

{{-- Time Entries --}}
<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📋 Time Entries</h3>
    
    <div class="space-y-3">
        @forelse($ticket->hours()->with('user')->latest()->get() as $hour)
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg group hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                <div class="flex items-center gap-3 flex-1">
                    <img src="{{ $hour->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($hour->user->name) }}" 
                         alt="{{ $hour->user->name }}" class="w-8 h-8 rounded-full">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $hour->user->name }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            {{ $hour->comment ?? 'No description' }} • {{ $hour->created_at->format('M d, Y') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $hour->value }}h</span>
                    @if($hour->user_id === auth()->id())
                        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button wire:click="editTime({{ $hour->id }})" class="p-1 text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button wire:click="deleteTime({{ $hour->id }})" class="p-1 text-red-600 hover:text-red-700 dark:text-red-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-gray-500 dark:text-gray-400 text-center py-8">No time entries yet</p>
        @endforelse
    </div>
</div>
