# Azure DevOps-Style Backlog Implementation Progress

**Started**: October 15, 2025, 5:17 PM  
**Status**: 🟡 In Progress (Backend Complete, Frontend In Progress)

---

## 📋 Overview

Implementing a comprehensive Azure DevOps-style hierarchical Backlog system to replace the flat task list with:
- **5-level hierarchy**: Epic → Feature → User Story → Task → Subtask
- **Drag-and-drop** ordering and reparenting
- **Sprint integration** with existing sprint system
- **Comments & history** tracking
- **Inline editing** capabilities

---

## ✅ Completed (Backend Foundation)

### 1. Database Schema ✅
**Files Created**:
- `database/migrations/2025_10_15_171700_create_backlog_items_table.php`
- `database/migrations/2025_10_15_171800_create_backlog_item_comments_and_history_tables.php`

**Tables Created**:
- ✅ `backlog_items` - Main hierarchical work items table
  - Supports 5 types: Epic, Feature, UserStory, Task, Subtask
  - Self-referencing `parent_id` for hierarchy
  - `order_index` for manual ordering
  - Sprint integration via `sprint_id`
  - Soft deletes enabled
  
- ✅ `backlog_item_comments` - Threaded comments
  - Parent-child comment structure
  - Soft deletes
  
- ✅ `backlog_item_histories` - Change tracking
  - Field-level change history
  - Action logging (created, updated, moved, deleted)

**Schema Extensions**:
- ✅ Added `backlog_item_id` to `tickets` table for backward compatibility

**Migration Status**: ✅ Ran successfully

---

### 2. Models ✅
**Files Created**:
- `app/Models/BacklogItem.php` (450+ lines)
- `app/Models/BacklogItemComment.php`
- `app/Models/BacklogItemHistory.php`

**BacklogItem Model Features**:
- ✅ Type constants (Epic, Feature, UserStory, Task, Subtask)
- ✅ Status constants (To Do, In Progress, Done, Blocked)
- ✅ Priority constants (Critical, High, Medium, Low)
- ✅ Automatic code generation (EP-1, FT-1, US-1, TK-1, ST-1)
- ✅ Hierarchical relationships (parent, children)
- ✅ Sprint relationship
- ✅ Comments and history relationships
- ✅ Helper methods:
  - `canHaveChildren()`, `getAllowedChildTypes()`, `canBeParentOf()`
  - `getTypeIcon()`, `getTypeColor()`, `getPriorityColor()`, `getStatusColor()`
  - `getDepth()`, `getHierarchyPath()`
  - `getTotalEstimatedHours()`, `getCompletionPercentage()`
  - `moveToSprint()`, `reorder()`
- ✅ Query scopes (epics, features, userStories, tasks, subtasks, topLevel, inSprint, inBacklog)
- ✅ Cascading deletes for children, comments, and history

---

### 3. API Controllers ✅
**Files Created**:
- `app/Http/Controllers/Api/BacklogItemController.php` (400+ lines)

**Endpoints Implemented**:
```
GET    /api/projects/{project}/backlog-items                    - Full hierarchy
GET    /api/projects/{project}/backlog-items/type/{type}        - By type
GET    /api/projects/{project}/backlog-items/sprint/{sprintId}  - By sprint
GET    /api/projects/{project}/backlog-items/backlog-only       - Unassigned items
POST   /api/projects/{project}/backlog-items                    - Create item
POST   /api/projects/{project}/backlog-items/bulk-update        - Bulk reorder

GET    /api/backlog-items/{backlogItem}                         - Show details
PUT    /api/backlog-items/{backlogItem}                         - Update item
DELETE /api/backlog-items/{backlogItem}                         - Delete item
PUT    /api/backlog-items/{backlogItem}/move                    - Reorder/reparent
PUT    /api/backlog-items/{backlogItem}/assign-sprint           - Assign to sprint

GET    /api/backlog-items/{backlogItem}/comments                - List comments
POST   /api/backlog-items/{backlogItem}/comments                - Add comment
DELETE /api/backlog-comments/{comment}                          - Delete comment

GET    /api/backlog-items/{backlogItem}/history                 - View history
```

**Features**:
- ✅ Full CRUD operations
- ✅ Hierarchy validation (prevents invalid parent-child relationships)
- ✅ Automatic history logging
- ✅ Bulk update support for drag-and-drop
- ✅ Sprint assignment with cascading to children
- ✅ Comments with threaded replies
- ✅ Statistics (counts by type)

---

### 4. Routes ✅
**File Modified**: `routes/api.php`

- ✅ Added all backlog item endpoints
- ✅ Maintained backward compatibility with existing backlog routes
- ✅ Proper route grouping and middleware

---

### 5. Permissions ✅
**File Modified**: `database/seeders/PermissionsSeeder.php`

- ✅ Added 'backlog item' to modules array
- ✅ Auto-generates permissions:
  - List backlog items
  - View backlog item
  - Create backlog item
  - Update backlog item
  - Delete backlog item

