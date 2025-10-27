<?php

namespace App\Http\Livewire\Project;

use App\Models\Project;
use App\Models\Budget;
use App\Models\BudgetExpense;
use App\Models\ResourceCost;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class BudgetView extends Component
{
    public $projectId;
    public $budget = null;
    public $expenses = [];
    public $resourceCosts = [];
    public $showBudgetForm = false;
    public $showExpenseForm = false;
    public $editingExpenseId = null;
    public $filterStatus = 'all';
    public $filterCategory = 'all';

    // Budget form fields
    public $totalBudget = '';
    public $budgetNotes = '';

    // Expense form fields
    public $expenseCategory = 'development';
    public $expenseDescription = '';
    public $expenseAmount = '';
    public $expenseDate = '';
    public $expenseStatus = 'pending';

    protected $budgetRules = [
        'totalBudget' => 'required|numeric|min:0',
        'budgetNotes' => 'nullable|string|max:1000',
    ];

    protected $expenseRules = [
        'expenseCategory' => 'required|string',
        'expenseDescription' => 'required|string|max:500',
        'expenseAmount' => 'required|numeric|min:0',
        'expenseDate' => 'required|date',
        'expenseStatus' => 'required|in:pending,approved,rejected',
    ];

    public function mount($projectId)
    {
        $this->projectId = $projectId;
        $this->expenseDate = now()->toDateString();
        $this->loadBudgetData();
    }

    public function getProjectProperty()
    {
        return Project::findOrFail($this->projectId);
    }

    public function loadBudgetData()
    {
        $this->budget = Budget::where('project_id', $this->projectId)->first();
        
        if (!$this->budget) {
            $this->budget = Budget::create([
                'project_id' => $this->projectId,
                'total_budget' => 0,
            ]);
        }

        $query = BudgetExpense::where('project_id', $this->projectId)
            ->with('createdBy')
            ->orderBy('expense_date', 'desc');

        if ($this->filterStatus !== 'all') {
            $query->byStatus($this->filterStatus);
        }

        if ($this->filterCategory !== 'all') {
            $query->byCategory($this->filterCategory);
        }

        $this->expenses = $query->get();
        $this->resourceCosts = ResourceCost::where('project_id', $this->projectId)
            ->with('user')
            ->active()
            ->get();
    }

    public function saveBudget()
    {
        $this->validate($this->budgetRules);

        $this->budget->update([
            'total_budget' => $this->totalBudget,
            'notes' => $this->budgetNotes,
        ]);

        $this->showBudgetForm = false;
        $this->loadBudgetData();
        session()->flash('success', 'Budget updated successfully!');
    }

    public function addExpense()
    {
        $this->validate($this->expenseRules);

        BudgetExpense::create([
            'budget_id' => $this->budget->id,
            'project_id' => $this->projectId,
            'category' => $this->expenseCategory,
            'description' => $this->expenseDescription,
            'amount' => $this->expenseAmount,
            'expense_date' => $this->expenseDate,
            'status' => $this->expenseStatus,
            'created_by' => Auth::id(),
        ]);

        $this->updateBudgetSpent();
        $this->resetExpenseForm();
        $this->loadBudgetData();
        session()->flash('success', 'Expense added successfully!');
    }

    public function editExpense($expenseId)
    {
        $expense = BudgetExpense::find($expenseId);
        $this->editingExpenseId = $expenseId;
        $this->expenseCategory = $expense->category;
        $this->expenseDescription = $expense->description;
        $this->expenseAmount = $expense->amount;
        $this->expenseDate = $expense->expense_date->toDateString();
        $this->expenseStatus = $expense->status;
        $this->showExpenseForm = true;
    }

    public function updateExpense()
    {
        $this->validate($this->expenseRules);

        $expense = BudgetExpense::find($this->editingExpenseId);
        $expense->update([
            'category' => $this->expenseCategory,
            'description' => $this->expenseDescription,
            'amount' => $this->expenseAmount,
            'expense_date' => $this->expenseDate,
            'status' => $this->expenseStatus,
        ]);

        $this->updateBudgetSpent();
        $this->resetExpenseForm();
        $this->loadBudgetData();
        session()->flash('success', 'Expense updated successfully!');
    }

    public function deleteExpense($expenseId)
    {
        BudgetExpense::find($expenseId)->delete();
        $this->updateBudgetSpent();
        $this->loadBudgetData();
        session()->flash('success', 'Expense deleted successfully!');
    }

    public function approveExpense($expenseId)
    {
        $expense = BudgetExpense::find($expenseId);
        $expense->update(['status' => 'approved']);
        $this->updateBudgetSpent();
        $this->loadBudgetData();
    }

    public function rejectExpense($expenseId)
    {
        $expense = BudgetExpense::find($expenseId);
        $expense->update(['status' => 'rejected']);
        $this->updateBudgetSpent();
        $this->loadBudgetData();
    }

    private function updateBudgetSpent()
    {
        $spent = BudgetExpense::where('budget_id', $this->budget->id)
            ->where('status', 'approved')
            ->sum('amount');

        $this->budget->update(['spent_budget' => $spent]);
    }

    public function resetExpenseForm()
    {
        $this->expenseCategory = 'development';
        $this->expenseDescription = '';
        $this->expenseAmount = '';
        $this->expenseDate = now()->toDateString();
        $this->expenseStatus = 'pending';
        $this->showExpenseForm = false;
        $this->editingExpenseId = null;
    }

    public function render()
    {
        return view('livewire.project.budget-view');
    }
}
