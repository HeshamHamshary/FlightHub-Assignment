<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Laravel 12 Comprehensive Diagnostic Test</h2>";
echo "<hr>";

try {
    echo "<h3>Step 1: Basic PHP Environment</h3>";
    echo "PHP Version: " . phpversion() . "<br>";
    echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "<br>";
    echo "Current Time: " . date('Y-m-d H:i:s') . "<br>";
    echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
    echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";
    echo "Display Errors: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "<br>";
    echo "Error Reporting: " . ini_get('error_reporting') . "<br>";
    echo "Loaded Extensions: " . implode(', ', get_loaded_extensions()) . "<br>";
    echo "✓ Basic PHP environment OK<br><br>";
    
    echo "<h3>Step 2: Composer & Autoloader</h3>";
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require __DIR__ . '/../vendor/autoload.php';
        echo "✓ Composer autoloader loaded<br>";
        
        // Check Composer packages
        if (class_exists('Composer\Autoload\ClassLoader')) {
            echo "✓ Composer ClassLoader available<br>";
        }
        
        // Check Laravel framework
        if (class_exists('Illuminate\Foundation\Application')) {
            echo "✓ Laravel Framework classes available<br>";
        }
    } else {
        throw new Exception("Composer autoloader not found!");
    }
    
    echo "<h3>Step 3: Laravel Bootstrap</h3>";
    if (file_exists(__DIR__ . '/../bootstrap/app.php')) {
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        echo "✓ Laravel app loaded<br>";
        echo "App instance: " . get_class($app) . "<br>";
        echo "App version: " . $app->version() . "<br>";
        echo "Base path: " . $app->basePath() . "<br>";
        // Remove these calls that require full bootstrap
        // echo "Environment: " . $app->environment() . "<br>";
        // echo "Is production: " . ($app->environment('production') ? 'YES' : 'NO') . "<br>";
    } else {
        throw new Exception("Laravel bootstrap file not found!");
    }
    
    echo "<h3>Step 4: Environment Variables</h3>";
    $envVars = [
        'APP_ENV', 'APP_DEBUG', 'APP_KEY', 'APP_URL', 'APP_NAME',
        'DB_CONNECTION', 'DATABASE_URL', 'DB_URL', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME',
        'CACHE_DRIVER', 'SESSION_DRIVER', 'QUEUE_CONNECTION', 'LOG_CHANNEL',
        'BROADCAST_DRIVER', 'MAIL_MAILER', 'REDIS_HOST', 'REDIS_PASSWORD', 'REDIS_PORT'
    ];
    
    foreach ($envVars as $var) {
        $value = getenv($var) ?: 'not set';
        $status = $value !== 'not set' ? '✅' : '❌';
        echo "$status $var: $value<br>";
    }
    
    echo "<h3>Step 5: Configuration System</h3>";
    try {
        $config = $app->make('Illuminate\Contracts\Config\Repository');
        echo "✓ Config repository resolved<br>";
        
        $configValues = [
            'app.name', 'app.env', 'app.debug', 'app.url', 'app.timezone', 'app.locale',
            'database.default', 'database.connections.pgsql.driver', 'database.connections.pgsql.host',
            'cache.default', 'session.driver', 'queue.default', 'mail.default',
            'logging.default', 'broadcasting.default'
        ];
        
        foreach ($configValues as $key) {
            try {
                $value = $config->get($key, 'not found');
                echo "✓ $key: $value<br>";
            } catch (Exception $e) {
                echo "❌ $key: " . $e->getMessage() . "<br>";
            }
        }
    } catch (Exception $e) {
        echo "❌ Config error: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>Step 6: Service Container & Service Providers</h3>";
    try {
        $container = $app->make('Illuminate\Container\Container');
        echo "✓ Service container resolved<br>";
        
        // Test core services
        $services = ['app', 'config', 'db', 'cache', 'session', 'queue', 'mail', 'log', 'view', 'router'];
        foreach ($services as $service) {
            try {
                $instance = $app->make($service);
                echo "✅ $service service: " . get_class($instance) . "<br>";
            } catch (Exception $e) {
                echo "❌ $service service: " . $e->getMessage() . "<br>";
            }
        }
        
        // Test service provider registration
        echo "<br><strong>Service Provider Tests:</strong><br>";
        $providers = [
            'Illuminate\Config\ConfigServiceProvider',
            'Illuminate\Database\DatabaseServiceProvider',
            'Illuminate\Cache\CacheServiceProvider',
            'Illuminate\Session\SessionServiceProvider',
            'Illuminate\View\ViewServiceProvider',
            'Illuminate\Routing\RoutingServiceProvider',
            'Illuminate\Cookie\CookieServiceProvider',
            'Illuminate\Encryption\EncryptionServiceProvider',
            'Illuminate\Filesystem\FilesystemServiceProvider',
            'Illuminate\Foundation\Providers\FoundationServiceProvider',
            'Illuminate\Hashing\HashServiceProvider',
            'Illuminate\Mail\MailServiceProvider',
            'Illuminate\Pipeline\PipelineServiceProvider',
            'Illuminate\Queue\QueueServiceProvider',
            'Illuminate\Redis\RedisServiceProvider',
            'Illuminate\Auth\AuthServiceProvider',
            'Illuminate\Broadcasting\BroadcastServiceProvider',
            'Illuminate\Bus\BusServiceProvider',
            'Illuminate\Console\ConsoleServiceProvider',
            'Illuminate\Database\MigrationServiceProvider',
            'Illuminate\Notifications\NotificationServiceProvider',
            'Illuminate\Pagination\PaginationServiceProvider',
            'Illuminate\Translation\TranslationServiceProvider',
            'Illuminate\Validation\ValidationServiceProvider'
        ];
        
        foreach ($providers as $provider) {
            try {
                if (class_exists($provider)) {
                    echo "✅ $provider: Available<br>";
                } else {
                    echo "❌ $provider: Not found<br>";
                }
            } catch (Exception $e) {
                echo "❌ $provider: " . $e->getMessage() . "<br>";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Container error: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>Step 7: Database Connection Test</h3>";
    try {
        $db = $app->make('db');
        echo "✓ Database manager resolved<br>";
        
        $connection = $db->connection();
        echo "✓ Database connection established<br>";
        echo "Database: " . $connection->getDatabaseName() . "<br>";
        echo "Driver: " . $connection->getDriverName() . "<br>";
        echo "Host: " . $connection->getConfig('host') . "<br>";
        echo "Port: " . $connection->getConfig('port') . "<br>";
        echo "Username: " . $connection->getConfig('username') . "<br>";
        
        // Test a simple query
        $result = $connection->select('SELECT version() as version');
        echo "✓ Database query test: " . $result[0]->version . "<br>";
        
        // Test if tables exist
        $tables = $connection->select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
        echo "✓ Tables found: " . count($tables) . "<br>";
        if (count($tables) > 0) {
            echo "First few tables: " . implode(', ', array_slice(array_column($tables, 'table_name'), 0, 5)) . "<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Database error: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>Step 8: Routing System</h3>";
    try {
        $router = $app->make('router');
        echo "✓ Router service resolved<br>";
        
        // Test route registration
        $routes = $router->getRoutes();
        echo "✓ Routes loaded: " . count($routes) . "<br>";
        
        // Test specific routes
        $testRoutes = ['/', '/test-simple', '/test-debug', '/test-no-db'];
        foreach ($testRoutes as $route) {
            try {
                $routeInfo = $router->getRoutes()->match(request()->create($route));
                if ($routeInfo) {
                    echo "✅ Route $route: Registered<br>";
                } else {
                    echo "❌ Route $route: Not found<br>";
                }
            } catch (Exception $e) {
                echo "❌ Route $route: " . $e->getMessage() . "<br>";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Routing error: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>Step 9: Middleware System</h3>";
    try {
        $middleware = $app->make('Illuminate\Contracts\Http\Kernel');
        echo "✓ HTTP Kernel resolved<br>";
        
        // Test middleware groups
        $middlewareGroups = ['web', 'api'];
        foreach ($middlewareGroups as $group) {
            try {
                $middlewares = $middleware->getMiddlewareGroups()[$group] ?? [];
                echo "✅ Middleware group '$group': " . count($middlewares) . " middlewares<br>";
            } catch (Exception $e) {
                echo "❌ Middleware group '$group': " . $e->getMessage() . "<br>";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Middleware error: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>Step 10: View System</h3>";
    try {
        $view = $app->make('view');
        echo "✓ View service resolved<br>";
        
        // Test view compilation
        try {
            $compiled = $view->exists('welcome');
            echo "✓ View 'welcome' exists: " . ($compiled ? 'YES' : 'NO') . "<br>";
        } catch (Exception $e) {
            echo "❌ View test error: " . $e->getMessage() . "<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ View error: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>Step 11: Cache System</h3>";
    try {
        $cache = $app->make('cache');
        echo "✓ Cache service resolved<br>";
        
        // Test cache operations
        try {
            $cache->put('test_key', 'test_value', 60);
            $value = $cache->get('test_key');
            if ($value === 'test_value') {
                echo "✓ Cache read/write test: PASSED<br>";
            } else {
                echo "❌ Cache read/write test: FAILED<br>";
            }
            $cache->forget('test_key');
        } catch (Exception $e) {
            echo "❌ Cache operations error: " . $e->getMessage() . "<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Cache error: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>Step 12: Session System</h3>";
    try {
        $session = $app->make('session');
        echo "✓ Session service resolved<br>";
        
        // Test session operations
        try {
            $session->put('test_session', 'test_value');
            $value = $session->get('test_session');
            if ($value === 'test_value') {
                echo "✓ Session read/write test: PASSED<br>";
            } else {
                echo "❌ Session read/write test: FAILED<br>";
            }
            $session->forget('test_session');
        } catch (Exception $e) {
            echo "❌ Session operations error: " . $e->getMessage() . "<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Session error: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>Step 13: File System Check</h3>";
    $paths = [
        'bootstrap/cache' => 'writable',
        'storage/logs' => 'writable',
        'storage/framework' => 'writable',
        'storage/framework/cache' => 'writable',
        'storage/framework/sessions' => 'writable',
        'storage/framework/views' => 'writable',
        'config' => 'readable',
        'routes' => 'readable',
        'app' => 'readable',
        'database' => 'readable',
        'resources' => 'readable'
    ];
    
    foreach ($paths as $path => $requirement) {
        $fullPath = __DIR__ . '/../' . $path;
        if (file_exists($fullPath)) {
            if ($requirement === 'writable' && is_writable($fullPath)) {
                echo "✅ $path: exists and writable<br>";
            } elseif ($requirement === 'readable' && is_readable($fullPath)) {
                echo "✅ $path: exists and readable<br>";
            } else {
                echo "⚠️ $path: exists but not $requirement<br>";
            }
        } else {
            echo "❌ $path: not found<br>";
        }
    }
    
    echo "<h3>Step 14: Laravel Artisan Commands</h3>";
    try {
        $artisan = $app->make('Illuminate\Contracts\Console\Kernel');
        echo "✓ Console Kernel resolved<br>";
        
        // Test if we can list commands
        try {
            $commands = $artisan->all();
            echo "✓ Artisan commands available: " . count($commands) . "<br>";
        } catch (Exception $e) {
            echo "❌ Artisan commands error: " . $e->getMessage() . "<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Console Kernel error: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>Step 15: Package Discovery</h3>";
    try {
        $manifest = $app->bootstrapPath('cache/packages.php');
        if (file_exists($manifest)) {
            echo "✓ Package manifest exists<br>";
            $packages = include $manifest;
            echo "✓ Packages discovered: " . count($packages) . "<br>";
        } else {
            echo "⚠️ Package manifest not found (run: php artisan package:discover)<br>";
        }
    } catch (Exception $e) {
        echo "❌ Package discovery error: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>🎉 Comprehensive Diagnostic Complete</h3>";
    echo "This test has checked all major Laravel 12 components.<br>";
    echo "Check the results above to identify any issues.<br>";
    
} catch (Exception $e) {
    echo "<h3>❌ Critical Error</h3>";
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?>
