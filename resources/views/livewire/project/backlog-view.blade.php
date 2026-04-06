{{-- Azure DevOps-Style Hierarchical Backlog View --}}
<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-fadeIn {
        animation: fadeIn 0.2s ease-out;
    }
</style>

<div class="h-full flex flex-col bg-gray-50 dark:bg-gray-900" x-data="backlogDragDrop()">
    
    {{-- Debug: Confirm BacklogView component is loaded --}}
    @if(config('app.debug'))
        <div class="bg-green-100 dark:bg-green-900 p-2 text-xs mb-2">
            BacklogView Component Loaded (ID: {{ $this->id }})
        </div>
    @endif
    
    {{-- Header with Actions --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-3 sm:px-4 md:px-6 py-3 sm:py-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
            <div class="flex items-center gap-2 sm:gap-4 min-w-0 flex-1">
                <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    Backlog
                    <x-help-sidebar 
                        title="Product Backlog"
                        description="Manage hierarchical backlog with epics, features, stories, tasks, and subtasks"
                        :features="[
                            'Hierarchical item structure (Epic → Feature → Story → Task)',
                            'Drag and drop to reorder items',
                            'Expand/collapse item hierarchies',
                            'Inline editing of item details',
                            'Bulk operations on multiple items',
                            'Sprint assignment and management',
                            'Comments and history tracking',
                            'Export backlog to CSV/JSON'
                        ]"
                        :benefits="[
                            'Organize work by hierarchy',
                            'Prioritize effectively',
                            'Plan sprints efficiently',
                            'Track work progress',
                            'Improve team alignment',
                            'Better project visibility'
                        ]"
                        implementation="<p>1. View backlog items in hierarchical tree</p><p>2. Click '+' to add new items</p><p>3. Drag items to reorder priority</p><p>4. Click expand arrow to see child items</p><p>5. Click item to edit details</p><p>6. Assign items to sprints</p>"
                        :examples="[
                            ['title' => 'Epic Planning', 'description' => 'Create epic with features and stories'],
                            ['title' => 'Sprint Planning', 'description' => 'Move items from backlog to sprint'],
                            ['title' => 'Prioritization', 'description' => 'Drag items to reorder by priority']
                        ]"
                    />
                </h1>
                <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-sm font-medium rounded-full">
                    {{ $this->backlogItems->count() }} items
                </span>
            </div>
            
            <div class="flex items-center space-x-3">
                {{-- View Mode Toggle --}}
                <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                    <button wire:click="setViewMode('tree')" 
                            class="px-3 py-1 text-sm rounded transition-colors {{ $viewMode === 'tree' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                        Tree
                    </button>
                    <button wire:click="setViewMode('flat')" 
                            class="px-3 py-1 text-sm rounded transition-colors {{ $viewMode === 'flat' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                        Flat
                    </button>
                </div>
                
                {{-- Selection Mode Toggle --}}
                <button wire:click="toggleSelectionMode()" 
                        class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $selectionMode ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
                        title="Toggle selection mode">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                        <span>{{ $selectionMode ? 'Done' : 'Select' }}</span>
                        @if(count($this->selectedItems) > 0)
                            <span class="px-1.5 py-0.5 bg-blue-600 text-white text-xs rounded-full">
                                {{ count($this->selectedItems) }}
                            </span>
                        @endif
                    </div>
                </button>
                
                {{-- Select All (only visible in selection mode) --}}
                @if($selectionMode && count($this->selectedItems) === 0)
                    <button wire:click="selectAll()" class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        Select All
                    </button>
                @endif
                
                {{-- Expand/Collapse All --}}
                <button wire:click="expandAll()" class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg" title="Expand All">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
                <button wire:click="collapseAll()" class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg" title="Collapse All">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                </button>
                
                {{-- Export Menu --}}
                <div class="relative" x-data="{ showExport: false }">
                    <button @click="showExport = !showExport" class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg" title="Export">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </button>
                    <div x-show="showExport" @click.away="showExport = false" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 rounded-lg shadow-lg z-10 border border-gray-200 dark:border-gray-600 py-1">
                        <button wire:click="exportToCSV()" @click="showExport = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                            Export to CSV
                        </button>
                        <button wire:click="exportToJSON()" @click="showExport = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                            Export to JSON
                        </button>
                    </div>
                </div>
                
                {{-- New Item Dropdown --}}
                <div class="relative" x-data="{ showNewMenu: @entangle('showNewMenu') }">
                    <button @click="showNewMenu = !showNewMenu" 
                            class="px-4 py-2 bg-blue-600 dark:bg-blue-700 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 flex items-center text-sm font-medium shadow-sm transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        New Item
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="showNewMenu" 
                         @click.away="showNewMenu = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg z-20 border border-gray-200 dark:border-gray-700 py-1">
                        <button wire:click="showInlineCreate(null, 'Epic')" 
                                class="flex items-center w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors">
                            <span class="text-lg mr-3">🎯</span>
                            <div>
                                <div class="font-medium">Epic</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Top-level initiative</div>
                            </div>
                        </button>
                        <button wire:click="showQuickAddForm('Feature', null)" 
                                class="flex items-center w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                            <span class="text-lg mr-3">🔷</span>
                            <div>
                                <div class="font-medium">Feature</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Under an Epic (select parent)</div>
                            </div>
                        </button>
                        <button wire:click="showQuickAddForm('UserStory', null)" 
                                class="flex items-center w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                            <span class="text-lg mr-3">📖</span>
                            <div>
                                <div class="font-medium">User Story</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Under a Feature (select parent)</div>
                            </div>
                        </button>
                        <a href="{{ route('filament.resources.tickets.create', ['project' => $this->project->id]) }}" 
                           class="flex items-center w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors">
                            <span class="text-lg mr-3">✓</span>
                            <div>
                                <div class="font-medium">Task (Create Ticket)</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Opens ticket creation form</div>
                            </div>
                        </a>
                        <a href="{{ route('filament.resources.tickets.create', ['project' => $this->project->id]) }}" 
                           class="flex items-center w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <span class="text-lg mr-3">▫</span>
                            <div>
                                <div class="font-medium">Subtask (Create Ticket)</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Opens ticket creation form</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Filters --}}
        <div class="mt-4 flex items-center space-x-3 flex-wrap gap-2">
            {{-- Search --}}
            <div class="flex-1 min-w-[300px]">
                <input type="text" 
                       wire:model.debounce.300ms="searchTerm" 
                       placeholder="Search by title, code, or description..." 
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
            </div>
            
            {{-- Type Filter --}}
            <select wire:model="filterType" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                <option value="all">All Types</option>
                <option value="Epic">Epic</option>
                <option value="Feature">Feature</option>
                <option value="UserStory">User Story</option>
                <option value="Task">Task</option>
                <option value="Subtask">Subtask</option>
            </select>
            
            {{-- Status Filter --}}
            <select wire:model="filterStatus" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                <option value="all">All Status</option>
                <option value="To Do">To Do</option>
                <option value="In Progress">In Progress</option>
                <option value="Done">Done</option>
                <option value="Blocked">Blocked</option>
            </select>
            
            {{-- Assignee Filter --}}
            <select wire:model="filterAssignee" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                <option value="all">All Assignees</option>
                @foreach($this->teamMembers as $member)
                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                @endforeach
            </select>
            
            {{-- Sprint Filter --}}
            <select wire:model="filterSprint" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                <option value="all">All Sprints</option>
                <option value="none">No Sprint</option>
                @foreach($this->sprints as $sprint)
                    <option value="{{ $sprint->id }}">{{ $sprint->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Main Content: Two-Panel Layout --}}
    <div class="flex-1 flex overflow-hidden">
        
        {{-- Left Panel: Hierarchical Tree --}}
        <div class="w-1/2 border-r border-gray-200 dark:border-gray-700 overflow-y-auto bg-white dark:bg-gray-800">
            
            {{-- Bulk Actions Panel --}}
            @if($showBulkPanel)
                <div class="sticky top-0 z-10 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-800 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Bulk Actions ({{ count($this->selectedItems) }} items)</h3>
                        <button wire:click="deselectAll()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-3 gap-3 mb-3">
                        <select wire:model="bulkStatus" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                            <option value="">Status...</option>
                            <option value="To Do">To Do</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Done">Done</option>
                            <option value="Blocked">Blocked</option>
                        </select>
                        <select wire:model="bulkPriority" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                            <option value="">Priority...</option>
                            <option value="Critical">Critical</option>
                            <option value="High">High</option>
                            <option value="Medium">Medium</option>
                            <option value="Low">Low</option>
                        </select>
                        <select wire:model="bulkSprintId" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                            <option value="">Sprint...</option>
                            <option value="">No Sprint</option>
                            @foreach($this->sprints as $sprint)
                                <option value="{{ $sprint->id }}">{{ $sprint->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button wire:click="bulkDelete()" onclick="return confirm('Are you sure? This will delete all selected items and their children.')" class="px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                            Delete Selected
                        </button>
                        <button wire:click="applyBulkAction()" class="px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Apply Changes
                        </button>
                    </div>
                </div>
            @endif
            
            <div class="p-4">
                
                {{-- Quick Add Form --}}
                @if($showQuickAdd)
                    <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-gray-900 dark:text-white">Add New {{ $quickAddType }}</h3>
                            <button wire:click="cancelQuickAdd()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <form wire:submit.prevent="quickAddItem" class="space-y-3">
                            {{-- Parent Selection (for non-Epic items) --}}
                            @if($quickAddType !== 'Epic')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Parent {{ $quickAddType === 'Feature' ? 'Epic' : ($quickAddType === 'UserStory' ? 'Feature' : ($quickAddType === 'Task' ? 'User Story' : 'Task')) }} <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="quickAddParentId" 
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                            required>
                                        <option value="">Select parent...</option>
                                        @foreach($this->availableParents as $parent)
                                            <option value="{{ $parent->id }}">{{ $parent->code }} - {{ $parent->title }}</option>
                                        @endforeach
                                    </select>
                                    @if($this->availableParents->isEmpty())
                                        <p class="mt-1 text-xs text-yellow-600 dark:text-yellow-400">
                                            No available parents. Create a {{ $quickAddType === 'Feature' ? 'Epic' : ($quickAddType === 'UserStory' ? 'Feature' : ($quickAddType === 'Task' ? 'User Story' : 'Task')) }} first.
                                        </p>
                                    @endif
                                </div>
                            @endif
                            
                            {{-- Title Input --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Title <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       wire:model.defer="quickAddTitle" 
                                       placeholder="Enter title..." 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                       required
                                       autofocus>
                            </div>
                            
                            {{-- Action Buttons --}}
                            <div class="flex justify-end space-x-2 pt-2">
                                <button type="button" 
                                        wire:click="cancelQuickAdd()" 
                                        class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600 rounded-lg transition-colors">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="px-4 py-2 text-sm bg-blue-600 dark:bg-blue-700 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors shadow-sm">
                                    Add {{ $quickAddType }}
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Hierarchical Tree or Flat List --}}
                @if($viewMode === 'tree')
                    {{-- Tree View: Show only root items, children render recursively --}}
                    @forelse($this->backlogItems->where('parent_id', null) as $item)
                        @include('livewire.project.partials.backlog-item-row', [
                            'item' => $item, 
                            'level' => 0, 
                            'selectionMode' => $selectionMode, 
                            'selectedItems' => $this->selectedItems, 
                            'selectedItemId' => $this->selectedItemId, 
                            'expandedItems' => $expandedItems, 
                            'inlineCreateParentId' => $inlineCreateParentId, 
                            'inlineCreateType' => $inlineCreateType, 
                            'inlineCreateTitle' => $inlineCreateTitle
                        ])
                    @empty
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No backlog items</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new Epic.</p>
                            <div class="mt-6">
                                <button wire:click="showInlineCreate(null, 'Epic')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 dark:bg-purple-700 dark:hover:bg-purple-600 transition-colors">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    New Epic
                                </button>
                            </div>
                        </div>
                    @endforelse
                @else
                    {{-- Flat View: Show all items without hierarchy --}}
                    @forelse($this->backlogItems as $item)
                        <div class="backlog-item-row" data-item-id="{{ $item->id }}" data-type="{{ $item->type }}">
                            <div class="flex items-center hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg group {{ $this->selectedItemId === $item->id ? 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500' : '' }} py-2 px-3">
                                {{-- Type Label --}}
                                <div class="pr-3 flex-shrink-0">
                                    <span class="text-xs font-medium px-2 py-0.5 rounded" 
                                          style="background-color: {{ $item->getTypeColor() }}15; color: {{ $item->getTypeColor() }}; border: 1px solid {{ $item->getTypeColor() }}30;">
                                        {{ $item->type }}
                                    </span>
                                </div>
                                
                                {{-- Item Content --}}
                                <div wire:click="selectItem({{ $item->id }})" class="flex-1 flex items-center cursor-pointer min-w-0">
                                    <span class="text-lg mr-2 flex-shrink-0">{!! $item->getTypeIcon() !!}</span>
                                    <span class="px-2 py-0.5 text-xs font-mono rounded mr-2 flex-shrink-0" 
                                          style="background-color: {{ $item->getTypeColor() }}20; color: {{ $item->getTypeColor() }}">
                                        {{ $item->code }}
                                    </span>
                                    <span class="text-sm text-gray-900 dark:text-white truncate flex-1">
                                        {{ $item->title }}
                                    </span>
                                    <span class="px-2 py-0.5 text-xs rounded ml-2 flex-shrink-0" 
                                          style="background-color: {{ $item->getStatusColor() }}20; color: {{ $item->getStatusColor() }}">
                                        {{ $item->status }}
                                    </span>
                                    <span class="px-2 py-0.5 text-xs rounded ml-2 flex-shrink-0" 
                                          style="background-color: {{ $item->getPriorityColor() }}20; color: {{ $item->getPriorityColor() }}">
                                        {{ $item->priority }}
                                    </span>
                                    @if($item->assignee)
                                        <div class="ml-2 flex-shrink-0" title="{{ $item->assignee->name }}">
                                            <div class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-medium">
                                                {{ substr($item->assignee->name, 0, 1) }}
                                            </div>
                                        </div>
                                    @endif
                                    @if($item->sprint)
                                        <span class="ml-2 px-2 py-0.5 text-xs bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded flex-shrink-0">
                                            {{ $item->sprint->name }}
                                        </span>
                                    @endif
                                </div>
                                
                                {{-- Selection Checkbox (Rightmost, only in selection mode) --}}
                                @if($selectionMode)
                                    <div class="pr-3 flex-shrink-0">
                                        <input type="checkbox" 
                                               wire:click="toggleItemSelection({{ $item->id }})"
                                               @if(in_array($item->id, $this->selectedItems)) checked @endif
                                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No backlog items</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new Epic.</p>
                            <div class="mt-6">
                                <button wire:click="showInlineCreate(null, 'Epic')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 dark:bg-purple-700 dark:hover:bg-purple-600 transition-colors">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    New Epic
                                </button>
                            </div>
                        </div>
                    @endforelse
                @endif
                
                <!-- {{-- Debug Info (Remove after testing) --}}
                @if(config('app.debug'))
                    <div class="mb-2 p-2 bg-yellow-100 dark:bg-yellow-900 text-xs">
                        <strong>Debug:</strong> 
                        inlineCreateParentId={{ var_export($inlineCreateParentId, true) }}, 
                        inlineCreateType={{ var_export($inlineCreateType, true) }}
                    </div>
                @endif -->
                
                {{-- Root-level Inline Creation Form (for Epics) --}}
                @if($inlineCreateParentId === null && $inlineCreateType === 'Epic')
                    <div class="inline-create-form mb-2" wire:key="inline-create-epic">
                        <div class="flex items-center py-2 px-3 bg-purple-50 dark:bg-purple-900/10 border-l-4 border-purple-500 rounded-r-lg ml-2 mr-2 animate-fadeIn">
                            {{-- Type Icon --}}
                            <span class="text-lg mr-2 flex-shrink-0">🎯</span>
                            
                            {{-- Input Field --}}
                            <input type="text" 
                                   wire:model.defer="inlineCreateTitle"
                                   wire:keydown.enter="createInlineItem"
                                   wire:keydown.escape="cancelInlineCreate"
                                   placeholder="Enter Epic title and press Enter..."
                                   class="flex-1 px-3 py-1.5 text-sm border-0 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-purple-500 rounded"
                                   autofocus>
                            
                            {{-- Action Buttons --}}
                            <div class="flex items-center space-x-1 ml-2">
                                <button wire:click="createInlineItem()"
                                        class="p-1.5 bg-purple-600 dark:bg-purple-700 text-white rounded hover:bg-purple-700 dark:hover:bg-purple-600 transition-colors"
                                        title="Create (Enter)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </button>
                                <button wire:click="cancelInlineCreate()"
                                        class="p-1.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                        title="Cancel (Esc)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Panel: Detail View --}}
        <div class="w-1/2 overflow-y-auto bg-white dark:bg-gray-800">
            @if($this->selectedItem)
                <div class="p-6">
                    {{-- Item Header --}}
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <span class="text-2xl">{!! $this->selectedItem->getTypeIcon() !!}</span>
                                <span class="px-2 py-1 text-xs font-mono rounded" style="background-color: {{ $this->selectedItem->getTypeColor() }}20; color: {{ $this->selectedItem->getTypeColor() }}">
                                    {{ $this->selectedItem->code }}
                                </span>
                                <span class="px-2 py-1 text-xs rounded" style="background-color: {{ $this->selectedItem->getStatusColor() }}20; color: {{ $this->selectedItem->getStatusColor() }}">
                                    {{ $this->selectedItem->status }}
                                </span>
                                <span class="px-2 py-1 text-xs rounded" style="background-color: {{ $this->selectedItem->getPriorityColor() }}20; color: {{ $this->selectedItem->getPriorityColor() }}">
                                    {{ $this->selectedItem->priority }}
                                </span>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->selectedItem->title }}</h2>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button wire:click="startEditing({{ $this->selectedItem->id }})" class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button wire:click="deleteItem({{ $this->selectedItem->id }})" onclick="return confirm('Are you sure? This will delete all child items too.')" class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Tabs --}}
                    <div class="border-b border-gray-200 dark:border-gray-700 mb-6" x-data="{ activeTab: 'details' }">
                        <nav class="-mb-px flex space-x-8">
                            <button @click="activeTab = 'details'" :class="activeTab === 'details' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Details
                            </button>
                            <button @click="activeTab = 'comments'" :class="activeTab === 'comments' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Comments ({{ $this->comments->count() }})
                            </button>
                            <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                History ({{ $this->history->count() }})
                            </button>
                        </nav>

                        {{-- Details Tab --}}
                        <div x-show="activeTab === 'details'" class="py-6">
                            @if($editingItemId === $this->selectedItem->id)
                                {{-- Edit Form --}}
                                <form wire:submit.prevent="saveItem" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                                        <input type="text" wire:model.defer="editTitle" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        @error('editTitle') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                        <textarea wire:model.defer="editDescription" rows="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                            <select wire:model.defer="editStatus" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                <option value="To Do">To Do</option>
                                                <option value="In Progress">In Progress</option>
                                                <option value="Done">Done</option>
                                                <option value="Blocked">Blocked</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority</label>
                                            <select wire:model.defer="editPriority" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                <option value="Critical">Critical</option>
                                                <option value="High">High</option>
                                                <option value="Medium">Medium</option>
                                                <option value="Low">Low</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assignee</label>
                                            <select wire:model.defer="editAssigneeId" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                <option value="">Unassigned</option>
                                                @foreach($this->teamMembers as $member)
                                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sprint</label>
                                            <select wire:model.defer="editSprintId" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                <option value="">No Sprint</option>
                                                @foreach($this->sprints as $sprint)
                                                    <option value="{{ $sprint->id }}">{{ $sprint->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estimated Hours</label>
                                            <input type="number" step="0.5" wire:model.defer="editEstimatedHours" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                                            <input type="date" wire:model.defer="editStartDate" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                                            <input type="date" wire:model.defer="editDueDate" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        </div>
                                    </div>

                                    <div class="flex justify-end space-x-3 pt-4">
                                        <button type="button" wire:click="cancelEdit()" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                                            Cancel
                                        </button>
                                        <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                            Save Changes
                                        </button>
                                    </div>
                                </form>
                            @else
                                {{-- View Mode --}}
                                <div class="space-y-6">
                                    @if($this->selectedItem->description)
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</h3>
                                            <div class="prose dark:prose-invert max-w-none text-gray-900 dark:text-white">
                                                {!! nl2br(e($this->selectedItem->description)) !!}
                                            </div>
                                        </div>
                                    @endif

                                    <div class="grid grid-cols-2 gap-6">
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assignee</h3>
                                            <p class="text-gray-900 dark:text-white">
                                                {{ $this->selectedItem->assignee ? $this->selectedItem->assignee->name : 'Unassigned' }}
                                            </p>
                                        </div>

                                        <div>
                                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sprint</h3>
                                            <p class="text-gray-900 dark:text-white">
                                                {{ $this->selectedItem->sprint ? $this->selectedItem->sprint->name : 'No Sprint' }}
                                            </p>
                                        </div>

                                        <div>
                                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estimated Hours</h3>
                                            <p class="text-gray-900 dark:text-white">
                                                {{ $this->selectedItem->estimated_hours ?? 'Not set' }}
                                            </p>
                                        </div>

                                        <div>
                                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Total Hours (with children)</h3>
                                            <p class="text-gray-900 dark:text-white">
                                                {{ $this->selectedItem->getTotalEstimatedHours() }} hours
                                            </p>
                                        </div>

                                        @if($this->selectedItem->start_date)
                                            <div>
                                                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Date</h3>
                                                <p class="text-gray-900 dark:text-white">
                                                    {{ $this->selectedItem->start_date->format('M d, Y') }}
                                                </p>
                                            </div>
                                        @endif

                                        @if($this->selectedItem->due_date)
                                            <div>
                                                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Due Date</h3>
                                                <p class="text-gray-900 dark:text-white">
                                                    {{ $this->selectedItem->due_date->format('M d, Y') }}
                                                </p>
                                            </div>
                                        @endif

                                        @if($this->selectedItem->children->count() > 0)
                                            <div>
                                                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Completion</h3>
                                                <div class="flex items-center space-x-3">
                                                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $this->selectedItem->getCompletionPercentage() }}%"></div>
                                                    </div>
                                                    <span class="text-sm text-gray-900 dark:text-white">{{ $this->selectedItem->getCompletionPercentage() }}%</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Comments Tab --}}
                        <div x-show="activeTab === 'comments'" class="py-6">
                            @include('livewire.project.partials.backlog-comments')
                        </div>

                        {{-- History Tab --}}
                        <div x-show="activeTab === 'history'" class="py-6">
                            @include('livewire.project.partials.backlog-history')
                        </div>
                    </div>
                </div>
            @else
                <div class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No item selected</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select an item from the left to view details</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('error') }}
        </div>
    @endif
