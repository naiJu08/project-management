{{-- Comments Section --}}
<div class="space-y-6">
    {{-- Search Comments --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input type="text" wire:model="searchComments" placeholder="Search comments..." 
                   class="flex-1 px-3 py-2 border-0 bg-transparent text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none">
            @if($searchComments)
                <button wire:click="$set('searchComments', '')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            @endif
        </div>
    </div>

    {{-- Comments List --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">💬 Comments ({{ $ticket->comments->count() }})</h3>
            @if(!$showCommentForm)
                <button wire:click="toggleCommentForm" class="px-3 py-1 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    + Add Comment
                </button>
            @endif
        </div>

        {{-- Add Comment Form --}}
        @if($showCommentForm)
            <div class="space-y-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg mb-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden">
                    <textarea wire:model="newComment" 
                              rows="4"
                              placeholder="Write your comment here..."
                              class="w-full px-4 py-3 bg-white dark:bg-gray-800 text-gray-900 dark:text-white border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>
                
                <div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('commentAttachment').click()" class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Attach File</span>
                    </button>
                    <input type="file" id="commentAttachment" wire:model="commentAttachment" class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.jpg,.png,.gif">
                </div>
                
                @if($commentAttachment)
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="text-sm text-blue-800 dark:text-blue-200">{{ $commentAttachment->getClientOriginalName() }}</span>
                        </div>
                        <button wire:click="$set('commentAttachment', null)" class="text-blue-600 hover:text-blue-700 dark:text-blue-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endif
                
                <div class="flex gap-2">
                    <button wire:click="addComment" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm font-medium">
                        ✓ Post Comment
                    </button>
                    <button wire:click="$set('showCommentForm', false)" class="flex-1 px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-700 text-gray-900 dark:text-white rounded-lg transition-colors text-sm font-medium">
                        Cancel
                    </button>
                </div>
            </div>
        @endif

        {{-- Comments Display --}}
        <div class="space-y-4">
            @forelse($filteredComments as $comment)
                <div class="flex gap-4 pb-4 border-b border-gray-200 dark:border-gray-700 last:border-0 group">
                    <img src="{{ $comment->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($comment->user->name) }}" 
                         alt="{{ $comment->user->name }}" class="w-10 h-10 rounded-full flex-shrink-0">
                    
                    <div class="flex-1">
                        {{-- Comment Header --}}
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $comment->user->name }}</p>
                                @if($comment->user_id === auth()->id())
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">You</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</p>
                        </div>

                        {{-- Comment Content --}}
                        @if($editingCommentId === $comment->id)
                            <div class="space-y-2">
                                <textarea wire:model="editingCommentContent" rows="2"
                                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"></textarea>
                                <div class="flex gap-2">
                                    <button wire:click="updateComment" class="px-3 py-1 text-sm bg-green-600 hover:bg-green-700 text-white rounded transition-colors">
                                        Save
                                    </button>
                                    <button wire:click="$set('editingCommentId', null)" class="px-3 py-1 text-sm bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-700 text-gray-900 dark:text-white rounded transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="prose dark:prose-invert max-w-none text-sm text-gray-700 dark:text-gray-300">
                                {!! $comment->content !!}
                            </div>

                            {{-- Attachments Section --}}
                            @if($comment->attachment_path)
                                <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    @php
                                        $filePath = $comment->attachment_path;
                                        $fileName = basename($filePath);
                                        $fileExt = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                        $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                                    @endphp
                                    
                                    @if($isImage)
                                        <div class="mb-2">
                                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">📸 Image Preview:</p>
                                            <img src="{{ Storage::url($filePath) }}" alt="{{ $fileName }}" class="max-w-xs max-h-64 rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer hover:opacity-80 transition-opacity" onclick="window.open('{{ Storage::url($filePath) }}', '_blank')">
                                        </div>
                                    @endif
                                    
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            @if($isImage)
                                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            @endif
                                            <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">{{ $fileName }}</span>
                                        </div>
                                        <a href="{{ Storage::url($filePath) }}" download="{{ $fileName }}" class="px-3 py-1 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors font-medium">
                                            ⭳ Download
                                        </a>
                                    </div>
                                </div>
                            @endif

                            {{-- Comment Actions --}}
                            @if($comment->user_id === auth()->id())
                                <div class="flex gap-2 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button wire:click="editComment({{ $comment->id }})" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium">
                                        Edit
                                    </button>
                                    <button wire:click="deleteComment({{ $comment->id }})" class="text-sm text-red-600 hover:text-red-700 dark:text-red-400 font-medium">
                                        Delete
                                    </button>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                    @if($searchComments)
                        No comments match your search
                    @else
                        No comments yet. Be the first to comment!
                    @endif
                </p>
            @endforelse
        </div>
    </div>
</div>
