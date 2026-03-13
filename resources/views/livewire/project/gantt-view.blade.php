<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                Gantt
                <x-help-sidebar 
                    title="Gantt Chart"
                    description="Visualize project timeline with task dependencies and schedules"
                    :features="[
                        'Visual timeline representation',
                        'Task scheduling and duration',
                        'Task dependencies',
                        'Milestone markers',
                        'Resource allocation',
                        'Progress tracking',
                        'Zoom and pan controls',
                        'Export timeline'
                    ]"
                    :benefits="[
                        'See project timeline at a glance',
                        'Identify critical path',
                        'Manage task dependencies',
                        'Track progress visually',
                        'Better resource planning',
                        'Improve scheduling'
                    ]"
                    implementation="<p>1. View tasks on timeline</p><p>2. Drag tasks to reschedule</p><p>3. Click task for details</p><p>4. Use zoom controls</p><p>5. View dependencies</p><p>6. Export chart</p>"
                    :examples="[
                        ['title' => 'Sprint Timeline', 'description' => 'View sprint tasks and dependencies'],
                        ['title' => 'Release Planning', 'description' => 'Plan release timeline with milestones'],
                        ['title' => 'Resource Allocation', 'description' => 'See team member workload']
                    ]"
                />
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Gantt chart for {{ $this->project->name }}</p>
        </div>
    </div>

    {{-- Controls --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-4">
        {{-- Zoom and View Controls --}}
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Zoom:</span>
                <button wire:click="zoomOut()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors" title="Zoom out">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                </button>
                <div class="flex gap-1">
                    <button wire:click="setZoomLevel('day')" class="px-3 py-1 text-xs font-medium rounded {{ $zoomLevel === 'day' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        Day
                    </button>
                    <button wire:click="setZoomLevel('week')" class="px-3 py-1 text-xs font-medium rounded {{ $zoomLevel === 'week' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        Week
                    </button>
                    <button wire:click="setZoomLevel('month')" class="px-3 py-1 text-xs font-medium rounded {{ $zoomLevel === 'month' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        Month
                    </button>
                </div>
                <button wire:click="zoomIn()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors" title="Zoom in">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
            </div>

            <div class="border-l border-gray-300 dark:border-gray-600 h-6"></div>

            {{-- Toggle Visibility --}}
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model="showSprints" class="rounded">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Show Sprints</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model="showTickets" class="rounded">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Show Tickets</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Gantt Chart --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <div class="min-w-full">
                {{-- Header with dates --}}
                <div class="flex border-b border-gray-200 dark:border-gray-700">
                    {{-- Task names column --}}
                    <div class="w-64 flex-shrink-0 border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-4">
                        <div class="font-semibold text-gray-900 dark:text-white text-sm">Tasks</div>
                    </div>

                    {{-- Timeline header --}}
                    <div class="flex-1 flex">
                        @foreach($dateRange as $date)
                            @php
                                if ($zoomLevel === 'day') {
                                    $label = $date->format('M d');
                                    $width = 'w-16';
                                } elseif ($zoomLevel === 'week') {
                                    $label = 'W' . $date->format('W');
                                    $width = 'w-24';
                                } else {
                                    $label = $date->format('M Y');
                                    $width = 'w-32';
                                }
                            @endphp
                            <div class="{{ $width }} flex-shrink-0 border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-2 text-center">
                                <div class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $label }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Gantt rows --}}
                @forelse($ganttData as $item)
                    @php
                        $position = ($getBarPosition)($item);
                        $colorClass = match($item['color']) {
                            'purple' => 'bg-purple-500',
                            'red' => 'bg-red-500',
                            'orange' => 'bg-orange-500',
                            'yellow' => 'bg-yellow-500',
                            'green' => 'bg-green-500',
                            default => 'bg-blue-500',
                        };
                    @endphp
                    <div class="flex border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        {{-- Task name --}}
                        <div class="w-64 flex-shrink-0 border-r border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-800">
                            <div class="flex items-start gap-2">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $item['name'] }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        @if($item['type'] === 'sprint')
                                            <span class="inline-block px-2 py-0.5 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded">Sprint</span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">{{ $item['status'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Timeline bar --}}
                        <div class="flex-1 relative p-2 bg-white dark:bg-gray-800">
                            <div class="relative h-10 bg-gray-100 dark:bg-gray-700 rounded">
                                {{-- Bar --}}
                                <div class="absolute top-1 bottom-1 rounded {{ $colorClass }} opacity-80 hover:opacity-100 transition-opacity cursor-pointer group" 
                                     style="left: {{ $position['left'] }}%; width: {{ $position['width'] }}%;" 
                                     title="{{ $item['name'] }} - {{ $item['startDate']->format('M d, Y') }} to {{ $item['endDate']->format('M d, Y') }}">
                                    {{-- Progress indicator --}}
                                    <div class="absolute top-0 left-0 bottom-0 {{ $colorClass }} opacity-40 rounded" 
                                         style="width: {{ $item['progress'] }}%;"></div>
                                    
                                    {{-- Progress text --}}
                                    @if($position['width'] > 10)
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <span class="text-xs font-semibold text-white drop-shadow">{{ $item['progress'] }}%</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex border-b border-gray-200 dark:border-gray-700">
                        <div class="w-64 flex-shrink-0 border-r border-gray-200 dark:border-gray-700 p-4"></div>
                        <div class="flex-1 p-8 text-center text-gray-500 dark:text-gray-400">
                            <p>No items to display. Enable sprints or tickets in the controls above.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Legend --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Legend</h4>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="flex items-center gap-2">
                <div class="w-6 h-4 bg-purple-500 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Sprints</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-6 h-4 bg-red-500 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Critical</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-6 h-4 bg-orange-500 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">High</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-6 h-4 bg-yellow-500 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Medium</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-6 h-4 bg-green-500 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Low</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-6 h-4 bg-blue-500 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Tickets</span>
            </div>
        </div>
    </div>

    {{-- Timeline Info --}}
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex gap-2">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            <div class="text-sm text-blue-800 dark:text-blue-300">
                <p><strong>Timeline:</strong> {{ $startDate->format('M d, Y') }} to {{ $endDate->format('M d, Y') }}</p>
                <p class="mt-1">Darker color indicates progress. Hover over bars to see details.</p>
            </div>
        </div>
    </div>
</div>
