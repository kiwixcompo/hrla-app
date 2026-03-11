<?php
/**
 * Emergency Fix Script
 * This will check and fix the .htaccess issue
 */

$password = 'fixnow2026';
$inputPassword = $_GET['pass'] ?? '';

if ($inputPassword !== $password) {
    die('Access Denied. Usage: fix-now.php?pass=fixnow2026');
}

echo "<!DOCTYPE html><html><head><title>HRLA Emergency Fix</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;max-width:900px;margin:0 auto;}";
echo ".ok{color:green;font-weight:bold;}.error{color:red;font-weight:bold;}.warning{color:orange;font-weight:bold;}";
echo "pre{background:white;padding:15px;border:1px solid #ddd;overflow-x:auto;}</style></head><body>";

echo "<h1>🚨 HRLA Emergency Fix</h1>";
echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

// Step 1: Check if .htaccess exists
echo "<h2>Step 1: Check .htaccess File</h2>";
$htaccessExists = file_exists('.htaccess');
$htaccessPath = __DIR__ . '/.htaccess';

if ($htaccessExists) {
    $htaccessSize = filesize('.htaccess');
    echo "<p class='ok'>✓ .htaccess EXISTS (Size: $htaccessSize bytes)</p>";
    
    $content = file_get_contents('.htaccess');
    echo "<h3>.htaccess Content (first 500 chars):</h3>";
    echo "<pre>" . htmlspecialchars(substr($content, 0, 500)) . "</pre>";
    
    // Check if it has DirectoryIndex
    if (strpos($content, 'DirectoryIndex') !== false) {
        echo "<p class='ok'>✓ DirectoryIndex directive found</p>";
    } else {
        echo "<p class='error'>✗ DirectoryIndex directive MISSING</p>";
    }
} else {
    echo "<p class='error'>✗ .htaccess DOES NOT EXIST at: $htaccessPath</p>";
    echo "<p class='warning'>⚠ This is why you're seeing directory listing!</p>";
}

// Step 2: Check if index.php exists
echo "<h2>Step 2: Check index.php File</h2>";
$indexExists = file_exists('index.php');
if ($indexExists) {
    $indexSize = filesize('index.php');
    echo "<p class='ok'>✓ index.php EXISTS (Size: $indexSize bytes)</p>";
} else {
    echo "<p class='error'>✗ index.php DOES NOT EXIST</p>";
}

// Step 3: Create .htaccess if missing
echo "<h2>Step 3: Create/Fix .htaccess</h2>";

$htaccessContent = <<<'HTACCESS'
# HR Leave Assist - Apache Configuration
DirectoryIndex index.php index.html

# Prevent directory listing
Options -Indexes

# Enable PHP processing
<IfModule mod_php7.c>
    php_flag display_errors Off
    php_flag log_errors On
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
</IfModule>

# Protect sensitive files
<FilesMatch "^(\.htaccess|\.env|composer\.json|\.git)">
    Order allow,deny
    Deny from all
</FilesMatch>

# Set UTF-8 encoding
AddDefaultCharset UTF-8
HTACCESS;

$action = $_GET['action'] ?? '';

if ($action === 'create') {
    $result = file_put_contents('.htaccess', $htaccessContent);
    if ($result !== false) {
        chmod('.htaccess', 0644);
        echo "<p class='ok'>✓ .htaccess file created successfully! ($result bytes written)</p>";
        echo "<p><strong>Now try visiting:</strong> <a href='https://www.hrleaveassist.com/'>https://www.hrleaveassist.com/</a></p>";
    } else {
        echo "<p class='error'>✗ Failed to create .htaccess file</p>";
        echo "<p>Possible reasons:</p>";
        echo "<ul>";
        echo "<li>No write permission in this directory</li>";
        echo "<li>Disk quota exceeded</li>";
        echo "<li>SELinux or other security restrictions</li>";
        echo "</ul>";
    }
} else {
    echo "<p><a href='?pass=fixnow2026&action=create' style='display:inline-block;background:#0322D8;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;font-weight:bold;'>CREATE .htaccess FILE NOW</a></p>";
}

// Step 4: Check directory permissions
echo "<h2>Step 4: Check Permissions</h2>";
$currentDir = __DIR__;
$perms = fileperms($currentDir);
$permsOctal = substr(sprintf('%o', $perms), -4);
echo "<p>Current directory: <code>$currentDir</code></p>";
echo "<p>Permissions: <code>$permsOctal</code></p>";

