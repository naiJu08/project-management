<div class="space-y-6">
    {{-- Header with Button --}}
    <div class="px-3 sm:px-4 md:px-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
            <div class="flex-1">
                <h2 class="text-base sm:text-lg md:text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                    Time Tracking
                    <x-help-sidebar 
                        title="Time Tracking & Hours Management"
                        description="Log and manage project hours with billable/non-billable tracking"
                        :features="[
                            'Log hours with 0.25 to 24 hour increments',
                            'Mark hours as billable or non-billable',
                            'Categorize work (Development, Testing, Documentation, Meeting, Other)',
                            'Optional ticket association',
                            'Real-time filtering and search',
                            'Edit or delete your own logs',
                            'View summary statistics',
                            'Export time reports'
                        ]"
                        :benefits="[
                            'Accurate project costing and billing',
                            'Track team productivity',
                            'Monitor billable vs internal work',
                            'Generate invoices from time logs',
                            'Identify time-consuming tasks',
                            'Support payroll processing',
                            'Improve resource planning'
                        ]"
                        implementation="<p class='mb-2'><strong>How it works in this software:</strong></p><p>1. Click 'Log Time' button to create a new entry</p><p>2. Enter hours (minimum 0.25, maximum 24)</p><p>3. Select category and mark as billable/non-billable</p><p>4. Optionally link to a specific ticket</p><p>5. Set the date and add description</p><p>6. View all logs in the table with filtering options</p><p>7. Edit or delete your own entries anytime</p>"
                        :examples="[
                            ['title' => 'Development Work', 'description' => 'Log 4 hours of billable development work on feature X'],
                            ['title' => 'Team Meeting', 'description' => 'Log 1.5 hours of non-billable time for team standup'],
                            ['title' => 'Testing', 'description' => 'Log 2 hours of billable QA testing work']
                        ]"
                    />
                </h2>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Log and manage project hours for {{ $this->project->name }}</p>
            </div>
            <button wire:click="$toggle('showForm')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center gap-2 whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Log Time
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                        Total Hours
                        <x-help-tooltip 
                            content="Sum of all hours logged in this project"
                            position="right"
                        />
                    </p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($this->totalHours, 2) }}</p>
                </div>
                <svg class="w-12 h-12 text-blue-500 opacity-20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"></path>
                </svg>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                        Billable Hours
                        <x-help-tooltip 
                            content="Hours that can be billed to clients or projects"
                            position="right"
                        />
                    </p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-2">{{ number_format($this->billableHours, 2) }}</p>
                </div>
                <svg class="w-12 h-12 text-green-500 opacity-20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"></path>
                </svg>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                        Non-Billable Hours
                        <x-help-tooltip 
                            content="Internal hours not charged to clients (meetings, training, etc.)"
                            position="right"
                        />
                    </p>
                    <p class="text-3xl font-bold text-orange-600 dark:text-orange-400 mt-2">{{ number_format($this->nonBillableHours, 2) }}</p>
                </div>
                <svg class="w-12 h-12 text-orange-500 opacity-20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"></path>
                </svg>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Team Member</label>
                <select wire:model="filterUser" wire:change="loadTimeLogs()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="all">All Members</option>
                    @foreach($teamMembers as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                <select wire:model="filterCategory" wire:change="loadTimeLogs()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="all">All Categories</option>
                    <option value="development">Development</option>
                    <option value="testing">Testing</option>
                    <option value="documentation">Documentation</option>
                    <option value="meeting">Meeting</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Billable</label>
                <select wire:model="filterBillable" wire:change="loadTimeLogs()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="all">All</option>
                    <option value="billable">Billable Only</option>
                    <option value="non-billable">Non-Billable Only</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                <input type="date" wire:model="startDate" wire:change="loadTimeLogs()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                <input type="date" wire:model="endDate" wire:change="loadTimeLogs()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
        </div>
    </div>

    {{-- Log Time Form --}}
    @if($showForm)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $editingLogId ? 'Edit Time Log' : 'Log New Time' }}</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ticket (Optional)</label>
                    <select wire:model="ticketId" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">Select a ticket</option>
                        @foreach($tickets as $ticket)
                            <option value="{{ $ticket->id }}">{{ $ticket->code }} - {{ $ticket->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hours *</label>
                    <input type="number" wire:model="hours" step="0.25" min="0.25" max="24" placeholder="e.g., 2.5" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    @error('hours') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category *</label>
                    <select wire:model="category" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="development">Development</option>
                        <option value="testing">Testing</option>
                        <option value="documentation">Documentation</option>
                        <option value="meeting">Meeting</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date *</label>
                    <input type="date" wire:model="loggedDate" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    @error('loggedDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea wire:model="description" placeholder="What did you work on?" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="isBillable" id="billable" class="rounded">
                <label for="billable" class="text-sm text-gray-700 dark:text-gray-300">Mark as billable</label>
            </div>

            <div class="flex gap-2">
                <button wire:click="{{ $editingLogId ? 'updateLog' : 'logTime' }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    {{ $editingLogId ? 'Update' : 'Log Time' }}
                </button>
                <button wire:click="resetForm()" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-900 dark:text-white rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors font-medium">
                    Cancel
                </button>
            </div>
        </div>
    @endif

    {{-- Time Logs Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Date</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">User</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Ticket</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Category</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Hours</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Type</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Description</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($timeLogs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $log->logged_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $log->user->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                @if($log->ticket)
                                    <span class="text-blue-600 dark:text-blue-400">{{ $log->ticket->code }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs font-medium">
                                    {{ ucfirst($log->category) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($log->hours, 2) }}h</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 py-1 {{ $log->is_billable ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200' }} rounded text-xs font-medium">
                                    {{ $log->is_billable ? 'Billable' : 'Non-Billable' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 truncate max-w-xs">{{ $log->description ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                @if($log->user_id === Auth::id() || auth()->user()->can('manage_time_logs'))
                                    <button wire:click="editLog({{ $log->id }})" class="text-blue-600 dark:text-blue-400 hover:underline">Edit</button>
                                    <button wire:click="deleteLog({{ $log->id }})" wire:confirm="Delete this time log?" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                No time logs found. Start logging your time!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
