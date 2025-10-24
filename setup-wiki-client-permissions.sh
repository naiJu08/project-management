#!/bin/bash

# Wiki Client Enhancement - Permission Setup Script
# Run this after migration to configure client role permissions

echo "=========================================="
echo "Wiki Client Enhancement Setup"
echo "=========================================="
echo ""

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Please run this script from the project root."
    exit 1
fi

echo "📦 Step 1: Running migrations..."
php artisan migrate --force
if [ $? -eq 0 ]; then
    echo "✅ Migrations completed successfully"
else
    echo "❌ Migration failed"
    exit 1
fi

echo ""
echo "🔑 Step 2: Seeding permissions..."
php artisan db:seed --class=PermissionsSeeder --force
if [ $? -eq 0 ]; then
    echo "✅ Permissions seeded successfully"
else
    echo "❌ Permission seeding failed"
    exit 1
fi

echo ""
echo "👥 Step 3: Setting up Client role permissions..."
php artisan tinker --execute="
\$clientRole = App\Models\Role::firstOrCreate(['name' => 'Client']);
\$clientRole->givePermissionTo([
    'View client wiki',
    'Comment on wiki',
    'Sign off wiki'
]);
echo 'Client role configured with wiki permissions';
"

if [ $? -eq 0 ]; then
    echo "✅ Client role configured"
else
    echo "⚠️  Client role configuration may need manual setup"
fi

echo ""
echo "🧹 Step 4: Clearing caches..."
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
echo "✅ Caches cleared"

echo ""
echo "=========================================="
echo "✅ Setup Complete!"
echo "=========================================="
echo ""
echo "📋 Next Steps:"
echo "1. Assign 'Client' role to client users"
echo "2. Create wiki pages and mark them as 'Visible to Client'"
echo "3. Test the Client Wiki tab in project view"
echo ""
echo "📖 Documentation: WIKI_CLIENT_ENHANCEMENT_DOCUMENTATION.md"
echo ""
