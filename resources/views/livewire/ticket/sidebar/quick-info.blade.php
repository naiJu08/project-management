<div class="space-y-6">
    {{-- Quick Stats --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Stats</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <span class="text-sm text-gray-600 dark:text-gray-400">💬 Comments</span>
                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $ticket->comments->count() }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <span class="text-sm text-gray-600 dark:text-gray-400">📎 Attachments</span>
                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $ticket->attachment_count ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <span class="text-sm text-gray-600 dark:text-gray-400">👁️ Watchers</span>
                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $ticket->watcher_count ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <span class="text-sm text-gray-600 dark:text-gray-400">🔄 Reopened</span>
                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $ticket->reopened_count ?? 0 }}</span>
            </div>
        </div>
    </div>

    {{-- Time Tracking Summary --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">⏱️ Time Tracking</h3>
        <div class="space-y-3">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Estimated</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $ticket->estimated_hours ?? '—' }}h</span>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Logged</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ round($totalLoggedHours, 2) }}h</span>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Remaining</span>
                    <span class="text-sm font-semibold" :class="$remainingHours !== null && $remainingHours < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                        {{ $remainingHours !== null ? round($remainingHours, 2) : '—' }}h
                    </span>
                </div>
            </div>
            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Progress</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ round($progressPercentage) }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-2">
                    <div class="bg-blue-600 dark:bg-blue-500 h-2 rounded-full transition-all" style="width: {{ min(100, $progressPercentage) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- SLA Status --}}
    @if($ticket->sla_due_at)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">⏰ SLA Status</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 rounded-lg"
                     :class="@switch($ticket->sla_status)
                         @case('breached') bg-red-100 dark:bg-red-900 @break
                         @case('at_risk') bg-yellow-100 dark:bg-yellow-900 @break
                         @default bg-green-100 dark:bg-green-900
                     @endswitch">
                    <span class="text-sm font-semibold"
                          :class="@switch($ticket->sla_status)
                              @case('breached') text-red-800 dark:text-red-200 @break
                              @case('at_risk') text-yellow-800 dark:text-yellow-200 @break
                              @default text-green-800 dark:text-green-200
                          @endswitch">
                        {{ ucfirst(str_replace('_', ' ', $ticket->sla_status)) }}
                    </span>
                    <span class="text-xs font-semibold"
                          :class="@switch($ticket->sla_status)
                              @case('breached') text-red-800 dark:text-red-200 @break
                              @case('at_risk') text-yellow-800 dark:text-yellow-200 @break
                              @default text-green-800 dark:text-green-200
                          @endswitch">
                        {{ $ticket->sla_due_at->format('M d, H:i') }}
                    </span>
                </div>
                <button wire:click="updateSLAStatus" class="w-full px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-lg transition-colors">
                    Refresh SLA Status
                </button>
            </div>
        </div>
    @endif

    {{-- Risk Assessment --}}
    @if($ticket->risk_level)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">⚠️ Risk Assessment</h3>
            <div class="space-y-3">
                <div class="p-3 rounded-lg"
                     :class="@switch($ticket->risk_level)
                         @case('critical') bg-red-100 dark:bg-red-900 @break
                         @case('high') bg-orange-100 dark:bg-orange-900 @break
                         @case('medium') bg-yellow-100 dark:bg-yellow-900 @break
                         @default bg-green-100 dark:bg-green-900
                     @endswitch">
                    <p class="text-sm font-semibold"
                       :class="@switch($ticket->risk_level)
                           @case('critical') text-red-800 dark:text-red-200 @break
                           @case('high') text-orange-800 dark:text-orange-200 @break
                           @case('medium') text-yellow-800 dark:text-yellow-200 @break
                           @default text-green-800 dark:text-green-200
                       @endswitch">
                        Risk Level: {{ ucfirst($ticket->risk_level) }}
                    </p>
                </div>
                @if($ticket->risk_description)
                    <div>
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Description</p>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $ticket->risk_description }}</p>
                    </div>
                @endif
                @if($ticket->mitigation_plan)
                    <div>
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Mitigation Plan</p>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $ticket->mitigation_plan }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Approval Status --}}
    @if($ticket->requires_approval)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">✅ Approval Status</h3>
            <div class="space-y-3">
                <div class="p-3 rounded-lg"
                     :class="$allApprovalsCompleted ? 'bg-green-100 dark:bg-green-900' : 'bg-yellow-100 dark:bg-yellow-900'">
                    <p class="text-sm font-semibold"
                       :class="$allApprovalsCompleted ? 'text-green-800 dark:text-green-200' : 'text-yellow-800 dark:text-yellow-200'">
                        @if($allApprovalsCompleted)
                            ✓ All Approvals Complete
                        @else
                            ⏳ {{ $pendingApprovals }} Pending Approval(s)
                        @endif
                    </p>
                </div>
                <div class="space-y-2">
                    @forelse($ticket->approvals as $approval)
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded">
                            <div class="flex items-center gap-2">
                                <img src="{{ $approval->approver->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($approval->approver->name) }}" 
                                     alt="{{ $approval->approver->name }}" class="w-5 h-5 rounded-full">
                                <span class="text-xs text-gray-600 dark:text-gray-400">{{ $approval->approver->name }}</span>
                            </div>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded"
                                  :class="@switch($approval->status)
                                      @case('approved') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 @break
                                      @case('rejected') bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 @break
                                      @default bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-200
                                  @endswitch">
                                {{ ucfirst($approval->status) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500 dark:text-gray-400">No approvals required</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- Blocking Status --}}
    @if($ticket->isBlocked())
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-red-200 dark:border-red-900 p-6">
            <h3 class="text-lg font-semibold text-red-800 dark:text-red-200 mb-4">🚫 Blocked</h3>
            <div class="space-y-2">
                @foreach($blockingReasons as $reason)
                    <div class="flex items-start gap-2 p-2 bg-red-50 dark:bg-red-900/20 rounded">
                        <span class="text-red-600 dark:text-red-400 mt-0.5">•</span>
                        <span class="text-sm text-red-800 dark:text-red-200">{{ $reason }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Dates --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📅 Dates</h3>
        <div class="space-y-3 text-sm">
            <div class="flex items-center justify-between">
                <span class="text-gray-600 dark:text-gray-400">Created</span>
                <span class="text-gray-900 dark:text-white">{{ $ticket->created_at->format('M d, Y') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-600 dark:text-gray-400">Updated</span>
                <span class="text-gray-900 dark:text-white">{{ $ticket->updated_at->format('M d, Y H:i') }}</span>
            </div>
            @if($ticket->first_response_at)
                <div class="flex items-center justify-between">
                    <span class="text-gray-600 dark:text-gray-400">First Response</span>
                    <span class="text-gray-900 dark:text-white">{{ $ticket->first_response_at->format('M d, Y H:i') }}</span>
                </div>
            @endif
            @if($ticket->resolved_at)
                <div class="flex items-center justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Resolved</span>
                    <span class="text-gray-900 dark:text-white">{{ $ticket->resolved_at->format('M d, Y H:i') }}</span>
                </div>
            @endif
        </div>
    </div>
</div>
