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

    <div class="kanban-container" x-data="{ initSortable: null, reInitSortable: null }" x-init="
        setTimeout(() => {
            if (typeof initSortableFunction === 'function') {
                initSortableFunction();
            }
        }, 100);
    " x-on:window:focus.debounce.200ms="
        if (typeof reInitSortableFunction === 'function') {
            reInitSortableFunction();
        }
    " x-on:livewire-updated.debounce.300ms="
        if (typeof reInitSortableFunction === 'function') {
            reInitSortableFunction();
        }
    ">

        @foreach($this->getStatuses() as $status)
            @include('partials.kanban.status')
        @endforeach

    </div>

    @push('scripts')
        <script src="{{ asset('js/Sortable.js') }}"></script>
        <script>

            (() => {
                let sortableInstances = [];

                function initSortable() {
                    console.log('Initializing Sortable instances...');
                    // Destroy existing instances
                    sortableInstances.forEach(instance => {
                        try {
                            instance.destroy();
                        } catch (e) {
                            console.error('Error destroying Sortable instance:', e);
                        }
                    });
                    sortableInstances = [];

                    let record;
                    @foreach($this->getStatuses() as $status)
                        record = document.querySelector('#status-records-{{ $status['id'] }}');

                        if (record) {
                            console.log('Creating Sortable for:', record.id);
                            const sortable = Sortable.create(record, {
                                group: {
                                    name: 'status-{{ $status['id'] }}',
                                    pull: true,
                                    put: true
                                },
                                handle: '.handle',
                                animation: 100,
                                onEnd: function (evt) {
                                    Livewire.emit('recordUpdated',
                                        +evt.clone.dataset.id, // id
                                        +evt.newIndex, // newIndex
                                        +evt.to.dataset.status, // newStatus
                                    );
                                },
                            });
                            sortableInstances.push(sortable);
                        }
                    @endforeach
                }

                // Make functions globally accessible for Alpine
                window.initSortableFunction = initSortable;
                window.reInitSortableFunction = () => {
                    console.log('Re-initializing Sortable...');
                    setTimeout(() => {
                        initSortable();
                    }, 200);
                };

                // Initialize on page load
                initSortable();

                const reInitSortable = window.reInitSortableFunction;

                // Multiple event listeners for better reliability
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) {
                        console.log('Tab visible, re-initializing Sortable...');
                        reInitSortable();
                    }
                });

                window.addEventListener('focus', reInitSortable);
                window.addEventListener('pageshow', reInitSortable);
                window.addEventListener('resize', reInitSortable);

                // Livewire updated hook
                if (window.Livewire) {
                    Livewire.on('recordUpdated', () => {
                        console.log('Livewire recordUpdated, re-initializing Sortable...');
                        setTimeout(() => initSortable(), 300);
                    });
                }

                // MutationObserver to detect DOM changes
                const observer = new MutationObserver((mutations) => {
                    let shouldReInit = false;
                    mutations.forEach((mutation) => {
                        if (mutation.addedNodes.length > 0 || mutation.removedNodes.length > 0) {
                            shouldReInit = true;
                        }
                    });
                    if (shouldReInit) {
                        setTimeout(() => initSortable(), 300);
                    }
                });

                const kanbanContainer = document.querySelector('.kanban-container');
                if (kanbanContainer) {
                    observer.observe(kanbanContainer, {
                        childList: true,
                        subtree: true
                    });
                }

                // Periodic check every 5 seconds to ensure Sortable is working
                setInterval(() => {
                    const records = document.querySelectorAll('[id^="status-records-"]');
                    let needsReInit = false;
                    records.forEach(record => {
                        // Check if the element has the Sortable class or if drag is not working
                        if (!record.classList.contains('sortable-ghost') && sortableInstances.length === 0) {
                            needsReInit = true;
                        }
                    });
                    if (needsReInit || sortableInstances.length === 0) {
                        console.log('Periodic check: Re-initializing Sortable...');
                        initSortable();
                    }
                }, 5000);
            })();
        </script>
    @endpush

</div>
