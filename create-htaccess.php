<?php
/**
 * Direct .htaccess Creator
 * Run this file directly on your server to create .htaccess
 */

// No password needed - this is an emergency fix
echo "<!DOCTYPE html><html><head><title>Create .htaccess</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;background:#f5f5f5;}";
echo ".success{background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:15px;border-radius:5px;margin:20px 0;}";
echo ".error{background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:15px;border-radius:5px;margin:20px 0;}";
echo ".info{background:#d1ecf1;border:1px solid #bee5eb;color:#0c5460;padding:15px;border-radius:5px;margin:20px 0;}";
echo "pre{background:white;padding:15px;border:1px solid #ddd;overflow-x:auto;}</style></head><body>";

echo "<h1>🔧 Create .htaccess File</h1>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";
echo "<hr>";

// The .htaccess content
$htaccessContent = <<<'HTACCESS'
# HR Leave Assist - Apache Configuration
# This file tells Apache how to handle requests

# Set the default index file (homepage)
DirectoryIndex index.php index.html

# Prevent directory listing (security)
Options -Indexes

# Enable PHP processing
<IfModule mod_php7.c>
    php_flag display_errors Off
    php_flag log_errors On
</IfModule>

<IfModule mod_php8.c>
    php_flag display_errors Off
    php_flag log_errors On
</IfModule>

# Enable URL rewriting (optional but recommended)
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Force HTTPS (uncomment if you have SSL)
    # RewriteCond %{HTTPS} off
    # RewriteCond %{HTTP_HOST} ^(www\.)?hrleaveassist\.com$ [NC]
    # RewriteRule ^(.*)$ https://www.hrleaveassist.com/$1 [R=301,L]
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Protect sensitive files
<FilesMatch "^(\.htaccess|\.htpasswd|\.env|composer\.json|composer\.lock|package\.json|\.git)">
    Order allow,deny
    Deny from all
</FilesMatch>

# Set UTF-8 encoding
AddDefaultCharset UTF-8

# Prevent access to backup and config files
<FilesMatch "\.(bak|config|sql|fla|psd|ini|log|sh|inc|swp|dist)$">
    Order allow,deny
    Deny from all
</FilesMatch>

HTACCESS;

// Check if .htaccess already exists
$htaccessPath = __DIR__ . '/.htaccess';
$htaccessExists = file_exists($htaccessPath);

if ($htaccessExists) {
    $existingSize = filesize($htaccessPath);
    $existingContent = file_get_contents($htaccessPath);
    
    echo "<div class='info'>";
    echo "<h2>ℹ️ .htaccess Already Exists</h2>";
    echo "<p><strong>Location:</strong> $htaccessPath</p>";
    echo "<p><strong>Size:</strong> $existingSize bytes</p>";
    echo "<p><strong>Last Modified:</strong> " . date('Y-m-d H:i:s', filemtime($htaccessPath)) . "</p>";
    echo "</div>";
    
    echo "<h3>Current .htaccess Content:</h3>";
    echo "<pre>" . htmlspecialchars($existingContent) . "</pre>";
    
    // Check if it has the required directives
    $hasDirectoryIndex = strpos($existingContent, 'DirectoryIndex') !== false;
    $hasOptionsIndexes = strpos($existingContent, 'Options -Indexes') !== false;
    
    if ($hasDirectoryIndex && $hasOptionsIndexes) {
        echo "<div class='success'>";
        echo "<h3>✅ .htaccess Looks Good!</h3>";
        echo "<p>The file contains the required directives:</p>";
        echo "<ul>";
        echo "<li>✓ DirectoryIndex directive found</li>";
        echo "<li>✓ Options -Indexes directive found</li>";
        echo "</ul>";
        echo "<p><strong>If you're still seeing directory listing, the problem is:</strong></p>";
        echo "<ol>";
        echo "<li>Apache is not processing .htaccess files (AllowOverride is set to None)</li>";
        echo "<li>You need to contact your hosting provider</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<h3>⚠️ .htaccess Missing Required Directives</h3>";
        echo "<ul>";
        if (!$hasDirectoryIndex) echo "<li>✗ DirectoryIndex directive missing</li>";
        if (!$hasOptionsIndexes) echo "<li>✗ Options -Indexes directive missing</li>";
        echo "</ul>";
        echo "<p><a href='?action=replace' style='display:inline-block;background:#dc3545;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Replace with Correct .htaccess</a></p>";
        echo "</div>";
    }
} else {
    echo "<div class='error'>";
    echo "<h2>❌ .htaccess Does NOT Exist</h2>";
    echo "<p><strong>Expected Location:</strong> $htaccessPath</p>";
    echo "<p>This is why you're seeing a directory listing!</p>";
    echo "</div>";
}