</div>

{{-- Sortable.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

{{-- Drag and Drop Functionality --}}
<script>
function backlogDragDrop() {
    return {
        sortableInstances: [],
        
        init() {
            // Wait for Livewire to finish rendering
            this.$nextTick(() => {
                setTimeout(() => {
                    this.initializeSortable();
                }, 100);
            });
            
            // Reinitialize after Livewire updates
            Livewire.hook('message.processed', () => {
                setTimeout(() => {
                    this.destroyAllInstances();
                    this.initializeSortable();
                }, 100);
            });
        },
        
        destroyAllInstances() {
            this.sortableInstances.forEach(instance => {
                if (instance && instance.destroy) {
                    instance.destroy();
                }
            });
            this.sortableInstances = [];
        },
        
        initializeSortable() {
            const treePanel = this.$el.querySelector('.w-1\\/2.border-r .p-4');
            if (!treePanel) {
                console.log('Tree panel not found');
                return;
            }
            
            // Initialize root level sortable
            const rootInstance = this.createSortableInstance(treePanel, true);
            if (rootInstance) {
                this.sortableInstances.push(rootInstance);
            }
            
            // Initialize all children containers
            const childContainers = treePanel.querySelectorAll('.children');
            childContainers.forEach(container => {
                const instance = this.createSortableInstance(container, false);
                if (instance) {
                    this.sortableInstances.push(instance);
                }
            });
        },
        
        createSortableInstance(container, isRoot = false) {
            if (!container) return null;
            
            try {
                return new Sortable(container, {
                    animation: 200,
                    handle: '.drag-handle',
                    ghostClass: 'opacity-30 bg-blue-100 dark:bg-blue-900',
                    chosenClass: 'ring-2 ring-blue-500',
                    dragClass: 'opacity-50',
                    group: {
                        name: 'backlog-items',
                        pull: true,
                        put: true
                    },
                    fallbackOnBody: true,
                    swapThreshold: 0.65,
                    invertSwap: true,
                    direction: 'vertical',
                    
                    onEnd: (evt) => {
                        const itemElement = evt.item;
                        const itemId = itemElement.dataset.itemId;
                        const itemType = itemElement.dataset.type;
                        
                        // Find new parent
                        let newParentId = null;
                        let newParentType = null;
                        
                        // Check if dropped into a children container
                        if (evt.to.classList.contains('children')) {
                            const parentRow = evt.to.closest('.backlog-item-row');
                            if (parentRow) {
                                newParentId = parentRow.dataset.itemId;
                                newParentType = parentRow.dataset.type;
                            }
                        }
                        
                        // Validate the move
                        if (!this.isValidMove(itemType, newParentType)) {
                            // Revert the move
                            if (evt.from !== evt.to) {
                                evt.to.removeChild(evt.item);
                                evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex] || null);
                            } else {
                                const items = Array.from(evt.from.children);
                                evt.from.removeChild(evt.item);
                                evt.from.insertBefore(evt.item, items[evt.oldIndex] || null);
                            }
                            
                            // Show error
                            @this.call('$refresh');
                            alert('Invalid hierarchy: ' + itemType + ' cannot be placed under ' + (newParentType || 'root'));
                            return;
                        }
                        
                        // Call Livewire method
                        @this.call('handleItemMoved', parseInt(itemId), newParentId ? parseInt(newParentId) : null, evt.newIndex);
                    }
                });
            } catch (error) {
                console.error('Error creating Sortable instance:', error);
                return null;
            }
        },
        
        isValidMove(itemType, parentType) {
            // Root level moves
            if (!parentType) {
                return itemType === 'Epic'; // Only Epics can be at root
            }
            
            // Valid parent-child relationships
            const validMoves = {
                'Epic': [],  // Epics can't have parents
                'Feature': ['Epic'],
                'UserStory': ['Feature'],
                'Task': ['UserStory'],
                'Subtask': ['Task']
            };
            
            return validMoves[itemType] && validMoves[itemType].includes(parentType);
        }
    }
}
</script>
