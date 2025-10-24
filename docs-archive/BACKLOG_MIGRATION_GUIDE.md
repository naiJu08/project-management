# Backlog Migration Guide
## Converting Existing Tickets to Azure DevOps-Style Backlog

**Version**: 1.0  
**Date**: October 15, 2025

---

## 📋 Overview

This guide explains how to migrate your existing tickets and epics to the new hierarchical Backlog system.

### What Gets Migrated

| Old System | New System | Notes |
|------------|------------|-------|
| **Epics** | Epic (BacklogItem) | Top-level items |
| **Tickets** | User Story (BacklogItem) | Under their Epic if assigned |
| **Ticket Status** | Mapped to: To Do, In Progress, Done, Blocked | |
| **Ticket Priority** | Mapped to: Critical, High, Medium, Low | |
| **Sprint Assignment** | Preserved | |
| **Assignee** | Preserved | |
| **Ticket Code** | Preserved | Original code kept |
| **Estimation** | Preserved as estimated_hours | |

### What Doesn't Get Migrated

- Ticket comments (remain with original ticket)
- Ticket attachments (remain with original ticket)
- Ticket hours logged (remain with original ticket)
- Ticket relationships (can be manually recreated)

---

## 🚀 Migration Methods

### Method 1: Automatic Migration (Recommended)

Use the built-in Artisan command with dry-run option first:

```bash
# Step 1: Dry run to preview changes
php artisan backlog:migrate-tickets --dry-run

# Step 2: Review the output, then run for real
php artisan backlog:migrate-tickets

# Optional: Migrate specific project only
php artisan backlog:migrate-tickets --project=1

# Skip confirmation prompt
php artisan backlog:migrate-tickets --force
```

### Method 2: Database Migration

Run the migration file directly:

```bash
# Step 1: Create mapping tables
php artisan migrate --path=database/migrations/2025_10_15_171900_create_migration_mapping_tables.php

# Step 2: Run the data migration
php artisan migrate --path=database/migrations/2025_10_15_172000_migrate_tickets_to_backlog_items.php
```

---

## 📊 Migration Process Details

### Step 1: Epics Migration

```
Old Epic:
- ID: 1
- Name: "User Authentication"
- Status: "In Progress"
- Project: Project A

↓ Converts to ↓

New Backlog Item:
- Type: Epic
- Title: "User Authentication"
- Status: "In Progress"
- Parent: null (top-level)
- Code: PRJ-EP-1
```

### Step 2: Tickets Migration

```
Old Ticket:
- ID: 42
- Code: PRJ-42
- Name: "Login Form"
- Epic: "User Authentication"
- Status: "Open"
- Priority: "High"
- Sprint: Sprint 3
- Assignee: John Doe

↓ Converts to ↓

New Backlog Item:
- Type: User Story
- Title: "Login Form"
- Parent: Epic "User Authentication"
- Status: "To Do"
- Priority: "High"
- Sprint: Sprint 3
- Assignee: John Doe
- Code: PRJ-42 (preserved)
```

### Step 3: Relationship Linking

```
Epic: User Authentication (EP-1)
└── User Story: Login Form (PRJ-42)
└── User Story: Registration Form (PRJ-43)
└── User Story: Password Reset (PRJ-44)
```

---

## 🔄 Status Mapping

The migration intelligently maps your ticket statuses:

| Ticket Status | Backlog Status | Trigger Words |
|---------------|----------------|---------------|
| Open, New, To Do, Pending | **To Do** | "open", "new", "todo", "pending" |
| In Progress, Working, Active | **In Progress** | "progress", "working", "active" |
| Done, Completed, Closed, Resolved | **Done** | "done", "complete", "closed", "resolved" |
| Blocked, On Hold | **Blocked** | "block", "hold" |

---

## 🎯 Priority Mapping

| Ticket Priority | Backlog Priority | Trigger Words |
|-----------------|------------------|---------------|
| Urgent, Critical | **Critical** | "critical", "urgent" |
| High | **High** | "high" |
| Medium, Normal | **Medium** | "medium", "normal" |
| Low | **Low** | "low" |

---

## ✅ Pre-Migration Checklist

Before running the migration:

