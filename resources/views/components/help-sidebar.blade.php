@props(['title', 'description', 'features' => [], 'benefits' => [], 'implementation' => '', 'examples' => []])

@php
    $uniqueId = 'help-' . uniqid();
@endphp

<div class="group relative inline-block">
    {{-- Small superscript info icon --}}
    <button 
        onclick="openHelpSidebar('{{ $uniqueId }}')"
        class="inline-flex items-center justify-center w-4 h-4 ml-1 text-xs font-semibold text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 hover:text-gray-600 dark:hover:text-gray-300 cursor-help transition-colors"
        title="Click for detailed information"
    >
        i
    </button>
</div>

{{-- Help Sidebar --}}
<div 
    id="{{ $uniqueId }}"
    class="fixed inset-0 z-50 hidden help-sidebar-container"
    onclick="if(event.target === this) closeHelpSidebar(this)"
>
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 dark:bg-black/60 backdrop-blur-sm transition-opacity"></div>

    {{-- Sidebar --}}
    <div 
        class="absolute right-0 top-0 h-full w-full sm:w-96 bg-white dark:bg-gray-800 shadow-2xl overflow-y-auto transform transition-transform duration-300 translate-x-full"
        onclick="event.stopPropagation()"
    >
        {{-- Header --}}
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white leading-tight">{{ $title }}</h2>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1.5 leading-relaxed">{{ $description }}</p>
                </div>
                <button 
                    onclick="closeHelpSidebar(document.getElementById('{{ $uniqueId }}'))"
                    class="flex-shrink-0 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-4 sm:p-5 space-y-4">
            {{-- Features Section --}}
            @if(!empty($features))
                <div class="space-y-2">
                    <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Features
                    </h3>
                    <ul class="space-y-1.5 pl-6">
                        @foreach($features as $feature)
                            <li class="flex gap-2 text-xs text-gray-700 dark:text-gray-300 leading-relaxed">
                                <span class="text-blue-600 dark:text-blue-400 font-bold flex-shrink-0 mt-0.5">•</span>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Benefits Section --}}
            @if(!empty($benefits))
                <div class="space-y-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Benefits
                    </h3>
                    <ul class="space-y-1.5 pl-6">
                        @foreach($benefits as $benefit)
                            <li class="flex gap-2 text-xs text-gray-700 dark:text-gray-300 leading-relaxed">
                                <span class="text-green-600 dark:text-green-400 font-bold flex-shrink-0 mt-0.5">✓</span>
                                <span>{{ $benefit }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Implementation Section --}}
            @if($implementation)
                <div class="space-y-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"></path>
                        </svg>
                        How to Use
                    </h3>
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded p-3 text-gray-700 dark:text-gray-300 text-xs leading-relaxed space-y-1">
                        {!! $implementation !!}
                    </div>
                </div>
            @endif

            {{-- Examples Section --}}
            @if(!empty($examples))
                <div class="space-y-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Examples
                    </h3>
                    <div class="space-y-2">
                        @foreach($examples as $example)
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 rounded p-2.5">
                                <p class="font-medium text-blue-900 dark:text-blue-200 text-xs mb-0.5">{{ $example['title'] }}</p>
                                <p class="text-xs text-blue-800 dark:text-blue-300 leading-relaxed">{{ $example['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Tips Section --}}
            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded p-2.5">
                    <p class="text-xs text-amber-900 dark:text-amber-200 leading-relaxed">
                        <span class="font-semibold">💡 Tip:</span> Press <kbd class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">Esc</kbd> to close this panel
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openHelpSidebar(id) {
    const sidebar = document.getElementById(id);
    if (sidebar) {
        sidebar.classList.remove('hidden');
        setTimeout(() => {
            const sidebarDiv = sidebar.querySelector('div:nth-child(2)');
            if (sidebarDiv) {
                sidebarDiv.classList.remove('translate-x-full');
                sidebarDiv.classList.add('translate-x-0');
            }
        }, 10);
        document.body.style.overflow = 'hidden';
    }
}

function closeHelpSidebar(element) {
    const container = element.closest('.help-sidebar-container') || element;
    const sidebarDiv = container.querySelector('div:nth-child(2)');
    if (sidebarDiv) {
        sidebarDiv.classList.add('translate-x-full');
        sidebarDiv.classList.remove('translate-x-0');
        setTimeout(() => {
            container.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300);
    }
}

// Close on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const openSidebars = document.querySelectorAll('.help-sidebar-container:not(.hidden)');
        openSidebars.forEach(sidebar => closeHelpSidebar(sidebar));
    }
});
</script>
