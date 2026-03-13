<div class="space-y-6 h-full flex flex-col">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                Chat
                <x-help-sidebar 
                    title="Project Chat"
                    description="Real-time team communication with file and image sharing"
                    :features="[
                        'Real-time messaging',
                        'File and image uploads',
                        'Message search',
                        'Edit and delete messages',
                        'Threaded replies',
                        'User mentions',
                        'Message history',
                        'Notification support'
                    ]"
                    :benefits="[
                        'Improve team communication',
                        'Reduce email clutter',
                        'Share files easily',
                        'Keep discussions organized',
                        'Quick decision making',
                        'Better collaboration'
                    ]"
                    implementation="<p>1. Type message in input box</p><p>2. Attach files/images if needed</p><p>3. Click Send to post</p><p>4. Reply to specific messages</p><p>5. Search message history</p><p>6. Edit or delete your messages</p>"
                    :examples="[
                        ['title' => 'Quick Discussion', 'description' => 'Discuss ideas in real-time'],
                        ['title' => 'File Sharing', 'description' => 'Share documents and screenshots'],
                        ['title' => 'Decision Making', 'description' => 'Make quick decisions together']
                    ]"
                />
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Team discussion for {{ $this->project->name }}</p>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="flex gap-2">
        <div class="flex-1 relative">
            <input type="text" wire:model="searchQuery" wire:keyup="search" placeholder="Search messages..." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400">
            <svg class="absolute right-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        @if($searchQuery)
            <button wire:click="clearSearch()" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                Clear
            </button>
        @endif
    </div>

    {{-- Messages Container --}}
    <div class="flex-1 overflow-y-auto bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-4">
        @forelse($messages as $message)
            <div class="group" id="message-{{ $message->id }}">
                {{-- Replied to message --}}
                @if($message->replyTo)
                    <div class="mb-2 pl-4 border-l-2 border-blue-400 bg-blue-50 dark:bg-blue-900/20 p-2 rounded text-sm">
                        <div class="font-semibold text-blue-900 dark:text-blue-200">{{ $message->replyTo->user->name }}</div>
                        <div class="text-blue-800 dark:text-blue-300 truncate">{{ $message->replyTo->message }}</div>
                    </div>
                @endif

                {{-- Message --}}
                <div class="flex gap-3">
                    {{-- Avatar --}}
                    <div class="flex-shrink-0">
                        @if($message->user->avatar_url)
                            <img src="{{ $message->user->avatar_url }}" alt="{{ $message->user->name }}" class="w-10 h-10 rounded-full">
                        @else
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-sm">
                                {{ substr($message->user->name, 0, 1) }}
                            </div>
                        @endif
                    </div>

                    {{-- Message Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $message->user->name }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $message->created_at->diffForHumans() }}</span>
                            @if($message->is_edited)
                                <span class="text-xs text-gray-400 dark:text-gray-500 italic">(edited)</span>
                            @endif
                        </div>

                        {{-- Edit Mode --}}
                        @if($editingMessageId === $message->id)
                            <div class="space-y-2">
                                <textarea wire:model="editingText" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" rows="3"></textarea>
                                <div class="flex gap-2">
                                    <button wire:click="saveEdit()" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition-colors">
                                        Save
                                    </button>
                                    <button wire:click="cancelEdit()" class="px-3 py-1 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-sm hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        @else
                            {{-- Message Text --}}
                            <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-3 break-words">
                                @if($message->message)
                                    <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $message->message }}</p>
                                @endif

                                {{-- Display Attachments --}}
                                @php
                                    $attachments = $message->attachments ? json_decode($message->attachments, true) : null;
                                @endphp

                                @if($attachments)
                                    <div class="mt-3 space-y-2">
                                        {{-- Image Attachment --}}
                                        @if(isset($attachments['image']))
                                            <div class="mt-2">
                                                <img src="{{ asset('storage/' . $attachments['image']) }}" alt="Attached image" class="max-w-xs max-h-64 rounded-lg border border-gray-300 dark:border-gray-600">
                                            </div>
                                        @endif

                                        {{-- File Attachment --}}
                                        @if(isset($attachments['file']))
                                            <a href="{{ asset('storage/' . $attachments['file']['path']) }}" download class="flex items-center gap-2 p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                </svg>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-blue-900 dark:text-blue-200 truncate">{{ $attachments['file']['name'] }}</p>
                                                    <p class="text-xs text-blue-700 dark:text-blue-300">{{ number_format($attachments['file']['size'] / 1024, 2) }} KB</p>
                                                </div>
                                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex gap-2 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button wire:click="replyTo({{ $message->id }})" class="text-xs text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6-6m0 0l-6 6"></path>
                                    </svg>
                                    Reply
                                </button>
                                @if($message->user_id === Auth::id())
                                    <button wire:click="editMessage({{ $message->id }})" class="text-xs text-gray-600 dark:text-gray-400 hover:underline flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit
                                    </button>
                                    <button wire:click="deleteMessage({{ $message->id }})" wire:confirm="Are you sure you want to delete this message?" class="text-xs text-red-600 dark:text-red-400 hover:underline flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="flex items-center justify-center h-full">
                <div class="text-center">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">No messages yet. Start the conversation!</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Reply Indicator --}}
    @if($replyingTo)
        @php
            $replyMessage = \App\Models\ProjectChat::find($replyingTo);
        @endphp
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 flex items-start justify-between">
            <div class="flex-1">
                <div class="text-sm font-semibold text-blue-900 dark:text-blue-200">Replying to {{ $replyMessage->user->name }}</div>
                <div class="text-sm text-blue-800 dark:text-blue-300 truncate">{{ $replyMessage->message }}</div>
            </div>
            <button wire:click="cancelReply()" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    @endif

    {{-- Message Input --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <form wire:submit.prevent="sendMessage" class="space-y-3">
            {{-- Attachment Preview --}}
            @if($attachedImage)
                <div class="relative inline-block">
                    <img src="{{ $attachedImage->temporaryUrl() }}" alt="Preview" class="h-20 w-20 object-cover rounded-lg border border-gray-300 dark:border-gray-600">
                    <button type="button" wire:click="removeImage()" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            @endif

            @if($attachedFile)
                <div class="flex items-center gap-2 p-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $attachedFile->getClientOriginalName() }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($attachedFile->getSize() / 1024, 2) }} KB</p>
                    </div>
                    <button type="button" wire:click="removeFile()" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            @endif

            <textarea wire:model="newMessage" placeholder="Type your message..." rows="3" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 resize-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            @error('newMessage') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            @error('attachedFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            @error('attachedImage') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            
            <div class="flex items-center justify-between">
                <div class="flex gap-2">
                    {{-- Image Upload --}}
                    <label class="cursor-pointer p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors" title="Upload image">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <input type="file" wire:model="attachedImage" accept="image/*" class="hidden">
                    </label>

                    {{-- File Upload --}}
                    <label class="cursor-pointer p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors" title="Upload file">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <input type="file" wire:model="attachedFile" class="hidden">
                    </label>
                </div>

                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    Send
                </button>
            </div>
        </form>
    </div>
</div>
