<div class="space-y-6">
    {{-- Approval Status --}}
    @if($ticket->requires_approval)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Approval Workflow</h3>
            
            <div class="space-y-4">
                @forelse($ticket->approvals as $approval)
                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $approval->approver->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($approval->approver->name) }}" 
                                     alt="{{ $approval->approver->name }}" class="w-8 h-8 rounded-full">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $approval->approver->name }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $approval->type)) }}</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full"
                                  :class="@switch($approval->status)
                                      @case('approved') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 @break
                                      @case('rejected') bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 @break
                                      @case('approved_with_comments') bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 @break
                                      @default bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-200
                                  @endswitch">
                                {{ ucfirst(str_replace('_', ' ', $approval->status)) }}
                            </span>
                        </div>
                        
                        @if($approval->comments)
                            <p class="text-sm text-gray-700 dark:text-gray-300 p-3 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-600">
                                {{ $approval->comments }}
                            </p>
                        @endif
                        
                        @if($approval->status === 'pending' && $approval->approver_id === auth()->id())
                            <div class="mt-4 space-y-3">
                                @if(!$showApprovalForm)
                                    <button wire:click="$set('showApprovalForm', true)" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm font-medium">
                                        Add Approval Response
                                    </button>
                                @else
                                    <textarea wire:model="approvalComment" placeholder="Add comments..." rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"></textarea>
                                    <div class="flex gap-2">
                                        <button wire:click="approveTicket" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm font-medium">
                                            ✓ Approve
                                        </button>
                                        <button wire:click="approveWithComments" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm font-medium">
                                            💬 Approve with Comments
                                        </button>
                                        <button wire:click="rejectTicket" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm font-medium">
                                            ✕ Reject
                                        </button>
                                        <button wire:click="$set('showApprovalForm', false)" class="flex-1 px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-700 text-gray-900 dark:text-white rounded-lg transition-colors text-sm font-medium">
                                            Cancel
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">No approvals required for this ticket</p>
                @endforelse
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 text-center">
            <p class="text-gray-500 dark:text-gray-400">This ticket does not require approval</p>
        </div>
    @endif
</div>
