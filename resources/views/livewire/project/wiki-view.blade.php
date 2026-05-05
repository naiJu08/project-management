<div>
    {{-- Header with Help Sidebar --}}
    <div class="px-3 sm:px-4 md:px-6 mb-3 sm:mb-4">
        <h2 class="text-base sm:text-lg md:text-xl font-semibold text-gray-900 dark:text-white flex items-center">
            Wiki
            <x-help-sidebar 
                title="Project Wiki"
                description="Create and manage project documentation with hierarchical pages"
                :features="[
                    'Hierarchical page structure (parent-child)',
                    'Markdown and HTML editing',
                    'Version tracking',
                    'Search functionality',
                    'File attachments',
                    'Client visibility toggle',
                    'Comments and discussions',
                    'Full CRUD operations'
                ]"
                :benefits="[
                    'Centralize project documentation',
                    'Improve knowledge sharing',
                    'Track documentation changes',
                    'Share with clients',
                    'Better team onboarding',
                    'Reduce email clutter'
                ]"
                implementation="<p>1. Click 'New Page' to create</p><p>2. Enter title and content</p><p>3. Set parent page if needed</p><p>4. Toggle client visibility</p><p>5. Add attachments</p><p>6. Publish page</p>"
                :examples="[
                    ['title' => 'API Documentation', 'description' => 'Create API docs with code examples'],
                    ['title' => 'User Guide', 'description' => 'Write user guides for features'],
                    ['title' => 'Architecture', 'description' => 'Document system architecture']
                ]"
            />
        </h2>
    </div>

    {{-- Flash Messages --}}
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

        @if($this->canCreate())
            <button 
                wire:click="editPage()" 
                type="button"
                class="w-full mb-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-semibold rounded-md shadow-sm flex items-center justify-center transition-colors border border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800">
                <svg 
                    class="w-4 h-4 mr-2 flex-shrink-0 text-white" 
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path 
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        d="M12 4v16m8-8H4">
                    </path>
                </svg>
                <span class="tracking-wide">New Page</span>
            </button>
        @endif

        {{-- Export and AI Actions --}}
        @if($pages->count() > 0)
            <div class="flex flex-col space-y-2 mb-4">
                {{-- Export Master PDF --}}
                <button wire:click="exportMasterPdf"
                        class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white rounded-md flex items-center justify-center text-sm font-semibold transition-colors shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <span>Export Master PDF</span>
                </button>

                {{-- Generate Backlog Button --}}
                <button wire:click="generateBacklogFromWiki"
                        wire:loading.attr="disabled"
                        class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 text-white rounded-md flex items-center justify-center text-sm font-semibold transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span wire:loading.remove wire:target="generateBacklogFromWiki">Generate Backlog (AI)</span>
                    <span wire:loading wire:target="generateBacklogFromWiki" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ $generationProgress }}
                    </span>
                </button>
            </div>
        @endif

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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="text-xs">{{ $child->title }}</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No pages yet</p>
            @endforelse
        </div>
    </div>

    {{-- Main Content Area --}}
    <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        @if($isEditing)
            {{-- Edit Mode --}}
            <div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Page Title</label>
                    <input type="text" 
                           wire:model="title" 
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                           placeholder="Enter page title...">
                    @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Content</label>
                    <div class="bg-white dark:bg-gray-700 rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden">
                        <div wire:ignore class="trix-wrapper">
                            <trix-editor input="wiki-content" class="trix-content"></trix-editor>
                            <input id="wiki-content" type="hidden" wire:model="content">
                        </div>
                    </div>
                    @error('content') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                @push('scripts')
                    <script>
                        document.addEventListener('livewire:init', () => {
                            const trixEditor = document.querySelector('trix-editor');
                            const hiddenInput = document.getElementById('wiki-content');
                            
                            if (trixEditor && hiddenInput) {
                                // Sync Trix editor content with Livewire component
                                trixEditor.addEventListener('trix-change', () => {
                                    hiddenInput.value = trixEditor.innerHTML;
                                    // Trigger Livewire update
                                    @this.set('content', trixEditor.innerHTML);
                                });
                                
                                // Initialize editor content when component loads
                                @this.on('refreshEditor', () => {
                                    trixEditor.innerHTML = @this.get('content') || '';
                                });
                            }
                        });
                    </script>
                @endpush

                <div class="mb-4">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" 
                               wire:model="clientVisible" 
                               class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Visible to Client
                        </span>
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ml-6">Allow clients to view this page in their wiki view</p>
                </div>

                <div class="flex items-center space-x-2">
                    <button wire:click="savePage"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Save Page
                    </button>
                    <button wire:click="cancelEdit" 
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                </div>
            </div>
        @elseif($selectedPage)
            {{-- View Mode --}}
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $selectedPage->title }}</h1>
                    <div class="flex items-center space-x-2">
                        @if($this->canEdit())
                            <button wire:click="editPage({{ $selectedPage->id }})" 
                                    class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </button>
                        @endif

                        {{-- Export Page PDF --}}
                        <button wire:click="exportPagePdf({{ $selectedPage->id }})"
                                class="px-3 py-1 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 flex items-center transition-colors shadow-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export PDF
                        </button>
                        
                        @if($this->canCreate())
                            <button wire:click="createSubPage({{ $selectedPage->id }})" 
                                    class="px-3 py-1 text-sm border border-green-300 dark:border-green-600 text-green-700 dark:text-green-400 rounded-md hover:bg-green-50 dark:hover:bg-green-900 flex items-center transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Sub-page
                            </button>
                        @endif
                        
                        @if($this->canDelete())
                            <button wire:click="confirmDelete({{ $selectedPage->id }})" 
                                    class="px-3 py-1 text-sm border border-red-300 dark:border-red-600 text-red-700 dark:text-red-400 rounded-md hover:bg-red-50 dark:hover:bg-red-900 flex items-center transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete
                            </button>
                        @endif
                    </div>
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

                {{-- Client Visibility Indicator --}}
                @if($selectedPage->client_visible)
                    <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg flex items-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <span class="text-sm text-blue-700 dark:text-blue-300">This page is visible to clients</span>
                    </div>
                @endif

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
                                        <div class="flex items-center space-x-2">
                                            <button wire:click="replyToComment({{ $comment->id }})" 
                                                    class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                                                Reply
                                            </button>
                                            @if($comment->canDelete())
                                                <button wire:click="confirmDeleteComment({{ $comment->id }})" 
                                                        class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200">
                                                    Delete
                                                </button>
                                            @endif
                                        </div>
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
                                                        @if($reply->canDelete())
                                                            <button wire:click="confirmDeleteComment({{ $reply->id }})" 
                                                                    class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200">
                                                                Delete
                                                            </button>
                                                        @endif
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
                                @php
                                    // Empty state - no placeholder text
                                @endphp
                            @endforelse
                        </div>
                    </div>
                @endif

                {{-- Sign-off Section for Clients --}}
                @if(auth()->user()->hasRole('Client') && auth()->user()->can('Sign off wiki') && $selectedPage->client_visible)
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
                <p class="text-gray-500 dark:text-gray-400 mb-4">Select a page from the sidebar or create a new one</p>
                @if($this->canCreate())
                    <button wire:click="editPage()" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        Create First Page
                    </button>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">You don't have permission to create wiki pages.</p>
                @endif
            </div>
        @endif
    </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteConfirm)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Delete Wiki Page</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Are you sure you want to delete this page?</p>
                    </div>
                </div>
                
                <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-md p-3 mb-4">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <strong>Warning:</strong> This action cannot be undone. All sub-pages will also be deleted.
                    </p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button wire:click="cancelDelete" 
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="deletePage" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        Delete Page
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Comment Delete Confirmation Modal --}}
    @if($showCommentDeleteConfirm)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Delete Comment</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Are you sure you want to delete this comment?</p>
                    </div>
                </div>
                
                <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-md p-3 mb-4">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <strong>Warning:</strong> This action cannot be undone.
                    </p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button wire:click="cancelDeleteComment" 
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="deleteComment" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        Delete Comment
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Backlog Preview Modal --}}
    @if($showBacklogPreview && !empty($generatedBacklog))
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-6xl w-full max-h-[85vh] overflow-y-auto mx-4 shadow-2xl">
                <div class="flex items-center justify-between mb-6 sticky top-0 bg-white dark:bg-gray-800 pb-4 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Generated Backlog Preview</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Review the AI-generated backlog structure before importing</p>
                    </div>
                    <button wire:click="cancelBacklogGeneration" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-6 mb-6">
                    @foreach($generatedBacklog['epics'] as $epicIndex => $epic)
                        <div class="border border-purple-200 dark:border-purple-700 rounded-lg p-5 bg-purple-50 dark:bg-purple-900/20">
                            <div class="flex items-start space-x-3 mb-3">
                                <span class="text-3xl">🎯</span>
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <h3 class="text-lg font-bold text-purple-700 dark:text-purple-300">{{ $epic['title'] }}</h3>
                                        <span class="px-2 py-1 text-xs font-semibold rounded {{ $epic['priority'] === 'High' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : ($epic['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200') }}">
                                            {{ $epic['priority'] }} Priority
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $epic['description'] }}</p>
                                </div>
                            </div>

                            @if(isset($epic['features']) && count($epic['features']) > 0)
                                <div class="ml-10 space-y-4 mt-4">
                                    @foreach($epic['features'] as $featureIndex => $feature)
                                        <div class="border-l-4 border-blue-400 pl-4 bg-white dark:bg-gray-800 p-4 rounded-r-lg">
                                            <div class="flex items-start space-x-2 mb-2">
                                                <span class="text-xl">🔷</span>
                                                <div class="flex-1">
                                                    <div class="flex items-center space-x-2 mb-1">
                                                        <h4 class="font-semibold text-blue-700 dark:text-blue-300">{{ $feature['title'] }}</h4>
                                                        <span class="px-2 py-0.5 text-xs font-semibold rounded {{ $feature['priority'] === 'High' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : ($feature['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200') }}">
                                                            {{ $feature['priority'] }}
                                                        </span>
                                                    </div>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ $feature['description'] }}</p>
                                                </div>
                                            </div>

                                            @if(isset($feature['user_stories']) && count($feature['user_stories']) > 0)
                                                <div class="ml-6 space-y-2 mt-3">
                                                    @foreach($feature['user_stories'] as $storyIndex => $story)
                                                        <div class="border-l-4 border-green-400 pl-3 bg-green-50 dark:bg-green-900/20 p-3 rounded-r">
                                                            <div class="flex items-start space-x-2">
                                                                <span class="text-lg">📖</span>
                                                                <div class="flex-1">
                                                                    <div class="flex items-center space-x-2 mb-1">
                                                                        <span class="text-sm font-medium text-green-800 dark:text-green-300">{{ $story['title'] }}</span>
                                                                        <span class="px-1.5 py-0.5 text-xs bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded font-mono">
                                                                            {{ $story['estimated_hours'] ?? 8 }}h
                                                                        </span>
                                                                    </div>
                                                                    <p class="text-xs text-gray-700 dark:text-gray-400 italic mb-2">{{ $story['description'] }}</p>
                                                                    
                                                                    @if(isset($story['acceptance_criteria']) && count($story['acceptance_criteria']) > 0)
                                                                        <div class="mt-2">
                                                                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Acceptance Criteria:</p>
                                                                            <ul class="text-xs text-gray-600 dark:text-gray-400 ml-4 space-y-1">
                                                                                @foreach($story['acceptance_criteria'] as $criteria)
                                                                                    <li class="flex items-start">
                                                                                        <span class="text-green-600 dark:text-green-400 mr-2">✓</span>
                                                                                        <span>{{ $criteria }}</span>
                                                                                    </li>
                                                                                @endforeach
                                                                            </ul>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700 sticky bottom-0 bg-white dark:bg-gray-800">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <span class="font-semibold">Total Items:</span>
                        {{ count($generatedBacklog['epics']) }} Epics,
                        {{ collect($generatedBacklog['epics'])->sum(function($epic) { return count($epic['features'] ?? []); }) }} Features,
                        {{ collect($generatedBacklog['epics'])->sum(function($epic) { 
                            return collect($epic['features'] ?? [])->sum(function($feature) { 
                                return count($feature['user_stories'] ?? []); 
                            }); 
                        }) }} User Stories
                    </div>
                    <div class="flex items-center space-x-3">
                        <button wire:click="cancelBacklogGeneration"
                                class="px-5 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancel
                        </button>
                        <button wire:click="confirmBacklogGeneration"
                                class="px-5 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors shadow-sm font-semibold">
                            Confirm & Create Backlog Items
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Progress Toast --}}
<div id="progress-toast" class="fixed bottom-4 right-4 z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl p-4 w-96 border border-gray-200 dark:border-gray-700">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <svg class="animate-spin h-6 w-6 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Generating Backlog</h4>
                <p id="progress-message" class="text-sm text-gray-600 dark:text-gray-400 mb-2">Starting...</p>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div id="progress-bar" class="bg-purple-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <p id="progress-percentage" class="text-xs text-gray-500 dark:text-gray-400 mt-1">0%</p>
            </div>
            <button onclick="hideProgressToast()" class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

