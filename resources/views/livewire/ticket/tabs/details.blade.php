{{-- Description Section --}}
<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Description</h3>
    <div class="prose dark:prose-invert max-w-none">
        @if($ticket->content)
            {!! nl2br(e($ticket->content)) !!}
        @else
            <p class="text-gray-500 dark:text-gray-400">No description provided</p>
        @endif
    </div>
</div>

{{-- Details Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Basic Information --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h3>
        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Type</label>
                <p class="text-gray-900 dark:text-white mt-1">{{ $ticket->type->name ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Priority</label>
                <p class="text-gray-900 dark:text-white mt-1">{{ $ticket->priority->name ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Component</label>
                <p class="text-gray-900 dark:text-white mt-1">{{ $ticket->component ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Severity</label>
                <p class="text-gray-900 dark:text-white mt-1">
                    @if($ticket->severity)
                        <span class="px-2 py-1 text-xs font-semibold rounded"
                              :class="@switch($ticket->severity)
                                  @case('critical') bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 @break
                                  @case('major') bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 @break
                                  @case('minor') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 @break
                                  @default bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200
                              @endswitch">
                            {{ ucfirst($ticket->severity) }}
                        </span>
                    @else
                        N/A
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Scheduling --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Scheduling</h3>
        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Start Date</label>
                <p class="text-gray-900 dark:text-white mt-1">{{ $ticket->start_date?->format('M d, Y') ?? 'Not set' }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Due Date</label>
                <p class="text-gray-900 dark:text-white mt-1">
                    {{ $ticket->due_date?->format('M d, Y') ?? 'Not set' }}
                    @if($ticket->due_date && now()->isAfter($ticket->due_date))
                        <span class="ml-2 px-2 py-1 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 text-xs rounded font-semibold">Overdue</span>
                    @endif
                </p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Estimated Hours</label>
                <p class="text-gray-900 dark:text-white mt-1">{{ $ticket->estimated_hours ?? 'Not set' }} hours</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Progress</label>
                <div class="mt-2">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ round($progressPercentage) }}%</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ round($totalLoggedHours) }}h / {{ $ticket->estimated_hours ?? '?' }}h</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-blue-600 dark:bg-blue-500 h-2 rounded-full transition-all" style="width: {{ min(100, $progressPercentage) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Assignment --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Assignment</h3>
        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Owner</label>
                <div class="flex items-center gap-2 mt-1">
                    <img src="{{ $ticket->owner->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($ticket->owner->name) }}" 
                         alt="{{ $ticket->owner->name }}" class="w-6 h-6 rounded-full">
                    <p class="text-gray-900 dark:text-white">{{ $ticket->owner->name }}</p>
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Assigned To</label>
                @if($ticket->responsible)
                    <div class="flex items-center gap-2 mt-1">
                        <img src="{{ $ticket->responsible->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($ticket->responsible->name) }}" 
                             alt="{{ $ticket->responsible->name }}" class="w-6 h-6 rounded-full">
                        <p class="text-gray-900 dark:text-white">{{ $ticket->responsible->name }}</p>
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Unassigned</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Risk & Budget --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Risk & Budget</h3>
        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Risk Level</label>
                <p class="text-gray-900 dark:text-white mt-1">
                    @if($ticket->risk_level)
                        <span class="px-2 py-1 text-xs font-semibold rounded"
                              :class="@switch($ticket->risk_level)
                                  @case('critical') bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 @break
                                  @case('high') bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 @break
                                  @case('medium') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 @break
                                  @default bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                              @endswitch">
                            {{ ucfirst($ticket->risk_level) }}
                        </span>
                    @else
                        <span class="text-gray-500 dark:text-gray-400">Not assessed</span>
                    @endif
                </p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Budget</label>
                @if($ticket->budget_allocated)
                    <div class="mt-2">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-gray-600 dark:text-gray-400">${{ number_format($ticket->budget_spent, 2) }} / ${{ number_format($ticket->budget_allocated, 2) }}</span>
                            <span class="text-sm font-semibold" :class="$ticket->isOverBudget() ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                {{ round(($ticket->budget_spent / $ticket->budget_allocated) * 100) }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all" 
                                 :class="$ticket->isOverBudget() ? 'bg-red-600 dark:bg-red-500' : 'bg-green-600 dark:bg-green-500'"
                                 style="width: {{ min(100, ($ticket->budget_spent / $ticket->budget_allocated) * 100) }}%"></div>
                        </div>
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 mt-1">No budget allocated</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Acceptance Criteria --}}
@if($ticket->content)
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Acceptance Criteria</h3>
        <div class="space-y-2">
            <label class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                <input type="checkbox" class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 mt-0.5">
                <span class="text-gray-900 dark:text-white">Criteria 1</span>
            </label>
            <label class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                <input type="checkbox" class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 mt-0.5">
                <span class="text-gray-900 dark:text-white">Criteria 2</span>
            </label>
        </div>
    </div>
@endif
