# Plog - Advanced Laravel Logging Package

Plog is a powerful Laravel logging enhancement package that captures extensive metadata, supports tagging, and provides an interactive web interface for log exploration.

## Features

- **Automatic Metadata Capture**: User ID, Session ID, Request ID, file/line, class/method
- **Request Tracking**: Track logs across HTTP requests, queued jobs, and CLI commands
- **Tagging System**: Organize logs with tags for easy filtering
- **Interactive Web Interface**: Filter, search, and explore logs with Livewire + Alpine.js
- **Flexible Storage**: SQLite by default, configurable to any Laravel database
- **Granular Retention**: Configure different retention periods for different log types

## Installation

```bash
composer require laikmosh/plog
```

## Configuration

Publish the configuration file and assets:

```bash
php artisan vendor:publish --tag=plog-config
php artisan vendor:publish --tag=plog-assets
```

Run migrations:

```bash
php artisan migrate
```

### Environment Variables

```env
# Enable/disable Plog
PLOG_ENABLED=true

# Authorized emails (comma-separated)
PLOG_AUTHORIZED_EMAILS=admin@example.com,developer@example.com

# Database connection (optional, defaults to SQLite)
PLOG_DB_CONNECTION=plog

# Default retention period
PLOG_RETENTION_DAYS=7

# Enable automatic cleanup
PLOG_CLEANUP_ENABLED=true
```

## Usage

### Basic Logging

All existing Laravel log calls automatically capture metadata:

```php
Log::info('User logged in', ['user_id' => $user->id]);
Log::error('Payment failed', ['order_id' => $orderId]);
```

### Using Tags

You can add tags to organize and filter your logs. Tags work seamlessly with Laravel's standard Log facade:

```php
use Illuminate\Support\Facades\Log;

// Add tags using the special '_tags' key in the context array
Log::info('Order processed', [
    'order_id' => $orderId,
    '_tags' => ['payment', 'stripe']
]);

Log::error('Connection failed', ['_tags' => ['database', 'error']]);

Log::warning('Slow query', [
    'time' => 2.5,
    '_tags' => ['performance', 'database']
]);

// The _tags key is automatically extracted and stored separately
// It won't appear in your logged context data
```

This approach works with ALL Laravel log methods and doesn't require any changes to existing code. Just add `'_tags' => ['tag1', 'tag2']` to your context array when needed.

### Viewing Logs

Access the web interface at `/logs` (requires authentication and authorization).

The interface allows you to:
- Filter by level, user, request ID, session, environment, endpoint, and tags
- Search through log messages and context
- Click any field to instantly filter by that value
- View detailed log entries with full context
- Group logs by request to trace execution flow

## Advanced Configuration

### Custom Database Connection

In `config/plog.php`:

```php
'database' => [
    'connection' => 'mysql', // Use your app's main database
    'table' => 'plog_entries',
],
```

### Retention Policies

Configure granular retention rules:

```php
'retention' => [
    'default_days' => 7,
    'rules' => [
        ['tags' => ['payment'], 'days' => 30],
        ['tags' => ['authentication'], 'days' => 90],
        ['level' => 'error', 'days' => 14],
    ],
],
```

### Authorization

Control access via email whitelist:

```php
'authorized_emails' => [
    'admin@example.com',
    'developer@example.com',
],
```

Or customize the gate in your `AuthServiceProvider`:

```php
Gate::define('viewPlog', function ($user) {
    return $user->hasRole('admin');
});
```

## Request ID Tracking

Plog automatically generates and tracks request IDs across:
- HTTP requests
- Queued jobs (preserves original request ID)
- CLI commands

Access the current request ID:

```php
use Laikmosh\Plog\Services\RequestIdService;

$requestId = app(RequestIdService::class)->getRequestId();
```

## Captured Metadata

Each log entry captures:

- **Time**: Timestamp with microseconds
- **Level**: debug, info, notice, warning, error, critical, alert, emergency
- **Message**: Log message
- **Context**: Additional data passed to the log
- **User ID**: Currently authenticated user
- **Session ID**: Current session identifier
- **Request ID**: Unique request identifier
- **Environment**: http, cli, queue, testing
- **Endpoint**: Route name or URI, CLI command
- **File & Line**: Source code location
- **Class & Method**: Calling class and method
- **Tags**: Custom tags for organization

## Performance Considerations

- Logs are written synchronously by default
- Consider using a dedicated database for high-volume applications
- Indexes are automatically created for common query patterns
- Use retention policies to manage database size

## License

MIT