<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
        <div>
            <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                Calendar
                <x-help-sidebar 
                    title="Project Calendar"
                    description="View project timeline with sprints and milestones"
                    :features="[
                        'Month, week, and day views',
                        'Sprint scheduling',
                        'Milestone tracking',
                        'Ticket deadlines',
                        'Team availability',
                        'Event filtering',
                        'Drag and drop scheduling',
                        'Calendar export'
                    ]"
                    :benefits="[
                        'Plan project timeline',
                        'Coordinate team schedules',
                        'Track important dates',
                        'Avoid scheduling conflicts',
                        'Better resource planning',
                        'Improved visibility'
                    ]"
                    implementation="<p>1. View calendar in month/week/day mode</p><p>2. Click date to see events</p><p>3. Drag events to reschedule</p><p>4. Click event for details</p><p>5. Use filters to focus</p><p>6. Export calendar</p>"
                    :examples="[
                        ['title' => 'Sprint Planning', 'description' => 'Schedule sprint start and end dates'],
                        ['title' => 'Milestone Tracking', 'description' => 'Mark important release dates'],
                        ['title' => 'Deadline Management', 'description' => 'Track ticket and task deadlines']
                    ]"
                />
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Timeline and schedule for {{ $this->project->name }}</p>
        </div>
    </div>

    {{-- View Mode Selector --}}
    <div class="flex gap-2 border-b border-gray-200 dark:border-gray-700">
        <button wire:click="setViewMode('month')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ $viewMode === 'month' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            Month
        </button>
        <button wire:click="setViewMode('week')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ $viewMode === 'week' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            Week
        </button>
        <button wire:click="setViewMode('day')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ $viewMode === 'day' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Day
        </button>
    </div>

    {{-- Calendar Controls --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <button wire:click="previousMonth()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>

        <div class="text-center flex-1">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                {{ \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)->format('F Y') }}
            </h3>
        </div>

        <button wire:click="nextMonth()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>

        <button wire:click="goToToday()" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
            Today
        </button>
    </div>

    {{-- Month View --}}
    @if($viewMode === 'month')
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            {{-- Day Headers --}}
            <div class="grid grid-cols-7 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                    <div class="p-4 text-center font-semibold text-gray-700 dark:text-gray-300 text-sm">
                        {{ $day }}
                    </div>
                @endforeach
            </div>

            {{-- Calendar Days --}}
            <div class="grid grid-cols-7">
                @foreach($calendarDays as $day)
                    @php
                        $isCurrentMonth = $day->month === $currentMonth;
                        $isToday = $day->toDateString() === now()->toDateString();
                        $events = ($getEventsForDate)($day);
                    @endphp
                    <div class="min-h-32 p-2 border border-gray-200 dark:border-gray-700 {{ !$isCurrentMonth ? 'bg-gray-50 dark:bg-gray-900' : 'bg-white dark:bg-gray-800' }} {{ $isToday ? 'ring-2 ring-blue-500' : '' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold {{ $isToday ? 'text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900 px-2 py-1 rounded' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $day->day }}
                            </span>
                        </div>
                        <button wire:click="selectDate('{{ $day->toDateString() }}')" class="w-full text-left text-xs text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 p-1 rounded transition-colors" title="Click to view week/day">
                            View
                        </button>

                        {{-- Events --}}
                        <div class="space-y-1">
                            @forelse($events as $event)
                                <div class="text-xs p-1 rounded truncate {{ $event['type'] === 'sprint' ? 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200' : 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' }}" title="{{ $event['title'] }}">
                                    {{ $event['title'] }}
                                </div>
                            @empty
                                <div class="text-xs text-gray-400 italic">No events</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Week View --}}
    @if($viewMode === 'week' && $selectedDate)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="grid grid-cols-7">
                @foreach($weekDays as $day)
                    @php
                        $isToday = $day->toDateString() === now()->toDateString();
                        $events = ($getEventsForDate)($day);
                    @endphp
                    <div class="border-r border-gray-200 dark:border-gray-700 last:border-r-0">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 {{ $isToday ? 'ring-2 ring-blue-500' : '' }}">
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $day->format('D') }}</div>
                            <div class="text-2xl font-bold {{ $isToday ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $day->day }}
                            </div>
                        </div>
                        <div class="p-4 min-h-96 space-y-2">
                            @forelse($events as $event)
                                <div class="p-2 rounded text-sm {{ $event['type'] === 'sprint' ? 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200' : 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' }}">
                                    <div class="font-semibold">{{ $event['title'] }}</div>
                                    <div class="text-xs opacity-75">{{ $event['type'] }}</div>
                                </div>
                            @empty
                                <div class="text-sm text-gray-400 italic">No events</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif($viewMode === 'week')
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-8 text-center">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <p class="text-gray-600 dark:text-gray-400 mb-2">Click "View" on any date in the month view to see that week</p>
            <p class="text-sm text-gray-500 dark:text-gray-500">Or switch back to Month view to select a date</p>
        </div>
    @endif

    {{-- Day View --}}
    @if($viewMode === 'day' && $selectedDate)
        @php
            $selectedDay = \Carbon\Carbon::parse($selectedDate);
            $dayEvents = ($getEventsForDate)($selectedDay);
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="mb-6">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $selectedDay->format('l, F j, Y') }}
                </h3>
            </div>

            <div class="space-y-4">
                @forelse($dayEvents as $event)
                    <div class="p-4 rounded-lg border-l-4 {{ $event['type'] === 'sprint' ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20' : 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $event['title'] }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ ucfirst($event['type']) }}</p>
                                @if($event['type'] === 'sprint' && $event['data']->goal)
                                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-2">Goal: {{ $event['data']->goal }}</p>
                                @endif
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $event['type'] === 'sprint' ? 'bg-purple-200 dark:bg-purple-800 text-purple-800 dark:text-purple-200' : 'bg-blue-200 dark:bg-blue-800 text-blue-800 dark:text-blue-200' }}">
                                {{ ucfirst($event['type']) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">No events scheduled for this day</p>
                    </div>
                @endforelse
            </div>
        </div>
    @elseif($viewMode === 'day')
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-8 text-center">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-gray-600 dark:text-gray-400 mb-2">Click "View" on any date in the month view to see that day's details</p>
            <p class="text-sm text-gray-500 dark:text-gray-500">Or switch back to Month view to select a date</p>
        </div>
    @endif

    {{-- Legend --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Legend</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-blue-100 dark:bg-blue-900 border border-blue-300 dark:border-blue-700 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Tickets</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-purple-100 dark:bg-purple-900 border border-purple-300 dark:border-purple-700 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Sprints</span>
            </div>
        </div>
    </div>
</div>
