<div class="h-full">
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex h-full gap-4">
        {{-- Left Sidebar - Page Tree --}}
        <div class="w-64 bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="mb-4">
                <input type="text" 
                       wire:model.debounce.300ms="searchTerm" 
                       placeholder="Search pages..."
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500">
            </div>

            <div class="mb-3">
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Client Documentation</h3>
            </div>

            <div class="space-y-1">
                @forelse($pages as $page)
                    <div>
                        <button wire:click="selectPage({{ $page->id }})" 
                                class="w-full text-left px-3 py-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ $selectedPage && $selectedPage->id === $page->id ? 'bg-blue-50 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }}">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="text-sm">{{ $page->title }}</span>
                            </div>
                        </button>
                        
                        @if($page->children->count() > 0)
                            <div class="ml-4 mt-1 space-y-1">
                                @foreach($page->children as $child)
                                    <button wire:click="selectPage({{ $child->id }})" 
                                            class="w-full text-left px-3 py-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 {{ $selectedPage && $selectedPage->id === $child->id ? 'bg-blue-50 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }}">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                            <span class="text-sm">{{ $child->title }}</span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No pages available</p>
                @endforelse
            </div>
        </div>

        {{-- Main Content Area --}}
        <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            @if($selectedPage)
                {{-- View Mode --}}
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $selectedPage->title }}</h1>
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <span>Version {{ $selectedPage->version }}</span>
                            <span class="mx-2">•</span>
                            <span>Last updated {{ $selectedPage->updated_at->diffForHumans() }}</span>
                            @if($selectedPage->updater)
                                <span class="mx-2">•</span>
                                <span>by {{ $selectedPage->updater->name }}</span>
                            @endif
                        </div>
                        
                        {{-- Sign-off Status Badge --}}
                        @php
                            $status = $selectedPage->getSignoffStatus();
                            $badgeClass = match($status) {
                                'Signed-Off' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                'Outdated' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                            };
                        @endphp
                        <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $badgeClass }}">
                            {{ $status }}
                        </span>
                    </div>

                    {{-- Sign-off Banner --}}
                    @if($selectedPage->isSignedOff())
                        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-semibold text-green-800 dark:text-green-300 mb-1">Document Signed Off</h4>
                                    @foreach($selectedPage->activeSignoffs as $signoff)
                                        <p class="text-sm text-green-700 dark:text-green-400">
                                            Signed by <strong>{{ $signoff->client->name }}</strong> on {{ $signoff->signed_off_at->format('M d, Y H:i') }} (Version {{ $signoff->version_signed }})
                                        </p>
                                        @if($signoff->remarks)
                                            <p class="text-sm text-green-600 dark:text-green-400 mt-1 italic">"{{ $signoff->remarks }}"</p>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="prose dark:prose-invert max-w-none mb-8">
                        {!! $selectedPage->processed_content ?? $selectedPage->content ?? '' !!}
                    </div>

                    {{-- Comments Section --}}
                    @if(auth()->user()->can('Comment on wiki'))
                        <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                Comments ({{ $selectedPage->allComments->count() }})
                            </h3>

                            {{-- Add Comment Form --}}
                            <div class="mb-6">
                                @if($replyToCommentId)
                                    <div class="mb-2 p-2 bg-blue-50 dark:bg-blue-900 rounded flex items-center justify-between">
                                        <span class="text-sm text-blue-700 dark:text-blue-300">Replying to comment...</span>
                                        <button wire:click="cancelReply" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                                <textarea wire:model="newComment" 
                                          rows="3"
                                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                                          placeholder="Add a comment..."></textarea>
                                @error('newComment') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                <button wire:click="addComment" 
                                        class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                    Post Comment
                                </button>
                            </div>

                            {{-- Comments List --}}
                            <div class="space-y-4">
                                @forelse($selectedPage->comments as $comment)
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex items-center space-x-2">
                                                <span class="font-semibold text-gray-900 dark:text-white">{{ $comment->user->name }}</span>
                                                @if($comment->isClientComment())
                                                    <span class="px-2 py-0.5 text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300 rounded">Client</span>
                                                @else
                                                    <span class="px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 rounded">Team</span>
                                                @endif
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <button wire:click="replyToComment({{ $comment->id }})" 
                                                    class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                                                Reply
                                            </button>
                                        </div>
                                        <div class="text-sm text-gray-700 dark:text-gray-300 prose dark:prose-invert prose-sm max-w-none">
                                            {!! \Illuminate\Support\Str::markdown($comment->content) !!}
                                        </div>

                                        {{-- Replies --}}
                                        @if($comment->replies->count() > 0)
                                            <div class="mt-3 ml-6 space-y-3 border-l-2 border-gray-300 dark:border-gray-600 pl-4">
                                                @foreach($comment->replies as $reply)
                                                    <div class="bg-white dark:bg-gray-800 rounded p-3">
                                                        <div class="flex items-start justify-between mb-2">
                                                            <div class="flex items-center space-x-2">
                                                                <span class="font-semibold text-sm text-gray-900 dark:text-white">{{ $reply->user->name }}</span>
                                                                @if($reply->isClientComment())
                                                                    <span class="px-2 py-0.5 text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300 rounded">Client</span>
                                                                @else
                                                                    <span class="px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 rounded">Team</span>
                                                                @endif
                                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="text-sm text-gray-700 dark:text-gray-300 prose dark:prose-invert prose-sm max-w-none">
                                                            {!! \Illuminate\Support\Str::markdown($reply->content) !!}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No comments yet. Be the first to comment!</p>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    {{-- Sign-off Section for Clients --}}
                    @if(auth()->user()->can('Sign off wiki'))
                        <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Sign-Off
                            </h3>

                            @if(!$selectedPage->signoffs()->where('client_id', auth()->id())->where('version_signed', $selectedPage->version)->where('is_outdated', false)->exists())
                                <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 mb-4">
                                    <p class="text-sm text-yellow-800 dark:text-yellow-300 mb-3">
                                        By signing off, you confirm that you have reviewed and approved this document (Version {{ $selectedPage->version }}).
                                    </p>
                                    <textarea wire:model="signoffRemarks" 
                                              rows="2"
                                              class="w-full px-3 py-2 border border-yellow-300 dark:border-yellow-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 text-sm mb-2"
                                              placeholder="Optional remarks..."></textarea>
                                    <button wire:click="signOffPage" 
                                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-semibold">
                                        Sign Off Document
                                    </button>
                                </div>
                            @else
                                <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg p-4">
                                    <p class="text-sm text-green-800 dark:text-green-300">
                                        ✓ You have signed off this document version.
                                    </p>
                                </div>
                            @endif

                            {{-- Sign-off History --}}
                            @if($selectedPage->signoffs->count() > 0)
                                <div class="mt-4">
                                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Sign-off History</h4>
                                    <div class="space-y-2">
                                        @foreach($selectedPage->signoffs->take(5) as $signoff)
                                            <div class="text-sm p-2 bg-gray-50 dark:bg-gray-700 rounded {{ $signoff->is_outdated ? 'opacity-60' : '' }}">
                                                <span class="font-medium">{{ $signoff->client->name }}</span>
                                                signed off version {{ $signoff->version_signed }}
                                                on {{ $signoff->signed_off_at->format('M d, Y H:i') }}
                                                @if($signoff->is_outdated)
                                                    <span class="text-yellow-600 dark:text-yellow-400">(Outdated)</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($selectedPage->children->count() > 0)
                        <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sub-pages</h3>
                            <div class="grid grid-cols-2 gap-4">
                                @foreach($selectedPage->children as $child)
                                    <button wire:click="selectPage({{ $child->id }})" 
                                            class="text-left p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $child->title }}</span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                {{-- Empty State --}}
                <div class="flex flex-col items-center justify-center h-full text-center">
                    <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No page selected</h3>
                    <p class="text-gray-500 dark:text-gray-400">Select a page from the sidebar to view documentation</p>
                </div>
            @endif
        </div>
    </div>
</div>
