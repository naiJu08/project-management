@props(['title' => null, 'content', 'position' => 'top'])

<div class="group relative inline-block">
    {{-- Small superscript info icon --}}
    <span class="inline-flex items-center justify-center w-4 h-4 ml-1 text-xs font-semibold text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 hover:text-gray-600 dark:hover:text-gray-300 cursor-help transition-colors">
        i
    </span>

    {{-- Tooltip --}}
    <div class="absolute z-50 invisible group-hover:visible bg-gray-900 dark:bg-gray-950 text-white text-sm rounded-lg shadow-lg px-3 py-2 whitespace-nowrap pointer-events-none transition-opacity opacity-0 group-hover:opacity-100 duration-200
        @if($position === 'top') bottom-full mb-2 left-1/2 -translate-x-1/2
        @elseif($position === 'bottom') top-full mt-2 left-1/2 -translate-x-1/2
        @elseif($position === 'left') right-full mr-2 top-1/2 -translate-y-1/2
        @elseif($position === 'right') left-full ml-2 top-1/2 -translate-y-1/2
        @endif
    ">
        @if($title)
            <div class="font-semibold text-white mb-1">{{ $title }}</div>
        @endif
        <div class="text-gray-200">{{ $content }}</div>

        {{-- Arrow --}}
        <div class="absolute w-2 h-2 bg-gray-900 dark:bg-gray-950 transform rotate-45
            @if($position === 'top') -bottom-1 left-1/2 -translate-x-1/2
            @elseif($position === 'bottom') -top-1 left-1/2 -translate-x-1/2
            @elseif($position === 'left') -right-1 top-1/2 -translate-y-1/2
            @elseif($position === 'right') -left-1 top-1/2 -translate-y-1/2
            @endif
        "></div>
    </div>
</div>
