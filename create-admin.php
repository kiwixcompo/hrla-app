<?php
/**
 * Create Admin User
 * Run this to create or reset the admin user
 */

require_once 'config/app.php';

echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n";
echo "<title>Create Admin User</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }\n";
echo ".success { color: #4FCD1A; font-weight: bold; }\n";
echo ".error { color: #dc3545; font-weight: bold; }\n";
echo ".info { color: #0023F5; }\n";
echo ".step { margin: 20px 0; padding: 15px; background: #f8f9fa; border-left: 4px solid #0023F5; }\n";
echo "code { background: #e9ecef; padding: 2px 6px; border-radius: 3px; }\n";
echo "</style>\n";
echo "</head>\n<body>\n";

echo "<h1>Create Admin User</h1>\n";

try {
    $db = getDB();
    
    echo "<div class='step'>\n";
    echo "<h3>Step 1: Checking Database Connection</h3>\n";
    echo "<p class='success'>✓ Connected to database: " . htmlspecialchars($db->getPdo()->query('SELECT DATABASE()')->fetchColumn()) . "</p>\n";
    echo "</div>\n";
    
    echo "<div class='step'>\n";
    echo "<h3>Step 2: Checking Users Table</h3>\n";
    
    if (!$db->tableExists('users')) {
        echo "<p class='error'>✗ Users table doesn't exist</p>\n";
        echo "<p class='info'>Creating tables...</p>\n";
        $db->createTables();
        echo "<p class='success'>✓ Tables created</p>\n";
    } else {
        echo "<p class='success'>✓ Users table exists</p>\n";
    }
    echo "</div>\n";
    
    echo "<div class='step'>\n";
    echo "<h3>Step 3: Checking Admin User</h3>\n";
    
    // Check if admin exists
    $admin = $db->fetch("SELECT * FROM users WHERE email = ?", ['talk2char@gmail.com']);
    
    if ($admin) {
        echo "<p class='info'>Admin user already exists</p>\n";
        echo "<p>Email: <code>" . htmlspecialchars($admin['email']) . "</code></p>\n";
        echo "<p>Name: " . htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) . "</p>\n";
        echo "<p>Is Admin: " . ($admin['is_admin'] ? 'Yes' : 'No') . "</p>\n";
        echo "<p>Email Verified: " . ($admin['email_verified'] ? 'Yes' : 'No') . "</p>\n";
        echo "<p>Access Level: " . htmlspecialchars($admin['access_level']) . "</p>\n";
        
        // Reset password
        echo "<p class='info'>Resetting password to default...</p>\n";
        $defaultPassword = defined('DEFAULT_ADMIN_PASSWORD') ? DEFAULT_ADMIN_PASSWORD : 'Password@123';
        $passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);
        
        $db->query("UPDATE users SET password_hash = ?, email_verified = 1, is_admin = 1, access_level = 'administrator' WHERE email = ?", 
            [$passwordHash, 'talk2char@gmail.com']);
        
        echo "<p class='success'>✓ Password reset to default</p>\n";
        
    } else {
        echo "<p class='error'>✗ Admin user not found</p>\n";
        echo "<p class='info'>Creating admin user...</p>\n";
        
        $defaultPassword = defined('DEFAULT_ADMIN_PASSWORD') ? DEFAULT_ADMIN_PASSWORD : 'Password@123';
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
    }
    echo "</div>\n";
    
    echo "<div class='step'>\n";
    echo "<h3>Step 4: Verify Password</h3>\n";
    
    // Verify the password works
    $admin = $db->fetch("SELECT * FROM users WHERE email = ?", ['talk2char@gmail.com']);
    $testPassword = defined('DEFAULT_ADMIN_PASSWORD') ? DEFAULT_ADMIN_PASSWORD : 'Password@123';
    
    if (password_verify($testPassword, $admin['password_hash'])) {
        echo "<p class='success'>✓ Password verification successful</p>\n";
    } else {
        echo "<p class='error'>✗ Password verification failed</p>\n";
    }
    echo "</div>\n";
    
    echo "<div class='step' style='border-left-color: #4FCD1A;'>\n";
    echo "<h3>✓ Admin User Ready!</h3>\n";
    echo "<p class='success'>You can now log in with these credentials:</p>\n";
    echo "<p><strong>Email:</strong> <code>talk2char@gmail.com</code></p>\n";
    echo "<p><strong>Password:</strong> <code>Password@123</code></p>\n";
    echo "<br>\n";
    echo "<p><a href='login.php' style='color: #0023F5; font-weight: bold;'>→ Go to Login Page</a></p>\n";
    echo "<br>\n";
    echo "<p style='color: #dc3545;'><strong>⚠️ Security:</strong> Delete this file (create-admin.php) after use!</p>\n";
    echo "</div>\n";
    
    // Show all users
    echo "<div class='step'>\n";
    echo "<h3>All Users in Database</h3>\n";
    $users = $db->fetchAll("SELECT id, email, first_name, last_name, is_admin, email_verified, access_level, created_at FROM users ORDER BY id");
    
    if (count($users) > 0) {
        echo "<table style='width: 100%; border-collapse: collapse;'>\n";
        echo "<tr style='background: #e9ecef;'>\n";
        echo "<th style='padding: 8px; text-align: left; border: 1px solid #dee2e6;'>ID</th>\n";
        echo "<th style='padding: 8px; text-align: left; border: 1px solid #dee2e6;'>Email</th>\n";
        echo "<th style='padding: 8px; text-align: left; border: 1px solid #dee2e6;'>Name</th>\n";
        echo "<th style='padding: 8px; text-align: left; border: 1px solid #dee2e6;'>Admin</th>\n";
        echo "<th style='padding: 8px; text-align: left; border: 1px solid #dee2e6;'>Verified</th>\n";
        echo "<th style='padding: 8px; text-align: left; border: 1px solid #dee2e6;'>Access Level</th>\n";
        echo "</tr>\n";
        
        foreach ($users as $user) {
            echo "<tr>\n";
            echo "<td style='padding: 8px; border: 1px solid #dee2e6;'>" . $user['id'] . "</td>\n";
            echo "<td style='padding: 8px; border: 1px solid #dee2e6;'>" . htmlspecialchars($user['email']) . "</td>\n";
            echo "<td style='padding: 8px; border: 1px solid #dee2e6;'>" . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . "</td>\n";
            echo "<td style='padding: 8px; border: 1px solid #dee2e6;'>" . ($user['is_admin'] ? '✓' : '✗') . "</td>\n";
            echo "<td style='padding: 8px; border: 1px solid #dee2e6;'>" . ($user['email_verified'] ? '✓' : '✗') . "</td>\n";
            echo "<td style='padding: 8px; border: 1px solid #dee2e6;'>" . htmlspecialchars($user['access_level']) . "</td>\n";
            echo "</tr>\n";
        }
        
        echo "</table>\n";
    } else {
        echo "<p class='info'>No users found in database</p>\n";
    }
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<div class='step' style='border-left-color: #dc3545;'>\n";
    echo "<h3>✗ Error</h3>\n";
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p class='info'>Stack trace:</p>\n";
    echo "<pre style='background: #f8f9fa; padding: 10px; overflow: auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
    echo "</div>\n";
}

echo "</body>\n</html>";
?>
