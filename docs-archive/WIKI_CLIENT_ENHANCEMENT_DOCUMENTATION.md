# Wiki Module Enhancement: Client View, Comments & Sign-Offs

**Implementation Date**: October 15, 2025  
**Version**: 1.0  
**Status**: ✅ Complete

---

## Overview

Enhanced the existing Wiki module with dedicated client-facing features including:
- **Client Wiki View** - Read-only documentation access for clients
- **Collaborative Comments** - Threaded discussions between team and clients
- **Document Sign-Off** - Formal approval workflow with version tracking

All existing Wiki functionality (editing, versioning, markdown, hierarchy, permissions) remains fully intact.

---

## Database Schema Changes

### 1. `wiki_pages` Table (Extended)
```sql
ALTER TABLE wiki_pages ADD COLUMN:
- client_visible BOOLEAN DEFAULT false
- client_visible_at TIMESTAMP NULL
```

### 2. `wiki_comments` Table (New)
```sql
CREATE TABLE wiki_comments (
    id BIGINT PRIMARY KEY,
    wiki_page_id BIGINT FOREIGN KEY → wiki_pages.id (CASCADE DELETE),
    user_id BIGINT FOREIGN KEY → users.id (CASCADE DELETE),
    parent_comment_id BIGINT NULL FOREIGN KEY → wiki_comments.id (CASCADE DELETE),
    content TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

**Features**:
- Threaded comments (parent-child hierarchy)
- Soft deletes
- Markdown support
- Role badges (Client/Team)

### 3. `wiki_signoffs` Table (New)
```sql
CREATE TABLE wiki_signoffs (
    id BIGINT PRIMARY KEY,
    wiki_page_id BIGINT FOREIGN KEY → wiki_pages.id (CASCADE DELETE),
    client_id BIGINT FOREIGN KEY → users.id (CASCADE DELETE),
    signed_off_by BIGINT FOREIGN KEY → users.id (CASCADE DELETE),
    signed_off_at TIMESTAMP,
    version_signed INT,
    remarks TEXT NULL,
    is_outdated BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Features**:
- Version tracking
- Automatic outdating on content changes
- Optional remarks
- Sign-off history

---

## Models

### WikiPage (Extended)
**Location**: `app/Models/WikiPage.php`

**New Relationships**:
```php
public function comments(): HasMany
public function allComments(): HasMany
public function signoffs(): HasMany
public function activeSignoffs(): HasMany
```

**New Methods**:
```php
public function isSignedOff(): bool
public function getSignoffStatus(): string  // Returns: Pending|Signed-Off|Outdated
public function makeClientVisible(): void
public function hideFromClient(): void
public function scopeClientVisible($query)
```

**Automatic Behavior**:
- On update: Marks existing sign-offs as outdated if content/title changes
- On delete: Cascades to comments and sign-offs

### WikiComment (New)
**Location**: `app/Models/WikiComment.php`

**Relationships**:
```php
public function wikiPage(): BelongsTo
public function user(): BelongsTo
public function parentComment(): BelongsTo
public function replies(): HasMany
```

**Methods**:
```php
public function isClientComment(): bool
public function canDelete(): bool
```

### WikiSignoff (New)
**Location**: `app/Models/WikiSignoff.php`

**Relationships**:
```php
public function wikiPage(): BelongsTo
public function client(): BelongsTo
public function signedOffBy(): BelongsTo
```

**Methods**:
```php
public function markAsOutdated(): void
public function isValid(): bool
```

---

## API Endpoints

### Internal Wiki (Existing + Extended)
```
GET    /api/projects/{project}/wiki
POST   /api/projects/{project}/wiki
GET    /api/wiki/{wikiPage}
PUT    /api/wiki/{wikiPage}
DELETE /api/wiki/{wikiPage}
```

### Comments
```
GET    /api/wiki/{wikiPage}/comments       - List all comments
POST   /api/wiki/{wikiPage}/comments       - Add comment
DELETE /api/comments/{comment}             - Delete own comment
```

### Sign-Offs
```
GET    /api/wiki/{wikiPage}/signoffs       - List sign-offs + status
POST   /api/wiki/{wikiPage}/signoff        - Create sign-off (clients only)
```

### Client Wiki View
```
GET    /api/client/projects/{project}/wiki - List client-visible pages
GET    /api/client/wiki/{wikiPage}         - View specific page
```

---

## Controllers

### WikiCommentController (New)
**Location**: `app/Http/Controllers/Api/WikiCommentController.php`

**Methods**:
- `index()` - Get comments with replies
- `store()` - Add comment + notify project members
- `destroy()` - Delete comment (permission check)

### WikiSignoffController (New)
**Location**: `app/Http/Controllers/Api/WikiSignoffController.php`

**Methods**:
- `index()` - Get sign-offs + status
- `store()` - Create sign-off (client role required) + notify team

### ClientWikiController (New)
**Location**: `app/Http/Controllers/Api/ClientWikiController.php`

**Methods**:
- `index()` - List client-visible pages only
- `show()` - View page with comments/sign-offs

---

## Livewire Components

### WikiView (Extended)
**Location**: `app/Http/Livewire/Project/WikiView.php`

**New Properties**:
```php
public $clientVisible = false;
public $newComment = '';
public $replyToCommentId = null;
public $signoffRemarks = '';
```

**New Methods**:
```php
public function addComment()
public function replyToComment($commentId)
public function cancelReply()
public function deleteComment($commentId)
public function signOffPage()
```

**UI Enhancements**:
- Client visibility toggle checkbox
- Comments section with threaded replies
- Sign-off panel for clients
- Status badges (Pending/Signed-Off/Outdated)
- Sign-off banner when approved

### ClientWikiView (New)
**Location**: `app/Http/Livewire/Project/ClientWikiView.php`

**Features**:
- Read-only view of client-visible pages
- Comment functionality
- Sign-off capability
- Clean, simplified interface

---

## Permissions

### New Permissions Added
```php
'View client wiki'    - Access client wiki tab
'Comment on wiki'     - Post comments on wiki pages
'Sign off wiki'       - Sign off documents (clients only)
```

### Permission Seeder
**Location**: `database/seeders/PermissionsSeeder.php`

Updated `$extraPermissions` array to include new permissions.

---

## Notifications

### WikiCommentAdded
**Location**: `app/Notifications/WikiCommentAdded.php`

**Triggers**: When any user adds a comment  
**Recipients**: All project members (except commenter)  
**Channels**: Email + Database  
**Content**: Commenter role, comment preview, link to page

### WikiPageSignedOff
**Location**: `app/Notifications/WikiPageSignedOff.php`

**Triggers**: When a client signs off a page  
**Recipients**: Project owner + all project members  
**Channels**: Email + Database  
**Content**: Client name, version, remarks, timestamp

---

## UI/UX Features

### Internal Wiki View
1. **Client Visibility Toggle**
   - Checkbox in edit mode
   - Eye icon indicator
   - Blue banner when visible to clients

2. **Sign-Off Status Badge**
   - Green: Signed-Off
   - Yellow: Outdated
   - Gray: Pending

3. **Sign-Off Banner**
   - Displays when page is signed off
   - Shows client name, date, version
   - Includes remarks if provided

4. **Comments Section**
   - Markdown support
   - Threaded replies (indented)
   - Role badges (Client/Team)
   - Reply and Delete actions
   - Timestamps (relative)

5. **Sign-Off Panel** (for clients)
   - Confirmation message
   - Optional remarks textarea
   - Sign-off button
   - History of past sign-offs

### Client Wiki View
1. **Simplified Navigation**
   - "Client Documentation" header
   - Only client-visible pages shown
   - Clean tree structure

2. **Read-Only Content**
   - No edit/delete buttons
   - Full markdown rendering
   - Version info displayed

3. **Interactive Features**
   - Comment and reply
   - Sign-off capability
   - View sign-off history

---

## Workflow Examples

### Scenario 1: Publishing Documentation for Client
1. Team member creates wiki page
2. Checks "Visible to Client" checkbox
3. Saves page
4. Client sees page in "Client Wiki" tab
5. Client can comment and ask questions
6. Team responds via comments
7. Client signs off when satisfied

### Scenario 2: Updating Signed-Off Document
1. Team updates content of signed-off page
2. System automatically marks existing sign-offs as "Outdated"
3. Status badge changes from "Signed-Off" to "Outdated"
4. Client is notified (optional)
5. Client reviews changes
6. Client signs off new version

### Scenario 3: Threaded Discussion
1. Client posts comment on wiki page
2. Team member replies to comment (threaded)
3. Another team member adds separate comment
4. Client replies to both threads
5. All participants notified via email

---

## Version Control & Sign-Off Logic

### Version Tracking
- Version increments on every content/title update
- Displayed in page header
- Tracked in sign-off records

### Sign-Off Validation
```php
// Check if already signed off
$existingSignoff = $page->signoffs()
    ->where('client_id', auth()->id())
    ->where('version_signed', $page->version)
    ->where('is_outdated', false)
    ->first();
```

### Automatic Outdating
```php
// In WikiPage::boot()
static::updating(function ($wikiPage) {
    if ($wikiPage->isDirty(['content', 'title'])) {
        $wikiPage->signoffs()
            ->where('is_outdated', false)
            ->update(['is_outdated' => true]);
    }
});
```

---

## Security & Access Control

### Client Access Rules
- Only see pages with `client_visible = true`
- Cannot edit or delete pages
- Can comment and sign off
- Must have "View client wiki" permission

### Team Access Rules
- Full CRUD on wiki pages
- Can toggle client visibility
- Can comment and reply
- Can view all sign-offs
- Cannot sign off (client-only action)

### API Authorization
```php
// ClientWikiController checks:
1. User has role 'Client' OR is project member/owner
2. Page has client_visible = true
3. User has 'View client wiki' permission
```

---

## Migration Instructions

### Step 1: Run Migration
```bash
php artisan migrate
```

### Step 2: Seed Permissions
```bash
php artisan db:seed --class=PermissionsSeeder
```

### Step 3: Assign Permissions to Client Role
```php
// Via Filament or tinker
$clientRole = Role::where('name', 'Client')->first();
$clientRole->givePermissionTo([
    'View client wiki',
    'Comment on wiki',
    'Sign off wiki'
]);
```

### Step 4: Clear Caches
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

---

## Testing Checklist

### Internal Users
- [ ] Create wiki page
- [ ] Toggle "Visible to Client" checkbox
- [ ] Save and verify client_visible flag
- [ ] Add comment to page
- [ ] Reply to comment
- [ ] Delete own comment
- [ ] View sign-off status
- [ ] See sign-off banner when client signs off

### Client Users
- [ ] Access "Client Wiki" tab
- [ ] See only client-visible pages
- [ ] View page content (read-only)
- [ ] Add comment
- [ ] Reply to team comment
- [ ] Sign off document
- [ ] Verify cannot sign off twice
- [ ] See sign-off confirmation
- [ ] View sign-off history

### Edge Cases
- [ ] Update signed-off page → sign-offs marked outdated
- [ ] Delete page → comments and sign-offs cascade deleted
- [ ] Delete comment with replies → replies also deleted
- [ ] Non-client tries to sign off → error
- [ ] Already signed off → error message
- [ ] Search pages → client-visible filter works

---

## File Structure

```
app/
├── Models/
│   ├── WikiPage.php (extended)
│   ├── WikiComment.php (new)
│   └── WikiSignoff.php (new)
├── Http/
│   ├── Controllers/Api/
│   │   ├── WikiController.php (existing)
│   │   ├── WikiCommentController.php (new)
│   │   ├── WikiSignoffController.php (new)
│   │   └── ClientWikiController.php (new)
│   └── Livewire/Project/
│       ├── WikiView.php (extended)
│       └── ClientWikiView.php (new)
└── Notifications/
    ├── WikiCommentAdded.php (new)
    └── WikiPageSignedOff.php (new)

database/
└── migrations/
    └── 2025_10_15_163700_add_client_visibility_comments_signoffs_to_wiki.php

resources/views/livewire/project/
├── wiki-view.blade.php (extended)
├── client-wiki-view.blade.php (new)
└── project-detail.blade.php (extended - added Client Wiki tab)

routes/
└── api.php (extended with new endpoints)
```

---

## Future Enhancements

### Potential Features
1. **Email Digest** - Daily summary of comments for clients
2. **Mention System** - @mention users in comments
3. **File Attachments** - Attach files to comments
4. **Comment Reactions** - Like/emoji reactions
5. **Sign-Off Reminders** - Automated reminders for pending sign-offs
6. **Bulk Sign-Off** - Sign off multiple pages at once
7. **Version Diff** - Show changes between versions
8. **Export to PDF** - Download signed-off documents
9. **Approval Workflow** - Multi-level approval process
10. **Comment Notifications** - Real-time notifications via WebSockets

---

## Troubleshooting

### Issue: Client Wiki tab not visible
**Solution**: Ensure user has "View client wiki" permission

### Issue: Cannot sign off
**Solution**: 
1. Verify user has "Client" role
2. Check "Sign off wiki" permission
3. Ensure page is client_visible
4. Confirm not already signed off current version

### Issue: Comments not showing
**Solution**: 
1. Check "Comment on wiki" permission
2. Verify comments relationship loaded in Livewire component
3. Clear Livewire cache: `php artisan livewire:discover`

### Issue: Sign-offs not marked outdated
**Solution**: 
1. Verify WikiPage::boot() updating logic
2. Check isDirty() detects content/title changes
3. Manually run: `$page->signoffs()->update(['is_outdated' => true])`

---

## API Response Examples

### GET /api/wiki/{wikiPage}/comments
```json
[
  {
    "id": 1,
    "wiki_page_id": 5,
    "user_id": 10,
    "parent_comment_id": null,
    "content": "This looks great!",
    "created_at": "2025-10-15T10:30:00Z",
    "user": {
      "id": 10,
      "name": "John Client",
      "roles": ["Client"]
    },
    "replies": [
      {
        "id": 2,
        "parent_comment_id": 1,
        "content": "Thanks! Let me know if you have questions.",
        "user": {
          "name": "Jane Developer"
        }
      }
    ]
  }
]
```

### GET /api/wiki/{wikiPage}/signoffs
```json
{
  "signoffs": [
    {
      "id": 1,
      "wiki_page_id": 5,
      "client_id": 10,
      "signed_off_at": "2025-10-15T14:00:00Z",
      "version_signed": 3,
      "remarks": "Approved for production",
      "is_outdated": false,
      "client": {
        "name": "John Client"
      }
    }
  ],
  "status": "Signed-Off",
  "is_signed_off": true
}
```

---

## Backward Compatibility

✅ **All existing Wiki features preserved**:
- Hierarchical page structure
- Markdown editing
- Version tracking
- Search functionality
- File attachments (Spatie Media Library)
- Full CRUD operations
- Tree navigation sidebar
- Permissions system

✅ **No breaking changes**:
- Existing wiki pages work as before
- Default `client_visible = false` maintains privacy
- New features are opt-in
- API endpoints backward compatible

---

## Credits

**Implementation**: Cascade AI Assistant  
**Date**: October 15, 2025  
**Framework**: Laravel 9 + Filament v2 + Livewire v2  
**Database**: MySQL  
**Styling**: Tailwind CSS  

---

## Support

For issues or questions:
1. Check this documentation
2. Review code comments in controllers/models
3. Test with provided checklist
4. Verify permissions are correctly assigned

---

**End of Documentation**
