<div class="space-y-6">
    {{-- Time Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
            <p class="text-2xl font-bold" :class="$remainingHours !== null && $remainingHours < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                {{ $remainingHours !== null ? round($remainingHours, 2) : '—' }}h
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Progress</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ round($progressPercentage) }}%</p>
        </div>
    </div>

    {{-- Time Entries --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Time Entries</h3>
        
        <div class="space-y-3">
            @forelse($ticket->hours()->with('user', 'activity')->latest()->get() as $hour)
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex items-center gap-3">
                        <img src="{{ $hour->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($hour->user->name) }}" 
                             alt="{{ $hour->user->name }}" class="w-8 h-8 rounded-full">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $hour->user->name }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ $hour->activity->name ?? 'N/A' }} • {{ $hour->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $hour->value }}h</span>
                </div>
            @empty
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">No time entries yet</p>
            @endforelse
        </div>
    </div>
</div>
