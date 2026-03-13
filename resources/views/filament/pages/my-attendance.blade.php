<x-filament::page>
    <div class="space-y-6">
        {{-- Real-time Clock and Timer Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="text-center">
                {{-- Current Time --}}
                <div class="text-6xl font-bold text-gray-900 dark:text-white mb-2" id="currentTime">
                    {{ now()->format('H:i:s') }}
                </div>
                <div class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                    {{ now()->format('l, F j, Y') }}
                </div>

                {{-- Status Badge --}}
                @if($isCheckedIn)
                    <div class="inline-flex items-center space-x-2 mb-6">
                        <span class="flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </span>
                        <span class="text-green-600 dark:text-green-400 font-semibold">
                            @if($isOnBreak)
                                On Break
                            @else
                                Checked In
                            @endif
                        </span>
                    </div>
                @else
                    <div class="inline-flex items-center space-x-2 mb-6">
                        <span class="flex h-3 w-3 rounded-full bg-gray-400"></span>
                        <span class="text-gray-600 dark:text-gray-400 font-semibold">Not Checked In</span>
                    </div>
                @endif

                {{-- Timer Display --}}
                @if($isCheckedIn && !$todayRecord->check_out)
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 rounded-lg p-6 mb-6">
                        <div class="text-sm text-gray-600 dark:text-gray-300 mb-2">Time Elapsed</div>
                        <div class="text-5xl font-mono font-bold text-blue-600 dark:text-blue-400" id="elapsedTime">
                            00:00:00
                        </div>
                    </div>
                @endif

                {{-- Action Buttons --}}
                <div class="flex justify-center space-x-4">
                    @if(!$isCheckedIn)
                        <button 
                            wire:click="checkIn"
                            class="px-8 py-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                            <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Check In
                        </button>
                    @elseif(!$todayRecord->check_out)
                        @if(!$isOnBreak)
                            <button 
                                wire:click="startBreak"
                                class="px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg shadow hover:shadow-lg transition-all duration-200">
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Start Break
                            </button>
                        @else
                            <button 
                                wire:click="endBreak"
                                class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg shadow hover:shadow-lg transition-all duration-200">
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Resume Work
                            </button>
                        @endif

                        <button 
                            wire:click="checkOut"
                            class="px-8 py-4 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                            <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Check Out
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Today's Summary --}}
        @if($todayRecord)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Check In</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $todayRecord->check_in?->format('h:i A') ?? '—' }}
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Check Out</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $todayRecord->check_out?->format('h:i A') ?? '—' }}
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Break Time</div>
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                        {{ number_format($breakDuration, 1) }}h
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Work Hours</div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ number_format($workHours, 1) }}h
                    </div>
                </div>
            </div>
        @endif

        {{-- Recent Attendance --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Attendance</h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check In</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check Out</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Work Hours</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($recentRecords as $record)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $record->date->format('M d, Y') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        {{ $record->check_in?->format('h:i A') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        {{ $record->check_out?->format('h:i A') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($record->work_hours ?? 0, 1) }}h
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            @if($record->status === 'present') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($record->status === 'late') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @elseif($record->status === 'absent') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                            @endif">
                                            {{ ucfirst($record->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No attendance records found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Update current time every second
        setInterval(() => {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { hour12: false });
            document.getElementById('currentTime').textContent = timeString;
        }, 1000);

        // Update elapsed time if checked in
        @if($isCheckedIn && !$todayRecord->check_out)
            const checkInTime = new Date('{{ $todayRecord->check_in }}');
            
            function updateElapsedTime() {
                const now = new Date();
                const diff = now - checkInTime;
                
                const hours = Math.floor(diff / 3600000);
                const minutes = Math.floor((diff % 3600000) / 60000);
                const seconds = Math.floor((diff % 60000) / 1000);
                
                const timeString = 
                    String(hours).padStart(2, '0') + ':' +
                    String(minutes).padStart(2, '0') + ':' +
                    String(seconds).padStart(2, '0');
                
                document.getElementById('elapsedTime').textContent = timeString;
            }
            
            updateElapsedTime();
            setInterval(updateElapsedTime, 1000);
        @endif

        // Auto-refresh page data every 30 seconds
        setInterval(() => {
            @this.call('loadTodayRecord');
        }, 30000);
    </script>
    @endpush
</x-filament::page>
