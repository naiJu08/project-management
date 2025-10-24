{{-- Recursive Backlog Item Row Component --}}
@php
    $isExpanded = in_array($item->id, $expandedItems);
    $hasChildren = $item->children->count() > 0;
    $isSelected = $selectedItemId === $item->id;
    $indent = $level * 24; // 24px per level
@endphp

<div class="backlog-item-row" data-item-id="{{ $item->id }}" data-parent-id="{{ $item->parent_id }}" data-type="{{ $item->type }}">
    {{-- Item Row --}}
    <div class="flex items-center hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg group {{ $isSelected ? 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500' : '' }}"
         style="padding-left: {{ $indent }}px">
        
        {{-- Selection Checkbox --}}
        <div class="pl-2">
            <input type="checkbox" 
                   wire:click="toggleItemSelection({{ $item->id }})"
                   @if(in_array($item->id, $selectedItems)) checked @endif
                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 cursor-pointer">
        </div>
        
        {{-- Drag Handle --}}
        <div class="drag-handle p-2 cursor-move opacity-0 group-hover:opacity-100 transition-opacity">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
            </svg>
        </div>

        {{-- Expand/Collapse Button --}}
        <button wire:click="toggleExpand({{ $item->id }})" 
                class="p-1 {{ $hasChildren ? '' : 'invisible' }}">
            @if($hasChildren)
                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400 transform transition-transform {{ $isExpanded ? 'rotate-90' : '' }}" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            @endif
        </button>

        {{-- Item Content --}}
        <div wire:click="selectItem({{ $item->id }})" 
             class="flex-1 flex items-center py-2 px-3 cursor-pointer min-w-0">
            
            {{-- Type Icon --}}
            <span class="text-lg mr-2 flex-shrink-0">{!! $item->getTypeIcon() !!}</span>

            {{-- Code Badge --}}
            <span class="px-2 py-0.5 text-xs font-mono rounded mr-2 flex-shrink-0" 
                  style="background-color: {{ $item->getTypeColor() }}20; color: {{ $item->getTypeColor() }}">
                {{ $item->code }}
            </span>

            {{-- Title --}}
            <span class="text-sm text-gray-900 dark:text-white truncate flex-1">
                {{ $item->title }}
            </span>

            {{-- Status Badge --}}
            <span class="px-2 py-0.5 text-xs rounded ml-2 flex-shrink-0" 
                  style="background-color: {{ $item->getStatusColor() }}20; color: {{ $item->getStatusColor() }}">
                {{ $item->status }}
            </span>

            {{-- Priority Badge --}}
            <span class="px-2 py-0.5 text-xs rounded ml-2 flex-shrink-0" 
                  style="background-color: {{ $item->getPriorityColor() }}20; color: {{ $item->getPriorityColor() }}">
                {{ $item->priority }}
            </span>

            {{-- Assignee Avatar --}}
            @if($item->assignee)
                <div class="ml-2 flex-shrink-0" title="{{ $item->assignee->name }}">
                    <div class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-medium">
                        {{ substr($item->assignee->name, 0, 1) }}
                    </div>
                </div>
            @endif

            {{-- Sprint Badge --}}
            @if($item->sprint)
                <span class="ml-2 px-2 py-0.5 text-xs bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded flex-shrink-0">
                    {{ $item->sprint->name }}
                </span>
            @endif

            {{-- Estimated Hours --}}
            @if($item->estimated_hours)
                <span class="ml-2 text-xs text-gray-500 dark:text-gray-400 flex-shrink-0">
                    {{ $item->estimated_hours }}h
                </span>
            @endif
            
            {{-- Linked Ticket Indicator --}}
            @if(in_array($item->type, ['Task', 'Subtask']) && $item->tickets->count() > 0)
                <span class="ml-2 px-2 py-0.5 text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded flex-shrink-0 flex items-center" title="Linked to {{ $item->tickets->count() }} ticket(s)">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                    {{ $item->tickets->first()->code }}
                </span>
            @endif
        </div>

        {{-- Quick Actions (visible on hover) --}}
        <div class="flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition-opacity pr-2" x-data="{ showMenu: false }">
            {{-- Add Child Button --}}
            @if($item->canHaveChildren())
                <div class="relative">
                    <button @click="showMenu = !showMenu" 
                            class="p-1 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors"
                            title="Add child item">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </button>
                    
                    {{-- Dropdown Menu --}}
                    <div x-show="showMenu" 
                         @click.away="showMenu = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-1 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg z-20 border border-gray-200 dark:border-gray-700 py-1">
                        @foreach($item->getAllowedChildTypes() as $childType)
                            @if(in_array($childType, ['Task', 'Subtask']))
                                {{-- Task/Subtask: Redirect to ticket form --}}
                                <a href="{{ route('filament.resources.tickets.create', ['project' => $item->project_id, 'backlog_parent' => $item->id, 'backlog_type' => $childType]) }}" 
                                   class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <span class="text-base mr-2">
                                        @if($childType === 'Task') ✓
                                        @else ▫
                                        @endif
                                    </span>
                                    Add {{ $childType }} (Ticket)
                                </a>
                            @else
                                {{-- Epic, Feature, User Story: Use inline creation --}}
                                <button wire:click="showInlineCreate({{ $item->id }}, '{{ $childType }})" 
                                        @click="showMenu = false"
                                        class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <span class="text-base mr-2">
                                        @if($childType === 'Feature') 🔷
                                        @elseif($childType === 'UserStory') 📖
                                        @endif
                                    </span>
                                    Add {{ $childType }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- More Actions --}}
            <div class="relative" x-data="{ showActions: false }">
                <button @click="showActions = !showActions" 
                        class="p-1 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                    </svg>
                </button>
                
                <div x-show="showActions" 
                     @click.away="showActions = false"
                     class="absolute right-0 mt-1 w-48 bg-white dark:bg-gray-700 rounded-lg shadow-lg z-10 border border-gray-200 dark:border-gray-600 py-1">
                    <button wire:click="startEditing({{ $item->id }})" 
                            @click="showActions = false"
                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                        Edit
                    </button>
                    
                    @if(in_array($item->type, ['Task', 'Subtask']) && $item->tickets->count() > 0)
                        <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                        @foreach($item->tickets as $ticket)
                            <a href="{{ route('filament.resources.tickets.edit', ['record' => $ticket->id]) }}" 
                               target="_blank"
                               class="flex items-center w-full text-left px-4 py-2 text-sm text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                </svg>
                                View Ticket {{ $ticket->code }}
                            </a>
                        @endforeach
                    @endif
                    
                    @if($this->sprints->count() > 0)
                        <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                        <div class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400">Assign to Sprint</div>
                        @foreach($this->sprints as $sprint)
                            <button wire:click="assignToSprint({{ $item->id }}, {{ $sprint->id }})" 
                                    @click="showActions = false"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                {{ $sprint->name }}
                            </button>
                        @endforeach
                        @if($item->sprint_id)
                            <button wire:click="removeFromSprint({{ $item->id }})" 
                                    @click="showActions = false"
                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                Remove from Sprint
                            </button>
                        @endif
                    @endif
                    
                    <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                    <button wire:click="deleteItem({{ $item->id }})" 
                            onclick="return confirm('Are you sure? This will delete all child items too.')"
                            @click="showActions = false"
                            class="block w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Inline Creation Form --}}
    @if($inlineCreateParentId === $item->id && $inlineCreateType)
        <div class="inline-create-form" style="padding-left: {{ ($level + 1) * 24 }}px">
            <div class="flex items-center py-2 px-3 bg-green-50 dark:bg-green-900/10 border-l-4 border-green-500 rounded-r-lg ml-2 mr-2 animate-fadeIn">
                {{-- Type Icon --}}
                <span class="text-lg mr-2 flex-shrink-0">
                    @if($inlineCreateType === 'Feature') 🔷
                    @elseif($inlineCreateType === 'UserStory') 📖
                    @elseif($inlineCreateType === 'Task') ✓
                    @elseif($inlineCreateType === 'Subtask') ▫
                    @endif
                </span>
                
                {{-- Input Field --}}
                <input type="text" 
                       wire:model.defer="inlineCreateTitle"
                       wire:keydown.enter="createInlineItem"
                       wire:keydown.escape="cancelInlineCreate"
                       placeholder="Enter {{ $inlineCreateType }} title and press Enter..."
                       class="flex-1 px-3 py-1.5 text-sm border-0 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-green-500 rounded"
                       autofocus>
                
                {{-- Action Buttons --}}
                <div class="flex items-center space-x-1 ml-2">
                    <button wire:click="createInlineItem()"
                            class="p-1.5 bg-green-600 dark:bg-green-700 text-white rounded hover:bg-green-700 dark:hover:bg-green-600 transition-colors"
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

    {{-- Children (recursive) --}}
    @if($isExpanded && ($hasChildren || $inlineCreateParentId === $item->id))
        <div class="children">
            @foreach($item->children->sortBy('order_index') as $child)
                @include('livewire.project.partials.backlog-item-row', ['item' => $child, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>
