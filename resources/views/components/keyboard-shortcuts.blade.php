{{-- Global Keyboard Shortcuts Handler --}}
<div x-data="keyboardShortcuts()" @keydown.window="handleKeydown($event)">
    {{-- Shortcuts Help Modal --}}
    <div x-show="showHelp" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70" @click="showHelp = false">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl max-w-2xl w-full mx-4 max-h-96 overflow-y-auto" @click.stop>
            {{-- Header --}}
            <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-900 dark:to-blue-800 text-white p-4 flex items-center justify-between">
                <h2 class="text-lg font-bold">Keyboard Shortcuts</h2>
                <button @click="showHelp = false" class="text-white hover:bg-blue-800 p-1 rounded transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Shortcuts Grid --}}
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Search --}}
                <div class="flex items-start gap-3">
                    <div class="flex gap-1">
                        <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">Cmd</kbd>
                        <span class="text-gray-400">+</span>
                        <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">K</kbd>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white text-sm">Search</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Open global search</p>
                    </div>
                </div>

                {{-- New Task --}}
                <div class="flex items-start gap-3">
                    <div class="flex gap-1">
                        <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">Cmd</kbd>
                        <span class="text-gray-400">+</span>
                        <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">N</kbd>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white text-sm">New Task</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Create new task</p>
                    </div>
                </div>

                {{-- Save --}}
                <div class="flex items-start gap-3">
                    <div class="flex gap-1">
                        <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">Cmd</kbd>
                        <span class="text-gray-400">+</span>
                        <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">S</kbd>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white text-sm">Save</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Save current form</p>
                    </div>
                </div>

                {{-- Close Modal --}}
                <div class="flex items-start gap-3">
                    <div class="flex gap-1">
                        <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">Esc</kbd>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white text-sm">Close</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Close modal or dialog</p>
                    </div>
                </div>

                {{-- Help --}}
                <div class="flex items-start gap-3">
                    <div class="flex gap-1">
                        <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">Cmd</kbd>
                        <span class="text-gray-400">+</span>
                        <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">/</kbd>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white text-sm">Help</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Show keyboard shortcuts</p>
                    </div>
                </div>

                {{-- Toggle Board/List --}}
                <div class="flex items-start gap-3">
                    <div class="flex gap-1">
                        <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">Cmd</kbd>
                        <span class="text-gray-400">+</span>
                        <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">B</kbd>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white text-sm">Toggle Board</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Switch to board view</p>
                    </div>
                </div>

                {{-- Toggle List --}}
                <div class="flex items-start gap-3">
                    <div class="flex gap-1">
                        <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">Cmd</kbd>
                        <span class="text-gray-400">+</span>
                        <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">L</kbd>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white text-sm">Toggle List</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Switch to list view</p>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 dark:bg-gray-900 p-4 border-t border-gray-200 dark:border-gray-700 text-center text-xs text-gray-600 dark:text-gray-400">
                Press <kbd class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">Cmd</kbd> + <kbd class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">/</kbd> anytime to show this help
            </div>
        </div>
    </div>
</div>

<script>
function keyboardShortcuts() {
    return {
        showHelp: false,
        handleKeydown(event) {
            const isMac = /Mac|iPhone|iPad|iPod/.test(navigator.platform);
            const isCtrlOrCmd = isMac ? event.metaKey : event.ctrlKey;

            if (!isCtrlOrCmd) return;

            switch(event.key.toLowerCase()) {
                case 'k':
                    event.preventDefault();
                    this.openSearch();
                    break;
                case 'n':
                    event.preventDefault();
                    this.createNewTask();
                    break;
                case 's':
                    event.preventDefault();
                    this.saveForm();
                    break;
                case '/':
                    event.preventDefault();
                    this.showHelp = !this.showHelp;
                    break;
                case 'b':
                    event.preventDefault();
                    this.toggleBoard();
                    break;
                case 'l':
                    event.preventDefault();
                    this.toggleList();
                    break;
            }
        },
        openSearch() {
            // Dispatch event for search modal
            window.dispatchEvent(new CustomEvent('keyboard:search'));
        },
        createNewTask() {
            // Dispatch event for new task modal
            window.dispatchEvent(new CustomEvent('keyboard:newTask'));
        },
        saveForm() {
            // Find and click save button
            const saveBtn = document.querySelector('[data-action="save"]');
            if (saveBtn) saveBtn.click();
        },
        toggleBoard() {
            window.dispatchEvent(new CustomEvent('keyboard:toggleBoard'));
        },
        toggleList() {
            window.dispatchEvent(new CustomEvent('keyboard:toggleList'));
        }
    }
}
</script>
