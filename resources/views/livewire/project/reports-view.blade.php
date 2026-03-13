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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Range</label>
                <div class="flex gap-2">
                    <button wire:click="setDateRange(7)" class="px-3 py-2 text-sm {{ $dateRange === '7' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }} rounded-lg transition-colors">7d</button>
                    <button wire:click="setDateRange(30)" class="px-3 py-2 text-sm {{ $dateRange === '30' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }} rounded-lg transition-colors">30d</button>
                    <button wire:click="setDateRange(90)" class="px-3 py-2 text-sm {{ $dateRange === '90' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }} rounded-lg transition-colors">90d</button>
                    <button wire:click="setDateRange(365)" class="px-3 py-2 text-sm {{ $dateRange === '365' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }} rounded-lg transition-colors">1y</button>
                </div>
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
                        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-2">{{ $overviewStats['total_hours'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Billable: {{ $overviewStats['billable_hours'] }}</p>
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
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Active</p>
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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Hours Distribution</h3>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Billable</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $overviewStats['total_hours'] > 0 ? round(($overviewStats['billable_hours'] / $overviewStats['total_hours']) * 100) : 0 }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $overviewStats['total_hours'] > 0 ? round(($overviewStats['billable_hours'] / $overviewStats['total_hours']) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
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
