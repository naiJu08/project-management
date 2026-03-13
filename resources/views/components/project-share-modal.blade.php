@props(['projectId' => null, 'projectName' => ''])

<div x-data="projectShare()" 
     x-show="showModal" 
     x-transition
     @open-share-modal.window="showModal = true"
     class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
    {{-- Backdrop with blur --}}
    <div class="fixed inset-0 bg-black/40 dark:bg-black/60 backdrop-blur-sm" @click="showModal = false"></div>

    {{-- Modal --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto relative">
        {{-- Header --}}
        <div class="sticky top-0 bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-500 dark:from-blue-900 dark:via-blue-800 dark:to-cyan-900 text-white p-6 sm:p-8 flex items-center justify-between border-b border-blue-400/20">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-white/20 backdrop-blur-md rounded-xl">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C9.589 12.438 10 11.166 10 9.5c0-1.933-.5-3.5-1.5-3.5S7 7.567 7 9.5c0 1.666.411 2.938 1.316 3.842m0 0h6.632m-6.632 0A7.001 7.001 0 0121 12a7 7 0 01-7 7m0 0H4.684m6.632 0a7.00097 7.00097 0 01-1.318-13.843"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold">Share Project</h2>
                    <p class="text-sm opacity-90 mt-1">{{ $projectName }}</p>
                </div>
            </div>
            <button @click="showModal = false" class="p-2 hover:bg-white/20 rounded-lg transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        {{-- Content --}}
        <div class="p-6 sm:p-8 space-y-8">
            {{-- Share Link Section --}}
            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6 border border-blue-200 dark:border-blue-900">
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.658 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                    </div>
                    Shareable Link
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Share this link with anyone to grant access to your project</p>
                <div class="flex gap-2">
                    <input type="text" 
                           :value="shareLink" 
                           readonly
                           class="flex-1 px-4 py-3 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono">
                    <button @click="copyToClipboard()" 
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all font-medium text-sm shadow-md hover:shadow-lg">
                        <span x-show="!copied" class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Copy
                        </span>
                        <span x-show="copied" class="flex items-center gap-2 text-green-200">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Copied!
                        </span>
                    </button>
                </div>
            </div>

            {{-- Two Column Layout --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Share with Users Section --}}
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
                        <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm0 0h6v-2a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        Share with Team
                    </h3>
                    <div class="space-y-3">
                        <input type="email" 
                               x-model="emailToShare"
                               placeholder="Enter email address"
                               class="w-full px-4 py-3 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400">
                        <div class="flex gap-2">
                            <select x-model="shareRole" class="flex-1 px-4 py-3 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="viewer">👁️ Viewer</option>
                                <option value="editor">✏️ Editor</option>
                                <option value="admin">⚙️ Admin</option>
                            </select>
                            <button @click="shareWithEmail()" 
                                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all font-medium text-sm shadow-md hover:shadow-lg">
                                Share
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Share Options --}}
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
                        <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                        </div>
                        Permissions
                    </h3>
                    <div class="space-y-3">
                        <label class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                            <input type="checkbox" x-model="allowPublicAccess" class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 mt-0.5">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Public Access</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Anyone with link can view</p>
                            </div>
                        </label>
                        <label class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                            <input type="checkbox" x-model="allowComments" class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 mt-0.5">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Allow Comments</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Users can comment on tasks</p>
                            </div>
                        </label>
                        <label class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                            <input type="checkbox" x-model="allowDownload" class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 mt-0.5">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Allow Download</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Users can export data</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Shared Users List --}}
            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6 border border-gray-200 dark:border-gray-600">
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
                    <div class="p-2 bg-indigo-100 dark:bg-indigo-900 rounded-lg">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM15 20H9m6 0h6v-2a6 6 0 00-9-5.656V9a2 2 0 11-4 0V7a10 10 0 1120 0z"></path>
                        </svg>
                    </div>
                    Current Access
                </h3>
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    <template x-for="user in sharedUsers" :key="user.id">
                        <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-600 transition-colors">
                            <div class="flex items-center gap-3 flex-1">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-sm font-bold shadow-md">
                                    <span x-text="user.name.charAt(0).toUpperCase()"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white" x-text="user.name"></p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 truncate" x-text="user.email"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full" 
                                      :class="user.role === 'admin' ? 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200' : user.role === 'editor' ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200' : 'bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300'"
                                      x-text="user.role.charAt(0).toUpperCase() + user.role.slice(1)">
                                </span>
                                <button @click="removeAccess(user.id)" 
                                        class="p-2 hover:bg-red-100 dark:hover:bg-red-900 text-red-600 dark:text-red-400 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                    <div x-show="sharedUsers.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        <p class="text-sm">No one has access yet</p>
                    </div>
                </div>
            </div>

            {{-- Export Options --}}
            <div class="bg-gradient-to-r from-orange-50 to-red-50 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6 border border-orange-200 dark:border-orange-900">
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
                    <div class="p-2 bg-orange-100 dark:bg-orange-900 rounded-lg">
                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    Export Project
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Download your project data in various formats</p>
                <div class="grid grid-cols-3 gap-3">
                    <button @click="exportProject('pdf')" 
                            class="px-4 py-3 bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 text-red-700 dark:text-red-200 rounded-lg transition-all font-medium text-sm shadow-sm hover:shadow-md">
                        📄 PDF
                    </button>
                    <button @click="exportProject('excel')" 
                            class="px-4 py-3 bg-green-100 dark:bg-green-900 hover:bg-green-200 dark:hover:bg-green-800 text-green-700 dark:text-green-200 rounded-lg transition-all font-medium text-sm shadow-sm hover:shadow-md">
                        📊 Excel
                    </button>
                    <button @click="exportProject('json')" 
                            class="px-4 py-3 bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-200 rounded-lg transition-all font-medium text-sm shadow-sm hover:shadow-md">
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
        sharedUsers: [],
        
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
        }
    }
}
</script>
