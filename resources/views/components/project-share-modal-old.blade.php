@props(['projectId' => null, 'projectName' => ''])

<div x-data="projectShare()" 
     x-show="showModal" 
     x-transition
     @open-share-modal.window="showModal = true"
     class="fixed inset-0 z-50 flex items-center justify-center p-4">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 dark:bg-black/70" @click="showModal = false"></div>

    {{-- Modal --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl max-w-2xl w-full max-h-96 overflow-y-auto relative">
        {{-- Header --}}
        <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-900 dark:to-blue-800 text-white p-4 sm:p-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C9.589 12.438 10 11.166 10 9.5c0-1.933-.5-3.5-1.5-3.5S7 7.567 7 9.5c0 1.666.411 2.938 1.316 3.842m0 0h6.632m-6.632 0A7.001 7.001 0 0121 12a7 7 0 01-7 7m0 0H4.684m6.632 0a7.00097 7.00097 0 01-1.318-13.843"></path>
                </svg>
                <div>
                    <h2 class="text-lg font-bold">Share Project</h2>
                    <p class="text-xs opacity-90">{{ $projectName }}</p>
                </div>
            </div>
            <button @click="showModal = false" class="text-white hover:bg-blue-800 p-1 rounded transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        {{-- Content --}}
        <div class="p-4 sm:p-6 space-y-6">
            {{-- Share Link Section --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.658 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                    </svg>
                    Shareable Link
                </h3>
                <div class="flex gap-2">
                    <input type="text" 
                           :value="shareLink" 
                           readonly
                           class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white"
                           placeholder="Share link will be generated">
                    <button @click="copyToClipboard()" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm font-medium">
                        <span x-show="!copied">Copy</span>
                        <span x-show="copied" class="text-green-300">✓ Copied</span>
                    </button>
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">Share this link with team members to grant access</p>
            </div>

            {{-- Share with Users Section --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm0 0h6v-2a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Share with Team Members
                </h3>
                <div class="space-y-2">
                    <div class="flex gap-2">
                        <input type="email" 
                               x-model="emailToShare"
                               placeholder="Enter email address"
                               class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <select x-model="shareRole" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="viewer">Viewer</option>
                            <option value="editor">Editor</option>
                            <option value="admin">Admin</option>
                        </select>
                        <button @click="shareWithEmail()" 
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm font-medium">
                            Share
                        </button>
                    </div>
                </div>
            </div>

            {{-- Shared Users List --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Current Access</h3>
                <div class="space-y-2 max-h-40 overflow-y-auto">
                    <template x-for="user in sharedUsers" :key="user.id">
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-xs font-bold">
                                    <span x-text="user.name.charAt(0).toUpperCase()"></span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="user.name"></p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400" x-text="user.email"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 text-xs font-medium rounded" 
                                      :class="user.role === 'admin' ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : user.role === 'editor' ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' : 'bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-200'"
                                      x-text="user.role.charAt(0).toUpperCase() + user.role.slice(1)">
                                </span>
                                <button @click="removeAccess(user.id)" 
                                        class="p-1 hover:bg-red-100 dark:hover:bg-red-900 text-red-600 dark:text-red-400 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                    <div x-show="sharedUsers.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
                        No one has access yet
                    </div>
                </div>
            </div>

            {{-- Share Options --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Share Options</h3>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        <input type="checkbox" x-model="allowPublicAccess" class="w-4 h-4 rounded border-gray-300 dark:border-gray-600">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Allow Public Access</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Anyone with the link can view</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        <input type="checkbox" x-model="allowComments" class="w-4 h-4 rounded border-gray-300 dark:border-gray-600">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Allow Comments</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Shared users can comment on tasks</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        <input type="checkbox" x-model="allowDownload" class="w-4 h-4 rounded border-gray-300 dark:border-gray-600">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Allow Download</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Shared users can export project data</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Export Options --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Export Project</h3>
                <div class="flex gap-2">
                    <button @click="exportProject('pdf')" 
                            class="flex-1 px-4 py-2 bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 text-red-700 dark:text-red-200 rounded-lg transition-colors text-sm font-medium">
                        📄 PDF
                    </button>
                    <button @click="exportProject('excel')" 
                            class="flex-1 px-4 py-2 bg-green-100 dark:bg-green-900 hover:bg-green-200 dark:hover:bg-green-800 text-green-700 dark:text-green-200 rounded-lg transition-colors text-sm font-medium">
                        📊 Excel
                    </button>
                    <button @click="exportProject('json')" 
                            class="flex-1 px-4 py-2 bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-200 rounded-lg transition-colors text-sm font-medium">
                        📋 JSON
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function projectShare() {
    return {
        showModal: false,
        shareLink: window.location.href,
        copied: false,
        emailToShare: '',
        shareRole: 'viewer',
        allowPublicAccess: false,
        allowComments: true,
        allowDownload: false,
        sharedUsers: [
            // Sample data - will be replaced with real data from backend
        ],
        
        copyToClipboard() {
            navigator.clipboard.writeText(this.shareLink);
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        },
        
        shareWithEmail() {
            if (!this.emailToShare) {
                alert('Please enter an email address');
                return;
            }
            
            // Add to shared users list
            this.sharedUsers.push({
                id: Date.now(),
                name: this.emailToShare.split('@')[0],
                email: this.emailToShare,
                role: this.shareRole
            });
            
            this.emailToShare = '';
            this.shareRole = 'viewer';
        },
        
        removeAccess(userId) {
            this.sharedUsers = this.sharedUsers.filter(u => u.id !== userId);
        },
        
        exportProject(format) {
            alert(`Exporting project as ${format.toUpperCase()}...`);
            // Will implement actual export functionality
        }
    }
}
</script>
