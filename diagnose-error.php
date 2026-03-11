<?php
/**
 * Error Diagnostic Script
 * This will show you the exact error causing the 500 error
 */

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>HRLA Error Diagnostic</h1>";
echo "<pre>";

echo "=== PHP Information ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Current Directory: " . __DIR__ . "\n\n";

echo "=== Checking Required Files ===\n";

$requiredFiles = [
    'config/app.php',
    'config/database.php',
    'config/local.php',
    'includes/auth.php',
    'index.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✓ $file exists\n";
    } else {
        echo "✗ $file MISSING\n";
    }
}

echo "\n=== Checking PHP Extensions ===\n";
$requiredExtensions = ['pdo', 'pdo_mysql', 'mysqli', 'json', 'mbstring', 'curl'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ $ext loaded\n";
    } else {
        echo "✗ $ext NOT loaded\n";
    }
}

echo "\n=== Testing config/app.php ===\n";
try {
    require_once 'config/app.php';
    echo "✓ config/app.php loaded successfully\n";
    echo "  APP_URL: " . (defined('APP_URL') ? APP_URL : 'NOT DEFINED') . "\n";
    echo "  APP_ENV: " . (defined('APP_ENV') ? APP_ENV : 'NOT DEFINED') . "\n";
} catch (Exception $e) {
    echo "✗ ERROR in config/app.php:\n";
    echo "  " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
}

echo "\n=== Testing Database Connection ===\n";
try {
    if (function_exists('getDB')) {
        $db = getDB();
        echo "✓ Database connection successful\n";
    } else {
        echo "✗ getDB() function not found\n";
    }
} catch (Exception $e) {
    echo "✗ Database connection failed:\n";
    echo "  " . $e->getMessage() . "\n";
}

echo "\n=== Testing index.php ===\n";
ob_start();
try {
    // Don't actually include it, just check for syntax errors
    $indexContent = file_get_contents('index.php');
    if ($indexContent === false) {
        echo "✗ Cannot read index.php\n";
    } else {
        echo "✓ index.php is readable (" . strlen($indexContent) . " bytes)\n";
        
        // Check for common issues
        if (strpos($indexContent, '<?php') === false) {
            echo "✗ WARNING: index.php doesn't start with <?php\n";
        }
        
        // Try to check syntax
        $result = exec('php -l index.php 2>&1', $output, $return);
        if ($return === 0) {
            echo "✓ index.php syntax is valid\n";
        } else {
            echo "✗ index.php has syntax errors:\n";
            echo "  " . implode("\n  ", $output) . "\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error checking index.php:\n";
    echo "  " . $e->getMessage() . "\n";
}
ob_end_clean();

echo "\n=== Checking File Permissions ===\n";
$checkFiles = ['index.php', 'config/app.php', 'config/local.php'];
foreach ($checkFiles as $file) {
    if (file_exists($file)) {
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        echo "$file: $perms ";
        if (is_readable($file)) {
            echo "(readable) ";
        } else {
            echo "(NOT readable) ";
        }
        if (is_writable($file)) {
            echo "(writable)\n";
        } else {
            echo "(not writable)\n";
        }
    }
}

echo "\n=== Checking .htaccess ===\n";
if (file_exists('.htaccess')) {
    echo "✓ .htaccess exists\n";
    echo "Content:\n";
    echo file_get_contents('.htaccess');
} else {
    echo "✗ .htaccess does NOT exist\n";
}

echo "\n=== Checking Error Log ===\n";
$errorLogLocations = [
    'error_log',
    '../error_log',
    'logs/error.log',
    '/home/hrledkhw/public_html/error_log'
];

foreach ($errorLogLocations as $logFile) {
    if (file_exists($logFile)) {
        echo "Found error log: $logFile\n";
        echo "Last 20 lines:\n";
        $lines = file($logFile);
        $lastLines = array_slice($lines, -20);
        echo implode('', $lastLines);
        break;
    }
}

echo "\n=== DIAGNOSIS COMPLETE ===\n";
echo "If you see errors above, those are causing the 500 error.\n";
echo "Share this output to get help fixing the issue.\n";

echo "</pre>";
?>