- [ ] **Backup your database**
  ```bash
  mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
  ```

- [ ] **Run in dry-run mode first**
  ```bash
  php artisan backlog:migrate-tickets --dry-run
  ```

- [ ] **Check for orphaned tickets** (tickets without a project)
  ```sql
  SELECT * FROM tickets WHERE project_id IS NULL;
  ```

- [ ] **Verify epic assignments**
  ```sql
  SELECT COUNT(*) FROM tickets WHERE epic_id IS NOT NULL;
  ```

- [ ] **Clear caches**
  ```bash
  php artisan cache:clear
  php artisan config:clear
  ```

---

## 🧪 Testing the Migration

### 1. Dry Run Test

```bash
php artisan backlog:migrate-tickets --dry-run
```

**Expected Output**:
```
===========================================
Ticket to Backlog Items Migration Tool
===========================================

Current System Statistics:
+---------------------------+-------+
| Type                      | Count |
+---------------------------+-------+
| Epics                     | 5     |
| Tickets                   | 42    |
| Existing Backlog Items    | 0     |
+---------------------------+-------+

🔍 DRY RUN MODE - No changes will be made

Step 1: Migrating Epics...
  → Epic: User Authentication
  → Epic: Dashboard Features
✓ Migrated 5 epics

Step 2: Migrating Tickets to User Stories...
  → Ticket: PRJ-1 - Login Form
  → Ticket: PRJ-2 - Registration
...
✓ Migrated 42 tickets

🔍 Dry run complete - no changes were saved
```

### 2. Verify Mapping Tables

After migration, check the mapping tables:

```sql
-- Check epic mappings
SELECT e.name, bi.title, bi.type 
FROM epic_backlog_mapping ebm
JOIN epics e ON e.id = ebm.epic_id
JOIN backlog_items bi ON bi.id = ebm.backlog_item_id;

-- Check ticket mappings
SELECT t.code, t.name, bi.title, bi.type 
FROM ticket_backlog_mapping tbm
JOIN tickets t ON t.id = tbm.ticket_id
JOIN backlog_items bi ON bi.id = tbm.backlog_item_id
LIMIT 10;
```

### 3. Verify Hierarchy

```sql
-- Check Epic → User Story hierarchy
SELECT 
    parent.title as Epic,
    child.title as UserStory,
    child.code as OriginalCode
FROM backlog_items child
JOIN backlog_items parent ON parent.id = child.parent_id
WHERE parent.type = 'Epic' AND child.type = 'UserStory'
LIMIT 10;
```

---

## 🔧 Troubleshooting

### Issue 1: "Epic already migrated" warnings

**Cause**: Running migration multiple times  
**Solution**: This is safe to ignore. Already migrated items are skipped.

### Issue 2: Tickets not linked to Epics

**Cause**: Epic was not migrated first  
**Solution**: Run epic migration first, or re-run full migration

```bash
# Re-run with force flag
php artisan backlog:migrate-tickets --force
```

### Issue 3: Status mapping incorrect

**Cause**: Custom status names not recognized  
**Solution**: Manually update statuses after migration

```sql
UPDATE backlog_items 
SET status = 'In Progress' 
WHERE status = 'To Do' 
AND title LIKE '%currently working%';
```

### Issue 4: Missing backlog_item_id in tickets

**Cause**: Migration didn't complete  
**Solution**: Re-run migration or manually link

```sql
-- Check unlinked tickets
SELECT * FROM tickets WHERE backlog_item_id IS NULL;

-- Manual linking (if needed)
UPDATE tickets t
JOIN ticket_backlog_mapping tbm ON tbm.ticket_id = t.id
SET t.backlog_item_id = tbm.backlog_item_id
WHERE t.backlog_item_id IS NULL;
```

---

## 📈 Post-Migration Tasks

### 1. Verify Data Integrity

```bash
# Check counts match
php artisan tinker
>>> \App\Models\Ticket::count()
>>> \App\Models\BacklogItem::whereType('UserStory')->count()
>>> // Should be equal
```

### 2. Organize Hierarchy

After migration, you may want to:

