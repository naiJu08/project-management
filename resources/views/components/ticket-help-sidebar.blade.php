@props(['activeTab' => 'details'])

<div class="relative">
    <!-- Help Button -->
    <button @click="helpOpen = !helpOpen" class="fixed bottom-8 right-8 z-40 p-3 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg transition-all duration-300 hover:scale-110">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
    </button>

    <!-- Help Sidebar -->
    <div x-show="helpOpen" @click.outside="helpOpen = false" x-transition class="fixed right-0 top-0 h-screen w-96 bg-white dark:bg-gray-800 shadow-2xl z-50 overflow-y-auto">
        <!-- Header -->
        <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6 border-b border-blue-800">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-xl font-bold">Help & Guidance</h2>
                <button @click="helpOpen = false" class="text-white hover:text-blue-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <p class="text-sm text-blue-100">Current Tab: <span class="font-semibold capitalize">{{ $activeTab }}</span></p>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6">
            @if($activeTab === 'details')
                <!-- Details Tab Help -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">📋 Details Tab</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">What You Can Do:</h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 ml-4">
                                <li>✓ View ticket description and content</li>
                                <li>✓ Check ticket type, priority, and severity</li>
                                <li>✓ See scheduling dates and deadlines</li>
                                <li>✓ Track progress and estimated hours</li>
                                <li>✓ View ticket owner and assignee</li>
                                <li>✓ Monitor risk level and budget</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">Key Information:</h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 ml-4">
                                <li>• <strong>Progress Bar:</strong> Shows completion percentage</li>
                                <li>• <strong>Overdue Badge:</strong> Red badge if past due date</li>
                                <li>• <strong>Risk Level:</strong> Color-coded severity indicator</li>
                                <li>• <strong>Budget:</strong> Shows spent vs allocated</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">Tips:</h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 ml-4">
                                <li>💡 Hover over badges to see more details</li>
                                <li>💡 Check the sidebar for quick metrics</li>
                                <li>💡 Use other tabs for more information</li>
                            </ul>
                        </div>
                    </div>
                </div>

            @elseif($activeTab === 'activity')
                <!-- Activity Tab Help -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">📅 Activity Tab</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">What You Can Do:</h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 ml-4">
                                <li>✓ View complete activity timeline</li>
                                <li>✓ See status change history</li>
                                <li>✓ Track who made changes and when</li>
                                <li>✓ Monitor ticket progress over time</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">Timeline Shows:</h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 ml-4">
                                <li>• User avatar and name</li>
                                <li>• Action performed (status change)</li>
                                <li>• Old and new status</li>
                                <li>• Timestamp (relative time)</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">Tips:</h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 ml-4">
                                <li>💡 Newest activities appear at the top</li>
                                <li>💡 Use for audit trail and accountability</li>
                            </ul>
                        </div>
                    </div>
                </div>

            @elseif($activeTab === 'time')
                <!-- Time Tracking Tab Help -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">⏱️ Time Tracking Tab</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">What You Can Do:</h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 ml-4">
                                <li>✓ View total hours logged</li>
                                <li>✓ See billable vs non-billable hours</li>
                                <li>✓ Track remaining hours</li>
                                <li>✓ View progress percentage</li>
                                <li>✓ See detailed time entries</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">Summary Cards:</h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 ml-4">
                                <li>• <strong>Estimated:</strong> Total hours planned</li>
                                <li>• <strong>Logged:</strong> Hours actually spent</li>
                                <li>• <strong>Remaining:</strong> Hours left to complete</li>
                                <li>• <strong>Progress:</strong> Completion percentage</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">Tips:</h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 ml-4">
                                <li>💡 Red remaining hours = over budget</li>
                                <li>💡 Green remaining hours = on track</li>
                                <li>💡 Check entries for detailed breakdown</li>
                            </ul>
                        </div>
                    </div>
                </div>

            @elseif($activeTab === 'approvals')
                <!-- Approvals Tab Help -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">✅ Approvals Tab</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">What You Can Do:</h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 ml-4">
                                <li>✓ View approval workflow status</li>
                                <li>✓ See pending approvals</li>
                                <li>✓ Approve or reject tickets</li>
                                <li>✓ Add comments to approvals</li>
                                <li>✓ Track approval history</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">Approval Status:</h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 ml-4">
                                <li>• <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Pending</span> - Awaiting approval</li>
                                <li>• <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Approved</span> - Approved</li>
                                <li>• <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Rejected</span> - Rejected</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">Actions:</h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 ml-4">
                                <li>• <strong>Approve:</strong> Accept the ticket</li>
                                <li>• <strong>Approve with Comments:</strong> Approve + feedback</li>
                                <li>• <strong>Reject:</strong> Decline the ticket</li>
                            </ul>
                        </div>
                    </div>
                </div>

            @elseif($activeTab === 'dependencies')
                <!-- Dependencies Tab Help -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">🔗 Dependencies Tab</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">What You Can Do:</h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 ml-4">
                                <li>✓ View tickets blocking this one</li>
                                <li>✓ See tickets this one blocks</li>
                                <li>✓ Add new dependencies</li>
                                <li>✓ Remove existing dependencies</li>
                                <li>✓ View subtasks</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">Sections:</h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 ml-4">
                                <li>• <strong>🚫 Blocked By:</strong> Red - tickets blocking this</li>
                                <li>• <strong>⚠️ Blocks:</strong> Orange - tickets this blocks</li>
                                <li>• <strong>👶 Subtasks:</strong> Child tickets</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">Tips:</h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 ml-4">
                                <li>💡 Resolve blocked tickets first</li>
                                <li>💡 Use for project planning</li>
                                <li>💡 Click tickets to view details</li>
                            </ul>
                        </div>
                    </div>
                </div>

            @elseif($activeTab === 'comments')
                <!-- Comments Tab Help -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">💬 Comments Tab</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">What You Can Do:</h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 ml-4">
                                <li>✓ View all comments on ticket</li>
                                <li>✓ Add new comments</li>
                                <li>✓ Edit your own comments</li>
                                <li>✓ Delete your own comments</li>
                                <li>✓ Reply to specific comments</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">Comment Features:</h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 ml-4">
                                <li>• User avatar and name</li>
                                <li>• Timestamp (relative time)</li>
                                <li>• Comment content</li>
                                <li>• Edit/Delete buttons (own only)</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">Tips:</h4>
                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 ml-4">
                                <li>💡 Use for team discussion</li>
                                <li>💡 Share updates and feedback</li>
                                <li>💡 Mention team members for attention</li>
                            </ul>
                        </div>
                    </div>
                </div>

            @endif

            <!-- General Tips -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">⚡ General Tips:</h4>
                <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 ml-4">
                    <li>• Use tabs to navigate different sections</li>
                    <li>• Check sidebar for quick information</li>
                    <li>• Hover over elements for more details</li>
                    <li>• Use dark mode for comfortable viewing</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('ticketHelp', () => ({
            helpOpen: false,
        }));
    });
</script>