**Seeder Status**: ✅ Ran successfully

---

### 6. Project Model Integration ✅
**File Modified**: `app/Models/Project.php`

- ✅ Added `backlogItems()` relationship
- ✅ Added cascading delete for backlog items in boot method

---

## 🟡 In Progress (Frontend Components)

### 7. Enhanced BacklogView Livewire Component
**Status**: 🔄 Next Step

**File to Create/Modify**: `app/Http/Livewire/Project/BacklogView.php`

**Required Features**:
- [ ] Load hierarchical backlog items
- [ ] Tree view rendering with expand/collapse
- [ ] Drag-and-drop support (Alpine.js + Sortable.js)
- [ ] Inline editing for title, status, priority, assignee
- [ ] Quick-add buttons at each level
- [ ] Filter by type, status, assignee, sprint
- [ ] Sprint panel integration
- [ ] Context menu (Add Child, Move, Delete)

---

### 8. Backlog View Blade Template
**Status**: ⏳ Pending

**File to Create/Modify**: `resources/views/livewire/project/backlog-view.blade.php`

**Required UI Components**:
- [ ] Left panel: Hierarchical tree with indentation
- [ ] Right panel: Detail view with tabs (Details, Comments, History)
- [ ] Type icons and color coding
- [ ] Status/priority badges
- [ ] Assignee avatars
- [ ] Sprint assignment dropdown
- [ ] Drag handles
- [ ] Context menus
- [ ] Quick-add inputs

---

### 9. Drag-and-Drop Implementation
**Status**: ⏳ Pending

**Technology**: Alpine.js + Sortable.js

**Requirements**:
- [ ] Drag items within same parent
- [ ] Drag items to different parent (with validation)
- [ ] Visual feedback during drag
- [ ] API call on drop to update order_index
- [ ] Prevent invalid moves (e.g., Task under Epic)

---

### 10. Detail Panel Component
**Status**: ⏳ Pending

**Features**:
- [ ] Tabbed interface (Details, Discussion, History)
- [ ] Rich text editor for description
- [ ] Inline field editing
- [ ] Assignee selector
- [ ] Sprint selector
- [ ] Date pickers
- [ ] Estimated hours input
- [ ] Status/priority dropdowns

---

### 11. Comments Component
**Status**: ⏳ Pending

**Features**:
- [ ] Threaded comment display
- [ ] Markdown support
- [ ] Reply functionality
- [ ] Delete own comments
- [ ] Real-time updates (optional)

---

### 12. History Component
**Status**: ⏳ Pending

**Features**:
- [ ] Timeline view of changes
- [ ] User avatars
- [ ] Formatted change descriptions
- [ ] Timestamps

---

## ⏳ Pending (Integration & Migration)

### 13. Sprint Integration
**Status**: ⏳ Pending

**Tasks**:
- [ ] Update Sprint model to work with backlog items
- [ ] Sprint board view integration
- [ ] Burndown chart updates
- [ ] Capacity planning

---

### 14. Data Migration Script
**Status**: ⏳ Pending

**File to Create**: `database/migrations/2025_10_15_migrate_tickets_to_backlog_items.php`

**Migration Strategy**:
- [ ] Convert existing tickets to UserStory type
- [ ] Preserve all ticket data
- [ ] Link tickets to backlog_items
- [ ] Maintain sprint assignments
- [ ] Preserve order
- [ ] Handle epic relationships

---

### 15. Backward Compatibility Layer
**Status**: ⏳ Pending

**Tasks**:
- [ ] Keep existing Ticket model functional
- [ ] Sync ticket changes to backlog items
- [ ] Sync backlog item changes to tickets
- [ ] Dual-mode operation during transition

---

## 📊 Progress Summary

| Component | Status | Progress |
|-----------|--------|----------|
| Database Schema | ✅ Complete | 100% |
| Models | ✅ Complete | 100% |
| API Controllers | ✅ Complete | 100% |
| Routes | ✅ Complete | 100% |
| Permissions | ✅ Complete | 100% |
| Livewire Component | 🔄 In Progress | 0% |
| Blade Templates | ⏳ Pending | 0% |
| Drag-and-Drop | ⏳ Pending | 0% |
| Detail Panel | ⏳ Pending | 0% |
| Comments UI | ⏳ Pending | 0% |
| History UI | ⏳ Pending | 0% |
| Sprint Integration | ⏳ Pending | 0% |
| Data Migration | ⏳ Pending | 0% |
| Testing | ⏳ Pending | 0% |
| Documentation | ⏳ Pending | 0% |

**Overall Progress**: ~40% (Backend Complete)

---

## 🎯 Next Steps

### Immediate (Current Session):
1. ✅ Complete BacklogView Livewire component
2. ✅ Create backlog-view.blade.php with tree structure
3. ✅ Implement basic drag-and-drop
4. ✅ Add detail panel with tabs

