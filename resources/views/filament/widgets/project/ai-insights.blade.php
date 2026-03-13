<div class="space-y-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">AI Insights & Recommendations</h3>
        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
            <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM15.657 14.243a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM11 17a1 1 0 102 0v-1a1 1 0 10-2 0v1zM5.757 15.657a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM2 10a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.757 4.343a1 1 0 00-1.414 1.414l.707.707a1 1 0 001.414-1.414l-.707-.707z"></path>
        </svg>
    </div>

    @if(empty($insights))
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <p>No insights available yet. Create tickets to get started.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($insights as $insight)
                <div class="p-4 rounded-lg border-l-4 {{ match($insight['type']) {
                    'success' => 'bg-green-50 dark:bg-green-900/20 border-green-500',
                    'warning' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-500',
                    'danger' => 'bg-red-50 dark:bg-red-900/20 border-red-500',
                    'info' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-500',
                    default => 'bg-gray-50 dark:bg-gray-700/20 border-gray-500'
                } }}">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 mt-0.5 flex-shrink-0 {{ match($insight['type']) {
                            'success' => 'text-green-600 dark:text-green-400',
                            'warning' => 'text-yellow-600 dark:text-yellow-400',
                            'danger' => 'text-red-600 dark:text-red-400',
                            'info' => 'text-blue-600 dark:text-blue-400',
                            default => 'text-gray-600 dark:text-gray-400'
                        } }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($insight['icon'] === 'heroicon-m-check-circle')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            @elseif($insight['icon'] === 'heroicon-m-exclamation-circle')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            @elseif($insight['icon'] === 'heroicon-m-clock')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            @elseif($insight['icon'] === 'heroicon-m-users')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm0 0h6v-2a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            @elseif($insight['icon'] === 'heroicon-m-rocket-launch')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            @elseif($insight['icon'] === 'heroicon-m-flag')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5m0 16h18m-18-4v4m0-11v4"></path>
                            @elseif($insight['icon'] === 'heroicon-m-arrow-path')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            @elseif($insight['icon'] === 'heroicon-m-chart-pie')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            @endif
                        </svg>
                        <div class="flex-1">
                            <h4 class="font-semibold {{ match($insight['type']) {
                                'success' => 'text-green-900 dark:text-green-100',
                                'warning' => 'text-yellow-900 dark:text-yellow-100',
                                'danger' => 'text-red-900 dark:text-red-100',
                                'info' => 'text-blue-900 dark:text-blue-100',
                                default => 'text-gray-900 dark:text-gray-100'
                            } }}">
                                {{ $insight['title'] }}
                            </h4>
                            <p class="text-sm mt-1 {{ match($insight['type']) {
                                'success' => 'text-green-800 dark:text-green-200',
                                'warning' => 'text-yellow-800 dark:text-yellow-200',
                                'danger' => 'text-red-800 dark:text-red-200',
                                'info' => 'text-blue-800 dark:text-blue-200',
                                default => 'text-gray-800 dark:text-gray-200'
                            } }}">
                                {{ $insight['description'] }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
