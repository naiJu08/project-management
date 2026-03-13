<div class="kanban-record group flex flex-col gap-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow" data-id="{{ $record['id'] }}">
    <div class="flex items-start gap-3">
        <button type="button" class="handle shrink-0 mt-0.5 inline-flex items-center justify-center w-7 h-7 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 cursor-move">
            <x-heroicon-o-arrows-expand class="w-4 h-4" />
        </button>
        <div class="record-info min-w-0">
        @if($this->isMultiProject())
            <span class="record-subtitle">
                {{ $record['project']->name }}
            </span>
        @endif
        <a href="{{ route('filament.resources.tickets.view', $record['id']) }}"
           target="_blank"
           class="record-title block">
            <span class="code inline-flex items-center px-2 py-0.5 mr-2 text-xs font-semibold rounded bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">{{ $record['code'] }}</span>
            <span class="title font-medium text-gray-900 dark:text-white break-words">{{ $record['title'] }}</span>
        </a>
        </div>
    </div>
    <div class="record-footer flex items-center justify-between">
        <div class="record-type-code flex items-center gap-2 flex-wrap mt-1">
            @php($epic = $record['epic'])
            @if($epic && $epic != "")
                <div class="px-3 py-0.5 rounded flex items-center justify-center text-center text-xs text-white bg-blue-600" title="{{ __('Epic') }}">
                    {{ $epic->name }}
                </div>
            @endif
            <x-ticket-priority :priority="$record['priority']" />
            <x-ticket-type :type="$record['type']" />
        </div>
        @if($record['responsible'])
            <x-user-avatar :user="$record['responsible']" class="ml-2" />
        @endif
    </div>
    @if($record['relations']?->count())
        <div class="record-relations mt-2 space-y-1">
            @foreach($record['relations'] as $relation)
                <div class="text-xs">
                    <span class="type mr-2 inline-flex items-center px-1.5 py-0.5 rounded bg-{{ config('system.tickets.relations.colors.' . $relation->type) }}-100 text-{{ config('system.tickets.relations.colors.' . $relation->type) }}-700">
                        {{ __(config('system.tickets.relations.list.' . $relation->type)) }}
                    </span>
                    <a target="_blank" class="relation text-blue-600 dark:text-blue-400 hover:underline"
                        href="{{ route('filament.resources.tickets.share', $relation->relation->code) }}">
                        {{ $relation->relation->code }}
                    </a>
                </div>
            @endforeach
        </div>
    @endif
    @if($record['totalLoggedHours'])
        <div class="record-logged-hours mt-2 inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-300">
            <x-heroicon-o-clock class="w-4 h-4" /> {{ $record['totalLoggedHours'] }}h
        </div>
    @endif
</div>
