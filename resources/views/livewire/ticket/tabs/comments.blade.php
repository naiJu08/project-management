<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Comments</h3>
    
    <div class="space-y-4">
        @forelse($ticket->comments()->with('user')->latest()->get() as $comment)
            <div class="flex gap-4 pb-4 border-b border-gray-200 dark:border-gray-700 last:border-0">
                <img src="{{ $comment->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($comment->user->name) }}" 
                     alt="{{ $comment->user->name }}" class="w-8 h-8 rounded-full flex-shrink-0">
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $comment->user->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="prose dark:prose-invert max-w-none text-sm">
                        {!! nl2br(e($comment->content)) !!}
                    </div>
                </div>
            </div>
        @empty
            <p class="text-gray-500 dark:text-gray-400 text-center py-8">No comments yet</p>
        @endforelse
    </div>

    {{-- Add Comment --}}
    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
        <textarea placeholder="Add a comment..." rows="3" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"></textarea>
        <div class="flex gap-2 mt-3">
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm font-medium">
                Comment
            </button>
            <button class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-lg transition-colors text-sm font-medium">
                Cancel
            </button>
        </div>
    </div>
</div>
