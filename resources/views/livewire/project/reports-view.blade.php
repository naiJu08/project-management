<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="px-3 sm:px-4 md:px-6">
            <h2 class="text-base sm:text-lg md:text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                Reports & Analytics
                <x-help-sidebar 
                    title="Reports & Analytics"
                    description="Comprehensive project metrics and team performance insights"
                    :features="[
                        'Project Overview - Key metrics and completion rates',
                        'Ticket Analytics - Status, priority, and overdue tracking',
                        'Hours & Productivity - Billable breakdown and team contributors',
                        'Team Performance - Individual productivity scores',
                        'Sprint Analytics - Sprint progress and completion rates',
                        'Date range filtering (7d, 30d, 90d, 1y)',
                        'Visual charts and progress bars',
                        'Export reports for presentations'
                    ]"
                    :benefits="[
                        'Monitor project health and progress',
                        'Identify bottlenecks and risks early',
                        'Track team productivity trends',
                        'Make data-driven decisions',
                        'Generate stakeholder reports',
                        'Improve resource allocation',
                        'Benchmark team performance'
                    ]"
                    implementation="<p class='mb-2'><strong>How it works in this software:</strong></p><p>1. Select report type from dropdown (Overview, Tickets, Hours, Team, Sprint)</p><p>2. Choose date range (7d, 30d, 90d, 1y)</p><p>3. View metrics and charts automatically calculated</p><p>4. Click on metrics to drill down for details</p><p>5. Export reports for sharing with stakeholders</p>"
                    :examples="[
                        ['title' => 'Project Overview', 'description' => 'See total tickets, completion rate, team size, and active sprints'],
                        ['title' => 'Team Performance', 'description' => 'Compare individual productivity scores and hours logged'],
                        ['title' => 'Sprint Analytics', 'description' => 'Track sprint progress and completion rates over time']
                    ]"
                />
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Project insights and performance metrics for {{ $this->project->name }}</p>
        </div>
    </div>

    {{-- Report Type Selector & Date Range --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Report Type</label>
                <select wire:model="selectedReport" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    @foreach($reportTypes as $key => $name)
                        <option value="{{ $key }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="date-range" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Range</label>
                <div class="flex gap-2" role="group" aria-label="Date range selection">
                    <button 
                        wire:click="setDateRange(7)" 
                        type="button"
                        role="button"
                        aria-pressed="{{ $dateRange === '7' ? 'true' : 'false' }}"
                        aria-label="Last 7 days"
                        tabindex="0"
                        class="px-3 py-2 text-sm {{ $dateRange === '7' ? 'bg-blue-600 text-white ring-2 ring-blue-600 ring-offset-2' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }} rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                        @keydown.enter="$wire.call('setDateRange', 7)"
                        @keydown.space.prevent="$wire.call('setDateRange', 7)"
                    >
                        7d
                    </button>
                    <button 
                        wire:click="setDateRange(30)" 
                        type="button"
                        role="button"
                        aria-pressed="{{ $dateRange === '30' ? 'true' : 'false' }}"
                        aria-label="Last 30 days"
                        tabindex="0"
                        class="px-3 py-2 text-sm {{ $dateRange === '30' ? 'bg-blue-600 text-white ring-2 ring-blue-600 ring-offset-2' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }} rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                        @keydown.enter="$wire.call('setDateRange', 30)"
                        @keydown.space.prevent="$wire.call('setDateRange', 30)"
                    >
                        30d
                    </button>
                    <button 
                        wire:click="setDateRange(90)" 
                        type="button"
                        role="button"
                        aria-pressed="{{ $dateRange === '90' ? 'true' : 'false' }}"
                        aria-label="Last 90 days"
                        tabindex="0"
                        class="px-3 py-2 text-sm {{ $dateRange === '90' ? 'bg-blue-600 text-white ring-2 ring-blue-600 ring-offset-2' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }} rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                        @keydown.enter="$wire.call('setDateRange', 90)"
                        @keydown.space.prevent="$wire.call('setDateRange', 90)"
                    >
                        90d
                    </button>
                    <button 
                        wire:click="setDateRange(365)" 
                        type="button"
                        role="button"
                        aria-pressed="{{ $dateRange === '365' ? 'true' : 'false' }}"
                        aria-label="Last 365 days"
                        tabindex="0"
                        class="px-3 py-2 text-sm {{ $dateRange === '365' ? 'bg-blue-600 text-white ring-2 ring-blue-600 ring-offset-2' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }} rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                        @keydown.enter="$wire.call('setDateRange', 365)"
                        @keydown.space.prevent="$wire.call('setDateRange', 365)"
                    >
                        1y
                    </button>
                </div>
                @if($startDate && $endDate)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" aria-live="polite">
                        Showing data from {{ \Carbon\Carbon::parse($startDate)->format('M j, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M j, Y') }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Overview Report --}}
    @if($selectedReport === 'overview')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Tickets</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $overviewStats['total_tickets'] }}</p>
                    </div>
                    <svg class="w-12 h-12 text-blue-500 opacity-20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Completion Rate</p>
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-2">{{ $overviewStats['completion_rate'] }}%</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $overviewStats['completed_tickets'] }}/{{ $overviewStats['total_tickets'] }}</p>
                    </div>
                    <svg class="w-12 h-12 text-green-500 opacity-20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Hours</p>
                        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-2">
                            {{ $overviewStats['total_hours'] > 0 ? number_format($overviewStats['total_hours'], 1) : '0' }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            @if($overviewStats['total_hours'] > 0)
                                Billable: {{ number_format($overviewStats['billable_hours'], 1) }}
                            @else
                                No hours logged
                            @endif
                        </p>
                    </div>
                    <svg class="w-12 h-12 text-blue-500 opacity-20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Team Members</p>
                        <p class="text-3xl font-bold text-purple-600 dark:text-purple-400 mt-2">{{ $overviewStats['team_members'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            @if($overviewStats['team_members'] > 0)
                                {{ $overviewStats['team_members'] }} member{{ $overviewStats['team_members'] > 1 ? 's' : '' }}
                            @else
                                No members assigned
                            @endif
                        </p>
                    </div>
                    <svg class="w-12 h-12 text-purple-500 opacity-20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 4.354a4 4 0 110 8.646 4 4 0 010-8.646M12 14.5a8 8 0 100 3.5"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sprint Summary</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Active Sprints</span>
                        <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $overviewStats['active_sprints'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Completed Sprints</span>
                        <span class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $overviewStats['completed_sprints'] }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Hours Distribution</h3>
                    @if($overviewStats['total_hours'] > 0)
                        <button 
                            wire:click="$toggle('showHoursDetails')" 
                            class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex items-center gap-1"
                            aria-expanded="{{ $showHoursDetails ?? 'false' }}"
                        >
                            <span>Details</span>
                            <svg class="w-4 h-4 transition-transform {{ $showHoursDetails ?? false ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    @endif
                </div>
                
                <div class="space-y-3">
                    @if($overviewStats['total_hours'] > 0)
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Billable</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ round(($overviewStats['billable_hours'] / $overviewStats['total_hours']) * 100) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full transition-all duration-300" style="width: {{ round(($overviewStats['billable_hours'] / $overviewStats['total_hours']) * 100) }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $overviewStats['billable_hours'] }} of {{ $overviewStats['total_hours'] }} hours
                            </p>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Non-Billable</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ round((($overviewStats['total_hours'] - $overviewStats['billable_hours']) / $overviewStats['total_hours']) * 100) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-orange-500 h-2 rounded-full transition-all duration-300" style="width: {{ round((($overviewStats['total_hours'] - $overviewStats['billable_hours']) / $overviewStats['total_hours']) * 100) }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ round($overviewStats['total_hours'] - $overviewStats['billable_hours'], 2) }} of {{ $overviewStats['total_hours'] }} hours
                            </p>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">No hours logged in the selected period</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Try adjusting the date range or log some time entries</p>
                        </div>
                    @endif
                </div>

                {{-- Detailed Breakdown Section --}}
                @if($showHoursDetails && $overviewStats['total_hours'] > 0)
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 space-y-6">
                        {{-- Team Member Breakdown --}}
                        <div>
                            <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 8.646 4 4 0 010-8.646M12 14.5a8 8 0 100 3.5"></path>
                                </svg>
                                By Team Member
                            </h4>
                            <div class="space-y-2">
                                @forelse($hoursDistributionDetails['by_team_member']->take(5) as $member)
                                    <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $member['name'] }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $member['billable_hours'] }}h billable / {{ $member['non_billable_hours'] }}h non-billable
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $member['total_hours'] }}h</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $member['billable_percentage'] }}% billable</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">No team member data available</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Category Breakdown --}}
                        <div>
                            <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                By Category
                            </h4>
                            <div class="space-y-2">
                                @forelse($hoursDistributionDetails['by_category'] as $category => $data)
                                    <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ ucfirst($category ?: 'Uncategorized') }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $data['billable_hours'] }}h billable / {{ $data['non_billable_hours'] }}h non-billable
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $data['total_hours'] }}h</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $data['billable_percentage'] }}% billable</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">No category data available</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Daily Trend --}}
                        @if($hoursDistributionDetails['by_date']->count() > 1)
                            <div>
                                <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    Recent Daily Trend
                                </h4>
                                <div class="space-y-1 max-h-32 overflow-y-auto">
                                    @foreach($hoursDistributionDetails['by_date']->take(-7)->reverse() as $date => $data)
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($date)->format('M j') }}</span>
                                            <div class="flex items-center gap-2">
                                                <span class="text-green-600 dark:text-green-400">{{ $data['billable_hours'] }}h</span>
                                                <span class="text-gray-400">/</span>
                                                <span class="text-orange-600 dark:text-orange-400">{{ $data['non_billable_hours'] }}h</span>
                                                <span class="text-gray-500 dark:text-gray-400">({{ $data['total_hours'] }}h total)</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Tickets Report --}}
    @if($selectedReport === 'tickets')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">By Status</h3>
                <div class="space-y-2">
                    @foreach($ticketStats['by_status'] as $status => $count)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $status }}</span>
                            <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-sm font-medium">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">By Priority</h3>
                <div class="space-y-2">
                    @foreach($ticketStats['by_priority'] as $priority => $count)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $priority }}</span>
                            <span class="px-2 py-1 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded text-sm font-medium">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Metrics</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Avg Resolution Time</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $ticketStats['average_resolution_time'] }} days</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Overdue Tickets</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $ticketStats['overdue_tickets'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Hours Report --}}
    @if($selectedReport === 'hours')
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Hours</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $hoursStats['total_hours'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400">Billable Hours</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-2">{{ $hoursStats['billable_hours'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400">Non-Billable</p>
                <p class="text-3xl font-bold text-orange-600 dark:text-orange-400 mt-2">{{ $hoursStats['non_billable_hours'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400">Daily Average</p>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-2">{{ $hoursStats['daily_average'] }}h</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Hours by Category</h3>
                <div class="space-y-2">
                    @foreach($hoursStats['by_category'] as $category => $hours)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ ucfirst($category) }}</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ round($hours, 2) }}h</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Contributors</h3>
                <div class="space-y-2">
                    @foreach($hoursStats['by_user']->take(5) as $user)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $user['name'] }}</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ round($user['hours'], 2) }}h</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Team Report --}}
    @if($selectedReport === 'team')
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Team Member</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Hours Logged</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Billable</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Tickets</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Completed</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Score</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($teamStats as $member)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $member['name'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $member['hours_logged'] }}h</td>
                                <td class="px-6 py-4 text-sm text-green-600 dark:text-green-400">{{ $member['billable_hours'] }}h</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $member['tickets_assigned'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $member['tickets_completed'] }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $member['productivity_score'] }}%"></div>
                                        </div>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $member['productivity_score'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No team data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Sprint Report --}}
    @if($selectedReport === 'sprints')
        <div class="grid grid-cols-1 gap-4">
            @forelse($sprintStats as $sprint)
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $sprint['name'] }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $sprint['start_date'] }} - {{ $sprint['end_date'] }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $sprint['status'] === 'active' ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' : 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' }}">
                            {{ ucfirst($sprint['status']) }}
                        </span>
                    </div>

                    @if($sprint['goal'])
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Goal: {{ $sprint['goal'] }}</p>
                    @endif

                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Progress</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $sprint['completed_items'] }}/{{ $sprint['total_items'] }} ({{ $sprint['completion_rate'] }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $sprint['completion_rate'] }}%"></div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-8 text-center">
                    <p class="text-gray-500 dark:text-gray-400">No sprints available</p>
                </div>
            @endforelse
        </div>
    @endif
</div>