1. **Create Features** between Epics and User Stories
   ```
   Epic: User Authentication
   └── Feature: Login System (NEW)
       └── User Story: Login Form
       └── User Story: Remember Me
   └── Feature: Registration System (NEW)
       └── User Story: Registration Form
   ```

2. **Break Down User Stories into Tasks**
   ```
   User Story: Login Form
   └── Task: Design UI (NEW)
   └── Task: Implement Backend (NEW)
   └── Task: Write Tests (NEW)
   ```

3. **Add Subtasks to Tasks**
   ```
   Task: Design UI
   └── Subtask: Create Wireframes (NEW)
   └── Subtask: Design Mockups (NEW)
   ```

### 3. Update Team

- Notify team about new Backlog system
- Provide training on hierarchical structure
- Update documentation/wiki

---

## 🔄 Rollback Procedure

If you need to rollback the migration:

```bash
# Rollback the data migration
php artisan migrate:rollback --step=1

# This will:
# 1. Remove backlog_item_id from tickets
# 2. Drop mapping tables
# 3. Keep backlog_items (manual cleanup needed)
```

**Manual cleanup** (if needed):

```sql
-- Delete migrated backlog items
DELETE FROM backlog_items 
WHERE code IN (SELECT code FROM tickets);

-- Or delete all backlog items
TRUNCATE TABLE backlog_items;
```

---

## 📊 Migration Statistics

After successful migration, you'll see:

```
===========================================
Migration Summary
===========================================
+--------------------+-------+
| Item               | Count |
+--------------------+-------+
| Epics Migrated     | 5     |
| Tickets Migrated   | 42    |
| Total Backlog Items| 47    |
+--------------------+-------+

Next Steps:
  1. Review the migrated data in the Backlog tab
  2. Organize items into Features if needed
  3. Break down User Stories into Tasks
  4. Assign items to sprints
```

---

## 🎓 Best Practices

### 1. Gradual Migration

For large projects, migrate one project at a time:

```bash
php artisan backlog:migrate-tickets --project=1 --dry-run
php artisan backlog:migrate-tickets --project=1
```

### 2. Backup Before Migration

```bash
# Full database backup
php artisan backup:run --only-db

# Or manual backup
mysqldump -u root -p project_management > backup_before_migration.sql
```

### 3. Test in Staging First

1. Clone production database to staging
2. Run migration in staging
3. Test thoroughly
4. Run in production

### 4. Monitor Performance

After migration, monitor:
- Page load times
- API response times
- Database query performance

```sql
-- Check backlog_items table size
SELECT 
    COUNT(*) as total_items,
    AVG(LENGTH(description)) as avg_description_length
FROM backlog_items;
```

---

## 🆘 Support

### Common Questions

**Q: Will my existing tickets still work?**  
A: Yes! Tickets remain functional. The `backlog_item_id` field links them to the new system.

**Q: Can I use both systems simultaneously?**  
A: Yes, during transition period. Eventually, you should use the Backlog system.

**Q: What happens to ticket comments?**  
A: They remain with the original ticket. You can add new comments in the Backlog system.

**Q: Can I undo the migration?**  
A: Yes, use the rollback procedure above. Always backup first!

**Q: How long does migration take?**  
A: Depends on data size. Typically:
- Small (< 100 tickets): < 1 minute
- Medium (100-1000 tickets): 1-5 minutes
- Large (> 1000 tickets): 5-15 minutes

---

## 📝 Migration Checklist

Use this checklist for your migration:

- [ ] Read this guide completely
- [ ] Backup database
- [ ] Run dry-run migration
- [ ] Review dry-run output
- [ ] Clear caches
- [ ] Run actual migration
- [ ] Verify data integrity
- [ ] Check mapping tables
- [ ] Test Backlog UI
- [ ] Organize hierarchy
- [ ] Train team
- [ ] Update documentation
- [ ] Monitor performance
- [ ] Celebrate! 🎉

---

## 📚 Additional Resources

- **Main Implementation Doc**: `BACKLOG_AZURE_DEVOPS_IMPLEMENTATION_PROGRESS.md`
- **API Documentation**: See routes in `routes/api.php`
- **Model Documentation**: See `app/Models/BacklogItem.php`

---

**Last Updated**: October 15, 2025  
**Version**: 1.0  
**Status**: Ready for Production
