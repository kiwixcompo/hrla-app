<?php
/**
 * Fix Server Configuration
 * This script fixes the config/local.php file on the server
 * Run this once, then delete this file
 */

$configPath = __DIR__ . '/config/local.php';
$backupPath = __DIR__ . '/config/local.php.backup';

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Fix Server Config</title></head><body>\n";
echo "<h1>Fixing Server Configuration</h1>\n";

// Backup existing file if it exists
if (file_exists($configPath)) {
    echo "<p>Backing up existing config/local.php...</p>\n";
    copy($configPath, $backupPath);
    echo "<p style='color: green;'>✓ Backup created at config/local.php.backup</p>\n";
}

// Create proper config/local.php
$content = <<<'PHP'
<?php
/**
 * Local Configuration
 * This file contains local/server-specific settings
 * NOT committed to git for security
 */

// Default admin password (used during initial setup)
define('DEFAULT_ADMIN_PASSWORD', 'Password@123');

// SMTP Password (set this on your server)
define('SMTP_PASSWORD_LOCAL', 'Password@123');

// Database credentials are already set as defaults in config/database.php
// No need to set them here unless you want to override
?>
PHP;

file_put_contents($configPath, $content);
echo "<p style='color: green;'>✓ Created clean config/local.php</p>\n";

// Set proper permissions
chmod($configPath, 0644);
echo "<p style='color: green;'>✓ Set file permissions to 644</p>\n";

echo "<h2>Done!</h2>\n";
echo "<p>The config/local.php file has been fixed.</p>\n";
echo "<p><strong>Next steps:</strong></p>\n";
echo "<ol>\n";
echo "<li>Delete this file (fix-server-config.php) for security</li>\n";
echo "<li>Visit <a href='login.php'>login.php</a> to verify no credentials are showing</li>\n";
echo "<li>Visit <a href='register.php'>register.php</a> to verify no credentials are showing</li>\n";
echo "<li>The database will auto-initialize on first page load</li>\n";
echo "</ol>\n";

echo "</body></html>";
?>
