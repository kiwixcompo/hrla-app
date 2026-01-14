<?php
/**
 * Database Initialization Script
 * Run this to initialize or reset the database
 */

require_once 'config/app.php';

echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n";
echo "<title>Database Initialization</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }\n";
echo ".success { color: #4FCD1A; font-weight: bold; }\n";
echo ".error { color: #dc3545; font-weight: bold; }\n";
echo ".info { color: #0023F5; }\n";
echo ".step { margin: 20px 0; padding: 15px; background: #f8f9fa; border-left: 4px solid #0023F5; }\n";
echo "</style>\n";
echo "</head>\n<body>\n";

echo "<h1>Database Initialization</h1>\n";
echo "<p>This script will initialize your database with all required tables and default data.</p>\n";

try {
    echo "<div class='step'>\n";
    echo "<h3>Step 1: Connecting to Database</h3>\n";
    $db = getDB();
    echo "<p class='success'>✓ Connected successfully</p>\n";
    echo "</div>\n";
    
    echo "<div class='step'>\n";
    echo "<h3>Step 2: Checking Tables</h3>\n";
    
    // Check if tables exist
    $tables = ['users', 'access_codes', 'api_config', 'user_sessions', 'conversations', 'transactions', 'pending_verifications', 'system_logs'];
    $existingTables = [];
    $missingTables = [];
    
    foreach ($tables as $table) {
        if ($db->tableExists($table)) {
            $existingTables[] = $table;
            echo "<p class='success'>✓ $table exists</p>\n";
        } else {
            $missingTables[] = $table;
            echo "<p class='error'>✗ $table missing</p>\n";
        }
    }
    
    if (count($missingTables) > 0) {
        echo "<p class='info'>Creating " . count($missingTables) . " missing tables...</p>\n";
        try {
            $db->createTables();
            echo "<p class='success'>✓ Tables created successfully</p>\n";
        } catch (Exception $e) {
            echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
            echo "<p class='info'>Some tables may already exist. This is usually not a problem.</p>\n";
        }
    } else {
        echo "<p class='success'>✓ All tables exist</p>\n";
    }
    echo "</div>\n";
    
    echo "<div class='step'>\n";
    echo "<h3>Step 3: Checking Admin User</h3>\n";
    try {
        $admin = $db->fetch("SELECT * FROM users WHERE email = 'talk2char@gmail.com'");
        if ($admin) {
            echo "<p class='success'>✓ Admin user exists</p>\n";
            echo "<p class='info'>Email: talk2char@gmail.com</p>\n";
            echo "<p class='info'>Access Level: " . $admin['access_level'] . "</p>\n";
        } else {
            echo "<p class='error'>✗ Admin user not found - creating...</p>\n";
            
            // Use default password from local config if available
            $defaultPassword = defined('DEFAULT_ADMIN_PASSWORD') ? DEFAULT_ADMIN_PASSWORD : 'ChangeMe123!';
            $passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);
            $trialExpiry = date('Y-m-d H:i:s', strtotime('+100 years'));
            
            $sql = "INSERT INTO users (email, password_hash, first_name, last_name, is_admin, email_verified, trial_started, trial_expiry, access_level) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, 'administrator')";
            
            $db->query($sql, [
                'talk2char@gmail.com',
                $passwordHash,
                'Super',
                'Admin',
                1,
                1,
                $trialExpiry
            ]);
            
            echo "<p class='success'>✓ Admin user created</p>\n";
            echo "<p class='info'>Default password is set from config/local.php</p>\n";
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    }
    echo "</div>\n";
    
    echo "<div class='step'>\n";
    echo "<h3>Step 4: Checking API Configuration</h3>\n";
    $apiConfig = $db->fetch("SELECT * FROM api_config WHERE id = 1");
    if ($apiConfig) {
        echo "<p class='success'>✓ API config entry exists</p>\n";
        if (!empty($apiConfig['openai_key'])) {
            echo "<p class='info'>OpenAI Key: Configured (" . substr($apiConfig['openai_key'], 0, 10) . "...)</p>\n";
        } else {
            echo "<p class='info'>OpenAI Key: Not configured yet</p>\n";
        }
        echo "<p class='info'>Total Requests: " . $apiConfig['total_requests'] . "</p>\n";
    } else {
        echo "<p class='error'>✗ API config not found - creating...</p>\n";
        
        // Get admin ID
        $admin = $db->fetch("SELECT id FROM users WHERE email = 'talk2char@gmail.com'");
        $adminId = $admin['id'] ?? 1;
        
        $sql = "INSERT INTO api_config (id, openai_key, total_requests, openai_requests, is_active, updated_by) 
                VALUES (1, '', 0, 0, 1, ?)";
        $db->query($sql, [$adminId]);
        
        echo "<p class='success'>✓ API config created</p>\n";
    }
    echo "</div>\n";
    
    echo "<div class='step'>\n";
    echo "<h3>Step 5: Database Statistics</h3>\n";
    foreach ($tables as $table) {
        $count = $db->fetch("SELECT COUNT(*) as count FROM $table");
        echo "<p class='info'>$table: " . $count['count'] . " records</p>\n";
    }
    echo "</div>\n";
    
    echo "<div class='step' style='border-left-color: #4FCD1A;'>\n";
    echo "<h3>✓ Initialization Complete!</h3>\n";
    echo "<p class='success'>Your database is ready to use.</p>\n";
    echo "<p><strong>Default Admin Credentials:</strong></p>\n";
    echo "<p>Email: <code>talk2char@gmail.com</code></p>\n";
    echo "<p>Password: <code>Password@123</code></p>\n";
    echo "<p style='color: #dc3545;'><strong>⚠️ Change this password immediately!</strong></p>\n";
    echo "<br>\n";
    echo "<p><a href='login.php' style='color: #0023F5;'>→ Go to Login</a></p>\n";
    echo "<p><a href='admin/index.php' style='color: #0023F5;'>→ Go to Admin Dashboard</a></p>\n";
    echo "<p><a href='test-connection.php' style='color: #0023F5;'>→ Run Connection Test</a></p>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<div class='step' style='border-left-color: #dc3545;'>\n";
    echo "<h3>✗ Initialization Failed</h3>\n";
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p class='info'>Please check your database configuration in config/app.php</p>\n";
    echo "</div>\n";
}

echo "</body>\n</html>";
?>