{{-- TinyMCE HTML Editor Script --}}
<script src="https://cdn.tiny.cloud/1/no-api-key-required/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    let progressPollingInterval = null;

    function showProgressToast() {
        document.getElementById('progress-toast').classList.remove('hidden');
    }

    function hideProgressToast() {
        document.getElementById('progress-toast').classList.add('hidden');
    }

    function updateProgressToast(message, percentage) {
        document.getElementById('progress-message').textContent = message;
        document.getElementById('progress-bar').style.width = percentage + '%';
        document.getElementById('progress-percentage').textContent = percentage + '%';
    }

    document.addEventListener('livewire:load', function () {
        function initTrixEditor() {
            const trixEditor = document.querySelector('trix-editor');
            const hiddenInput = document.getElementById('wiki-content');
            
            if (trixEditor && hiddenInput) {
                // Set initial content
                if (@this.content) {
                    trixEditor.editor.loadHTML(@this.content);
                }
                
                // Update Livewire when editor changes (debounced)
                trixEditor.addEventListener('trix-change', function() {
                    clearTimeout(trixEditor._debounceTimer);
                    trixEditor._debounceTimer = setTimeout(() => {
                        @this.set('content', hiddenInput.value);
                    }, 300);
                });

                // Handle file attachments
                trixEditor.addEventListener('trix-attachment-add', function(event) {
                    const attachment = event.attachment;
                    
                    if (attachment.file) {
                        // Show uploading state
                        attachment.setAttributes({
                            url: URL.createObjectURL(attachment.file),
                            filename: attachment.file.name,
                            contentType: attachment.file.type,
                            previewable: attachment.file.type.startsWith('image/')
                        });
                        
                        // Upload the file via Livewire
                        @this.upload('trixAttachment', attachment.file)
                            .then(() => {
                                // Listen for the browser event that will be dispatched
                                window.addEventListener('trix-attachment-uploaded', function handler(e) {
                                    const { url, filename, contentType } = e.detail;
                                    
                                    // Update the attachment with the uploaded URL
                                    attachment.setAttributes({
                                        url: url,
                                        filename: filename,
                                        contentType: contentType,
                                        previewable: contentType.startsWith('image/')
                                    });
                                    
                                    // Remove the event listener
                                    window.removeEventListener('trix-attachment-uploaded', handler);
                                }, { once: true });
                            });
                    }
                });
            }
        }
        
        // Initialize on page load
        setTimeout(() => {
            initTrixEditor();
        }, 100);

        // Re-initialize only when entering edit mode
        let wasEditing = @this.isEditing;
        Livewire.hook('message.processed', (message, component) => {
            if (@this.isEditing && !wasEditing) {
                setTimeout(() => {
                    initTrixEditor();
                }, 100);
            }
            wasEditing = @this.isEditing;
        });

        // Listen for job polling events
        window.addEventListener('start-job-polling', event => {
            showProgressToast();
            updateProgressToast('Starting backlog generation...', 0);
            
            // Start polling every 2 seconds
            progressPollingInterval = setInterval(() => {
                @this.call('checkJobProgress');
            }, 2000);
        });

        window.addEventListener('stop-job-polling', event => {
            if (progressPollingInterval) {
                clearInterval(progressPollingInterval);
                progressPollingInterval = null;
            }
            
            // Hide toast after 3 seconds
            setTimeout(() => {
                hideProgressToast();
            }, 3000);
        });

        // Update progress when Livewire updates
        Livewire.hook('message.processed', (message, component) => {
            if (@this.isGeneratingBacklog && @this.generationProgress) {
                updateProgressToast(@this.generationProgress, @this.generationPercentage);
            }
        });
    });
</script>
