{{-- Backlog Item Comments Section --}}
<div class="space-y-6">
    {{-- Add Comment Form --}}
    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
        <form wire:submit.prevent="addComment">
            @if($replyToCommentId)
                <div class="mb-2 flex items-center justify-between bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded">
                    <span class="text-sm text-blue-800 dark:text-blue-200">Replying to comment...</span>
                    <button type="button" wire:click="cancelReply" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            @endif
            
            <textarea wire:model="newComment" 
                      rows="3" 
                      placeholder="Add a comment..." 
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
            
            @error('newComment') 
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
            @enderror
            
            <div class="mt-2 flex justify-end">
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                        {{ !$newComment ? 'disabled' : '' }}>
                    {{ $replyToCommentId ? 'Reply' : 'Comment' }}
                </button>
            </div>
        </form>
    </div>

    {{-- Comments List --}}
    <div class="space-y-4">
        @forelse($this->comments as $comment)
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                {{-- Comment Header --}}
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-3">
                        {{-- User Avatar --}}
                        <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-medium flex-shrink-0">
                            {{ substr($comment->user->name, 0, 1) }}
                        </div>
                        
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $comment->user->name }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $comment->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Actions --}}
                    <div class="flex items-center space-x-2">
                        <button wire:click="replyToComment({{ $comment->id }})" 
                                class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                            Reply
                        </button>
                        
                        @if($comment->user_id === auth()->id() || auth()->user()->can('Delete backlog item'))
                            <button onclick="if(confirm('Are you sure you want to delete this comment?')) { @this.call('deleteComment', {{ $comment->id }}) }"
                                    class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200">
                                Delete
                            </button>
                        @endif
                    </div>
                </div>
                
                {{-- Comment Content --}}
                <div class="text-gray-900 dark:text-white prose dark:prose-invert max-w-none ml-11">
                    {!! nl2br(e($comment->content)) !!}
                </div>
                
                {{-- Replies --}}
                @if($comment->replies->count() > 0)
                    <div class="mt-4 ml-11 space-y-3 border-l-2 border-gray-200 dark:border-gray-700 pl-4">
                        @foreach($comment->replies as $reply)
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center text-white text-xs font-medium flex-shrink-0">
                                            {{ substr($reply->user->name, 0, 1) }}
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $reply->user->name }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $reply->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    
                                    @if($reply->user_id === auth()->id() || auth()->user()->can('Delete backlog item'))
                                        <button onclick="if(confirm('Are you sure you want to delete this reply?')) { @this.call('deleteComment', {{ $reply->id }}) }"
                                                class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200">
                                            Delete
                                        </button>
                                    @endif
                                </div>
                                
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {!! nl2br(e($reply->content)) !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No comments yet</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Be the first to comment on this item.</p>
            </div>
        @endforelse
    </div>
</div>
