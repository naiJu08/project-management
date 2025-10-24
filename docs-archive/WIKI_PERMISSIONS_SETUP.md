# Wiki Module - Permissions & CRUD Setup

## Overview

Complete permissions system and enhanced CRUD UI for the Wiki module has been implemented with role-based access control.

**Implementation Date**: October 15, 2025

---

## ✅ What Was Implemented

### 1. **Permissions System**

#### **Wiki Permissions Created**

The following permissions have been added to the system:

| Permission | Description |
|------------|-------------|
| `List wiki pages` | View list of all wiki pages |
| `View wiki page` | View individual wiki page details |
| `Create wiki page` | Create new wiki pages |
| `Update wiki page` | Edit existing wiki pages |
| `Delete wiki page` | Delete wiki pages (cascades to sub-pages) |

#### **Default Role Assignment**

All wiki permissions are automatically assigned to the **Default Role**, ensuring backward compatibility and immediate access for existing users.

---

### 2. **Filament Resource (Sidebar Access)**

#### **WikiPageResource**

A complete Filament resource has been created for master/admin users to manage wiki pages from the sidebar.

**Location**: `app/Filament/Resources/WikiPageResource.php`

**Features**:
- ✅ Full CRUD operations
- ✅ Markdown editor with toolbar
- ✅ Project selection dropdown
- ✅ Parent page selection (for sub-pages)
- ✅ Display order field
- ✅ Version tracking display
- ✅ Creator/updater information
- ✅ Advanced filtering (by project, root pages, sub-pages)
- ✅ Bulk delete operations
- ✅ Search functionality

**Navigation**:
- **Icon**: Book Open (heroicon-o-book-open)
- **Group**: Project Management
- **Sort Order**: 6
- **Label**: Wiki Pages

---

### 3. **Policy-Based Authorization**

#### **WikiPagePolicy**

**Location**: `app/Policies/WikiPagePolicy.php`

All CRUD operations are protected by policy methods:

```php
public function viewAny(User $user)    // List wiki pages
public function view(User $user, WikiPage $wikiPage)    // View single page
public function create(User $user)     // Create new page
public function update(User $user, WikiPage $wikiPage)  // Edit page
public function delete(User $user, WikiPage $wikiPage)  // Delete page
public function restore(User $user, WikiPage $wikiPage) // Restore soft-deleted
public function forceDelete(User $user, WikiPage $wikiPage) // Permanent delete
```

**Registered in**: `app/Providers/AuthServiceProvider.php`

---

### 4. **Enhanced WikiView Component**

#### **New Features**

**Location**: `app/Http/Livewire/Project/WikiView.php`

**Permission-Based Methods**:
```php
public function canCreate()  // Check create permission
public function canEdit()    // Check edit permission
public function canDelete()  // Check delete permission
```

**New Public Properties**:
- `$isCreating` - Flag for create mode
- `$parentId` - Parent page ID for sub-pages
- `$showDeleteConfirm` - Delete confirmation modal state
- `$pageToDelete` - Page ID pending deletion

**Enhanced Methods**:

1. **`editPage($pageId = null)`**
   - Permission checks before editing
   - Separate handling for create vs edit
   - Flash messages for permission errors

2. **`createSubPage($parentId)`**
   - Create child page under parent
   - Permission validation
   - Auto-set parent_id

3. **`savePage()`**
   - Permission checks for create/update
   - Success flash messages
   - Version auto-increment
   - Parent-child relationship support

4. **`confirmDelete($pageId)`**
   - Show confirmation modal
   - Permission check

5. **`deletePage()`**
   - Permission validation
   - Cascade delete sub-pages
   - Count and display deleted sub-pages
   - Success flash messages

6. **`cancelDelete()`**
   - Close confirmation modal

---

### 5. **Enhanced UI (wiki-view.blade.php)**

#### **New UI Components**

**Flash Messages**:
- ✅ Success messages (green)
- ✅ Error messages (red)
- ✅ Icons and styling
- ✅ Auto-dismiss capability

