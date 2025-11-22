#!/bin/bash

# Clear Sessions - Based on .env configuration

# Load .env file
if [ -f .env ]; then
    export $(cat .env | grep -v '^#' | xargs)
else
    echo "❌ .env file not found"
    exit 1
fi

echo "🔍 Detected SESSION_DRIVER: ${SESSION_DRIVER:-file}"

case "$SESSION_DRIVER" in
    file)
        echo "🗑️  Clearing file-based sessions..."
        rm -rf storage/framework/sessions/*
        echo "✅ File sessions cleared"
        ;;

    database)
        echo "🗑️  Clearing database sessions..."
        php artisan tinker --execute="DB::table('sessions')->truncate(); echo '✅ Database sessions cleared';"
        ;;

    redis)
        echo "🗑️  Clearing Redis sessions..."
        php artisan cache:clear
        echo "✅ Redis sessions cleared via cache:clear"
        ;;

    memcached|dynamodb|array)
        echo "🗑️  Clearing cache-based sessions..."
        php artisan cache:clear
        echo "✅ Cache sessions cleared"
        ;;

    cookie)
        echo "⚠️  Cookie sessions are stored client-side"
        echo "💡 Users need to clear browser cookies manually"
        ;;

    *)
        echo "⚠️  Unknown session driver: $SESSION_DRIVER"
        echo "🗑️  Attempting generic cache clear..."
        php artisan cache:clear
        rm -rf storage/framework/sessions/* 2>/dev/null
        echo "✅ Cache and file sessions cleared"
        ;;
esac

echo ""
echo "🧹 Also clearing auth cache..."
php artisan cache:clear
php artisan auth:clear-resets 2>/dev/null || true

echo ""
echo "✅ Sessions cleared! Users must re-login."
