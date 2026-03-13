# 🔑 Livewire Variable Reference for Backlog

**Critical:** Understanding when to use `$this->` vs direct variable access

---

## ✅ CORRECT Usage in Included Templates

When a template is `@include`'d in a Livewire component, variable access depends on **how they're defined**:

### Public Properties (NO $this->)
These are defined as `public $variableName` in the component class:

```blade
✅ {{ $selectionMode }}
✅ {{ $selectedItems }}
✅ {{ $viewMode }}
✅ {{ $selectedItemId }}
✅ {{ $expandedItems }}
✅ {{ $inlineCreateParentId }}
✅ {{ $inlineCreateType }}
✅ {{ $inlineCreateTitle }}
✅ @if($selectionMode)
✅ @if(count($selectedItems) > 0)
```

**Why:** Public properties are automatically available in the template scope.

---

### Computed Properties (MUST use $this->)
These are defined as `getXxxProperty()` methods in the component class:

```blade
✅ {{ $this->backlogItems }}
✅ {{ $this->project }}
✅ {{ $this->teamMembers }}
✅ {{ $this->sprints }}
✅ {{ $this->availableParents }}
✅ {{ $this->selectedItem }}
✅ {{ $this->comments }}
✅ {{ $this->history }}
✅ @forelse($this->backlogItems as $item)
✅ @foreach($this->teamMembers as $member)
```

**Why:** Computed properties are methods that Livewire calls when you access them with `$this->`.

---

## 📋 Complete Reference for ProjectDetail Component

### In `app/Http/Livewire/ProjectDetail.php`:

#### Public Properties (Direct Access)
```php
public $projectId;
public $activeTab = 'board';
public $selectedItemId;
public $expandedItems = [];
public $filterType = 'all';
public $filterStatus = 'all';
public $filterAssignee = 'all';
public $filterSprint = 'all';
public $searchTerm = '';
public $showQuickAdd = false;
public $quickAddType = 'Epic';
public $quickAddTitle = '';
public $quickAddParentId = null;
public $inlineCreateParentId = null;
public $inlineCreateType = '';
public $inlineCreateTitle = '';
public $editingItemId = null;
public $editTitle = '';
public $editDescription = '';
public $editStatus = '';
public $editPriority = '';
public $editAssigneeId = null;
public $editSprintId = null;
public $editEstimatedHours = null;
public $editStartDate = null;
public $editDueDate = null;
public $newComment = '';
public $replyToCommentId = null;
public $viewMode = 'tree';
public $selectionMode = false;  // ← Added for selection mode
public $selectedItems = [];
public $bulkAction = '';
public $bulkStatus = '';
public $bulkPriority = '';
public $bulkSprintId = null;
public $showBulkPanel = false;
```

**In Blade:** Use `$variableName` (no $this->)

---

#### Computed Properties (Need $this->)
```php
public function getProjectProperty()
{
    return Project::findOrFail($this->projectId);
}

public function getBacklogItemsProperty()
{
    return $this->project->backlogItems()->get();
}

public function getSelectedItemProperty()
{
    return BacklogItem::find($this->selectedItemId);
}

public function getSprintsProperty()
{
    return $this->project->sprints()->get();
}

public function getTeamMembersProperty()
{
    return $this->project->users;
}

public function getAvailableParentsProperty()
{
    // Returns collection based on quickAddType
}

public function getCommentsProperty()
{
    return BacklogItemComment::where('backlog_item_id', $this->selectedItemId)->get();
}

public function getHistoryProperty()
{
    return $this->selectedItem->histories()->get();
}
```

**In Blade:** Use `$this->propertyName`

---

## 🎯 Quick Rule

**Ask yourself:** "Is this a variable or a method?"

### Variable (public $xxx)
```blade
{{ $variableName }}
```

### Method (getXxxProperty)
```blade
{{ $this->propertyName }}
```

---

## 💡 Examples from Backlog View

### ✅ CORRECT

```blade
{{-- Public property - no $this-> --}}
<button wire:click="toggleSelectionMode()" 
        class="{{ $selectionMode ? 'active' : 'inactive' }}">
    <span>{{ $selectionMode ? 'Done' : 'Select' }}</span>
    @if(count($selectedItems) > 0)
        <span>{{ count($selectedItems) }}</span>
    @endif
</button>

{{-- Computed property - use $this-> --}}
<span>{{ $this->backlogItems->count() }} items</span>

@forelse($this->backlogItems->where('parent_id', null) as $item)
    {{-- Item content --}}
@empty
    <p>No items</p>
@endforelse

{{-- Mix both types --}}
@if($selectionMode)  {{-- Public property --}}
    <input type="checkbox" 
           @if(in_array($item->id, $selectedItems)) checked @endif>  {{-- Public property --}}
@endif

{{-- Computed property for detail view --}}
@if($this->selectedItem)
    <h2>{{ $this->selectedItem->title }}</h2>
@endif
```

---

### ❌ WRONG

```blade
{{-- Don't use $this-> on public properties --}}
@if($this->selectionMode)  ❌ Wrong!
@if(count($this->selectedItems) > 0)  ❌ Wrong!

{{-- Don't omit $this-> on computed properties --}}
@forelse($backlogItems as $item)  ❌ Wrong! (undefined variable)
{{ $project->name }}  ❌ Wrong! (undefined variable)
```

---

## 🔍 How to Tell the Difference

Look at the component class:

```php
// Public property - access directly
public $selectionMode = false;
// In Blade: $selectionMode

// Computed property - needs $this->
public function getBacklogItemsProperty()
{
    return $this->project->backlogItems()->get();
}
// In Blade: $this->backlogItems
```

---

## 🎉 Result

When you follow these rules:

✅ No "Undefined variable" errors  
✅ All computed properties load correctly  
✅ All public properties accessible  
✅ Template works perfectly with @include  
✅ Livewire features function properly  

---

**Remember:** 
- **Public properties** = Direct access (`$variable`)
- **Computed properties** = Use `$this->` (`$this->property`)

This is why the backlog uses **both** patterns in the same template!
