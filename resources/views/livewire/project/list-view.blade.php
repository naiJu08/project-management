<div class="space-y-3 sm:space-y-4">
    {{-- Header with Help Sidebar --}}
    <div class="px-3 sm:px-4 md:px-6 mb-3 sm:mb-4">
        <h2 class="text-base sm:text-lg md:text-xl font-semibold text-gray-900 dark:text-white flex items-center">
            List
            <x-help-sidebar 
                title="Task List View"
                description="View and manage all project tasks in a detailed table format"
                :features="[
                    'View all tasks in table format',
                    'Search and filter tasks',
                    'Sort by status, priority, assignee',
                    'Bulk select and edit tasks',
                    'Quick task creation',
                    'View task details',
                    'Edit task information',
                    'Export task list'
                ]"
                :benefits="[
                    'See complete task inventory',
                    'Find tasks quickly',
                    'Organize work efficiently',
                    'Bulk update multiple tasks',
                    'Better task visibility',
                    'Improved planning'
                ]"
                implementation="<p>1. Use search bar to find tasks</p><p>2. Click column headers to sort</p><p>3. Use filters for specific views</p><p>4. Click task row to view details</p><p>5. Select multiple tasks for bulk actions</p><p>6. Click 'New Task' to create</p>"
                :examples="[
                    ['title' => 'Find Overdue', 'description' => 'Filter by status to see overdue tasks'],
                    ['title' => 'Team Tasks', 'description' => 'Filter by assignee to see team member work'],
                    ['title' => 'Priority View', 'description' => 'Sort by priority to focus on critical work']
                ]"
            />
        </h2>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg p-3 sm:p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-4">
            <div class="flex-1">
                <input type="text" 
                       wire:model.debounce.300ms="search" 
                       placeholder="Search tasks..."
                       class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400">
            </div>
            <button 
    wire:click="createTask"
    class="px-3 sm:px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center justify-center sm:justify-start text-sm sm:text-base font-medium">

    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
    </svg>

    New Task
</button>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Task
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Priority
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Assignee
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Sprint
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Created
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($tickets as $ticket)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" 
                        onclick="window.location='{{ route('filament.resources.tickets.view', ['record' => $ticket->id]) }}'">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $ticket->name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $ticket->code }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($ticket->status)
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                      style="background-color: {{ $ticket->status->color }}20; color: {{ $ticket->status->color }}">
                                    {{ $ticket->status->name }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($ticket->priority)
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                      style="background-color: {{ $ticket->priority->color }}20; color: {{ $ticket->priority->color }}">
                                    {{ $ticket->priority->name }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($ticket->responsible)
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-500 rounded-full flex items-center justify-center text-white text-xs font-semibold mr-2">
                                        {{ substr($ticket->responsible->name, 0, 1) }}
                                    </div>
                                    <span class="text-sm text-gray-900 dark:text-white">{{ $ticket->responsible->name }}</span>
                                </div>
                            @else
                                <span class="text-sm text-gray-400">Unassigned</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $ticket->sprint?->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $ticket->created_at->format('M d, Y') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <p class="text-gray-500 dark:text-gray-400">No tasks found</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
</div>
