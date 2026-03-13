# Employee Ticket Workflow - Quick Guide

**Status:** ✅ LIVE & PRODUCTION READY

---

## 🚀 Quick Start

### Access Ticket
```
URL: /tickets/{ticket_id}
Example: /tickets/110
```

---

## 📋 5 Main Tabs

| Tab | Icon | Purpose | Key Actions |
|-----|------|---------|-------------|
| **Overview** | 📋 | Ticket info | View details, Change status |
| **Dates** | 📅 | Timeline | Track deadlines, SLA, overdue |
| **Track Time** | ⏱️ | Hours | Log time, Edit entries |
| **Relationships** | 🔗 | Dependencies | Add/remove relationships |
| **Comments** | 💬 | Discussion | Add/edit/delete comments |

---

## ⏱️ How to Log Time

1. Click **Track Time** tab
2. Click **+ Add Time** button
3. Enter hours (0.25 - 24)
4. Add description (optional)
5. Click **✓ Log Time**

**Edit Time:**
- Click **Edit** on your time entry
- Update hours/description
- Click **Save**

**Delete Time:**
- Click **Delete** on your time entry
- Confirm deletion

---

## 💬 How to Comment

1. Click **Comments** tab
2. Click **+ Add Comment** button
3. Type your comment
4. Click **✓ Post Comment**

**Edit Comment:**
- Click **Edit** on your comment
- Update text
- Click **Save**

**Delete Comment:**
- Click **Delete** on your comment
- Confirm deletion

**Search Comments:**
- Use search box at top
- Type keyword
- Results filter automatically

---

## 🔗 How to Add Relationships

1. Click **Relationships** tab
2. Click **+ Add Relationship** button
3. Select relationship type:
   - **Related To** - General link
   - **Duplicates** - Duplicate ticket
   - **Blocks** - This blocks another
   - **Blocked By** - Blocked by another
4. Enter ticket ID or code
5. Click **✓ Add**

**View Relationships:**
- **All Relationships** - Complete list
- **🚫 Blocked By** - Red section
- **⚠️ Blocks** - Orange section
- **📋 Duplicates** - Purple section

**Remove Relationship:**
- Click **X** button on relationship
- Confirm removal

---

## 📅 Dates & Deadlines Tab

**Timeline Overview:**
- 📅 **Start Date** - When work begins
- 📅 **Due Date** - Deadline (red if overdue)
- ⏱️ **Time Estimate** - Hours planned
- 🎯 **SLA Due** - Service level deadline
- 💬 **First Response** - When first response given
- ✓ **Resolved** - When ticket resolved

**Status Indicators:**
- 🟢 Green = On track
- 🟡 Yellow = At risk
- 🔴 Red = Overdue/Breached

---

## 📊 Overview Tab

**View:**
- Full ticket description
- Type & Component
- Owner & Assigned person
- Sprint & Epic
- Severity & Risk level

**Actions:**
- **Change Status** - Update ticket status (if owner/responsible)
- Select new status from dropdown
- Click **✓ Update**

---

## 📌 Quick Info Sidebar

**Always Visible:**
- Status badge
- Priority badge
- Progress bar
- Dates (Start, Due)
- Owner & Responsible
- Sprint & Type
- Relationships count
- Comments count
- Time summary
- Blocked status (if applicable)

---

## 🎨 Color Coding

### Status Badges
- 🔵 **Blue** - Open
- 🟡 **Yellow** - In Progress
- 🟢 **Green** - Done

### Priority Badges
- 🔴 **Red** - Critical
- 🟠 **Orange** - High
- 🟡 **Yellow** - Medium
- 🟢 **Green** - Low

### Relationship Sections
- 🚫 **Red** - Blocked By
- ⚠️ **Orange** - Blocks
- 📋 **Purple** - Duplicates

---

## ⏱️ Time Tracking Summary

**Cards Show:**
- **Estimated** - Total hours planned
- **Logged** - Hours you've worked
- **Remaining** - Hours left (green = on track, red = over)
- **Progress** - % complete

**Progress Bar:**
- Shows visual progress
- Updates as you log time
- Turns red if over budget

---

## 🔐 Permissions

**What You Can Do:**
- ✅ Log time on any ticket
- ✅ Edit/delete your own time entries
- ✅ Comment on any ticket
- ✅ Edit/delete your own comments
- ✅ Add relationships
- ✅ Change status (if owner/responsible)

**What You Cannot Do:**
- ❌ Edit others' time entries
- ❌ Delete others' comments
- ❌ Change status (unless owner/responsible)

---

## 🔍 Search & Filter

**Search Comments:**
- Use search box in Comments tab
- Type keyword
- Results filter in real-time
- Click X to clear search

---

## 📱 Mobile Tips

- Tabs scroll horizontally
- Sidebar appears below content
- Touch-friendly buttons
- Swipe to navigate
- Full functionality on mobile

---

## 🌙 Dark Mode

- Click theme toggle (if available)
- Automatic theme detection
- Smooth transitions
- Full dark mode support

---

## ⚡ Quick Tips

💡 **Time Tracking:**
- Log time regularly for accuracy
- Add descriptions for context
- Check remaining hours to stay on track

💡 **Comments:**
- Use for team discussion
- Mention important updates
- Search for previous discussions

💡 **Relationships:**
- Link related tickets
- Mark blocking dependencies
- Track duplicates

💡 **Dates:**
- Check due dates regularly
- Watch for overdue indicator
- Monitor SLA status

💡 **Status:**
- Update status as you progress
- Keep team informed
- Triggers notifications

---

## 🆘 Need Help?

**Click the Help Button** (? icon, bottom-right)
- Get context-aware guidance
- Learn about current tab
- View tips and examples

---

## 📞 Common Issues

**Time not logging?**
- Check hours are 0.25 - 24
- Verify you're logged in
- Refresh page if needed

**Comment not showing?**
- Refresh page
- Check you're logged in
- Clear browser cache

**Status not updating?**
- Verify you're owner/responsible
- Refresh page
- Check browser console

**Relationship not adding?**
- Verify ticket ID exists
- Check ticket code format
- Ensure not self-referencing

---

## 🚀 Status

✅ **PRODUCTION READY**

**URL:** `/tickets/{ticket_id}`

**Last Updated:** October 26, 2025

