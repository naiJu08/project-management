# Wiki Client Enhancement - Quick Start Guide

## 🚀 What's New?

Your Wiki module now supports:
- ✅ **Client View** - Dedicated read-only wiki for clients
- ✅ **Comments** - Threaded discussions between team and clients
- ✅ **Sign-Offs** - Formal document approval with version tracking

## ⚡ Quick Setup (5 minutes)

### Option 1: Automated Setup
```bash
./setup-wiki-client-permissions.sh
```

### Option 2: Manual Setup
```bash
# 1. Run migration
php artisan migrate

# 2. Seed permissions
php artisan db:seed --class=PermissionsSeeder

# 3. Configure Client role (via Filament or tinker)
php artisan tinker
>>> $role = App\Models\Role::where('name', 'Client')->first();
>>> $role->givePermissionTo(['View client wiki', 'Comment on wiki', 'Sign off wiki']);

# 4. Clear caches
php artisan cache:clear && php artisan view:clear
```

## 📝 Basic Usage

### For Team Members

#### 1. Create a Wiki Page for Clients
1. Go to Project → Wiki tab
2. Click "New Page"
3. Write your content in Markdown
4. ✅ Check "Visible to Client"
5. Click "Save Page"

#### 2. Manage Comments
- View all comments at the bottom of each page
- Reply to client questions
- Delete inappropriate comments

#### 3. View Sign-Off Status
- Check the status badge: **Pending** | **Signed-Off** | **Outdated**
- See who signed off and when
- View sign-off history

### For Clients

#### 1. Access Client Wiki
1. Go to Project view
2. Click **"Client Wiki"** tab
3. Browse available documentation

#### 2. Add Comments
1. Open any wiki page
2. Scroll to "Comments" section
3. Type your comment (Markdown supported)
4. Click "Post Comment"
5. Reply to team responses

#### 3. Sign Off Documents
1. Review the wiki page content
2. Scroll to "Sign-Off" section
3. Add optional remarks
4. Click "Sign Off Document"
5. ✅ Done! Team will be notified

## 🎯 Key Features

### Client Visibility Control
```
Internal Wiki: Full edit access + client visibility toggle
Client Wiki:   Read-only view of approved pages only
```

### Comment System
- **Threaded replies** (nested conversations)
- **Role badges** (Client/Team labels)
- **Markdown support** (formatting, links, code)
- **Delete own comments** (with permissions)
- **Email notifications** (auto-sent to team)

### Sign-Off Workflow
```
1. Team publishes page → Status: Pending
2. Client reviews & signs off → Status: Signed-Off
3. Team updates content → Status: Outdated (auto)
4. Client signs off again → Status: Signed-Off (new version)
```

## 🔐 Permissions

### Client Role
- ✅ View client wiki
- ✅ Comment on wiki
- ✅ Sign off wiki
- ❌ Edit/delete pages

### Team Roles
- ✅ Full wiki CRUD
- ✅ Toggle client visibility
- ✅ Comment and reply
- ✅ View all sign-offs
- ❌ Cannot sign off (client-only)

## 📊 Status Indicators

| Badge | Meaning | Action Required |
|-------|---------|----------------|
| 🟢 **Signed-Off** | Client approved current version | None |
| 🟡 **Outdated** | Content changed after sign-off | Client needs to review |
| ⚪ **Pending** | Awaiting client sign-off | Client review needed |

## 🔔 Notifications

### Email Alerts Sent When:
- ✉️ Client adds a comment → Team notified
- ✉️ Team replies to comment → Client notified
- ✉️ Client signs off page → Team notified
- ✉️ Page updated after sign-off → Client notified (optional)

## 🎨 UI Elements

### Internal Wiki View
- **Client Visibility Toggle** - Checkbox in edit mode
- **Blue Banner** - "This page is visible to clients"
- **Status Badge** - Top-right corner
- **Sign-Off Banner** - Green box when approved
- **Comments Section** - Bottom of page
- **Sign-Off Panel** - For client users only

### Client Wiki View
- **Simplified Sidebar** - "Client Documentation" header
- **Read-Only Content** - No edit buttons
- **Comment Thread** - Full discussion view
- **Sign-Off Button** - Prominent green button
- **Sign-Off History** - Past approvals listed

## 🧪 Testing Checklist

### Team Member Test
- [ ] Create wiki page
- [ ] Mark as "Visible to Client"
- [ ] Add a comment
- [ ] Reply to client comment
- [ ] See sign-off notification

### Client Test
- [ ] Access "Client Wiki" tab
- [ ] View published pages
- [ ] Add a comment
- [ ] Reply to team comment
- [ ] Sign off a document
- [ ] Verify cannot sign off twice

## 🐛 Troubleshooting

### "Client Wiki tab not showing"
→ Check user has "View client wiki" permission

### "Cannot sign off"
→ Verify:
1. User has "Client" role
2. Page is marked client_visible
3. Not already signed off current version

### "Comments not appearing"
→ Check "Comment on wiki" permission

### "Sign-offs not outdating"
→ Clear cache: `php artisan cache:clear`

## 📚 Resources

- **Full Documentation**: `WIKI_CLIENT_ENHANCEMENT_DOCUMENTATION.md`
- **Database Schema**: See migration file
- **API Endpoints**: Check `routes/api.php`
- **Models**: `app/Models/WikiPage.php`, `WikiComment.php`, `WikiSignoff.php`

## 🆘 Support

**Common Issues**:
1. Clear all caches after setup
2. Verify permissions are assigned to roles
3. Check user has correct role assigned
4. Ensure page is marked client_visible

**Need Help?**
- Review full documentation
- Check Laravel logs: `storage/logs/laravel.log`
- Verify database tables created: `wiki_comments`, `wiki_signoffs`

---

## 🎉 You're All Set!

Start by creating a wiki page and marking it visible to clients. They'll see it in their Client Wiki tab and can comment and sign off.

**Happy Documenting! 📖**
