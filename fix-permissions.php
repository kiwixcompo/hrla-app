<?php
/**
 * Fix File Permissions on Server
 * Run this once after deployment to set correct permissions
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Permissions</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { color: #0322D8; }
        .success { color: #3DB20B; }
        .error { color: #d32f2f; }
        .warning { color: #ff9800; }
        .item { padding: 10px; margin: 5px 0; background: #f9f9f9; border-left: 4px solid #ddd; }
        .item.success { border-left-color: #3DB20B; }
        .item.error { border-left-color: #d32f2f; }
        .item.warning { border-left-color: #ff9800; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 Fix File Permissions</h1>";

$results = [];

// Files that should be 644 (readable by web server)
$files644 = [
    '.htaccess',
    'index.php',
    'config/.htaccess',
    'data/.htaccess',
    'logs/.htaccess',
    'includes/.htaccess'
];

// Directories that should be 755 (executable/listable)
$dirs755 = [
    'config',
    'data',
    'logs',
    'includes',
    'assets',
    'api',
    'admin'
];

// Directories that should be 777 (writable)
$dirs777 = [
    'logs',
    'data'
];

echo "<h2>Setting File Permissions (644)</h2>";
foreach ($files644 as $file) {
    if (file_exists($file)) {
        $result = @chmod($file, 0644);
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        if ($result) {
            echo "<div class='item success'>✓ {$file} - Set to 0644 (Current: {$perms})</div>";
            $results[] = ['file' => $file, 'status' => 'success', 'perms' => $perms];
        } else {
            echo "<div class='item error'>✗ {$file} - Failed to set permissions (Current: {$perms})</div>";
            $results[] = ['file' => $file, 'status' => 'error', 'perms' => $perms];
        }
    } else {
        echo "<div class='item warning'>⚠ {$file} - File not found</div>";
        $results[] = ['file' => $file, 'status' => 'missing'];
    }
}

echo "<h2>Setting Directory Permissions (755)</h2>";
foreach ($dirs755 as $dir) {
    if (is_dir($dir)) {
        $result = @chmod($dir, 0755);
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        if ($result) {
            echo "<div class='item success'>✓ {$dir}/ - Set to 0755 (Current: {$perms})</div>";
            $results[] = ['file' => $dir, 'status' => 'success', 'perms' => $perms];
        } else {
            echo "<div class='item error'>✗ {$dir}/ - Failed to set permissions (Current: {$perms})</div>";
            $results[] = ['file' => $dir, 'status' => 'error', 'perms' => $perms];
        }
    } else {
        echo "<div class='item warning'>⚠ {$dir}/ - Directory not found</div>";
        $results[] = ['file' => $dir, 'status' => 'missing'];
    }
}

echo "<h2>Setting Writable Directory Permissions (777)</h2>";
foreach ($dirs777 as $dir) {
    if (is_dir($dir)) {
        $result = @chmod($dir, 0777);
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        if ($result) {
            echo "<div class='item success'>✓ {$dir}/ - Set to 0777 (Current: {$perms})</div>";
            $results[] = ['file' => $dir, 'status' => 'success', 'perms' => $perms];
        } else {
            echo "<div class='item error'>✗ {$dir}/ - Failed to set permissions (Current: {$perms})</div>";
            $results[] = ['file' => $dir, 'status' => 'error', 'perms' => $perms];
        }
    }
}

// Check .htaccess specifically
echo "<h2>Checking .htaccess File</h2>";
if (file_exists('.htaccess')) {
    $perms = substr(sprintf('%o', fileperms('.htaccess')), -4);
    $size = filesize('.htaccess');
    $readable = is_readable('.htaccess');
    
    echo "<div class='item'>";
    echo "<strong>File:</strong> .htaccess<br>";
    echo "<strong>Permissions:</strong> {$perms}<br>";
    echo "<strong>Size:</strong> {$size} bytes<br>";
    echo "<strong>Readable:</strong> " . ($readable ? "<span class='success'>Yes</span>" : "<span class='error'>No</span>") . "<br>";
    echo "<strong>Owner:</strong> " . (function_exists('posix_getpwuid') ? posix_getpwuid(fileowner('.htaccess'))['name'] : 'Unknown') . "<br>";
    echo "</div>";
} else {
    echo "<div class='item error'>✗ .htaccess file not found!</div>";
}

// Summary
$successCount = count(array_filter($results, function($r) { return $r['status'] === 'success'; }));
$errorCount = count(array_filter($results, function($r) { return $r['status'] === 'error'; }));
$missingCount = count(array_filter($results, function($r) { return $r['status'] === 'missing'; }));

echo "<h2>Summary</h2>";
echo "<div class='item'>";
echo "<strong class='success'>✓ Success:</strong> {$successCount}<br>";
echo "<strong class='error'>✗ Errors:</strong> {$errorCount}<br>";
echo "<strong class='warning'>⚠ Missing:</strong> {$missingCount}<br>";
echo "</div>";

if ($errorCount > 0) {
    echo "<h2>Manual Fix Required</h2>";
    echo "<div class='item warning'>";
    echo "<p>Some permissions could not be set automatically. You need to fix them via cPanel File Manager or FTP:</p>";
    echo "<ol>";
    echo "<li>Log into cPanel</li>";
    echo "<li>Go to File Manager</li>";
    echo "<li>Navigate to public_html</li>";
    echo "<li>Right-click .htaccess → Change Permissions → Set to 644</li>";
    echo "<li>Right-click logs/ → Change Permissions → Set to 777</li>";
    echo "<li>Right-click data/ → Change Permissions → Set to 777</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<h2>Next Steps</h2>";
echo "<div class='item'>";
echo "<p>1. If all permissions are set correctly, try accessing your site</p>";
echo "<p>2. If you still see directory listing, check if index.php exists</p>";
echo "<p>3. If errors persist, contact your hosting provider</p>";
echo "<p><a href='index.php'>Go to Homepage</a></p>";
echo "</div>";

echo "</div>
</body>
</html>";
?>
