<div class="space-y-4 sm:space-y-6">
    {{-- Header with Help Sidebar --}}
    <div class="px-3 sm:px-4 md:px-6">
        <h2 class="text-base sm:text-lg md:text-xl font-semibold text-gray-900 dark:text-white flex items-center">
            Dashboard
            <x-help-sidebar 
                title="Project Dashboard"
                description="Comprehensive project analytics and performance metrics"
                :features="[
                    'Key project metrics and KPIs',
                    'Ticket status distribution',
                    'Priority breakdown',
                    'Team productivity metrics',
                    'Recent activity feed',
                    'Sprint progress tracking',
                    'Resource utilization',
                    'Trend analysis'
                ]"
                :benefits="[
                    'Quick project health check',
                    'Identify trends and patterns',
                    'Monitor team performance',
                    'Make data-driven decisions',
                    'Track progress over time',
                    'Improve visibility'
                ]"
                implementation="<p>1. View key metrics at the top</p><p>2. Check ticket distribution</p><p>3. Review team performance</p><p>4. Analyze trends</p><p>5. Monitor sprint progress</p><p>6. Export reports</p>"
                :examples="[
                    ['title' => 'Project Health', 'description' => 'See overall project status and metrics'],
                    ['title' => 'Team Performance', 'description' => 'Check individual and team productivity'],
                    ['title' => 'Trend Analysis', 'description' => 'Identify patterns in project data']
                ]"
            />
        </h2>
    </div>

    {{-- Stats Overview --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        @php
            $totalTickets = $this->project->tickets()->count();
            $openTickets = $this->project->tickets()->whereHas('status', function ($q) { $q->where('name', 'Open'); })->count();
            $completedTickets = $this->project->tickets()->whereHas('status', function ($q) { $q->where('name', 'Completed'); })->count();
            $teamMembers = $this->project->users()->count() + 1;
            $completionPercentage = $totalTickets > 0 ? round(($completedTickets / $totalTickets) * 100) : 0;
            $activeSprints = $this->project->sprints()
                ->where('starts_at', '<=', now()->toDateString())
                ->where('ends_at', '>=', now()->toDateString())
                ->count();
        @endphp

        {{-- Total Tickets --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Tickets</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $totalTickets }}</p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m-4 4v2m4 4v2M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2m-4 0V3a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V4a1 1 0 00-1-1h-2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Open Tickets --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Open Tickets</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $openTickets }}</p>
                </div>
                <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Completed Tickets --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Completed</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $completedTickets }}</p>
                </div>
                <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Completion Percentage --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Progress</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $completionPercentage }}%</p>
                </div>
                <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Team Members --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Team Members</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $teamMembers }}</p>
                </div>
                <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm0 0h6v-2a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Active Sprints --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Active Sprints</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $activeSprints }}</p>
                </div>
                <div class="p-3 bg-pink-100 dark:bg-pink-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Health Score Section --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $inProgressTickets = $this->project->tickets()->whereHas('status', function ($q) { $q->where('name', 'In Progress'); })->count();
            $completionRate = $totalTickets > 0 ? ($completedTickets / $totalTickets) * 100 : 0;
            $progressRate = $totalTickets > 0 ? (($inProgressTickets + $completedTickets) / $totalTickets) * 100 : 0;
            $openRate = $totalTickets > 0 ? ($openTickets / $totalTickets) * 100 : 0;
            $healthScore = ($completionRate * 0.4) + ($progressRate * 0.35) + ((100 - $openRate) * 0.25);
            $healthScore = min(100, max(0, round($healthScore)));
            $healthStatus = $healthScore >= 80 ? 'Excellent' : ($healthScore >= 60 ? 'Good' : ($healthScore >= 40 ? 'Fair' : 'Needs Attention'));
            $healthColor = $healthScore >= 80 ? '#10b981' : ($healthScore >= 60 ? '#3b82f6' : ($healthScore >= 40 ? '#f59e0b' : '#ef4444'));
        @endphp

        {{-- Health Score --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Health Score</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $healthScore }}%</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $healthStatus }}</p>
                </div>
                <div class="p-3 rounded-lg" style="background-color: {{ $healthColor }}20">
                    <svg class="w-6 h-6" style="color: {{ $healthColor }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Completion Rate --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Completion Rate</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ round($completionRate) }}%</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $completedTickets }} completed</p>
                </div>
                <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Progress Rate --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Progress Rate</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ round($progressRate) }}%</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">In progress + Completed</p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Open Rate --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Open Tickets %</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ round($openRate) }}%</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $openTickets }} open</p>
                </div>
                <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts and Tables --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Tickets by Status --}}
        <div class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tickets by Status</h3>
            @php
                $statusData = $this->project->tickets()
                    ->select('ticket_statuses.name', \DB::raw('count(*) as count'))
                    ->join('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
                    ->groupBy('ticket_statuses.name')
                    ->get();
            @endphp
            <div class="space-y-3">
                @forelse($statusData as $status)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $status->name }}</span>
                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium rounded">{{ $status->count }}</span>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No tickets yet</p>
                @endforelse
            </div>
        </div>

        {{-- Tickets by Priority --}}
        <div class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tickets by Priority</h3>
            @php
                $priorityData = $this->project->tickets()
                    ->select('ticket_priorities.name', \DB::raw('count(*) as count'))
                    ->join('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
                    ->groupBy('ticket_priorities.name')
                    ->get();
            @endphp
            <div class="space-y-3">
                @forelse($priorityData as $priority)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $priority->name }}</span>
                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium rounded">{{ $priority->count }}</span>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No tickets yet</p>
                @endforelse
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Stats</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Backlog Items</span>
                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium rounded">{{ $this->project->backlogItems()->count() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Sprints</span>
                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium rounded">{{ $this->project->sprints()->count() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Wiki Pages</span>
                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium rounded">{{ $this->project->wikiPages()->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- AI Insights Section --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">AI Insights & Recommendations</h3>
            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM15.657 14.243a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM11 17a1 1 0 102 0v-1a1 1 0 10-2 0v1zM5.757 15.657a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM2 10a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.757 4.343a1 1 0 00-1.414 1.414l.707.707a1 1 0 001.414-1.414l.707-.707z"></path>
            </svg>
        </div>
        <div class="space-y-3">
            @php
                $weeklyCompleted = $this->project->tickets()
                    ->whereHas('status', function ($q) { $q->where('name', 'Completed'); })
                    ->where('updated_at', '>=', now()->subDays(7))
                    ->count();
                $criticalTickets = $this->project->tickets()
                    ->whereHas('priority', function ($q) { $q->where('name', 'Critical'); })
                    ->count();
                $overdueTickets = 0; // due_date column doesn't exist in tickets table
            @endphp

            @if($totalTickets === 0)
                <div class="p-4 rounded-lg border-l-4 bg-blue-50 dark:bg-blue-900/20 border-blue-500">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 mt-0.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h4 class="font-semibold text-blue-900 dark:text-blue-100">Project Started</h4>
                            <p class="text-sm mt-1 text-blue-800 dark:text-blue-200">No tickets created yet. Start by creating your first ticket to begin tracking work.</p>
                        </div>
                    </div>
                </div>
            @elseif($completedTickets === $totalTickets)
                <div class="p-4 rounded-lg border-l-4 bg-green-50 dark:bg-green-900/20 border-green-500">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 mt-0.5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h4 class="font-semibold text-green-900 dark:text-green-100">Project Complete! 🎉</h4>
                            <p class="text-sm mt-1 text-green-800 dark:text-green-200">All tickets have been completed. Great work!</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="p-4 rounded-lg border-l-4 bg-blue-50 dark:bg-blue-900/20 border-blue-500">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 mt-0.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                        </svg>
                        <div>
                            <h4 class="font-semibold text-blue-900 dark:text-blue-100">Project Progress</h4>
                            <p class="text-sm mt-1 text-blue-800 dark:text-blue-200">{{ round($completionRate) }}% complete with {{ $totalTickets - $completedTickets }} tickets remaining.</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($criticalTickets > 0)
                <div class="p-4 rounded-lg border-l-4 bg-red-50 dark:bg-red-900/20 border-red-500">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 mt-0.5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h4 class="font-semibold text-red-900 dark:text-red-100">Critical Tickets Pending</h4>
                            <p class="text-sm mt-1 text-red-800 dark:text-red-200">You have {{ $criticalTickets }} critical ticket(s) that need immediate attention.</p>
                        </div>
                    </div>
                </div>
            @endif


            @if($weeklyCompleted > 0)
                <div class="p-4 rounded-lg border-l-4 bg-green-50 dark:bg-green-900/20 border-green-500">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 mt-0.5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <div>
                            <h4 class="font-semibold text-green-900 dark:text-green-100">Weekly Velocity</h4>
                            <p class="text-sm mt-1 text-green-800 dark:text-green-200">{{ $weeklyCompleted }} ticket(s) completed this week. Keep up the momentum!</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Latest Tickets Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Latest Tickets</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ticket</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Assigned To</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($this->project->tickets()->with(['status', 'priority', 'responsible'])->latest()->limit(5)->get() as $ticket)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('filament.resources.tickets.share', $ticket->code) }}" target="_blank" 
                                   class="text-primary-600 dark:text-primary-400 hover:underline font-medium">
                                    {{ $ticket->code }}
                                </a>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $ticket->name }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full" 
                                      style="background-color: {{ $ticket->status->color }}20; color: {{ $ticket->status->color }}">
                                    {{ $ticket->status->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full" 
                                      style="background-color: {{ $ticket->priority->color }}20; color: {{ $ticket->priority->color }}">
                                    {{ $ticket->priority->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($ticket->responsible)
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $ticket->responsible->avatar_url }}" alt="{{ $ticket->responsible->name }}" 
                                             class="w-6 h-6 rounded-full object-cover">
                                        <span class="text-sm text-gray-900 dark:text-white">{{ $ticket->responsible->name }}</span>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Unassigned</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                No tickets yet. Create your first ticket to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
