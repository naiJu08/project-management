<x-filament::page>
    <div class="space-y-6">
        {{-- Filters and Navigation --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                {{-- Month Navigation --}}
                <div class="flex items-center space-x-4">
                    <button 
                        wire:click="previousMonth"
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $this->getMonthName() }}
                    </h2>
                    <button 
                        wire:click="nextMonth"
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

                {{-- Employee Filter --}}
                <div class="w-full md:w-64">
                    <select 
                        wire:model="selectedUserId"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">All Employees</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Statistics --}}
        @php
            $stats = $this->getStats();
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Days</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_days'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Present</div>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['present'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Absent</div>
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['absent'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Late</div>
                <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['late'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">On Leave</div>
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['on_leave'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Hours</div>
                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['total_hours'] }}h</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Avg Hours</div>
                <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['avg_hours'] }}h</div>
            </div>
        </div>

        {{-- Calendar Grid --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            {{-- Day Headers --}}
            <div class="grid grid-cols-7 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                    <div class="p-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                        {{ $day }}
                    </div>
                @endforeach
            </div>

            {{-- Calendar Days --}}
            <div class="grid grid-cols-7">
                @php
                    $firstDay = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1);
                    $startPadding = $firstDay->dayOfWeek;
                @endphp

                {{-- Empty cells before month starts --}}
                @for($i = 0; $i < $startPadding; $i++)
                    <div class="min-h-32 p-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900"></div>
                @endfor

                {{-- Calendar days with data --}}
                @foreach($calendarData as $dateKey => $dayData)
                    @php
                        $isToday = $dayData['date']->isToday();
                        $records = $dayData['records'];
                        $hasRecords = $records->isNotEmpty();
                    @endphp
                    <div class="min-h-32 p-2 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition
                        {{ $isToday ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-sm font-semibold {{ $isToday ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-white' }}">
                                {{ $dayData['date']->day }}
                            </span>
                            @if($hasRecords)
                                <span class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-0.5 rounded-full">
                                    {{ $records->count() }}
                                </span>
                            @endif
                        </div>

                        {{-- Attendance Records --}}
                        <div class="space-y-1">
                            @foreach($records as $record)
                                <div class="text-xs p-1.5 rounded cursor-pointer
                                    @if($record->status === 'present') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                    @elseif($record->status === 'late') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200
                                    @elseif($record->status === 'absent') bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200
                                    @elseif($record->status === 'on-leave') bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200
                                    @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300
                                    @endif"
                                    x-data="{ open: false }"
                                    @click="open = true">
                                    <div class="font-semibold truncate">{{ $record->user->name }}</div>
                                    <div class="flex justify-between items-center">
                                        <span>{{ $record->check_in?->format('H:i') ?? '—' }}</span>
                                        <span>{{ number_format($record->work_hours ?? 0, 1) }}h</span>
                                    </div>

                                    {{-- Detail Modal --}}
                                    <div x-show="open" 
                                         @click.away="open = false"
                                         x-cloak
                                         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
                                         style="display: none;">
                                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6" @click.stop>
                                            <div class="flex justify-between items-start mb-4">
                                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                                    Attendance Details
                                                </h3>
                                                <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="space-y-3">
                                                <div>
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">Employee:</span>
                                                    <span class="text-sm font-semibold text-gray-900 dark:text-white ml-2">{{ $record->user->name }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">Date:</span>
                                                    <span class="text-sm font-semibold text-gray-900 dark:text-white ml-2">{{ $record->date->format('M d, Y') }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">Check In:</span>
                                                    <span class="text-sm font-semibold text-gray-900 dark:text-white ml-2">{{ $record->check_in?->format('h:i A') ?? '—' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">Check Out:</span>
                                                    <span class="text-sm font-semibold text-gray-900 dark:text-white ml-2">{{ $record->check_out?->format('h:i A') ?? '—' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Hours:</span>
                                                    <span class="text-sm font-semibold text-gray-900 dark:text-white ml-2">{{ number_format($record->total_hours ?? 0, 1) }}h</span>
                                                </div>
                                                <div>
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">Break Time:</span>
                                                    <span class="text-sm font-semibold text-yellow-600 dark:text-yellow-400 ml-2">{{ number_format($record->break_duration ?? 0, 1) }}h</span>
                                                </div>
                                                <div>
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">Work Hours:</span>
                                                    <span class="text-sm font-semibold text-green-600 dark:text-green-400 ml-2">{{ number_format($record->work_hours ?? 0, 1) }}h</span>
                                                </div>
                                                <div>
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">Status:</span>
                                                    <span class="text-sm font-semibold ml-2 px-2 py-1 rounded
                                                        @if($record->status === 'present') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                        @elseif($record->status === 'late') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                        @elseif($record->status === 'absent') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                                        @endif">
                                                        {{ ucfirst($record->status) }}
                                                    </span>
                                                </div>
                                                @if($record->notes)
                                                    <div>
                                                        <span class="text-sm text-gray-600 dark:text-gray-400">Notes:</span>
                                                        <p class="text-sm text-gray-900 dark:text-white mt-1">{{ $record->notes }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-filament::page>
