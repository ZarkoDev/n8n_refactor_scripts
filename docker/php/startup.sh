#!/bin/bash

# Laravel Docker Startup Script
# This script handles database setup and application startup

set -e

echo "🚀 Starting Laravel application setup..."

# Create necessary directories
echo "📁 Creating directories..."
mkdir -p storage/logs bootstrap/cache
touch storage/logs/laravel.log

echo "✅ Database is ready!"

# Run migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Run seeders
echo "🌱 Seeding database..."
php artisan db:seed --force

echo "✅ Database setup complete!"

# Start the development server
echo "🌐 Starting development server..."
exec php -S 0.0.0.0:8000 -t public
