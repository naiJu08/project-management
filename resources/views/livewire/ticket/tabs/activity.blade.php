<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Activity Timeline</h3>
    
    <div class="space-y-4">
        @forelse($ticket->activities()->with('user')->latest()->get() as $activity)
            <div class="flex gap-4 pb-4 border-b border-gray-200 dark:border-gray-700 last:border-0">
                <div class="flex-shrink-0">
                    <img src="{{ $activity->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($activity->user->name) }}" 
                         alt="{{ $activity->user->name }}" class="w-8 h-8 rounded-full">
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $activity->user->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $activity->created_at->diffForHumans() }}</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">
                        Changed status from <strong>{{ $activity->oldStatus->name ?? 'N/A' }}</strong> to <strong>{{ $activity->newStatus->name ?? 'N/A' }}</strong>
                    </p>
                </div>
            </div>
        @empty
            <p class="text-gray-500 dark:text-gray-400 text-center py-8">No activity yet</p>
        @endforelse
    </div>
</div>
