<div>
    {{-- Customize Button --}}
    <button wire:click="$toggle('showCustomizer')" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        Customize
    </button>

    {{-- Customizer Modal/Sidebar --}}
    @if($showCustomizer)
        <div class="fixed inset-0 z-50 overflow-hidden">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black bg-opacity-50 transition-opacity" wire:click="$toggle('showCustomizer')"></div>

            {{-- Sidebar --}}
            <div class="absolute right-0 top-0 bottom-0 w-96 bg-white dark:bg-gray-800 shadow-xl overflow-y-auto">
                <div class="p-6 space-y-6">
                    {{-- Header --}}
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Customize Tabs</h2>
                        <button wire:click="$toggle('showCustomizer')" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <p class="text-sm text-gray-600 dark:text-gray-400">Select which tabs you want to display and reorder them by moving up/down.</p>

                    {{-- Tabs List --}}
                    <div class="space-y-3">
                        @foreach($tabOrder as $tabKey)
                            @php
                                $tab = $allTabs[$tabKey] ?? null;
                                $isEnabled = in_array($tabKey, $enabledTabs);
                            @endphp

                            @if($tab)
                                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                    {{-- Checkbox --}}
                                    <input type="checkbox" wire:click="toggleTab('{{ $tabKey }}')" {{ $isEnabled ? 'checked' : '' }} class="w-5 h-5 rounded cursor-pointer">

                                    {{-- Tab Info --}}
                                    <div class="flex-1 min-w-0">
                                        <label class="text-sm font-medium text-gray-900 dark:text-white cursor-pointer">{{ $tab['name'] }}</label>
                                    </div>

                                    {{-- Move Buttons --}}
                                    <div class="flex gap-1">
                                        <button wire:click="moveTabUp('{{ $tabKey }}')" class="p-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors" title="Move up">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        </button>
                                        <button wire:click="moveTabDown('{{ $tabKey }}')" class="p-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors" title="Move down">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button wire:click="savePreferences()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                            Save Changes
                        </button>
                        <button wire:click="resetToDefaults()" class="flex-1 px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-900 dark:text-white rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors font-medium">
                            Reset
                        </button>
                    </div>

                    {{-- Info --}}
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <p class="text-xs text-blue-800 dark:text-blue-200">
                            <strong>Tip:</strong> Use the up/down arrows to reorder tabs. Uncheck to hide tabs.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
