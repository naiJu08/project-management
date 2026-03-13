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

    @push('scripts')
        <script src="{{ asset('js/Sortable.js') }}"></script>
        <script>

            (() => {
                let record;
                @foreach($this->getStatuses() as $status)
                    record = document.querySelector('#status-records-{{ $status['id'] }}');

                    Sortable.create(record, {
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
                    })
                @endforeach
            })();
        </script>
    @endpush

</div>
