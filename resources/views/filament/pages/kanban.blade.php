<x-filament::page>

    <div class="mx-auto w-full">
        <details class="w-full bg-white dark:bg-gray-800 open:bg-gray-100 dark:open:bg-gray-900 duration-300 ai-card rounded-lg border border-gray-200 dark:border-gray-700">
            <summary
                class="relative w-full bg-inherit px-5 py-3 text-base cursor-pointer text-gray-700 dark:text-gray-200">
                {{ __('Filters') }}
            </summary>
            <div class="bg-white dark:bg-gray-800 px-5 py-3 ai-surface rounded-b-lg">
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

</x-filament::page>
