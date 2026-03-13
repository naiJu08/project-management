<div class="kanban-statuses flex flex-colh-full bg-gray-50/60 dark:bg-gray-900/40 rounded-xl border border-gray-200 dark:border-gray-700">
    <div class="status-header sticky top-0 z-10 px-3 py-3 rounded-t-xl flex items-center justify-between text-sm font-semibold text-gray-800 dark:text-gray-200"
         style="background: {{ $status['color'] }}0D; border-color: {{ $status['color'] }}66;">
        <span class="flex items-center gap-2">
            <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $status['color'] }};"></span>
            {{ $status['title'] }}
        </span>
        @if($status['size'])
            <span class="text-xs text-gray-600 dark:text-gray-400">{{ $status['size'] }} {{ __($status['size'] > 1 ? 'tickets' : 'ticket') }}</span>
        @endif
    </div>
    <div class="status-container flex-1 min-h-[340px] px-3 pt-3 space-y-3 overflow-y-auto"
         data-status="{{ $status['id'] }}"
         id="status-records-{{ $status['id'] }}"
         style="border-color: {{ $status['color'] }}66;">
        @foreach($this->getRecords()->where('status', $status['id']) as $record)
            @include('partials.kanban.record')
        @endforeach

        <!-- href="{{ route('filament.resources.tickets.create', ['project' => request()->get('project')]) }}" -->
        @if($status['add_ticket'])
            <div class="-ml-1">
              <a
                href="{{ route('filament.resources.tickets.create', ['project' => $this->project->id]) }}"
                class="create-record hover:cursor-pointer mt-2 inline-flex items-center gap-1 text-xs px-2 py-1 rounded-md bg-white/60 dark:bg-gray-800/60 hover:bg-white dark:hover:bg-gray-800 border border-dashed border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300"
                >
                <x-heroicon-o-plus class="w-4 h-4" />
                {{ __('Create ticket') }}
                </a>
            </div>

            @if($ticket)
                <!-- Epic modal -->
                <div class="dialog-container">
                    <div class="dialog dialog-xl">
                        <div class="dialog-header">
                            {{ __('Create ticket') }}
                        </div>
                        <div class="dialog-content">
                        @livewire('road-map.issue-form', ['project_id' => $this->project->id])
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