**Permission-Based Buttons**:

1. **"New Page" Button** (Sidebar)
   - Only visible if user has `Create wiki page` permission
   - Blue button with plus icon

2. **"Edit" Button** (Page View)
   - Only visible if user has `Update wiki page` permission
   - Gray border button with edit icon

3. **"Add Sub-page" Button** (Page View)
   - Only visible if user has `Create wiki page` permission
   - Green border button with plus icon
   - Creates child page under current page

4. **"Delete" Button** (Page View)
   - Only visible if user has `Delete wiki page` permission
   - Red border button with trash icon
   - Opens confirmation modal

**Delete Confirmation Modal**:
- ✅ Warning icon and message
- ✅ Yellow alert box
- ✅ Explains cascade delete behavior
- ✅ Cancel and Delete buttons
- ✅ Dark mode support
- ✅ Overlay backdrop

**UI Improvements**:
- ✅ Transition effects on buttons
- ✅ Hover states
- ✅ Better spacing and layout
- ✅ Consistent color scheme
- ✅ Responsive design

---

## 📁 Files Created/Modified

### **New Files**

1. **WikiPageResource.php** - Filament resource
2. **WikiPageResource/Pages/ListWikiPages.php** - List page
3. **WikiPageResource/Pages/CreateWikiPage.php** - Create page
4. **WikiPageResource/Pages/EditWikiPage.php** - Edit page
5. **WikiPageResource/Pages/ViewWikiPage.php** - View page
6. **WikiPagePolicy.php** - Authorization policy
7. **WIKI_PERMISSIONS_SETUP.md** - This documentation

### **Modified Files**

1. **PermissionsSeeder.php** - Added 'wiki page' to modules array
2. **AuthServiceProvider.php** - Registered WikiPagePolicy
3. **WikiView.php** - Enhanced with permissions and CRUD
4. **wiki-view.blade.php** - Enhanced UI with permissions

---

## 🎨 Filament Resource Features

### **Form Fields**

1. **Project** (Select)
   - Dropdown with all projects
   - Searchable
   - Required

2. **Page Title** (Text Input)
   - Max 255 characters
   - Required

3. **Parent Page** (Select)
   - Optional
   - Dropdown with all wiki pages
   - Searchable
   - Helper text: "Select a parent page to create a sub-page"

4. **Content** (Markdown Editor)
   - Full-width
   - Rich toolbar with:
     - Attach files
     - Blockquote
     - Bold, Italic, Strike
     - Bullet/Ordered lists
     - Code block
     - Link, Table
     - Edit/Preview toggle
     - Undo/Redo

5. **Display Order** (Number)
   - Default: 0
   - Helper text: "Lower numbers appear first"

### **Table Columns**

1. **Project** - Searchable, sortable
2. **Page Title** - Searchable, sortable, limited to 50 chars
3. **Parent Page** - Searchable, sortable, shows "—" if none
4. **Version** - Badge column (primary color)
5. **Created By** - User name, searchable, sortable
6. **Created** - DateTime, sortable
7. **Last Updated** - DateTime with "since" format, sortable

### **Filters**

1. **Project Filter** - Dropdown with all projects
2. **Root Pages Only** - Show only top-level pages
3. **Sub-pages Only** - Show only child pages

### **Actions**

- **Row Actions**: View, Edit, Delete
- **Bulk Actions**: Delete multiple pages
- **Default Sort**: Last updated (descending)

---

## 🔐 Permission Checks

### **In Filament Resource**

```php
// WikiPageResource.php
public static function canViewAny(): bool
{
    return auth()->user()->can('List wiki pages');
}

public static function canCreate(): bool
{
    return auth()->user()->can('Create wiki page');
}

public static function canEdit($record): bool
{
    return auth()->user()->can('Update wiki page');
}

public static function canDelete($record): bool
{
    return auth()->user()->can('Delete wiki page');
}

public static function canView($record): bool
{
    return auth()->user()->can('View wiki page');
}
```

### **In WikiView Component**

