# Project Management System - Setup Guide

This guide will help you set up and deploy your own instance of this project management system.

## Prerequisites

Before you begin, ensure you have:

- **PHP 8.0+** with required extensions (iconv, mbstring, pdo_mysql, etc.)
- **MySQL 8.0+** or MariaDB 10.3+
- **Composer** (latest version)
- **Node.js 16+** and **NPM/Yarn**
- **Web server** (Apache/Nginx) with mod_rewrite enabled
- **Git** for version control
- Optional: **Pusher** account for real-time features

## Quick Start (Development)

```bash
# 1. Clone your repository
git clone <your-repository-url>
cd project-management

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Create environment file
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Configure database in .env
nano .env
```

Edit these database settings:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

```bash
# 7. Run migrations and seed database
php artisan migrate --seed

# 8. Build frontend assets
npm run build

# 9. Start development server
php artisan serve
```

Visit `http://localhost:8000` and login with:
- **Email:** admin@example.com
- **Password:** password

## Production Deployment

### Step 1: Server Setup

1. **Install dependencies** on your server
2. **Create database** for the application
3. **Configure web server** to point to `/public` directory
4. **Set proper permissions:**
   ```bash
   chown -R www-data:www-data storage bootstrap/cache
   chmod -R 775 storage bootstrap/cache
   ```

### Step 2: Deploy Application

```bash
# Clone repository
git clone <your-repository-url> /var/www/project-management
cd /var/www/project-management

# Install dependencies (production)
composer install --optimize-autoloader --no-dev
npm install --production

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure .env for production
nano .env
```

**Important production settings:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=production_db
DB_USERNAME=production_user
DB_PASSWORD=strong_password_here

# Cache & Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Optional: Pusher for real-time features
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=

# Optional: Mail configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

```bash
# Run migrations
php artisan migrate --force --seed

# Build and optimize
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Set permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Step 3: Web Server Configuration

#### Apache (.htaccess already included)

Virtual host configuration:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/project-management/public

    <Directory /var/www/project-management/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/project-management-error.log
    CustomLog ${APACHE_LOG_DIR}/project-management-access.log combined
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/project-management/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Step 4: Background Queue Worker

For processing background jobs, set up a supervisor configuration:

**File:** `/etc/supervisor/conf.d/project-management-worker.conf`
```ini
[program:project-management-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/project-management/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopascompromise=failed
stopwaitsecs=3600
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/project-management/storage/logs/worker.log
stopasgroup=true
killasgroup=true
```

```bash
# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start project-management-worker:*
```

### Step 5: Scheduled Tasks (Cron)

Add to crontab (`sudo crontab -e`):
```cron
* * * * * cd /var/www/project-management && php artisan schedule:run >> /dev/null 2>&1
```

### Step 6: SSL Certificate (Recommended)

Using Let's Encrypt (Certbot):
```bash
sudo apt install certbot python3-certbot-apache  # For Apache
# OR
sudo apt install certbot python3-certbot-nginx   # For Nginx

sudo certbot --apache -d yourdomain.com          # For Apache
# OR
sudo certbot --nginx -d yourdomain.com           # For Nginx
```

## Configuration

### Compact UI Theme

The application includes a compact, tightly-packed UI design. The configuration is in:
- `resources/css/compact-ui.css` - Main compact styles
- `tailwind.config.js` - Compact font sizes and spacing

To customize spacing or font sizes, edit these files and rebuild:
```bash
npm run build
```

### Localization

Change language in `.env`:
```env
APP_LOCALE=en  # Available: en, fr, es, de, ar, etc. (60+ languages)
```

Or change via admin panel: **Settings → General Settings**

### User Roles & Permissions

Default roles created after seeding:
- **Super Admin** - Full system access
- **Admin** - Administrative access
- **Project Manager** - Project management
- **Developer** - Development tasks
- **Client** - Limited client access

Customize in **Settings → Roles & Permissions**

### Optional Features

#### AI Task Generation
Configure in `.env`:
```env
AI_PROVIDER=local  # or: huggingface, openai, cohere
AI_LOCAL_SCRIPT_PATH=scripts/ai_task_gen.py
AI_LOCAL_PYTHON_BIN=python3
```

See `docs-archive/LOCAL_AI_SETUP_GUIDE.md` for detailed AI setup.

#### Social Login
Configure in `.env`:
```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=

GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_REDIRECT_URI=
```

Enable in admin panel: **Settings → General Settings**

## Updating the Application

```bash
# Pull latest changes
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev
npm install --production

# Run migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

npm run build

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Restart queue workers
sudo supervisorctl restart project-management-worker:*
```

## Troubleshooting

### Permission Issues
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Clear All Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
```

### Database Connection Errors
- Verify database credentials in `.env`
- Ensure MySQL service is running
- Check firewall rules

### 500 Error
- Check `storage/logs/laravel.log`
- Ensure `APP_DEBUG=true` temporarily to see errors
- Verify file permissions

### Queue Not Processing
- Check supervisor status: `sudo supervisorctl status`
- View worker logs: `tail -f storage/logs/worker.log`
- Restart workers: `sudo supervisorctl restart project-management-worker:*`

### Assets Not Loading
- Run `npm run build`
- Clear browser cache
- Check web server configuration

## Backup & Maintenance

### Database Backup
```bash
# Backup
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Restore
mysql -u username -p database_name < backup_20231024.sql
```

### File Backup
```bash
# Backup uploads and important files
tar -czf backup_files_$(date +%Y%m%d).tar.gz storage/app/public
```

### Automated Backup Script
Create a cron job for daily backups:
```bash
#!/bin/bash
BACKUP_DIR="/backups/project-management"
DATE=$(date +%Y%m%d_%H%M%S)

# Database backup
mysqldump -u user -ppassword dbname > "$BACKUP_DIR/db_$DATE.sql"

# Files backup
tar -czf "$BACKUP_DIR/files_$DATE.tar.gz" /var/www/project-management/storage/app/public

# Keep only last 30 days
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

## Security Recommendations

1. **Change default credentials** immediately after first login
2. **Use strong passwords** for database and admin accounts
3. **Enable HTTPS** with valid SSL certificate
4. **Keep software updated** (PHP, MySQL, Composer packages)
5. **Regular backups** of database and uploaded files
6. **Firewall configuration** to restrict access
7. **Disable debug mode** in production (`APP_DEBUG=false`)
8. **Use environment variables** for sensitive data
9. **Monitor logs** regularly for suspicious activity
10. **Implement rate limiting** for API endpoints

## Support & Resources

- **Documentation Archive:** `docs-archive/` folder contains detailed feature docs
- **Issue Tracking:** Open issues in your repository
- **Logs:** Check `storage/logs/laravel.log` for errors

## License

MIT License - See [LICENSE.md](LICENSE.md) for details.
