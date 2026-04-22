<div class="space-y-6">
    {{-- Header with Button --}}
    <div class="px-3 sm:px-4 md:px-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
            <div class="flex-1">
                <h2 class="text-base sm:text-lg md:text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                    Milestones & Releases
                    <x-help-sidebar 
                        title="Milestones & Releases"
                        description="Plan and track major project milestones with version tracking"
                        :features="[
                            'Create and manage project milestones',
                            'Version tracking for releases',
                            'Status tracking (Planned, In Progress, Completed, Cancelled)',
                            'Target date with overdue detection',
                            'Release notes and documentation',
                            'Link tickets to milestones',
                            'Track completion percentage',
                            'Visual progress bars'
                        ]"
                        :benefits="[
                            'Plan major project deliverables',
                            'Track release progress',
                            'Communicate timelines to stakeholders',
                            'Identify and manage delays',
                            'Organize work by releases',
                            'Document release notes',
                            'Maintain version history'
                        ]"
                        implementation="<p class='mb-2'><strong>How it works in this software:</strong></p><p>1. Click 'New Milestone' to create a milestone</p><p>2. Enter name, version number, and target date</p><p>3. Add description and release notes</p><p>4. Link relevant tickets to the milestone</p><p>5. Update status as work progresses</p><p>6. View completion percentage automatically calculated</p><p>7. Filter milestones by status</p>"
                        :examples="[
                            ['title' => 'Version 1.0 Release', 'description' => 'Major release with 5 linked features and 20 tickets'],
                            ['title' => 'Q4 Goals', 'description' => 'Quarterly milestone tracking 3 major features'],
                            ['title' => 'Bug Fix Release', 'description' => 'Minor patch release with 10 bug fixes']
                        ]"
                    />
                </h2>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Track project milestones and releases for {{ $this->project->name }}</p>
            </div>
            <button wire:click="showCreate()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center gap-2 whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Milestone
            </button>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="flex gap-2 border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
        <button wire:click="$set('filterStatus', 'all')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $filterStatus === 'all' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            All Milestones
        </button>
        <button wire:click="$set('filterStatus', 'planned')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $filterStatus === 'planned' ? 'border-gray-500 text-gray-600 dark:text-gray-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            Planned
        </button>
        <button wire:click="$set('filterStatus', 'in_progress')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $filterStatus === 'in_progress' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            In Progress
        </button>
        <button wire:click="$set('filterStatus', 'completed')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $filterStatus === 'completed' ? 'border-green-500 text-green-600 dark:text-green-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            Completed
        </button>
        <button wire:click="$set('filterStatus', 'cancelled')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $filterStatus === 'cancelled' ? 'border-red-500 text-red-600 dark:text-red-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            Cancelled
        </button>
    </div>

    {{-- Create/Edit Form --}}
    @if($showForm)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $editingMilestoneId ? 'Edit Milestone' : 'Create New Milestone' }}</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
                    <input type="text" wire:model="name" placeholder="e.g., Version 1.0 Release" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Target Date *</label>
                    <input type="date" wire:model="targetDate" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    @error('targetDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
                    <select wire:model="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="planned">Planned</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Version *</label>
                    <input type="number" wire:model="version" min="1" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    @error('version') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea wire:model="description" placeholder="Milestone description..." rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Release Notes</label>
                <textarea wire:model="releaseNotes" placeholder="Release notes and changes..." rows="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
            </div>

            <div class="flex gap-2">
                <button wire:click="{{ $editingMilestoneId ? 'updateMilestone' : 'createMilestone' }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    {{ $editingMilestoneId ? 'Update' : 'Create' }}
                </button>
                <button wire:click="resetForm()" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-900 dark:text-white rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors font-medium">
                    Cancel
                </button>
            </div>
        </div>
    @endif

    {{-- Milestones List --}}
    <div class="space-y-4">
        @forelse($milestones as $milestone)
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $milestone->name }}</h3>
                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ 
                                $milestone->status === 'completed' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' :
                                ($milestone->status === 'in_progress' ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' :
                                ($milestone->status === 'cancelled' ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' :
                                'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'))
                            }}">
                                {{ ucfirst(str_replace('_', ' ', $milestone->status)) }}
                            </span>
                            <span class="px-2 py-1 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded text-xs font-medium">v{{ $milestone->version }}</span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $milestone->description }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="editMilestone({{ $milestone->id }})" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">Edit</button>
                        <button wire:click="confirmDeleteMilestone({{ $milestone->id }})" class="text-red-600 dark:text-red-400 hover:underline text-sm">Delete</button>
                    </div>
                </div>

                {{-- Target Date & Progress --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Target Date</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $milestone->target_date->format('M d, Y') }}</p>
                        @if($milestone->is_overdue)
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">⚠️ Overdue by {{ abs($milestone->days_until_target) }} days</p>
                        @else
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $milestone->days_until_target }} days remaining</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Tickets</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $milestone->tickets->count() }} assigned</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Completion</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $milestone->completion_percentage }}%</p>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="mb-4">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full transition-all" style="width: {{ $milestone->completion_percentage }}%"></div>
                    </div>
                </div>

                {{-- Release Notes --}}
                @if($milestone->release_notes)
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Release Notes</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $milestone->release_notes }}</p>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-12 text-center">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400">No milestones yet. Create one to get started!</p>
            </div>
        @endforelse
    </div>

    {{-- Delete Confirmation Modal --}}
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
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Delete Milestone</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Are you sure you want to delete this milestone?</p>
                    </div>
                </div>
                
                <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-md p-3 mb-4">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <strong>Warning:</strong> This action cannot be undone.
                    </p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button wire:click="cancelDeleteMilestone" 
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="deleteMilestone" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        Delete Milestone
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
