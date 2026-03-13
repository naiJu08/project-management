# Important: Data Migration Note

## ⚠️ Migration File vs Migration Command

The migration file `2025_10_15_172000_migrate_tickets_to_backlog_items.php` is **intentionally empty** when run via `php artisan migrate`.

### Why?

To prevent:
- Duplicate entries
- Accidental data migration during deployment
- Conflicts with existing backlog items

### How to Migrate Data

**Use the dedicated Artisan command instead:**

```bash
# Test first with dry-run
php artisan backlog:migrate-tickets --dry-run

# Review the output, then run for real
php artisan backlog:migrate-tickets

# Or migrate specific project
php artisan backlog:migrate-tickets --project=1
```

### What Happens

The migration file (`php artisan migrate`):
- ✅ Creates the database schema (tables, columns)
- ❌ Does NOT migrate data automatically

The Artisan command (`php artisan backlog:migrate-tickets`):
- ✅ Migrates your existing tickets to backlog items
- ✅ Preserves all data
- ✅ Has dry-run mode for safety
- ✅ Provides detailed progress reporting

### Deployment Workflow

```bash
# 1. Run migrations (creates tables)
php artisan migrate

# 2. Seed permissions
php artisan db:seed --class=PermissionsSeeder

# 3. Test data migration with dry-run
php artisan backlog:migrate-tickets --dry-run

# 4. Run actual data migration
php artisan backlog:migrate-tickets

# 5. Clear caches
php artisan cache:clear
```

### Already Have Backlog Items?

If you already have backlog items (from testing or previous migration), the command will:
- Skip items that are already migrated
- Show warnings for duplicates
- Continue with unmigrated items

### Need to Re-migrate?

If you need to start fresh:

```bash
# 1. Truncate backlog tables (WARNING: deletes all backlog data)
php artisan tinker
>>> DB::table('backlog_items')->truncate();
>>> DB::table('backlog_item_comments')->truncate();
>>> DB::table('backlog_item_histories')->truncate();
>>> DB::table('ticket_backlog_mapping')->truncate();
>>> DB::table('epic_backlog_mapping')->truncate();
>>> exit

# 2. Run migration again
php artisan backlog:migrate-tickets
```

### Questions?

See `BACKLOG_MIGRATION_GUIDE.md` for comprehensive migration documentation.
