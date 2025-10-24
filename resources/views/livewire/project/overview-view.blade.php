<div class="space-y-6">
    {{-- Project Info Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Project Information</h2>
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Owner</label>
                <p class="text-gray-900 dark:text-white">{{ $this->project->owner->name }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                <p class="flex items-center">
                    <span class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $this->project->status->color }}"></span>
                    <span class="text-gray-900 dark:text-white">{{ $this->project->status->name }}</span>
                </p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</label>
                <p class="text-gray-900 dark:text-white capitalize">{{ $this->project->type }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</label>
                <p class="text-gray-900 dark:text-white">{{ $this->project->created_at->format('M d, Y') }}</p>
            </div>
        </div>
        @if($this->project->description)
            <div class="mt-4">
                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</label>
                <div class="prose dark:prose-invert max-w-none mt-2">
                    {!! $this->project->description !!}
                </div>
            </div>
        @endif
    </div>

    {{-- Progress Bar --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Project Completion</h3>
            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $stats['completion_percentage'] }}%</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
            <div class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: {{ $stats['completion_percentage'] }}%"></div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
            {{ $stats['completed_tickets'] }} of {{ $stats['total_tickets'] }} tasks completed
        </p>
    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tasks</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total_tickets'] }}</p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">In Progress</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['in_progress_tickets'] }}</p>
                </div>
                <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                    <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['completed_tickets'] }}</p>
                </div>
                <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Open Tasks</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['open_tickets'] }}</p>
                </div>
                <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-lg">
                    <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Additional Stats Row --}}
    <div class="grid grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Team Members</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['team_members'] }}</p>
                </div>
                <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Sprints</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total_sprints'] }}</p>
                </div>
                <div class="p-3 bg-indigo-100 dark:bg-indigo-900 rounded-lg">
                    <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Epics</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total_epics'] }}</p>
                </div>
                <div class="p-3 bg-pink-100 dark:bg-pink-900 rounded-lg">
                    <svg class="w-8 h-8 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Wiki Pages</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['wiki_pages'] }}</p>
                </div>
                <div class="p-3 bg-teal-100 dark:bg-teal-900 rounded-lg">
                    <svg class="w-8 h-8 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Team Members --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Team Members</h2>
        <div class="grid grid-cols-3 gap-4">
            <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                    {{ substr($this->project->owner->name, 0, 1) }}
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $this->project->owner->name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Owner</p>
                </div>
            </div>
            @foreach($this->project->users as $user)
                <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="w-10 h-10 bg-gray-500 rounded-full flex items-center justify-center text-white font-semibold">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->pivot->role ?? 'Member' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6">
        {{-- Active Sprint --}}
        @if($stats['active_sprint'])
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Active Sprint</h2>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $stats['active_sprint']->name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ $stats['active_sprint']->starts_at->format('M d') }} - {{ $stats['active_sprint']->ends_at->format('M d, Y') }}
                        </p>
                    </div>
                    @if($stats['active_sprint']->remaining)
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['active_sprint']->remaining }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">days remaining</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Recent Activity --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Recent Activity</h2>
            <div class="space-y-3">
                @forelse($stats['recent_tickets'] as $ticket)
                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-700 last:border-0">
                        <div class="flex-1">
                            <a href="{{ route('filament.resources.tickets.view', $ticket->id) }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                {{ $ticket->code }}
                            </a>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ Str::limit($ticket->name, 40) }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            @if($ticket->status)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium" style="background-color: {{ $ticket->status->color }}20; color: {{ $ticket->status->color }}">
                                    {{ $ticket->status->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">No recent activity</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
