<div class="min-h-screen bg-gray-50 dark:bg-gray-900" x-data="{ showShareModal: false }">
    {{-- Keyboard Shortcuts --}}
    <x-keyboard-shortcuts />

    {{-- Bulk Actions Bar --}}
    <x-bulk-actions :selectedCount="0" :totalCount="0" />

    {{-- Share Modal --}}
    <x-project-share-modal :projectId="$this->project->id" :projectName="$this->project->name" />

    {{-- Project Header --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-3 sm:py-4">
            {{-- Header Container --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
                {{-- Title Section --}}
                <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 flex-shrink-0 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <h1 class="text-base sm:text-lg md:text-xl lg:text-2xl font-bold text-gray-900 dark:text-white truncate">
                        {{ $this->project->name }}
                    </h1>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
                    {{-- Share Button --}}
                    <button @click="window.dispatchEvent(new CustomEvent('open-share-modal'))" class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center sm:justify-start gap-1 sm:gap-2 text-sm sm:text-base font-medium">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C9.589 12.438 10 11.166 10 9.5c0-1.933-.5-3.5-1.5-3.5S7 7.567 7 9.5c0 1.666.411 2.938 1.316 3.842m0 0h6.632m-6.632 0A7.001 7.001 0 0121 12a7 7 0 01-7 7m0 0H4.684m6.632 0a7.00097 7.00097 0 01-1.318-13.843"></path>
                        </svg>
                        <span class="hidden sm:inline">Share</span>
                    </button>

                    {{-- Customize Button --}}
                    <div class="flex-1 sm:flex-none">
                        @livewire('project.tab-customizer', ['projectId' => $this->project->id])
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab Navigation (Dynamic based on user preferences) --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
        <div class="px-4 sm:px-6 lg:px-8">
            <nav class="flex space-x-1" aria-label="Tabs">
                @php
                    $tabIcons = [
                        'board' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2',
                        'overview' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                        'list' => 'M4 6h16M4 10h16M4 14h16M4 18h16',
                        'backlog' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4',
                        'sprint' => 'M13 10V3L4 14h7v7l9-11h-7z',
                        'dashboard' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                        'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                        'wiki' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                        'gantt' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                        'chat' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                        'client-wiki' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                        'time-tracking' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                        'reports' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                        'milestones' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
                        'budget' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    ];
                    $tabNames = [
                        'board' => 'Board',
                        'overview' => 'Overview',
                        'list' => 'List',
                        'backlog' => 'Backlog',
                        'sprint' => 'Sprints',
                        'dashboard' => 'Dashboard',
                        'calendar' => 'Calendar',
                        'wiki' => 'Wiki',
                        'gantt' => 'Gantt',
                        'chat' => 'Chat',
                        'client-wiki' => 'Client Wiki',
                    ];
                @endphp

                @foreach($tabOrder as $tabKey)
                    @if(in_array($tabKey, $enabledTabs))
                        <button wire:click="switchTab('{{ $tabKey }}')" 
                            class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $activeTab === $tabKey ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tabIcons[$tabKey] ?? '' }}"></path>
                                </svg>
                                <span>{{ $tabNames[$tabKey] ?? ucfirst($tabKey) }}</span>
                            </div>
                        </button>
                    @endif
                @endforeach
            </nav>
        </div>
    </div>

    {{-- Tab Content --}}
    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if($activeTab === 'board')
            @livewire('project.board-view', ['projectId' => $projectId])
        @elseif($activeTab === 'overview')
            @livewire('project.overview-view', ['projectId' => $projectId])
        @elseif($activeTab === 'list')
            @livewire('project.list-view', ['projectId' => $projectId])
        @elseif($activeTab === 'backlog')
            @include('livewire.project.backlog-view')
        @elseif($activeTab === 'sprint')
            @livewire('project.sprint-view', ['projectId' => $projectId])
        @elseif($activeTab === 'dashboard')
            @livewire('project.dashboard-view', ['projectId' => $projectId])
        @elseif($activeTab === 'calendar')
            @livewire('project.calendar-view', ['projectId' => $projectId])
        @elseif($activeTab === 'wiki')
            @livewire('project.wiki-view', ['projectId' => $projectId])
        @elseif($activeTab === 'client-wiki')
            @livewire('project.client-wiki-view', ['projectId' => $projectId])
        @elseif($activeTab === 'gantt')
            @livewire('project.gantt-view', ['projectId' => $projectId])
        @elseif($activeTab === 'chat')
            @livewire('project.chat-view', ['projectId' => $projectId])
        @elseif($activeTab === 'time-tracking')
            @livewire('project.time-tracking-view', ['projectId' => $projectId])
        @elseif($activeTab === 'reports')
            @livewire('project.reports-view', ['projectId' => $projectId])
        @elseif($activeTab === 'milestones')
            @livewire('project.milestones-view', ['projectId' => $projectId])
        @elseif($activeTab === 'budget')
            @livewire('project.budget-view', ['projectId' => $projectId])
        @endif
    </div>
</div>
  {{-- Create Ticket Modal --}}
<x-filament::modal id="create-ticket" width="7xl">

    <x-slot name="heading">
        Create Ticket
    </x-slot>

    <form wire:submit.prevent="createTicket">

        {{ $this->form }}

        <div class="mt-4 flex justify-end gap-3">
            <x-filament::button type="submit">
                Save Ticket
            </x-filament::button>

            <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'create-ticket' })">
                Cancel
            </x-filament::button>
        </div>

    </form>

</x-filament::modal>
