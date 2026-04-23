<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="px-3 sm:px-4 md:px-6">
            <h2 class="text-base sm:text-lg md:text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                Budget & Cost Management
                <x-help-sidebar 
                    title="Budget & Cost Management"
                    description="Set budgets, track expenses, and monitor spending"
                    :features="[
                        'Set total project budget',
                        'Track spent vs remaining budget',
                        'Budget utilization percentage',
                        'Add and categorize expenses',
                        'Expense approval workflow',
                        'Resource cost tracking',
                        'Over-budget alerts',
                        'Budget progress visualization'
                    ]"
                    :benefits="[
                        'Control project costs',
                        'Prevent budget overruns',
                        'Track spending by category',
                        'Approve expenses systematically',
                        'Monitor resource costs',
                        'Generate budget reports',
                        'Improve financial planning'
                    ]"
                    implementation="<p class='mb-2'><strong>How it works in this software:</strong></p><p>1. Click 'Set Budget' to define total project budget</p><p>2. Add expenses with category and amount</p><p>3. Submit for approval (Pending → Approved/Rejected)</p><p>4. View budget utilization in real-time</p><p>5. Monitor remaining budget</p><p>6. Set resource hourly rates for cost calculation</p><p>7. Get alerts when approaching budget limit</p>"
                    :examples="[
                        ['title' => 'Development Budget', 'description' => 'Set $50,000 budget for development team costs'],
                        ['title' => 'Infrastructure Costs', 'description' => 'Track cloud hosting, tools, and licenses'],
                        ['title' => 'Resource Allocation', 'description' => 'Set hourly rates for team members ($75-150/hr)']
                    ]"
                />
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Track project budget and expenses for {{ $this->project->name }}</p>
        </div>
        <button wire:click="$toggle('showBudgetForm')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Set Budget
            <x-help-tooltip 
                content="Define the total project budget"
                position="bottom"
            />
        </button>
    </div>

    {{-- Budget Summary Cards --}}
    @if($budget)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                    Total Budget
                    <x-help-tooltip 
                        content="The total allocated budget for this project"
                        position="right"
                    />
                </p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">${{ number_format($budget->total_budget, 2) }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                    Spent
                    <x-help-tooltip 
                        content="Total amount spent on approved expenses"
                        position="right"
                    />
                </p>
                <p class="text-3xl font-bold {{ $budget->is_over_budget ? 'text-red-600 dark:text-red-400' : 'text-orange-600 dark:text-orange-400' }} mt-2">${{ number_format($budget->spent_budget, 2) }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                    Remaining
                    <x-help-tooltip 
                        content="Budget left available for spending"
                        position="right"
                    />
                </p>
                <p class="text-3xl font-bold {{ $budget->remaining_budget < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }} mt-2">${{ number_format($budget->remaining_budget, 2) }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                    Utilization
                    <x-help-tooltip 
                        content="Percentage of budget that has been spent"
                        position="right"
                    />
                </p>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-2">{{ $budget->budget_utilization }}%</p>
            </div>
        </div>

        {{-- Budget Progress Bar --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex justify-between items-center mb-2">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Budget Usage</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $budget->budget_utilization }}% of ${{ number_format($budget->total_budget, 2) }}</p>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                <div class="h-3 rounded-full transition-all {{ $budget->is_over_budget ? 'bg-red-500' : 'bg-blue-500' }}" style="width: {{ min($budget->budget_utilization, 100) }}%"></div>
            </div>
            @if($budget->is_over_budget)
                <p class="text-sm text-red-600 dark:text-red-400 mt-2">⚠️ Over budget by ${{ number_format(abs($budget->remaining_budget), 2) }}</p>
            @endif
        </div>
    @endif

    {{-- Budget Form --}}
    @if($showBudgetForm)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Set Project Budget</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total Budget *</label>
                    <input type="number" wire:model="totalBudget" step="0.01" min="0" placeholder="0.00" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    @error('totalBudget') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                <textarea wire:model="budgetNotes" placeholder="Budget notes and details..." rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
            </div>

            <div class="flex gap-2">
                <button wire:click="saveBudget()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">Save Budget</button>
                <button wire:click="$toggle('showBudgetForm')" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-900 dark:text-white rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors font-medium">Cancel</button>
            </div>
        </div>
    @endif

    {{-- Expenses Section --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Expenses</h3>
            <button wire:click="$toggle('showExpenseForm')" class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">Add Expense</button>
        </div>

        {{-- Expense Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select wire:model="filterStatus" wire:change="loadBudgetData()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                <select wire:model="filterCategory" wire:change="loadBudgetData()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="all">All Categories</option>
                    <option value="development">Development</option>
                    <option value="infrastructure">Infrastructure</option>
                    <option value="tools">Tools & Software</option>
                    <option value="personnel">Personnel</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>

        {{-- Expense Form --}}
        @if($showExpenseForm)
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $editingExpenseId ? 'Edit Expense' : 'Add New Expense' }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category *</label>
                        <select wire:model="expenseCategory" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="development">Development</option>
                            <option value="infrastructure">Infrastructure</option>
                            <option value="tools">Tools & Software</option>
                            <option value="personnel">Personnel</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount *</label>
                        <input type="number" wire:model="expenseAmount" step="0.01" min="0" placeholder="0.00" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('expenseAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date *</label>
                        <input type="date" wire:model="expenseDate" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('expenseDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
                        <select wire:model="expenseStatus" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description *</label>
                    <textarea wire:model="expenseDescription" placeholder="Expense description..." rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                    @error('expenseDescription') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex gap-2">
                    <button wire:click="{{ $editingExpenseId ? 'updateExpense' : 'addExpense' }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">{{ $editingExpenseId ? 'Update' : 'Add' }} Expense</button>
                    <button wire:click="resetExpenseForm()" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-900 dark:text-white rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors font-medium">Cancel</button>
                </div>
            </div>
        @endif

        {{-- Expenses Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Date</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Category</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Description</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Amount</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($expenses as $expense)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $expense->expense_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ ucfirst($expense->category) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $expense->description }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($expense->amount, 2) }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 rounded text-xs font-medium {{ 
                                        $expense->status === 'approved' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' :
                                        ($expense->status === 'rejected' ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' :
                                        'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200')
                                    }}">
                                        {{ ucfirst($expense->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    @if($expense->status === 'pending')
                                        <button wire:click="approveExpense({{ $expense->id }})" class="text-green-600 dark:text-green-400 hover:underline">Approve</button>
                                        <button wire:click="rejectExpense({{ $expense->id }})" class="text-red-600 dark:text-red-400 hover:underline">Reject</button>
                                    @endif
                                    <button wire:click="editExpense({{ $expense->id }})" class="text-blue-600 dark:text-blue-400 hover:underline">Edit</button>
                                    <button wire:click="confirmDeleteExpense({{ $expense->id }})" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No expenses recorded</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteConfirm)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Delete Expense</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Are you sure you want to delete this expense?</p>
                    </div>
                </div>
                
                <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-md p-3 mb-4">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <strong>Warning:</strong> This action cannot be undone.
                    </p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button wire:click="cancelDeleteExpense" 
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="deleteExpense" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        Delete Expense
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
