<?php
/**
 * Database Connection Test
 * Run this to verify your database setup
 */

// Suppress errors for clean output
error_reporting(0);
ini_set('display_errors', 0);

echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n";
echo "<title>Database Connection Test</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }\n";
echo ".success { color: #4FCD1A; font-weight: bold; }\n";
echo ".error { color: #dc3545; font-weight: bold; }\n";
echo ".info { color: #0023F5; }\n";
echo "table { width: 100%; border-collapse: collapse; margin: 20px 0; }\n";
echo "th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }\n";
echo "th { background: #f8f9fa; }\n";
echo "</style>\n";
echo "</head>\n<body>\n";

echo "<h1>HR Leave Assistant - Database Test</h1>\n";

// Test 1: Check if config files exist
echo "<h2>1. Configuration Files</h2>\n";
$configFiles = ['config/app.php', 'config/database.php', 'includes/auth.php'];
foreach ($configFiles as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✓ $file exists</p>\n";
    } else {
        echo "<p class='error'>✗ $file missing</p>\n";
    }
}

// Test 2: Load configuration
echo "<h2>2. Load Configuration</h2>\n";
try {
    require_once 'config/app.php';
    echo "<p class='success'>✓ Configuration loaded successfully</p>\n";
    echo "<p class='info'>App Name: " . APP_NAME . "</p>\n";
    echo "<p class='info'>App URL: " . APP_URL . "</p>\n";
    echo "<p class='info'>Database: " . config('database.name') . "</p>\n";
} catch (Exception $e) {
    echo "<p class='error'>✗ Configuration error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "</body></html>";
    exit;
}

// Test 3: Database connection
echo "<h2>3. Database Connection</h2>\n";
try {
    $db = getDB();
    echo "<p class='success'>✓ Database connected successfully</p>\n";
} catch (Exception $e) {
    echo "<p class='error'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p class='info'>Please check your database credentials in config/app.php</p>\n";
    echo "</body></html>";
    exit;
}

// Test 4: Check tables
echo "<h2>4. Database Tables</h2>\n";
try {
    $tables = $db->fetchAll("SHOW TABLES");
    if (count($tables) > 0) {
        echo "<p class='success'>✓ Found " . count($tables) . " tables</p>\n";
        echo "<table>\n<tr><th>Table Name</th><th>Row Count</th></tr>\n";
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            $count = $db->fetch("SELECT COUNT(*) as count FROM `$tableName`");
            echo "<tr><td>$tableName</td><td>" . $count['count'] . "</td></tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "<p class='error'>✗ No tables found. Database needs initialization.</p>\n";
        echo "<p class='info'>Visit index.php to initialize the database.</p>\n";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error checking tables: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

// Test 5: Check admin user
echo "<h2>5. Admin User</h2>\n";
try {
    $admin = $db->fetch("SELECT id, email, access_level, created_at FROM users WHERE email = 'talk2char@gmail.com'");
    if ($admin) {
        echo "<p class='success'>✓ Admin user exists</p>\n";
        echo "<table>\n";
        echo "<tr><th>Field</th><th>Value</th></tr>\n";
        echo "<tr><td>ID</td><td>" . $admin['id'] . "</td></tr>\n";
        echo "<tr><td>Email</td><td>" . $admin['email'] . "</td></tr>\n";
        echo "<tr><td>Access Level</td><td>" . $admin['access_level'] . "</td></tr>\n";
        echo "<tr><td>Created</td><td>" . $admin['created_at'] . "</td></tr>\n";
        echo "</table>\n";
        echo "<p class='info'>✓ Admin user exists. Use your configured password to login.</p>\n";
    } else {
        echo "<p class='error'>✗ Admin user not found</p>\n";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error checking admin: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

// Test 6: Check API configuration
echo "<h2>6. API Configuration</h2>\n";
try {
    $apiConfig = $db->fetch("SELECT * FROM api_config WHERE is_active = 1");
    if ($apiConfig) {
        $hasKey = !empty($apiConfig['openai_key']);
        if ($hasKey) {
            echo "<p class='success'>✓ OpenAI API key configured</p>\n";
            echo "<p class='info'>Key: " . substr($apiConfig['openai_key'], 0, 10) . "..." . substr($apiConfig['openai_key'], -4) . "</p>\n";
        } else {
            echo "<p class='error'>✗ OpenAI API key not configured</p>\n";
            echo "<p class='info'>Configure in Admin Dashboard → API Settings</p>\n";
        }
        echo "<p class='info'>Total Requests: " . $apiConfig['total_requests'] . "</p>\n";
        echo "<p class='info'>OpenAI Requests: " . $apiConfig['openai_requests'] . "</p>\n";
    } else {
        echo "<p class='error'>✗ API configuration not found</p>\n";
        echo "<p class='info'>Creating default API config entry...</p>\n";
        
        // Get admin ID
        $admin = $db->fetch("SELECT id FROM users WHERE email = 'talk2char@gmail.com'");
        $adminId = $admin['id'] ?? 1;
        
        // Create default entry
        $sql = "INSERT INTO api_config (id, openai_key, total_requests, openai_requests, is_active, updated_by) 
                VALUES (1, '', 0, 0, 1, ?)";
        $db->query($sql, [$adminId]);
        
        echo "<p class='success'>✓ Default API config created</p>\n";
        echo "<p class='info'>Configure your OpenAI API key in Admin Dashboard → API Settings</p>\n";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error checking API config: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p class='info'>You may need to run init-database.php to initialize the database</p>\n";
}

// Test 7: File permissions
echo "<h2>7. File Permissions</h2>\n";
$logsDir = 'logs';
if (is_dir($logsDir)) {
    if (is_writable($logsDir)) {
        echo "<p class='success'>✓ Logs directory is writable</p>\n";
    } else {
        echo "<p class='error'>✗ Logs directory is not writable</p>\n";
    }
} else {
    echo "<p class='info'>Logs directory will be created automatically</p>\n";
}

echo "<h2>Summary</h2>\n";
echo "<p class='success'>✓ Database connection test complete!</p>\n";
echo "<p class='info'>If all tests passed, your application is ready to use.</p>\n";
echo "<p class='info'><a href='index.php'>Go to Homepage</a> | <a href='login.php'>Login</a> | <a href='admin/index.php'>Admin Dashboard</a></p>\n";
echo "<p class='info'><a href='init-database.php'>Run Database Initialization</a> (if you need to reset or initialize)</p>\n";

echo "</body>\n</html>";
?>
