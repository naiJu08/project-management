@props(['type' => 'card', 'count' => 1])

@if($type === 'card')
    @for($i = 0; $i < $count; $i++)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-4">
            <div class="space-y-4">
                {{-- Header --}}
                <div class="flex items-center justify-between">
                    <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/3 animate-pulse"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4 animate-pulse"></div>
                </div>

                {{-- Content Lines --}}
                <div class="space-y-3">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-full animate-pulse"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-5/6 animate-pulse"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-4/5 animate-pulse"></div>
                </div>

                {{-- Footer --}}
                <div class="flex gap-2 pt-2">
                    <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-20 animate-pulse"></div>
                    <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-20 animate-pulse"></div>
                </div>
            </div>
        </div>
    @endfor

@elseif($type === 'table')
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        {{-- Table Header --}}
        <div class="grid grid-cols-4 gap-4 p-4 border-b border-gray-200 dark:border-gray-700">
            @for($i = 0; $i < 4; $i++)
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
            @endfor
        </div>

        {{-- Table Rows --}}
        @for($row = 0; $row < 5; $row++)
            <div class="grid grid-cols-4 gap-4 p-4 border-b border-gray-200 dark:border-gray-700">
                @for($col = 0; $col < 4; $col++)
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                @endfor
            </div>
        @endfor
    </div>

@elseif($type === 'list')
    <div class="space-y-2">
        @for($i = 0; $i < $count; $i++)
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-4">
                    {{-- Avatar --}}
                    <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-full animate-pulse flex-shrink-0"></div>

                    {{-- Content --}}
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/3 animate-pulse"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2 animate-pulse"></div>
                    </div>

                    {{-- Action --}}
                    <div class="w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded animate-pulse flex-shrink-0"></div>
                </div>
            </div>
        @endfor
    </div>

@elseif($type === 'form')
    <div class="space-y-4">
        {{-- Input 1 --}}
        <div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4 mb-2 animate-pulse"></div>
            <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
        </div>

        {{-- Input 2 --}}
        <div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4 mb-2 animate-pulse"></div>
            <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
        </div>

        {{-- Textarea --}}
        <div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4 mb-2 animate-pulse"></div>
            <div class="h-24 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
        </div>

        {{-- Buttons --}}
        <div class="flex gap-2 pt-4">
            <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded w-24 animate-pulse"></div>
            <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded w-24 animate-pulse"></div>
        </div>
    </div>

@elseif($type === 'header')
    <div class="space-y-4">
        <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-1/2 animate-pulse"></div>
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-2/3 animate-pulse"></div>
    </div>

@endif

<style>
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }

    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
