# Database Setup Documentation

This document describes the database migrations, seeders, and sample data that have been created to support the wireui-admin-template application.

## Overview

The database setup includes:
- **Full-text and composite indexes** for improved search performance
- **Activities table** for tracking user activities 
- **Laravel notifications table** for managing user notifications
- **Comprehensive sample data** for UI development and testing

## Database Tables

### 1. Users Table (Enhanced)
- Added full-text index on `name` and `email` columns for search functionality
- Added composite index on `is_active` and `created_at` for filtering
- Includes avatar and status fields

### 2. Activities Table (New)
```sql
- id (primary key)
- user_id (foreign key to users table)
- type (string, indexed) - Activity type like 'login', 'logout', 'profile_update', etc.
- description (text with full-text index) - Human-readable description
- meta (JSON) - Additional metadata about the activity
- created_at (timestamp, indexed)
- updated_at (timestamp)
```

**Indexes:**
- `activities_user_created_idx` - Composite index on user_id, created_at
- `activities_type_created_idx` - Composite index on type, created_at  
- `activities_description_fulltext` - Full-text index on description

### 3. Notifications Table (Laravel Standard)
- Uses Laravel's built-in notification structure with UUID primary keys
- Enhanced with performance indexes for common queries
- Supports polymorphic relationships

### 4. Activity Log Table (Spatie Package)
- Enhanced with additional indexes for better performance
- Full-text index on description field

## Migrations

### Created Migrations:
1. `add_full_text_indexes_for_searchable_fields` - Adds full-text and composite indexes
2. `create_activities_table` - Creates the activities tracking table
3. `create_notifications_table` - Laravel notifications table (standard)

## Models

### Activity Model
- **Relationships:** Belongs to User
- **Fillable:** user_id, type, description, meta
- **Casts:** meta (array), timestamps (datetime)
- **Scopes:** ofType(), recent()

### User Model (Enhanced)
- **New Relationship:** hasMany(Activity::class)
- **Existing:** Spatie Permission roles, Activity Log, Notifications

## Sample Data

### Activity Types
The system includes sample data for the following activity types:
- `login` - User logged in
- `logout` - User logged out  
- `profile_update` - User updated their profile
- `password_change` - User changed their password
- `email_change` - User changed their email address
- `avatar_update` - User updated their avatar
- `account_deactivated` - User account was deactivated
- `account_activated` - User account was activated
- `permission_granted` - User was granted new permissions
- `permission_revoked` - User permissions were revoked

### Notification Types
Sample notifications include:
- Welcome messages
- Profile completion reminders
- Security alerts
- System update notifications
- Password expiry warnings
- Account verification confirmations

### Sample Users
The seeder creates several test users:
- `admin@example.com` - Admin user with admin role
- `editor@example.com` - Editor user with editor role  
- `user@example.com` - Regular user
- `inactive@example.com` - Inactive user account
- 15+ additional random users

## Usage Examples

### Searching Users with Full-text
```php
// Search users by name or email
$users = User::whereRaw("MATCH(name, email) AGAINST(? IN BOOLEAN MODE)", [$searchTerm])->get();
```

### Querying Activities
```php
// Get recent activities for a user
$activities = Activity::where('user_id', $userId)
    ->recent(30) // Last 30 days
    ->with('user')
    ->latest()
    ->get();

// Get activities by type
$loginActivities = Activity::ofType('login')
    ->orderBy('created_at', 'desc')
    ->get();
```

### Working with Notifications
```php
// Get unread notifications for a user
$notifications = $user->unreadNotifications;

// Mark notification as read
$user->unreadNotifications->markAsRead();
```

### Activity Metadata Examples
```php
// Login activity metadata
[
    'ip_address' => '192.168.1.1',
    'user_agent' => 'Mozilla/5.0...',
    'session_id' => 'uuid-string',
    'location' => 'New York, United States'
]

// Profile update metadata  
[
    'ip_address' => '192.168.1.1',
    'user_agent' => 'Mozilla/5.0...',
    'fields_changed' => ['name', 'email']
]
```

## Performance Considerations

### Indexes Created
- Full-text indexes enable fast text search across large datasets
- Composite indexes optimize common query patterns
- Foreign key indexes ensure referential integrity performance

### Query Optimization
- Use scoped queries where possible (e.g., `recent()`, `ofType()`)
- Leverage full-text search for complex text searches
- Use eager loading (`with()`) to prevent N+1 query problems

## Running the Setup

To set up the database with sample data:

```bash
# Run migrations
php artisan migrate

# Run seeders (individually to avoid conflicts)
php artisan db:seed --class=PermissionSeeder  # May already exist
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=ActivitySeeder
php artisan db:seed --class=NotificationSeeder
```

## Data Verification

After running the setup, you should have:
- ~70 users (including test users and random generated ones)
- ~262 activities across all users
- ~402 notifications for all users  
- Activity log entries from Spatie Activity Log package

The full-text search should work on both users and activities tables, and all relationships should be properly established.

## Future Enhancements

Consider adding:
- Activity cleanup job for old entries
- Real-time activity broadcasting
- Activity analytics and reporting
- Custom notification channels
- Activity export functionality
