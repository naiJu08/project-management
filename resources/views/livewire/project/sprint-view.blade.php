<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                Sprints
                <x-help-sidebar 
                    title="Sprint Management"
                    description="Plan and execute project work in time-boxed sprints"
                    :features="[
                        'Create and manage sprints',
                        'Set sprint goals and duration',
                        'Add items to sprints',
                        'Track sprint progress',
                        'Sprint burndown charts',
                        'Sprint retrospectives',
                        'Velocity tracking',
                        'Sprint reports'
                    ]"
                    :benefits="[
                        'Organize work in iterations',
                        'Improve team focus',
                        'Track velocity trends',
                        'Better predictability',
                        'Faster feedback cycles',
                        'Continuous improvement'
                    ]"
                    implementation="<p>1. Click 'New Sprint' to create</p><p>2. Set sprint name and duration</p><p>3. Add items from backlog</p><p>4. Track progress during sprint</p><p>5. Review sprint metrics</p><p>6. Plan next sprint</p>"
                    :examples="[
                        ['title' => 'Two-Week Sprint', 'description' => 'Standard sprint with 10 story points'],
                        ['title' => 'Sprint Planning', 'description' => 'Move 15 items from backlog to sprint'],
                        ['title' => 'Sprint Review', 'description' => 'Check completed items and velocity']
                    ]"
                />
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Create and manage project sprints</p>
        </div>
        <button wire:click="showCreate()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            New Sprint
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex gap-2 border-b border-gray-200 dark:border-gray-700">
        <button wire:click="setFilter('all')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ $filterStatus === 'all' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            All Sprints
        </button>
        <button wire:click="setFilter('active')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ $filterStatus === 'active' ? 'border-green-500 text-green-600 dark:text-green-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            Active
        </button>
        <button wire:click="setFilter('upcoming')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ $filterStatus === 'upcoming' ? 'border-yellow-500 text-yellow-600 dark:text-yellow-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            Upcoming
        </button>
        <button wire:click="setFilter('completed')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ $filterStatus === 'completed' ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            Completed
        </button>
    </div>

    {{-- Create/Edit Form --}}
    @if($showCreateForm || $showEditForm)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                {{ $showEditForm ? 'Edit Sprint' : 'Create New Sprint' }}
            </h3>
            <form wire:submit.prevent="{{ $showEditForm ? 'updateSprint' : 'createSprint' }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sprint Name *</label>
                        <input type="text" wire:model="sprintName" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="e.g., Sprint 1 - User Authentication">
                        @error('sprintName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                        <select wire:model="sprintStatus" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="upcoming">Upcoming</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                        </select>
                        @error('sprintStatus') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sprint Goal</label>
                    <input type="text" wire:model="sprintGoal" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="e.g., Implement user login and registration">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                    <textarea wire:model="sprintDescription" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Sprint description and details..."></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Date *</label>
                        <input type="date" wire:model="sprintStartDate" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('sprintStartDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">End Date *</label>
                        <input type="date" wire:model="sprintEndDate" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('sprintEndDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        {{ $showEditForm ? 'Update Sprint' : 'Create Sprint' }}
                    </button>
                    <button type="button" wire:click="resetForm()" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-900 dark:text-white rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Sprints List --}}
    <div class="space-y-4">
        @forelse($sprints as $sprint)
            @php
                $status = ($this->getSprintStatusProperty())($sprint);
                $statusColor = $status === 'active' ? 'green' : ($status === 'completed' ? 'purple' : 'yellow');
                $itemCount = $sprint->backlogItems()->count();
                $completedCount = $sprint->backlogItems()->where('status', 'Done')->count();
                $completionPercent = $itemCount > 0 ? round(($completedCount / $itemCount) * 100) : 0;
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg transition-shadow cursor-pointer">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1 cursor-pointer"
                         wire:click="selectSprint({{ $sprint->id }})">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $sprint->name }}</h3>
                            <span class="px-2 py-1 text-xs font-medium rounded-full" style="background-color: {{ $statusColor === 'green' ? '#dcfce7' : ($statusColor === 'purple' ? '#f3e8ff' : '#fef3c7') }}; color: {{ $statusColor === 'green' ? '#166534' : ($statusColor === 'purple' ? '#581c87' : '#92400e') }}">
                                {{ ucfirst($status) }}
                            </span>
                        </div>
                        @if($sprint->goal)
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Goal: {{ $sprint->goal }}</p>
                        @endif
                        @if($sprint->description)
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ Str::limit($sprint->description, 100) }}</p>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        @if($status === 'upcoming')
                            <button type="button"
                                wire:click.stop="startSprint({{ $sprint->id }})"
                                class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition-colors">
                                Start
                            </button>
                        @elseif($status === 'active')
                            <button wire:click.stop="completeSprint({{ $sprint->id }})" class="px-3 py-1 bg-purple-600 text-white text-sm rounded hover:bg-purple-700 transition-colors">
                                Complete
                            </button>
                        @endif
                        <button wire:click.stop="showEdit({{ $sprint->id }})" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors">
                            Edit
                        </button>
                        <button wire:click.stop="confirmDeleteSprint({{ $sprint->id }})" class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition-colors">
                            Delete
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Start Date</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $sprint->starts_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">End Date</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $sprint->ends_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Duration</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $sprint->starts_at->diffInDays($sprint->ends_at) }} days</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Items</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $itemCount }}</p>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Progress</p>
                        <p class="text-xs font-medium text-gray-900 dark:text-white">{{ $completionPercent }}%</p>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all" style="width: {{ $completionPercent }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $completedCount }} of {{ $itemCount }} items completed</p>
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Sprints Yet</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4">Create your first sprint to get started</p>
                <button wire:click="showCreate()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Create Sprint
                </button>
            </div>
        @endforelse
    </div>

    {{-- Sprint Delete Confirmation Modal --}}
    @if($showDeleteConfirm)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Delete Sprint</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Are you sure you want to delete this sprint?</p>
                    </div>
                </div>
                
                <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-md p-3 mb-4">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <strong>Warning:</strong> All items in this sprint will be moved back to the backlog. This action cannot be undone.
                    </p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button wire:click="cancelDeleteSprint" 
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="deleteSprint" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        Delete Sprint
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
