#!/bin/bash

# Cleanup script for development environment
# This removes cache files that can be safely deleted

echo "🧹 Starting cleanup..."

# 1. Clear Laravel/Sage compiled views (safe - will regenerate)
echo "📁 Clearing compiled Blade views..."
rm -rf storage/framework/views/*.php
echo "✅ Cleared compiled views"

# 2. Clear Laravel cache (safe - will regenerate)
echo "📁 Clearing framework cache..."
rm -rf storage/framework/cache/*
echo "✅ Cleared framework cache"

# 3. Clear compiled assets (safe - will regenerate on next build)
echo "📁 Clearing compiled assets..."
rm -rf public/build/*
rm -rf public/hot
echo "✅ Cleared compiled assets"

# 4. Clear log files (optional - but logs can get large)
echo "📁 Clearing log files..."
find storage/logs -name "*.log" -type f -delete 2>/dev/null
echo "✅ Cleared log files"

# 5. Show space saved
echo ""
echo "✨ Cleanup complete!"
echo ""
echo "📊 Current directory sizes:"
du -sh storage node_modules vendor public/build 2>/dev/null