```php
// WikiView.php
public function canCreate()
{
    return auth()->user()->can('Create wiki page');
}

public function canEdit()
{
    return auth()->user()->can('Update wiki page');
}

public function canDelete()
{
    return auth()->user()->can('Delete wiki page');
}
```

### **In Blade Template**

```blade
@if($this->canCreate())
    <button wire:click="editPage()">New Page</button>
@endif

@if($this->canEdit())
    <button wire:click="editPage({{ $page->id }})">Edit</button>
@endif

@if($this->canDelete())
    <button wire:click="confirmDelete({{ $page->id }})">Delete</button>
@endif
```

---

## 🚀 Usage Guide

### **For Master/Admin Users**

#### **Access from Sidebar**

1. Navigate to **Project Management** → **Wiki Pages** in sidebar
2. See list of all wiki pages across all projects
3. Use filters to narrow down results
4. Click **New Wiki Page** to create
5. Click row actions to View/Edit/Delete

#### **Creating a Page**

1. Click **New Wiki Page** button
2. Select project from dropdown
3. Enter page title
4. (Optional) Select parent page for sub-page
5. Write content in markdown
6. Set display order
7. Click **Create**

#### **Editing a Page**

1. Click **Edit** action on any page
2. Modify title, content, or parent
3. Version auto-increments
4. Click **Save**

#### **Deleting a Page**

1. Click **Delete** action
2. Confirm deletion
3. Sub-pages are automatically deleted

### **For Project Users (Wiki Tab)**

#### **Creating a Page**

1. Go to project detail page
2. Click **Wiki** tab
3. Click **New Page** button (if permission granted)
4. Enter title and content
5. Click **Save Page**

#### **Creating a Sub-page**

1. View any page
2. Click **Add Sub-page** button (if permission granted)
3. Enter title and content
4. Parent is automatically set
5. Click **Save Page**

#### **Editing a Page**

1. View any page
2. Click **Edit** button (if permission granted)
3. Modify title or content
4. Click **Save Page**

#### **Deleting a Page**

1. View any page
2. Click **Delete** button (if permission granted)
3. Confirm in modal
4. Click **Delete Page**

---

## 🎯 Permission Scenarios

### **Scenario 1: Full Access User**

**Permissions**: All wiki permissions

**Can Do**:
- ✅ View wiki pages in sidebar
- ✅ Create new pages
- ✅ Edit any page
- ✅ Delete any page
- ✅ Create sub-pages
- ✅ View all pages

### **Scenario 2: Read-Only User**

**Permissions**: `List wiki pages`, `View wiki page`

**Can Do**:
- ✅ View wiki pages in sidebar (list)
- ✅ View page content
- ❌ Cannot create pages
- ❌ Cannot edit pages
- ❌ Cannot delete pages

**UI Behavior**:
- "New Page" button hidden
- "Edit" button hidden
- "Add Sub-page" button hidden
- "Delete" button hidden

### **Scenario 3: Editor User**

**Permissions**: `List wiki pages`, `View wiki page`, `Update wiki page`

**Can Do**:
- ✅ View wiki pages
- ✅ Edit existing pages
- ❌ Cannot create new pages
- ❌ Cannot delete pages

**UI Behavior**:
- "New Page" button hidden
- "Edit" button visible
- "Add Sub-page" button hidden
- "Delete" button hidden

### **Scenario 4: Contributor User**

**Permissions**: `List wiki pages`, `View wiki page`, `Create wiki page`

**Can Do**:
- ✅ View wiki pages
- ✅ Create new pages
- ✅ Create sub-pages
- ❌ Cannot edit existing pages
- ❌ Cannot delete pages

**UI Behavior**:
- "New Page" button visible
- "Edit" button hidden
- "Add Sub-page" button visible
- "Delete" button hidden

---

## 🔧 Customization

### **Adding Custom Permissions**

To add more granular permissions:

1. **Update PermissionsSeeder**:
```php
private array $extraPermissions = [
    'Manage general settings',
    'Import from Jira',
    'Export wiki to PDF',  // New
    'Manage wiki templates',  // New
];
```

