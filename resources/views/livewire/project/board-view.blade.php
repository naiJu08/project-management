<div>
    {{-- Header with Help Sidebar --}}
    <div class="flex items-center justify-between mb-4 px-4 sm:px-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
            Board
            <x-help-sidebar 
                title="Kanban Board"
                description="Visualize and manage tasks using a Kanban board with drag-and-drop functionality"
                :features="[
                    'Drag and drop tasks between columns',
                    'View tasks by status (To Do, In Progress, Done)',
                    'Filter by assignee, priority, and type',
                    'Quick task creation in any column',
                    'Task details and editing',
                    'Real-time updates',
                    'Customizable columns',
                    'Search and filter options'
                ]"
                :benefits="[
                    'Visualize workflow at a glance',
                    'Improve team productivity',
                    'Identify bottlenecks quickly',
                    'Reduce context switching',
                    'Better task prioritization',
                    'Enhance collaboration'
                ]"
                implementation="<p>1. View tasks organized by status columns</p><p>2. Click and drag tasks to move between columns</p><p>3. Use filters to focus on specific work</p><p>4. Click '+' to add new tasks</p><p>5. Click task card to view details</p><p>6. Update task status by dragging</p>"
                :examples="[
                    ['title' => 'Sprint Planning', 'description' => 'Organize sprint tasks from backlog to in-progress'],
                    ['title' => 'Daily Standup', 'description' => 'Review board status and identify blockers'],
                    ['title' => 'Release Management', 'description' => 'Track features through development to release']
                ]"
            />
        </h2>
    </div>

    <div class="mx-auto w-full" wire:ignore>
        <details class="w-full bg-white dark:bg-gray-800 open:bg-gray-200 dark:open:bg-gray-700 duration-300 ai-card">
            <summary
                class="relative w-full bg-inherit px-5 py-3 text-base cursor-pointer text-gray-500 dark:text-gray-400">
                {{ __('Filters') }}
            </summary>
            <div class="bg-white dark:bg-gray-800 px-5 py-3 ai-surface rounded-xl">
                <form>
                    {{ $this->form }}
                </form>
            </div>
        </details>
    </div>

    <div class="kanban-container">

        @foreach($this->getStatuses() as $status)
            @include('partials.kanban.status')
        @endforeach

    </div>

    <script>
        (function () {
            var _kanbanSortableInstances = [];
            var _kanbanVisibilityHandler = null;
            var _kanbanLivewireCleanup = null;

            function destroyKanbanInstances() {
                _kanbanSortableInstances.forEach(function (inst) {
                    if (inst && typeof inst.destroy === 'function') {
                        inst.destroy();
                    }
                });
                _kanbanSortableInstances = [];
            }

            function initKanbanSortable() {
                destroyKanbanInstances();

                @foreach($this->getStatuses() as $status)
                var el{{ $status['id'] }} = document.querySelector('#status-records-{{ $status['id'] }}');
                if (el{{ $status['id'] }}) {
                    _kanbanSortableInstances.push(Sortable.create(el{{ $status['id'] }}, {
                        group: {
                            name: 'kanban-board',
                            pull: true,
                            put: true
                        },
                        handle: '.handle',
                        animation: 100,
                        onEnd: function (evt) {
                            Livewire.emit('recordUpdated',
                                +evt.clone.dataset.id,
                                +evt.newIndex,
                                +evt.to.dataset.status
                            );
                        }
                    }));
                }
                @endforeach
            }

            function setupKanban() {
                if (typeof Sortable === 'undefined') {
                    return;
                }

                initKanbanSortable();

                // Reinitialize after every Livewire re-render (register only once)
                if (typeof Livewire !== 'undefined' && typeof Livewire.hook === 'function' && !window._kanbanBoardLivewireCleanup) {
                    window._kanbanBoardLivewireCleanup = Livewire.hook('message.processed', function () {
                        initKanbanSortable();
                    });
                }

                // Reinitialize when user returns to this browser tab (register only once)
                if (!window._kanbanBoardVisibilityHandler) {
                    window._kanbanBoardVisibilityHandler = function () {
                        if (document.visibilityState === 'visible') {
                            initKanbanSortable();
                        }
                    };
                    document.addEventListener('visibilitychange', window._kanbanBoardVisibilityHandler);
                }
            }

            function loadSortableAndSetup() {
                if (typeof Sortable !== 'undefined') {
                    setupKanban();
                    return;
                }
                // Load Sortable.js if not already present
                if (!document.querySelector('script[data-sortable-board]')) {
                    var s = document.createElement('script');
                    s.src = '{{ asset('js/Sortable.js') }}';
                    s.setAttribute('data-sortable-board', '1');
                    s.onload = setupKanban;
                    document.head.appendChild(s);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', loadSortableAndSetup);
            } else {
                setTimeout(loadSortableAndSetup, 50);
            }
        })();
    </script>

</div>