### Short-term (Next Session):
5. Add inline editing capabilities
6. Implement comments UI
7. Add history timeline
8. Sprint integration

### Medium-term:
9. Create data migration script
10. Test all functionality
11. Write comprehensive documentation
12. Create quick-start guide

---

## 🔧 Technical Decisions

### Hierarchy Rules
```
Epic (top-level only)
└── Feature
    └── User Story
        └── Task
            └── Subtask (leaf node)
```

### Code Prefixes
- Epic: `EP-{n}`
- Feature: `FT-{n}`
- User Story: `US-{n}`
- Task: `TK-{n}`
- Subtask: `ST-{n}`

### Color Scheme
- Epic: Purple (#9333EA)
- Feature: Blue (#3B82F6)
- User Story: Green (#10B981)
- Task: Orange (#F97316)
- Subtask: Gray (#6B7280)

### Drag-and-Drop Library
- **Choice**: Sortable.js with Alpine.js integration
- **Reason**: Lightweight, no jQuery dependency, works well with Livewire

---

## 📝 Files Created So Far

### Migrations (2)
1. `database/migrations/2025_10_15_171700_create_backlog_items_table.php`
2. `database/migrations/2025_10_15_171800_create_backlog_item_comments_and_history_tables.php`

### Models (3)
1. `app/Models/BacklogItem.php`
2. `app/Models/BacklogItemComment.php`
3. `app/Models/BacklogItemHistory.php`

### Controllers (1)
1. `app/Http/Controllers/Api/BacklogItemController.php`

### Modified Files (3)
1. `app/Models/Project.php` - Added backlogItems relationship
2. `routes/api.php` - Added backlog item endpoints
3. `database/seeders/PermissionsSeeder.php` - Added backlog item permissions

**Total**: 6 new files, 3 modified files

---

## 🐛 Known Issues / Considerations

1. **Performance**: Loading 4 levels deep may be slow for large projects
   - **Solution**: Implement lazy loading for children

2. **Concurrent Editing**: Multiple users editing same item
   - **Solution**: Add optimistic locking or last-write-wins with conflict detection

3. **Drag-and-Drop on Mobile**: Touch events may need special handling
   - **Solution**: Test and add touch event support

4. **Large Hierarchies**: UI may become cluttered
   - **Solution**: Add collapse all/expand all, search/filter

---

## 📚 API Documentation

### Create Backlog Item
```http
POST /api/projects/{project}/backlog-items
Content-Type: application/json

{
  "type": "Epic",
  "title": "User Authentication System",
  "description": "Complete authentication flow",
  "priority": "High",
  "estimated_hours": 40
}
```

### Move Item
```http
PUT /api/backlog-items/{id}/move
Content-Type: application/json

{
  "new_order_index": 2,
  "new_parent_id": 15
}
```

### Add Comment
```http
POST /api/backlog-items/{id}/comments
Content-Type: application/json

{
  "content": "This needs to be done before sprint 3",
  "parent_comment_id": null
}
```

---

## 🎨 UI Mockup (Text-based)

```
┌─────────────────────────────────────────────────────────────────┐
│ Backlog                                    [Filters] [+ New Epic]│
├──────────────────────┬──────────────────────────────────────────┤
│ Tree View            │ Detail Panel                             │
│                      │                                          │
│ 🎯 EP-1 Auth System  │ ┌─────────────────────────────────────┐ │
│   ├─ 🔷 FT-1 Login   │ │ Details │ Comments │ History        │ │
│   │   ├─ 📖 US-1...  │ ├─────────────────────────────────────┤ │
│   │   │   ├─ ✓ TK-1  │ │ Title: [User Login Feature____]    │ │
│   │   │   └─ ✓ TK-2  │ │ Type: Feature                       │ │
│   │   └─ 📖 US-2...  │ │ Status: [In Progress ▼]            │ │
│   └─ 🔷 FT-2 Signup  │ │ Priority: [High ▼]                  │ │
│                      │ │ Assignee: [@john ▼]                 │ │
│ 🎯 EP-2 Dashboard    │ │ Sprint: [Sprint 3 ▼]                │ │
│   └─ 🔷 FT-3...      │ │ Est. Hours: [8___]                  │ │
│                      │ │ Description:                         │ │
│ [+ Add Epic]         │ │ [Rich text editor____________]      │ │
│                      │ └─────────────────────────────────────┘ │
└──────────────────────┴──────────────────────────────────────────┘
```

---

## 🚀 Deployment Checklist

- [ ] Run migrations
- [ ] Seed permissions
- [ ] Clear caches
- [ ] Test API endpoints
- [ ] Test UI interactions
- [ ] Test drag-and-drop
- [ ] Test sprint integration
- [ ] Migrate existing data
- [ ] Train users
- [ ] Monitor performance

---

**Last Updated**: October 15, 2025, 5:30 PM  
**Next Update**: After frontend components completion
