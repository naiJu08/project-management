# Wiki Module - Quick Start Guide

## 🚀 Getting Started (5 Minutes)

### Step 1: Access the Wiki
1. Navigate to any project
2. Click the **"Wiki"** tab in the project detail page
3. You'll see the wiki interface with sidebar and main content area

### Step 2: Create Your First Page
1. Click the **"New Page"** button in the left sidebar
2. Enter a **title** (e.g., "Getting Started")
3. Write **content** in markdown format
4. Click **"Save Page"**

### Step 3: View Your Page
- Your page now appears in the sidebar
- Click it to view the rendered content
- See version number and last updated info

### Step 4: Edit a Page
1. Click any page to view it
2. Click the **"Edit"** button at the top
3. Modify the title or content
4. Click **"Save Page"** (version auto-increments)

### Step 5: Search Pages
- Type in the search box at the top of the sidebar
- Results filter in real-time
- Click any result to view that page

---

## 📝 Markdown Cheat Sheet

### Headings
```markdown
# Heading 1
## Heading 2
### Heading 3
```

### Text Formatting
```markdown
**Bold text**
*Italic text*
~~Strikethrough~~
`Inline code`
```

### Lists
```markdown
- Bullet point 1
- Bullet point 2
  - Nested bullet

1. Numbered item 1
2. Numbered item 2
```

### Links & Images
```markdown
[Link text](https://example.com)
![Image alt text](image-url.jpg)
```

### Code Blocks
````markdown
```javascript
function hello() {
  console.log("Hello World!");
}
```
````

### Tables
```markdown
| Column 1 | Column 2 | Column 3 |
|----------|----------|----------|
| Data 1   | Data 2   | Data 3   |
| Data 4   | Data 5   | Data 6   |
```

### Quotes
```markdown
> This is a quote
> It can span multiple lines
```

---

## 🎯 Common Use Cases

### 1. Project Documentation
```markdown
# Project Overview

## Purpose
Describe what this project does...

## Architecture
- Frontend: React
- Backend: Laravel
- Database: MySQL

## Getting Started
1. Clone the repository
2. Run `composer install`
3. Configure `.env` file
```

### 2. Meeting Notes
```markdown
# Team Meeting - Oct 15, 2025

## Attendees
- John Doe
- Jane Smith

## Agenda
1. Sprint review
2. Planning next sprint
3. Technical discussions

## Action Items
- [ ] John: Complete feature X
- [ ] Jane: Review PR #123
```

### 3. Technical Specifications
```markdown
# API Specification

## Endpoints

### GET /api/users
Returns list of all users

**Response:**
```json
{
  "users": [...]
}
```

### POST /api/users
Creates a new user

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com"
}
```
```

### 4. Troubleshooting Guide
```markdown
# Common Issues

## Issue: Application won't start

**Symptoms:**
- Error message: "Connection refused"
- Server not responding

**Solution:**
1. Check if database is running
2. Verify `.env` configuration
3. Run `php artisan migrate`

## Issue: Login fails

**Solution:**
- Clear cache: `php artisan cache:clear`
- Check user credentials
```

---

## 🔑 Keyboard Shortcuts

| Action | Shortcut |
|--------|----------|
| Save page | Ctrl + S (in edit mode) |
| Cancel edit | Esc |
| Search | Ctrl + F |

---

## ✅ Best Practices

### DO ✅
- ✅ Use descriptive page titles
- ✅ Organize pages hierarchically
- ✅ Keep content up-to-date
- ✅ Use markdown formatting
- ✅ Link related pages together
- ✅ Add code examples when relevant

### DON'T ❌
- ❌ Create duplicate pages
- ❌ Use vague titles like "Page 1"
- ❌ Leave pages empty
- ❌ Forget to save changes
- ❌ Delete pages without checking dependencies

---

## 🎨 Page Organization Tips

### Create a Home Page
```markdown
# Welcome to [Project Name] Wiki

## Quick Links
- [Getting Started](link)
- [Architecture](link)
- [API Documentation](link)
- [Troubleshooting](link)

## Recent Updates
- Oct 15: Added API documentation
- Oct 14: Updated deployment guide
```

### Use Hierarchical Structure
```
📄 Home
├── 📄 Getting Started
│   ├── 📄 Installation
│   └── 📄 Configuration
├── 📄 Development
│   ├── 📄 Frontend Guide
│   └── 📄 Backend Guide
└── 📄 Deployment
    ├── 📄 Production Setup
    └── 📄 CI/CD Pipeline
```

---

## 🔧 Quick Fixes

### Page Not Saving?
1. Check if title is filled (required)
2. Verify you have edit permissions
3. Check browser console for errors

### Search Not Working?
1. Refresh the page
2. Clear browser cache
3. Check if pages exist

### Content Not Rendering?
1. Check markdown syntax
2. Verify no special characters breaking format
3. Try editing and re-saving

---

## 📱 Mobile Usage

The wiki is fully responsive:
- Sidebar collapses on mobile
- Touch-friendly buttons
- Swipe to navigate
- Optimized for small screens

---

## 🎓 Video Tutorials (Coming Soon)

1. **Creating Your First Wiki Page** (2 min)
2. **Markdown Formatting Basics** (5 min)
3. **Organizing Pages Hierarchically** (3 min)
4. **Advanced Markdown Features** (7 min)
5. **Collaborative Wiki Management** (5 min)

---

## 💡 Pro Tips

### Tip 1: Use Templates
Create a template page and copy its content when creating similar pages.

### Tip 2: Version Control
Each edit increments the version number - use this to track major changes.

### Tip 3: Cross-Reference
Link related pages together for easy navigation:
```markdown
See also: [Related Page](link)
```

### Tip 4: Code Documentation
Use code blocks with language specification for syntax highlighting:
````markdown
```php
<?php
function example() {
    return "Hello";
}
```
````

### Tip 5: Table of Contents
For long pages, create a TOC at the top:
```markdown
## Table of Contents
- [Section 1](#section-1)
- [Section 2](#section-2)
- [Section 3](#section-3)
```

---

## 🆘 Need Help?

1. **Documentation**: Read WIKI_MODULE_DOCUMENTATION.md
2. **Support**: Contact your project admin
3. **Bugs**: Report via issue tracker
4. **Feature Requests**: Submit via feedback form

---

## 🎉 You're Ready!

You now know how to:
- ✅ Create and edit wiki pages
- ✅ Use markdown formatting
- ✅ Organize pages hierarchically
- ✅ Search and navigate
- ✅ Follow best practices

**Start documenting your project today!** 📚
