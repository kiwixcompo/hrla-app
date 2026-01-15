<?php
/**
 * Local Database Setup Script
 * Run this once on your local machine to set up the database
 */

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Local Database Setup</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }\n";
echo ".card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 1rem; }\n";
echo ".success { color: #4FCD1A; font-weight: bold; }\n";
echo ".error { color: #dc3545; font-weight: bold; }\n";
echo ".info { color: #0023F5; }\n";
echo ".code { background: #f8f9fa; padding: 1rem; border-radius: 4px; font-family: monospace; margin: 1rem 0; }\n";
echo "h1 { color: #333; }\n";
echo "h2 { color: #0023F5; margin-top: 0; }\n";
echo "</style>\n";
echo "</head><body>\n";

echo "<h1>🔧 Local Database Setup</h1>\n";

// Step 1: Check if we're on localhost
$isLocal = ($_SERVER['HTTP_HOST'] ?? '') === 'localhost' || 
           strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false;

if (!$isLocal) {
    echo "<div class='card'>\n";
    echo "<p class='error'>⚠️ This script should only be run on localhost!</p>\n";
    echo "<p>Current host: " . htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'unknown') . "</p>\n";
    echo "</div>\n";
    echo "</body></html>";
    exit;
}

echo "<div class='card'>\n";
echo "<h2>Step 1: Create Database in phpMyAdmin</h2>\n";
echo "<p>Before running this script, you need to create the database:</p>\n";
echo "<ol>\n";
echo "<li>Open phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>\n";
echo "<li>Click 'New' in the left sidebar</li>\n";
echo "<li>Database name: <code>hrla_database</code></li>\n";
echo "<li>Collation: <code>utf8mb4_unicode_ci</code></li>\n";
echo "<li>Click 'Create'</li>\n";
echo "</ol>\n";
echo "<p class='info'>✓ Once created, refresh this page to continue</p>\n";
echo "</div>\n";

// Step 2: Try to connect
echo "<div class='card'>\n";
echo "<h2>Step 2: Testing Database Connection</h2>\n";

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=hrla_database;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "<p class='success'>✓ Successfully connected to database!</p>\n";
    
    // Step 3: Initialize database
    echo "</div>\n";
    echo "<div class='card'>\n";
    echo "<h2>Step 3: Initializing Database</h2>\n";
    
    require_once 'config/app.php';
    require_once 'config/database.php';
    
    $db = getDB();
    
    // Create tables
    echo "<p class='info'>Creating tables...</p>\n";
    $db->createTables();
    echo "<p class='success'>✓ Tables created successfully!</p>\n";
    
    // Create site settings table
    echo "<p class='info'>Creating site settings table...</p>\n";
    require_once 'config/site-settings.php';
    createSiteSettingsTable($db);
    initializeSiteSettings($db);
    echo "<p class='success'>✓ Site settings initialized!</p>\n";
    
    echo "</div>\n";
    
    // Step 4: Success
    echo "<div class='card' style='background: #e8f5e9; border-left: 4px solid #4FCD1A;'>\n";
    echo "<h2>✓ Setup Complete!</h2>\n";
    echo "<p class='success'>Your local database is ready to use!</p>\n";
    echo "<div class='code'>\n";
    echo "<strong>Default Admin Login:</strong><br>\n";
    echo "Email: talk2char@gmail.com<br>\n";
    echo "Password: Password@123\n";
    echo "</div>\n";
    echo "<p><a href='index.php' style='color: #0023F5; font-weight: bold;'>→ Go to Homepage</a></p>\n";
    echo "<p><a href='login.php' style='color: #0023F5; font-weight: bold;'>→ Go to Login</a></p>\n";
    echo "<p><a href='admin/index.php' style='color: #0023F5; font-weight: bold;'>→ Go to Admin Dashboard</a></p>\n";
    echo "</div>\n";
    
} catch (PDOException $e) {
    echo "<p class='error'>✗ Connection failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "<p class='info'>The database doesn't exist yet. Please create it in phpMyAdmin first (see Step 1 above).</p>\n";
    } else {
        echo "<p class='info'>Make sure WAMP/XAMPP is running and MySQL service is started.</p>\n";
    }
    echo "</div>\n";
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "</div>\n";
}

echo "<div class='card'>\n";
echo "<h2>📝 Local Development Notes</h2>\n";
echo "<ul>\n";
echo "<li><strong>Database:</strong> hrla_database</li>\n";
echo "<li><strong>User:</strong> root</li>\n";
echo "<li><strong>Password:</strong> (empty)</li>\n";
echo "<li><strong>Host:</strong> localhost</li>\n";
echo "</ul>\n";
echo "<p class='info'>These settings are automatically used when accessing via localhost.</p>\n";
echo "<p class='info'>Production settings are used when accessing via your domain.</p>\n";
echo "</div>\n";

echo "</body></html>";
?>
