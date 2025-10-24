<div>
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
