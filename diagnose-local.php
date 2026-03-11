<?php
/**
 * Local Diagnostics Script
 * Run this to diagnose local setup issues
 */

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>HRLA - Local Diagnostics</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #0322D8; margin-bottom: 30px; }
        .section { margin: 20px 0; padding: 20px; background: #f9f9f9; border-left: 4px solid #0322D8; }
        .section h2 { margin: 0 0 15px 0; color: #333; font-size: 18px; }
        .success { color: #3DB20B; font-weight: bold; }
        .error { color: #d32f2f; font-weight: bold; }
        .warning { color: #ff9800; font-weight: bold; }
        .info { color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table td { padding: 8px; border-bottom: 1px solid #ddd; }
        table td:first-child { font-weight: bold; width: 200px; }
        .code { background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; overflow-x: auto; font-family: 'Courier New', monospace; font-size: 13px; }
        .btn { display: inline-block; padding: 10px 20px; background: #0322D8; color: white; text-decoration: none; border-radius: 4px; margin: 10px 5px 0 0; }
        .btn:hover { background: #1800AD; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 HRLA Local Diagnostics</h1>";

// 1. PHP Version Check
echo "<div class='section'>
    <h2>1. PHP Environment</h2>
    <table>
        <tr><td>PHP Version:</td><td>" . phpversion() . " " . (version_compare(phpversion(), '7.4.0', '>=') ? "<span class='success'>✓ OK</span>" : "<span class='error'>✗ Requires PHP 7.4+</span>") . "</td></tr>
        <tr><td>Server Software:</td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</td></tr>
        <tr><td>Document Root:</td><td>" . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</td></tr>
        <tr><td>Script Path:</td><td>" . __FILE__ . "</td></tr>
    </table>
</div>";

// 2. Required Extensions
echo "<div class='section'>
    <h2>2. Required PHP Extensions</h2>
    <table>";

$requiredExtensions = ['pdo', 'pdo_sqlite', 'json', 'mbstring', 'openssl', 'curl'];
foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<tr><td>{$ext}:</td><td>" . ($loaded ? "<span class='success'>✓ Loaded</span>" : "<span class='error'>✗ Missing</span>") . "</td></tr>";
}

echo "</table>
</div>";

// 3. File System Checks
echo "<div class='section'>
    <h2>3. File System</h2>
    <table>";

$paths = [
    'config/app.php' => 'Main config',
    'config/database.php' => 'Database config',
    'config/local.php' => 'Local config',
    'includes/auth.php' => 'Auth system',
    'includes/error_handler.php' => 'Error handler',
    'data/' => 'Data directory',
    'logs/' => 'Logs directory'
];

foreach ($paths as $path => $desc) {
    $fullPath = __DIR__ . '/' . $path;
    $exists = file_exists($fullPath);
    $writable = $exists && is_writable($fullPath);
    
    $status = '';
    if (!$exists) {
        $status = "<span class='error'>✗ Missing</span>";
    } elseif (!$writable && is_dir($fullPath)) {
        $status = "<span class='warning'>⚠ Not writable</span>";
    } else {
        $status = "<span class='success'>✓ OK</span>";
    }
    
    echo "<tr><td>{$desc}:</td><td>{$status} <span class='info'>({$path})</span></td></tr>";
}

echo "</table>
</div>";

// 4. Database Check
echo "<div class='section'>
    <h2>4. Database Connection</h2>";

try {
    require_once __DIR__ . '/config/database.php';
    $db = getDB();
    echo "<p class='success'>✓ Database connection successful</p>";
    
    // Check tables
    $tables = ['users', 'conversations', 'site_settings'];
    echo "<table>";
    foreach ($tables as $table) {
        $exists = $db->tableExists($table);
        echo "<tr><td>{$table} table:</td><td>" . ($exists ? "<span class='success'>✓ Exists</span>" : "<span class='warning'>⚠ Missing (will be created)</span>") . "</td></tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// 5. Config Check
echo "<div class='section'>
    <h2>5. Configuration</h2>";

try {
    require_once __DIR__ . '/config/app.php';
    echo "<table>
        <tr><td>APP_NAME:</td><td>" . (defined('APP_NAME') ? APP_NAME : "<span class='error'>Not defined</span>") . "</td></tr>
        <tr><td>APP_URL:</td><td>" . (defined('APP_URL') ? APP_URL : "<span class='error'>Not defined</span>") . "</td></tr>
        <tr><td>Environment:</td><td>" . (strpos(APP_URL, 'localhost') !== false ? "<span class='info'>Development</span>" : "<span class='info'>Production</span>") . "</td></tr>
        <tr><td>Error Logging:</td><td>" . (defined('ERROR_LOG_FILE') ? "<span class='success'>✓ Enabled</span>" : "<span class='warning'>⚠ Not configured</span>") . "</td></tr>
    </table>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Config error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// 6. Test Error Logging
echo "<div class='section'>
    <h2>6. Error Logging Test</h2>";

try {
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $testLogFile = $logDir . '/test.log';
    $testMessage = "Test log entry - " . date('Y-m-d H:i:s');
    file_put_contents($testLogFile, $testMessage . PHP_EOL, FILE_APPEND);
    
    if (file_exists($testLogFile)) {
        echo "<p class='success'>✓ Log writing successful</p>";
        echo "<p class='info'>Test log created at: logs/test.log</p>";
    } else {
        echo "<p class='error'>✗ Could not create log file</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Logging test failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// 7. Test Index Page
echo "<div class='section'>
    <h2>7. Index Page Test</h2>";

try {
    ob_start();
    include __DIR__ . '/index.php';
    $output = ob_get_clean();
    
    if (strlen($output) > 0) {
        echo "<p class='success'>✓ Index page loads successfully</p>";
        echo "<p class='info'>Output length: " . strlen($output) . " bytes</p>";
    } else {
        echo "<p class='warning'>⚠ Index page loaded but produced no output</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Index page error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<div class='code'>" . htmlspecialchars($e->getTraceAsString()) . "</div>";
}

echo "</div>";

// 8. Recent Errors
echo "<div class='section'>
    <h2>8. Recent Errors</h2>";

$errorLog = __DIR__ . '/logs/error.log';
if (file_exists($errorLog) && filesize($errorLog) > 0) {
    $errors = file_get_contents($errorLog);
    $lines = explode("\n", $errors);
    $recentErrors = array_slice($lines, -20);
    
    echo "<p class='warning'>Found " . count($lines) . " log entries. Showing last 20:</p>";
    echo "<div class='code'>" . htmlspecialchars(implode("\n", $recentErrors)) . "</div>";
} else {
    echo "<p class='success'>✓ No errors logged yet</p>";
}

echo "</div>";

// Actions
echo "<div class='section'>
    <h2>Next Steps</h2>
    <p>Use these tools to continue diagnostics:</p>
    <a href='view-error-log.php' class='btn'>View Error Log</a>
    <a href='index.php' class='btn'>Test Homepage</a>
    <a href='login.php' class='btn'>Test Login Page</a>
    <a href='diagnose-local.php' class='btn'>Refresh Diagnostics</a>
</div>";

echo "</div>
</body>
</html>";
?>
