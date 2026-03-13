# Project Management System

<p align="center">
    <a href="https://laravel.com"><img alt="Laravel v9.x" src="https://img.shields.io/badge/Laravel-v9.x-FF2D20?style=for-the-badge&logo=laravel"></a>
    <a href="https://laravel-livewire.com"><img alt="Livewire v2.x" src="https://img.shields.io/badge/Livewire-v2.x-FB70A9?style=for-the-badge"></a>
    <a href="https://filamentphp.com/"><img alt="Filament v2.x" src="https://img.shields.io/badge/Filament-v2.x-e9b228?style=for-the-badge"></a>
    <a href="https://php.net"><img alt="PHP 8.0" src="https://img.shields.io/badge/PHP-8.0-777BB4?style=for-the-badge&logo=php"></a>
</p>

# Introduction

A comprehensive project management system built for teams who need powerful tools in a compact, efficient interface. Manage projects, tickets, sprints, backlogs, and collaborate with your team - all in one tightly-integrated platform.

**Core Features:**
- Azure DevOps-style backlog management with hierarchical work items
- Kanban board with drag-and-drop
- Wiki with client collaboration and sign-offs
- Sprint planning and tracking
- Time tracking and reporting
- HR module with attendance and leave management
- Multi-language support (60+ languages)
- Compact, purpose-oriented UI design

## Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Composer
- Node.js & NPM
- Optional: Pusher account for real-time features

## Installation

```bash
# Clone the repository
git clone <your-repository-url>
cd project-management

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Run migrations and seed
php artisan migrate --seed

# Build assets
npm run build

# Start the server
php artisan serve
```

## Default Credentials

After seeding, you can login with:
- **Email:** admin@example.com
- **Password:** password

## Key Features

### Project Management
- Multi-project workspace
- Role-based access control
- Team member assignment
- Project templates

### Backlog & Sprint Management
- Hierarchical work items (Epic → Feature → User Story → Task → Subtask)
- Drag-and-drop reordering
- Sprint planning and velocity tracking
- Bulk operations
- Export to CSV/JSON

### Kanban Board
- Customizable columns
- Drag-and-drop ticket management
- Swimlanes by assignee or priority
- Quick filters

### Wiki & Documentation
- Hierarchical page structure
- Markdown editor with live preview
- Client collaboration with comments
- Document sign-off workflow
- Version tracking
- File attachments

### Time Tracking
- Log hours per ticket
- Activity-based tracking
- Export timesheets
- Reporting dashboard

### HR Module
- Employee profiles
- Attendance tracking (check-in/out)
- Leave management
- Performance reviews
- Payroll tracking
- Document management

## UI Design Philosophy

This application features a **compact, purpose-oriented UI** designed to:
- Maximize content visibility with minimal spacing
- Reduce font sizes for information density
- Remove unnecessary page headings and whitespace
- Maintain full responsive support
- Preserve dark/light mode functionality

## Configuration

### Compact UI
The application uses custom compact CSS located at `resources/css/compact-ui.css` with minimal spacing and optimized font sizes.

### Localization
Supports 60+ languages. Change locale in Settings or via `.env`:
```
APP_LOCALE=en
```

## License

MIT License. See [LICENSE.md](LICENSE.md) for details.

## Support

For issues, feature requests, or questions, please open an issue in the repository.


## AI Features (Optional)

### Task Generation
- Automatic project task breakdown
- AI-powered ticket sub-task generation
- Context-aware suggestions
- Duplicate detection

### Configuration
Configure AI provider in `.env`:
```env
AI_PROVIDER=local  # or huggingface, openai, etc.
```

For detailed AI setup, see `LOCAL_AI_SETUP_GUIDE.md`

## Development

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Build assets
npm run build

# Start development server
php artisan serve

# Watch for changes (in separate terminal)
npm run dev
```

## Testing

```bash
php artisan test
```

## Deployment

### Production Build
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Queue Worker
For background jobs:
```bash
php artisan queue:work
```

## Tech Stack

- **Backend:** Laravel 9, Livewire 2
- **Frontend:** Alpine.js, Tailwind CSS
- **Admin Panel:** Filament v2
- **Database:** MySQL 8
- **Real-time:** Pusher (optional)
- **Permissions:** Spatie Laravel Permission