// Handle creation/replacement
$action = $_GET['action'] ?? '';

if ($action === 'create' || $action === 'replace') {
    echo "<hr>";
    echo "<h2>Creating .htaccess File...</h2>";
    
    // Try to create the file
    $result = @file_put_contents($htaccessPath, $htaccessContent);
    
    if ($result !== false) {
        // Set proper permissions
        @chmod($htaccessPath, 0644);
        
        echo "<div class='success'>";
        echo "<h3>✅ SUCCESS!</h3>";
        echo "<p><strong>.htaccess file created successfully!</strong></p>";
        echo "<ul>";
        echo "<li>Location: $htaccessPath</li>";
        echo "<li>Size: $result bytes</li>";
        echo "<li>Permissions: 0644</li>";
        echo "</ul>";
        echo "<h3>Next Steps:</h3>";
        echo "<ol>";
        echo "<li>Visit your homepage: <a href='https://www.hrleaveassist.com/' target='_blank'>https://www.hrleaveassist.com/</a></li>";
        echo "<li>Press Ctrl+F5 to clear cache</li>";
        echo "<li>You should see the HRLA homepage (not directory listing)</li>";
        echo "</ol>";
        echo "</div>";
        
        echo "<h3>Created .htaccess Content:</h3>";
        echo "<pre>" . htmlspecialchars($htaccessContent) . "</pre>";
        
    } else {
        echo "<div class='error'>";
        echo "<h3>❌ Failed to Create .htaccess</h3>";
        echo "<p><strong>Possible reasons:</strong></p>";
        echo "<ul>";
        echo "<li>No write permission in directory: " . __DIR__ . "</li>";
        echo "<li>Disk quota exceeded</li>";
        echo "<li>SELinux or security restrictions</li>";
        echo "<li>File system is read-only</li>";
        echo "</ul>";
        
        // Check directory permissions
        $dirPerms = fileperms(__DIR__);
        $dirPermsOctal = substr(sprintf('%o', $dirPerms), -4);
        echo "<p><strong>Current directory permissions:</strong> $dirPermsOctal</p>";
        echo "<p><strong>Directory writable:</strong> " . (is_writable(__DIR__) ? 'Yes' : 'No') . "</p>";
        
        echo "<h3>Manual Solution:</h3>";
        echo "<p>Copy the content below and create .htaccess manually via cPanel File Manager:</p>";
        echo "<pre>" . htmlspecialchars($htaccessContent) . "</pre>";
        echo "</div>";
    }
} else {
    // Show create button
    if (!$htaccessExists) {
        echo "<div style='text-align:center;margin:30px 0;'>";
        echo "<a href='?action=create' style='display:inline-block;background:#28a745;color:white;padding:20px 40px;text-decoration:none;border-radius:5px;font-size:18px;font-weight:bold;'>CREATE .htaccess FILE NOW</a>";
        echo "</div>";
    }
}

// Show .htaccess content that will be created
if (!$htaccessExists && $action === '') {
    echo "<hr>";
    echo "<h2>Preview: .htaccess Content</h2>";
    echo "<p>This is what will be created:</p>";
    echo "<pre>" . htmlspecialchars($htaccessContent) . "</pre>";
}

// Additional diagnostics
echo "<hr>";
echo "<h2>Server Information</h2>";
echo "<ul>";
echo "<li><strong>PHP Version:</strong> " . phpversion() . "</li>";
echo "<li><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</li>";
echo "<li><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</li>";
echo "<li><strong>Current Script:</strong> " . __FILE__ . "</li>";
echo "<li><strong>Directory Writable:</strong> " . (is_writable(__DIR__) ? 'Yes ✓' : 'No ✗') . "</li>";
echo "</ul>";

if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "<h3>Apache Modules:</h3>";
    echo "<ul>";
    echo "<li>mod_rewrite: " . (in_array('mod_rewrite', $modules) ? 'Loaded ✓' : 'Not loaded ✗') . "</li>";
    echo "<li>mod_headers: " . (in_array('mod_headers', $modules) ? 'Loaded ✓' : 'Not loaded ✗') . "</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><small>Generated: " . date('Y-m-d H:i:s T') . "</small></p>";
echo "</body></html>";
?>
