<?php
/**
 * Deployment Diagnostic Script
 * Check if all files are properly deployed
 */

// Simple password protection
$password = 'check2026';
$inputPassword = $_GET['pass'] ?? '';

if ($inputPassword !== $password) {
    die('Access Denied. Usage: check-deployment.php?pass=check2026');
}

echo "<!DOCTYPE html><html><head><title>HRLA Deployment Check</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}";
echo ".ok{color:green;}.error{color:red;}.warning{color:orange;}";
echo "table{border-collapse:collapse;background:white;margin:20px 0;}";
echo "th,td{border:1px solid #ddd;padding:8px;text-align:left;}";
echo "th{background:#333;color:white;}</style></head><body>";

echo "<h1>HRLA Deployment Diagnostic</h1>";
echo "<p><strong>Server Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

// Check PHP version
echo "<h2>1. PHP Configuration</h2>";
echo "<table>";
echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";

$phpVersion = phpversion();
$phpOk = version_compare($phpVersion, '7.4', '>=');
echo "<tr><td>PHP Version</td><td>$phpVersion</td><td class='" . ($phpOk ? 'ok' : 'error') . "'>" . ($phpOk ? '✓ OK' : '✗ Too Old') . "</td></tr>";

$displayErrors = ini_get('display_errors');
echo "<tr><td>Display Errors</td><td>$displayErrors</td><td class='ok'>✓</td></tr>";

$maxExecution = ini_get('max_execution_time');
echo "<tr><td>Max Execution Time</td><td>{$maxExecution}s</td><td class='ok'>✓</td></tr>";

$memoryLimit = ini_get('memory_limit');
echo "<tr><td>Memory Limit</td><td>$memoryLimit</td><td class='ok'>✓</td></tr>";

echo "</table>";

// Check critical files
echo "<h2>2. Critical Files</h2>";
echo "<table>";
echo "<tr><th>File</th><th>Exists</th><th>Readable</th><th>Size</th></tr>";

$criticalFiles = [
    '.htaccess',
    'index.php',
    'login.php',
    'register.php',
    'dashboard.php',
    'config/app.php',
    'config/database.php',
    'includes/auth.php',
    'api/auth.php',
    'api/ai.php'
];

foreach ($criticalFiles as $file) {
    $exists = file_exists($file);
    $readable = $exists && is_readable($file);
    $size = $exists ? filesize($file) : 0;
    $sizeFormatted = $exists ? number_format($size) . ' bytes' : 'N/A';
    
    $status = $exists && $readable ? 'ok' : 'error';
    $existsText = $exists ? '✓ Yes' : '✗ No';
    $readableText = $readable ? '✓ Yes' : '✗ No';
    
    echo "<tr class='$status'>";
    echo "<td>$file</td>";
    echo "<td>$existsText</td>";
    echo "<td>$readableText</td>";
    echo "<td>$sizeFormatted</td>";
    echo "</tr>";
}

echo "</table>";

// Check directories
echo "<h2>3. Required Directories</h2>";
echo "<table>";
echo "<tr><th>Directory</th><th>Exists</th><th>Writable</th><th>Files</th></tr>";

$directories = [
    'admin',
    'api',
    'assets',
    'config',
    'includes',
    'data'
];

foreach ($directories as $dir) {
    $exists = is_dir($dir);
    $writable = $exists && is_writable($dir);
    $fileCount = $exists ? count(scandir($dir)) - 2 : 0;
    
    $status = $exists ? 'ok' : 'error';
    $existsText = $exists ? '✓ Yes' : '✗ No';
    $writableText = $writable ? '✓ Yes' : '✗ No';
    
    echo "<tr class='$status'>";
    echo "<td>$dir/</td>";
    echo "<td>$existsText</td>";
    echo "<td>$writableText</td>";
    echo "<td>$fileCount files</td>";
    echo "</tr>";
}

echo "</table>";

