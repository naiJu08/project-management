<div x-data="{ helpOpen: false }" class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z10">
        <div class="px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                            {{ $ticket->code }}
                        </span>
                        <span class="px-3 py-1 text-sm font-semibold rounded-full"
                              :class="@switch($ticket->status->name)
                                  @case('Open') bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 @break
                                  @case('In Progress') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 @break
                                  @case('Done') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 @break
                                  @default bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200
                              @endswitch">
                            {{ $ticket->status->name }}
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">{{ $ticket->name }}</h1>
                </div>
                <div class="flex gap-2">
                    <button wire:click="toggleBlocked" class="px-4 py-2 rounded-lg transition-colors"
                            :class="$ticket->is_blocked ? 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'">
                        @if($ticket->is_blocked)
                            🚫 Blocked
                        @else
                            ✓ Active
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-16 z-10">
        <div class="px-4 sm:px-6 lg:px-8">
            <nav class="flex space-x-8 overflow-x-auto" role="tablist">
                <button wire:click="selectTab('details')" 
                        class="px-1 py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
                        :class="$activeTab === 'details' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300'">
                    📋 Details
                </button>
                <button wire:click="selectTab('activity')" 
                        class="px-1 py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
                        :class="$activeTab === 'activity' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300'">
                    📅 Activity
                </button>
                <button wire:click="selectTab('time')" 
                        class="px-1 py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
                        :class="$activeTab === 'time' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300'">
                    ⏱️ Time Tracking
                </button>
                <button wire:click="selectTab('approvals')" 
                        class="px-1 py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
                        :class="$activeTab === 'approvals' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300'">
                    ✅ Approvals @if($pendingApprovals > 0) <span class="ml-1 px-2 py-0.5 bg-red-500 text-white text-xs rounded-full">{{ $pendingApprovals }}</span> @endif
                </button>
                <button wire:click="selectTab('dependencies')" 
                        class="px-1 py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
                        :class="$activeTab === 'dependencies' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300'">
                    🔗 Dependencies
                </button>
                <button wire:click="selectTab('comments')" 
                        class="px-1 py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
                        :class="$activeTab === 'comments' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300'">
                    💬 Comments ({{ $ticket->comments->count() }})
                </button>
            </nav>
        </div>
    </div>

    {{-- Content Area --}}
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-8">
                @if($activeTab === 'details')
                    @include('livewire.ticket.tabs.details')
                @elseif($activeTab === 'activity')
                    @include('livewire.ticket.tabs.activity')
                @elseif($activeTab === 'time')
                    @include('livewire.ticket.tabs.time-tracking')
                @elseif($activeTab === 'approvals')
                    @include('livewire.ticket.tabs.approvals')
                @elseif($activeTab === 'dependencies')
                    @include('livewire.ticket.tabs.dependencies')
                @elseif($activeTab === 'comments')
                    @include('livewire.ticket.tabs.comments')
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-1">
                @include('livewire.ticket.sidebar.quick-info')
            </div>
        </div>
    </div>

    {{-- Help Sidebar --}}
    <x-ticket-help-sidebar :activeTab="$activeTab" />
</div>
