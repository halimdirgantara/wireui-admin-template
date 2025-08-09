# Redis Setup Guide

This guide will help you set up Redis for improved caching, session management, and rate limiting performance.

## Why Redis?

Redis provides significant performance benefits over file-based caching:
- **Faster rate limiting**: In-memory operations vs. file I/O
- **Better session management**: Shared sessions across multiple servers
- **Improved caching**: Much faster than database or file caching
- **Atomic operations**: Better for concurrent operations
- **Scalability**: Easy to scale horizontally

## Installation

### Ubuntu/Debian
```bash
sudo apt update
sudo apt install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

### macOS (using Homebrew)
```bash
brew install redis
brew services start redis
```

### Docker
```bash
docker run -d --name redis -p 6379:6379 redis:alpine
```

## Laravel Configuration

### 1. Install PHP Redis Extension

**Option A: Using pecl**
```bash
pecl install redis
```

**Option B: Using apt (Ubuntu/Debian)**
```bash
sudo apt install php-redis
```

**Option C: Using Homebrew (macOS)**
```bash
brew install php-redis
```

### 2. Update Environment Variables

Add to your `.env` file:
```env
# Cache Configuration
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0

# Redis Cache Connection
REDIS_CACHE_DB=1

# Redis Session Connection
REDIS_SESSION_DB=2
```

### 3. Update Bootstrap Configuration

In `bootstrap/app.php`, uncomment the Redis rate limiting:
```php
->withMiddleware(function (Middleware $middleware): void {
    // Global security headers middleware
    $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
    
    // Register custom middleware aliases
    $middleware->alias([
        'admin.access' => \App\Http\Middleware\AdminAccessMiddleware::class,
    ]);
    
    // API rate limiting
    $middleware->throttleApi();
    
    // Enable Redis-based rate limiting for better performance
    $middleware->throttleWithRedis();
})
```

### 4. Update Session Configuration

In `config/session.php`, ensure Redis is properly configured:
```php
'driver' => env('SESSION_DRIVER', 'redis'),
'connection' => env('SESSION_CONNECTION', 'session'),
```

## Verification

### Test Redis Connection
```bash
# Test Redis CLI
redis-cli ping
# Should return: PONG

# Test Laravel connection
php artisan tinker
>>> Cache::put('test', 'Hello Redis!');
>>> Cache::get('test');
# Should return: "Hello Redis!"
```

### Test Rate Limiting
```bash
# Clear caches to use Redis
php artisan cache:clear
php artisan config:clear

# Test admin routes with Redis rate limiting
curl -I http://your-app.local/admin/dashboard
```

## Performance Benefits

With Redis enabled, you'll experience:

### Rate Limiting Performance
- **File-based**: ~50-100 requests/second
- **Redis-based**: ~1000+ requests/second

### Session Management
- **Database sessions**: Query on every request
- **Redis sessions**: In-memory operations, ~10x faster

### Caching Performance
- **File cache**: Disk I/O limitations
- **Redis cache**: Memory speed, ~100x faster for small objects

## Production Configuration

### Redis Security
```bash
# Edit Redis config
sudo nano /etc/redis/redis.conf

# Add authentication
requirepass your_strong_password_here

# Bind to specific interfaces only
bind 127.0.0.1 ::1

# Disable dangerous commands
rename-command FLUSHDB ""
rename-command FLUSHALL ""
rename-command KEYS ""
```

Update `.env`:
```env
REDIS_PASSWORD=your_strong_password_here
```

### Redis Persistence
```bash
# In /etc/redis/redis.conf
save 900 1      # Save after 900 sec if at least 1 key changed
save 300 10     # Save after 300 sec if at least 10 keys changed
save 60 10000   # Save after 60 sec if at least 10000 keys changed
```

### Memory Optimization
```bash
# Set memory limit and eviction policy
maxmemory 256mb
maxmemory-policy allkeys-lru
```

## Monitoring

### Redis CLI Commands
```bash
# Monitor Redis activity
redis-cli monitor

# Check memory usage
redis-cli info memory

# Check connected clients
redis-cli info clients

# View all keys (don't use in production with many keys)
redis-cli --scan
```

### Laravel Commands
```bash
# Clear Redis cache
php artisan cache:clear

# View cache statistics
php artisan cache:table

# Monitor queue jobs
php artisan queue:work redis --verbose
```

## Troubleshooting

### Common Issues

**Connection refused**
```bash
# Check if Redis is running
sudo systemctl status redis-server

# Check Redis logs
sudo tail -f /var/log/redis/redis-server.log
```

**Authentication failed**
```bash
# Check password in .env matches Redis config
redis-cli auth your_password
```

**Memory issues**
```bash
# Check Redis memory usage
redis-cli info memory

# Clear all data if needed (use with caution!)
redis-cli flushall
```

## Integration with Admin Panel

Once Redis is configured, the admin panel will automatically benefit from:

1. **Faster rate limiting**: Especially important for admin routes
2. **Better session handling**: Shared sessions across load balancers
3. **Improved caching**: Dashboard statistics and permissions
4. **Real-time features**: Better support for future real-time features

## Backup Strategy

### Automated Backups
```bash
# Create backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
redis-cli --rdb /backup/redis/dump_$DATE.rdb

# Schedule with cron
0 2 * * * /path/to/redis-backup.sh
```

### Restore from Backup
```bash
sudo systemctl stop redis-server
sudo cp /backup/redis/dump_YYYYMMDD_HHMMSS.rdb /var/lib/redis/dump.rdb
sudo chown redis:redis /var/lib/redis/dump.rdb
sudo systemctl start redis-server
```

---

## Next Steps

After setting up Redis:
1. Run `php artisan app:optimize-production --force` to cache configurations
2. Update your deployment scripts to include Redis
3. Monitor Redis performance and adjust configurations as needed
4. Consider Redis Cluster for high-availability setups

For more advanced Redis configurations, refer to the [Redis documentation](https://redis.io/documentation).