if (is_writable($currentDir)) {
    echo "<p class='ok'>✓ Directory is writable</p>";
} else {
    echo "<p class='error'>✗ Directory is NOT writable</p>";
    echo "<p>You need to set permissions to 755 or 775</p>";
}

// Step 5: Check Apache configuration
echo "<h2>Step 5: Apache Configuration</h2>";

if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "<p class='ok'>✓ Can check Apache modules</p>";
    
    $hasModRewrite = in_array('mod_rewrite', $modules);
    $hasModHeaders = in_array('mod_headers', $modules);
    
    echo "<ul>";
    echo "<li>mod_rewrite: " . ($hasModRewrite ? "<span class='ok'>✓ Loaded</span>" : "<span class='warning'>⚠ Not loaded</span>") . "</li>";
    echo "<li>mod_headers: " . ($hasModHeaders ? "<span class='ok'>✓ Loaded</span>" : "<span class='warning'>⚠ Not loaded</span>") . "</li>";
    echo "</ul>";
} else {
    echo "<p class='warning'>⚠ Cannot check Apache modules (function not available)</p>";
}

// Check if .htaccess is being processed
echo "<h3>Is .htaccess Being Processed?</h3>";
if (function_exists('apache_get_version')) {
    echo "<p class='ok'>✓ Apache version: " . apache_get_version() . "</p>";
} else {
    echo "<p class='warning'>⚠ Cannot detect Apache version</p>";
}

// Step 6: Alternative solution - create index.html redirect
echo "<h2>Step 6: Alternative Solution</h2>";
echo "<p>If .htaccess doesn't work, we can create an index.html that redirects:</p>";

if ($action === 'createhtml') {
    $htmlContent = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0;url=index.php">
    <title>Redirecting...</title>
</head>
<body>
    <p>Redirecting to homepage...</p>
    <p>If not redirected, <a href="index.php">click here</a>.</p>
</body>
</html>
HTML;
    
    $result = file_put_contents('index.html', $htmlContent);
    if ($result !== false) {
        echo "<p class='ok'>✓ index.html redirect created!</p>";
    } else {
        echo "<p class='error'>✗ Failed to create index.html</p>";
    }
} else {
    echo "<p><a href='?pass=fixnow2026&action=createhtml' style='display:inline-block;background:#3DB20B;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;font-weight:bold;'>CREATE index.html REDIRECT</a></p>";
}

// Step 7: Contact hosting support
echo "<h2>Step 7: If Nothing Works</h2>";
echo "<div style='background:#fff3cd;border:1px solid #ffc107;padding:15px;border-radius:5px;'>";
echo "<h3>Contact Your Hosting Support</h3>";
echo "<p>If the above steps don't work, contact your hosting provider with this information:</p>";
echo "<pre>";
echo "Issue: Directory listing showing instead of homepage\n";
echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
echo "Current Directory: " . __DIR__ . "\n";
echo "\nRequest:\n";
echo "1. Enable .htaccess processing (AllowOverride All)\n";
echo "2. Ensure PHP handler is configured for .php files\n";
echo "3. Set DirectoryIndex to index.php\n";
echo "</pre>";
echo "</div>";

// Summary
echo "<hr>";
echo "<h2>Summary</h2>";
echo "<ol>";
echo "<li>.htaccess exists: " . ($htaccessExists ? "<span class='ok'>YES</span>" : "<span class='error'>NO</span>") . "</li>";
echo "<li>index.php exists: " . ($indexExists ? "<span class='ok'>YES</span>" : "<span class='error'>NO</span>") . "</li>";
echo "<li>Directory writable: " . (is_writable($currentDir) ? "<span class='ok'>YES</span>" : "<span class='error'>NO</span>") . "</li>";
echo "</ol>";

if (!$htaccessExists) {
    echo "<p class='error' style='font-size:18px;'>⚠ ACTION REQUIRED: Click the button above to create .htaccess file!</p>";
} elseif ($htaccessExists && $indexExists) {
    echo "<p class='warning' style='font-size:18px;'>⚠ Files exist but .htaccess may not be processed by Apache. Contact hosting support.</p>";
}

echo "<hr>";
echo "<p><small>Generated: " . date('Y-m-d H:i:s T') . "</small></p>";
echo "</body></html>";
?>
