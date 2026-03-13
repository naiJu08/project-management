<div x-data="{ helpOpen: false }" class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Header with Quick Actions --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-5">
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
                        @if($ticket->priority)
                            <span class="px-3 py-1 text-sm font-semibold rounded-full"
                                  :class="@switch($ticket->priority->name)
                                      @case('Critical') bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 @break
                                      @case('High') bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 @break
                                      @case('Medium') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 @break
                                      @default bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                  @endswitch">
                                {{ $ticket->priority->name }}
                            </span>
                        @endif
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">{{ $ticket->name }}</h1>
                </div>
                <div class="flex gap-2">
                    @if($ticket->responsible_id === auth()->id() || $ticket->owner_id === auth()->id())
                        <button wire:click="toggleStatusForm" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm font-medium">
                            📊 Change Status
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.addEventListener('showStatusForm', () => {
                const el = document.getElementById('status-form');
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>

    {{-- Status Change Form --}}
    @if($showStatusForm)
        <div id="status-form" class="bg-blue-50 dark:bg-blue-900/20 border-b-2 border-blue-400 dark:border-blue-600 px-4 sm:px-6 lg:px-8 py-4 sticky top-0 z-50 shadow-lg">
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">📊 Change Status</label>
                    <div class="flex gap-2 items-center">
                        <select wire:model="newStatus" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-medium">
                            <option value="">Select new status...</option>
                            @foreach($availableStatuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                        <button wire:click="updateStatus" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm font-medium whitespace-nowrap">
                            ✓ Update
                        </button>
                        <button wire:click="$set('showStatusForm', false)" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-700 text-gray-900 dark:text-white rounded-lg transition-colors text-sm font-medium whitespace-nowrap">
                            ✕ Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Tab Navigation --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-16 z-5">
        <div class="px-4 sm:px-6 lg:px-8">
            <nav class="flex space-x-8 overflow-x-auto" role="tablist">
                <button wire:click="selectTab('overview')" 
                        class="px-1 py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $activeTab === 'overview' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300' }}">
                    📋 Overview
                </button>
                <button wire:click="selectTab('dates')" 
                        class="px-1 py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $activeTab === 'dates' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300' }}">
                    📅 Dates & Deadlines
                </button>
                <button wire:click="selectTab('time')" 
                        class="px-1 py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $activeTab === 'time' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300' }}">
                    ⏱️ Track Time
                </button>
                <button wire:click="selectTab('relationships')" 
                        class="px-1 py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $activeTab === 'relationships' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300' }}">
                    🔗 Relationships
                </button>
                <button wire:click="selectTab('comments')" 
                        class="px-1 py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $activeTab === 'comments' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300' }}">
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
                @if($activeTab === 'overview')
                    @include('livewire.ticket.tabs.employee.overview')
                @elseif($activeTab === 'dates')
                    @include('livewire.ticket.tabs.employee.dates')
                @elseif($activeTab === 'time')
                    @include('livewire.ticket.tabs.employee.time-tracking')
                @elseif($activeTab === 'relationships')
                    @include('livewire.ticket.tabs.employee.relationships')
                @elseif($activeTab === 'comments')
                    @include('livewire.ticket.tabs.employee.comments')
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-1">
                @include('livewire.ticket.sidebar.employee-quick-info')
            </div>
        </div>
    </div>

    {{-- Help Sidebar --}}
    <x-ticket-help-sidebar :activeTab="$activeTab" />
</div>