// Check .htaccess content
echo "<h2>4. .htaccess Configuration</h2>";
if (file_exists('.htaccess')) {
    $htaccessContent = file_get_contents('.htaccess');
    $hasDirectoryIndex = strpos($htaccessContent, 'DirectoryIndex') !== false;
    $hasRewriteEngine = strpos($htaccessContent, 'RewriteEngine') !== false;
    
    echo "<table>";
    echo "<tr><th>Check</th><th>Status</th></tr>";
    echo "<tr class='" . ($hasDirectoryIndex ? 'ok' : 'error') . "'><td>DirectoryIndex directive</td><td>" . ($hasDirectoryIndex ? '✓ Present' : '✗ Missing') . "</td></tr>";
    echo "<tr class='" . ($hasRewriteEngine ? 'ok' : 'warning') . "'><td>RewriteEngine directive</td><td>" . ($hasRewriteEngine ? '✓ Present' : '⚠ Missing') . "</td></tr>";
    echo "</table>";
    
    echo "<h3>.htaccess Content (first 20 lines):</h3>";
    echo "<pre style='background:white;padding:15px;border:1px solid #ddd;overflow-x:auto;'>";
    $lines = explode("\n", $htaccessContent);
    echo htmlspecialchars(implode("\n", array_slice($lines, 0, 20)));
    if (count($lines) > 20) echo "\n... (" . (count($lines) - 20) . " more lines)";
    echo "</pre>";
} else {
    echo "<p class='error'>✗ .htaccess file NOT FOUND - This is the problem!</p>";
    echo "<p><strong>Solution:</strong> The .htaccess file is missing. Deploy it from your repository.</p>";
}

// Check Apache modules
echo "<h2>5. Apache Modules</h2>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    $requiredModules = ['mod_rewrite', 'mod_php', 'mod_php7'];
    
    echo "<table>";
    echo "<tr><th>Module</th><th>Status</th></tr>";
    
    foreach ($requiredModules as $module) {
        $loaded = in_array($module, $modules);
        $status = $loaded ? 'ok' : 'warning';
        $text = $loaded ? '✓ Loaded' : '⚠ Not loaded';
        echo "<tr class='$status'><td>$module</td><td>$text</td></tr>";
    }
    
    echo "</table>";
} else {
    echo "<p class='warning'>⚠ Cannot check Apache modules (apache_get_modules not available)</p>";
}

// Check database connection
echo "<h2>6. Database Connection</h2>";
try {
    if (file_exists('config/database.php')) {
        require_once 'config/database.php';
        $db = getDB();
        echo "<p class='ok'>✓ Database connection successful</p>";
        
        // Check tables
        $tables = ['users', 'conversations', 'site_settings'];
        echo "<table>";
        echo "<tr><th>Table</th><th>Status</th></tr>";
        foreach ($tables as $table) {
            $exists = $db->tableExists($table);
            $status = $exists ? 'ok' : 'warning';
            $text = $exists ? '✓ Exists' : '⚠ Missing';
            echo "<tr class='$status'><td>$table</td><td>$text</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>✗ Database config file not found</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Summary
echo "<hr>";
echo "<h2>Summary</h2>";

$allFilesExist = true;
foreach ($criticalFiles as $file) {
    if (!file_exists($file)) {
        $allFilesExist = false;
        break;
    }
}

if ($allFilesExist && file_exists('.htaccess')) {
    echo "<p class='ok' style='font-size:18px;font-weight:bold;'>✓ All critical files are present!</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li>Visit <a href='https://www.hrleaveassist.com/'>https://www.hrleaveassist.com/</a></li>";
    echo "<li>Clear browser cache (Ctrl+F5)</li>";
    echo "<li>If still showing directory listing, contact hosting support about PHP handler configuration</li>";
    echo "</ol>";
} else {
    echo "<p class='error' style='font-size:18px;font-weight:bold;'>✗ Some files are missing!</p>";
    echo "<p><strong>Action required:</strong></p>";
    echo "<ol>";
    echo "<li>Run the deployment script: <a href='simple-deploy.php?pass=deploy2026'>simple-deploy.php</a></li>";
    echo "<li>Or manually copy files from repository to public_html</li>";
    echo "<li>Ensure .htaccess file is present</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<p><small>Generated: " . date('Y-m-d H:i:s T') . "</small></p>";
echo "</body></html>";
?>
