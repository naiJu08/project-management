{{-- Quick Info Sidebar --}}
<div class="space-y-4">
    {{-- Status Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-2">Status</p>
        <div class="flex items-center gap-2">
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
    </div>

    {{-- Priority Card --}}
    @if($ticket->priority)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-2">Priority</p>
            <span class="px-3 py-1 text-sm font-semibold rounded-full"
                  :class="@switch($ticket->priority->name)
                      @case('Critical') bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 @break
                      @case('High') bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 @break
                      @case('Medium') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 @break
                      @default bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                  @endswitch">
                {{ $ticket->priority->name }}
            </span>
        </div>
    @endif

    {{-- Progress Card --}}
    @if($ticket->estimated_hours)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-2">Progress</p>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ round($progressPercentage) }}%</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ round($totalLoggedHours) }}h / {{ $ticket->estimated_hours }}h</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-blue-600 dark:bg-blue-500 h-2 rounded-full transition-all" style="width: {{ min(100, $progressPercentage) }}%"></div>
                </div>
            </div>
        </div>
    @endif

    {{-- Dates Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-3">Dates</p>
        <div class="space-y-2 text-sm">
            @if($ticket->start_date)
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Start</p>
                    <p class="text-gray-900 dark:text-white font-medium">{{ $ticket->start_date->format('M d') }}</p>
                </div>
            @endif
            @if($ticket->due_date)
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Due</p>
                    <p class="text-gray-900 dark:text-white font-medium"
                       :class="@if(now()->isAfter($ticket->due_date)) text-red-600 dark:text-red-400 @endif">
                        {{ $ticket->due_date->format('M d') }}
                        @if(now()->isAfter($ticket->due_date))
                            <span class="text-xs text-red-600 dark:text-red-400">(Overdue)</span>
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Assignment Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-3">Assignment</p>
        <div class="space-y-2">
            @if($ticket->owner)
                <div class="flex items-center gap-2">
                    <img src="{{ $ticket->owner->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($ticket->owner->name) }}" 
                         alt="{{ $ticket->owner->name }}" class="w-6 h-6 rounded-full">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Owner</p>
                        <p class="text-sm text-gray-900 dark:text-white font-medium truncate">{{ $ticket->owner->name }}</p>
                    </div>
                </div>
            @endif
            @if($ticket->responsible)
                <div class="flex items-center gap-2">
                    <img src="{{ $ticket->responsible->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($ticket->responsible->name) }}" 
                         alt="{{ $ticket->responsible->name }}" class="w-6 h-6 rounded-full">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Assigned</p>
                        <p class="text-sm text-gray-900 dark:text-white font-medium truncate">{{ $ticket->responsible->name }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Sprint Card --}}
    @if($ticket->sprint)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-2">Sprint</p>
            <a href="#" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium">
                {{ $ticket->sprint->name }}
            </a>
        </div>
    @endif

    {{-- Type Card --}}
    @if($ticket->type)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-2">Type</p>
            <p class="text-sm text-gray-900 dark:text-white font-medium">{{ $ticket->type->name }}</p>
        </div>
    @endif

    {{-- Relationships Summary --}}
    @if($ticket->relations->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-2">Relationships</p>
            <p class="text-sm text-gray-900 dark:text-white font-medium">{{ $ticket->relations->count() }} linked</p>
        </div>
    @endif

    {{-- Comments Summary --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-2">Comments</p>
        <p class="text-sm text-gray-900 dark:text-white font-medium">{{ $ticket->comments->count() }} comment{{ $ticket->comments->count() !== 1 ? 's' : '' }}</p>
    </div>

    {{-- Time Summary --}}
    @if($ticket->estimated_hours)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-2">Time</p>
            <div class="space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Logged:</span>
                    <span class="text-gray-900 dark:text-white font-medium">{{ round($totalLoggedHours, 1) }}h</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Remaining:</span>
                    <span class="font-medium {{ $remainingHours !== null && $remainingHours < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                        {{ $remainingHours !== null ? round($remainingHours, 1) : '—' }}h
                    </span>
                </div>
            </div>
        </div>
    @endif

    {{-- Blocked Status --}}
    @if($ticket->is_blocked)
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <p class="text-xs font-semibold text-red-800 dark:text-red-200 mb-1">🚫 BLOCKED</p>
            <p class="text-xs text-red-700 dark:text-red-300">{{ $ticket->blocked_reason ?? 'No reason provided' }}</p>
        </div>
    @endif
</div>
