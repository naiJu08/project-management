<div class="fixed bottom-6 right-6 z-40">
    {{-- Guide Toggle Button --}}
    <button 
        wire:click="toggleGuide()" 
        class="w-12 h-12 rounded-full bg-blue-600 hover:bg-blue-700 text-white shadow-lg flex items-center justify-center transition-all duration-300 hover:scale-110"
        title="Help & Guidance"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
    </button>

    {{-- Guide Panel Sidebar --}}
    @if($showGuide && $guide)
        <div class="absolute bottom-16 right-0 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-300">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-4 text-white">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-bold">{{ $guide['title'] }}</h3>
                    <button 
                        wire:click="toggleGuide()" 
                        class="text-white hover:bg-blue-800 p-1 rounded transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-blue-100">{{ $guide['description'] }}</p>
            </div>

            {{-- Content --}}
            <div class="max-h-96 overflow-y-auto">
                {{-- Steps Section --}}
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Steps to Follow
                    </h4>
                    <div class="space-y-3">
                        @foreach($guide['steps'] as $step)
                            <div class="flex gap-3">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white text-sm font-bold">
                                        {{ $step['number'] }}
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h5 class="font-medium text-gray-900 dark:text-white text-sm">{{ $step['title'] }}</h5>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $step['description'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Tips Section --}}
                @if(!empty($guide['tips']))
                    <div class="p-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"></path>
                            </svg>
                            Pro Tips
                        </h4>
                        <ul class="space-y-2">
                            @foreach($guide['tips'] as $tip)
                                <li class="flex gap-2 text-sm">
                                    <span class="text-amber-600 font-bold mt-0.5">•</span>
                                    <span class="text-gray-600 dark:text-gray-400">{{ $tip }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 dark:bg-gray-900 p-3 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                    💡 Click the help icon anytime for guidance
                </p>
            </div>
        </div>
    @endif
</div>

<style>
    @keyframes slide-in-from-bottom-4 {
        from {
            opacity: 0;
            transform: translateY(1rem);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-in {
        animation: slide-in-from-bottom-4 0.3s ease-out;
    }

    .fade-in {
        animation: fade-in 0.3s ease-out;
    }

    @keyframes fade-in {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
</style>