2. **Run Seeder**:
```bash
php artisan db:seed --class=PermissionsSeeder
```

3. **Update Policy**:
```php
public function export(User $user, WikiPage $wikiPage)
{
    return $user->can('Export wiki to PDF');
}
```

### **Customizing UI**

**Change Button Colors**:
```blade
{{-- In wiki-view.blade.php --}}
<button class="bg-purple-600 hover:bg-purple-700">
    Custom Button
</button>
```

**Add Custom Actions**:
```php
// In WikiView.php
public function duplicatePage($pageId)
{
    if (!$this->canCreate()) {
        session()->flash('error', 'No permission');
        return;
    }
    
    $original = WikiPage::find($pageId);
    $duplicate = $original->replicate();
    $duplicate->title = $original->title . ' (Copy)';
    $duplicate->save();
    
    session()->flash('success', 'Page duplicated!');
}
```

---

## 📊 Database Impact

### **New Permissions**

5 new permissions added to `permissions` table:
- List wiki pages
- View wiki page
- Create wiki page
- Update wiki page
- Delete wiki page

### **Role Assignments**

All wiki permissions automatically assigned to **Default Role** via seeder.

---

## ✅ Testing Checklist

### **Permission Tests**

- [ ] User with all permissions can access sidebar
- [ ] User with all permissions can create pages
- [ ] User with all permissions can edit pages
- [ ] User with all permissions can delete pages
- [ ] User without permissions sees no buttons
- [ ] User without permissions gets error message
- [ ] Read-only user can view but not modify

### **UI Tests**

- [ ] Flash messages display correctly
- [ ] Delete confirmation modal works
- [ ] Sub-page creation sets parent correctly
- [ ] Buttons show/hide based on permissions
- [ ] Dark mode works properly
- [ ] Mobile responsive layout

### **Functional Tests**

- [ ] Create page from sidebar
- [ ] Create page from wiki tab
- [ ] Edit page updates version
- [ ] Delete page removes sub-pages
- [ ] Search filters pages correctly
- [ ] Parent-child relationships work

---

## 🐛 Troubleshooting

### **Issue: Sidebar not showing Wiki Pages**

**Solution**: 
1. Check user has `List wiki pages` permission
2. Clear cache: `php artisan cache:clear`
3. Verify policy is registered in AuthServiceProvider

### **Issue: Buttons not showing in Wiki tab**

**Solution**:
1. Check permissions are assigned to user's role
2. Verify `canCreate()`, `canEdit()`, `canDelete()` methods
3. Clear view cache: `php artisan view:clear`

### **Issue: Permission denied errors**

**Solution**:
1. Run seeder: `php artisan db:seed --class=PermissionsSeeder`
2. Check role has permissions assigned
3. Verify user has correct role

### **Issue: Delete confirmation not showing**

**Solution**:
1. Check `$showDeleteConfirm` property exists
2. Verify modal code is in blade template
3. Check z-index of modal (should be z-50)

---

## 📚 Related Documentation

- **WIKI_MODULE_DOCUMENTATION.md** - Complete wiki features
- **WIKI_QUICK_START.md** - User guide
- **PROJECT_DETAIL_FIXES.md** - Implementation notes

---

## 🎉 Summary

### **What You Get**

✅ **Complete Permissions System**
- 5 granular wiki permissions
- Policy-based authorization
- Default role integration

✅ **Filament Resource (Sidebar)**
- Full CRUD operations
- Advanced filtering
- Markdown editor
- Version tracking

✅ **Enhanced Wiki UI**
- Permission-based buttons
- Flash messages
- Delete confirmation
- Sub-page creation
- Better UX

✅ **Backward Compatible**
- All existing features preserved
- Default role has all permissions
- No breaking changes

### **Ready to Use**

The wiki module now has:
- ✅ Complete permission system
- ✅ Sidebar access for admins
- ✅ Enhanced CRUD UI
- ✅ Role-based access control
- ✅ Professional user experience

**Start managing wiki pages today!** 📚
