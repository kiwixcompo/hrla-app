<?php
/**
 * Test Error Logging System
 * This script tests if error logging is working correctly
 */

require_once 'config/app.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Test Error Logging</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { color: #0322D8; }
        .test { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #0322D8; }
        .success { color: #3DB20B; font-weight: bold; }
        .error { color: #d32f2f; font-weight: bold; }
        .btn { display: inline-block; padding: 10px 20px; background: #0322D8; color: white; text-decoration: none; border-radius: 4px; margin: 10px 5px 0 0; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🧪 Error Logging Test</h1>";

// Test 1: Check if error handler is loaded
echo "<div class='test'>
    <h3>Test 1: Error Handler Loaded</h3>";
if (function_exists('logError')) {
    echo "<p class='success'>✓ Error handler is loaded</p>";
} else {
    echo "<p class='error'>✗ Error handler not loaded</p>";
}
echo "</div>";

// Test 2: Check if log directory exists
echo "<div class='test'>
    <h3>Test 2: Log Directory</h3>";
$logDir = __DIR__ . '/logs';
if (is_dir($logDir)) {
    echo "<p class='success'>✓ Log directory exists</p>";
    if (is_writable($logDir)) {
        echo "<p class='success'>✓ Log directory is writable</p>";
    } else {
        echo "<p class='error'>✗ Log directory is not writable</p>";
    }
} else {
    echo "<p class='error'>✗ Log directory does not exist</p>";
}
echo "</div>";

// Test 3: Test custom error logging
echo "<div class='test'>
    <h3>Test 3: Custom Error Logging</h3>";
try {
    logCustomError('This is a test error message from test-error-logging.php');
    echo "<p class='success'>✓ Custom error logged successfully</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Failed to log custom error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Test 4: Test warning
echo "<div class='test'>
    <h3>Test 4: PHP Warning</h3>";
try {
    // Trigger a warning (accessing undefined variable)
    @$undefinedVar = $thisVariableDoesNotExist;
    trigger_error('This is a test warning', E_USER_WARNING);
    echo "<p class='success'>✓ Warning triggered (check error log)</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Test 5: Test notice
echo "<div class='test'>
    <h3>Test 5: PHP Notice</h3>";
try {
    trigger_error('This is a test notice', E_USER_NOTICE);
    echo "<p class='success'>✓ Notice triggered (check error log)</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Test 6: Check log file
echo "<div class='test'>
    <h3>Test 6: Log File Contents</h3>";
$logFile = __DIR__ . '/logs/error.log';
if (file_exists($logFile)) {
    $logSize = filesize($logFile);
    echo "<p class='success'>✓ Log file exists</p>";
    echo "<p>File size: " . number_format($logSize) . " bytes</p>";
    
    if ($logSize > 0) {
        echo "<p class='success'>✓ Log file has content</p>";
        $lastLines = file($logFile);
        $recentLines = array_slice($lastLines, -5);
        echo "<p><strong>Last 5 lines:</strong></p>";
        echo "<pre style='background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; overflow-x: auto;'>";
        echo htmlspecialchars(implode('', $recentLines));
        echo "</pre>";
    } else {
        echo "<p class='error'>⚠ Log file is empty</p>";
    }
} else {
    echo "<p class='error'>✗ Log file does not exist yet</p>";
}
echo "</div>";

// Summary
echo "<div class='test'>
    <h3>Summary</h3>
    <p>If all tests passed, your error logging system is working correctly!</p>
    <p>Any errors that occur in your application will now be logged to <code>logs/error.log</code></p>
    <a href='view-error-log.php' class='btn'>View Error Log</a>
    <a href='diagnose-local.php' class='btn'>Run Diagnostics</a>
    <a href='index.php' class='btn'>Go to Homepage</a>
</div>";

echo "</div>
</body>
</html>";
?>
