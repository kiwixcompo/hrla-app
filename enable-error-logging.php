<?php
/**
 * Enable Error Logging
 * Run this once to enable comprehensive error logging
 */

// Create logs directory if it doesn't exist
if (!is_dir('logs')) {
    mkdir('logs', 0777, true);
    echo "✓ Created logs/ directory<br>";
}

// Create error_log file
$errorLogFile = 'logs/error_log';
if (!file_exists($errorLogFile)) {
    file_put_contents($errorLogFile, "# Error Log Started: " . date('Y-m-d H:i:s') . "\n");
    chmod($errorLogFile, 0666);
    echo "✓ Created logs/error_log file<br>";
} else {
    echo "✓ logs/error_log already exists<br>";
}

// Create .htaccess in root to enable error logging
$htaccessContent = file_get_contents('.htaccess');

// Check if PHP error logging is already configured
if (strpos($htaccessContent, 'php_value error_log') === false) {
    // Add PHP error logging configuration
    $errorConfig = "\n# Enable PHP Error Logging\n";
    $errorConfig .= "<IfModule mod_php7.c>\n";
    $errorConfig .= "    php_flag display_errors Off\n";
    $errorConfig .= "    php_flag log_errors On\n";
    $errorConfig .= "    php_value error_log " . __DIR__ . "/logs/error_log\n";
    $errorConfig .= "    php_value error_reporting 32767\n";
    $errorConfig .= "</IfModule>\n\n";
    $errorConfig .= "<IfModule mod_php.c>\n";
    $errorConfig .= "    php_flag display_errors Off\n";
    $errorConfig .= "    php_flag log_errors On\n";
    $errorConfig .= "    php_value error_log " . __DIR__ . "/logs/error_log\n";
    $errorConfig .= "    php_value error_reporting 32767\n";
    $errorConfig .= "</IfModule>\n";
    
    // Add to .htaccess
    file_put_contents('.htaccess', $htaccessContent . $errorConfig);
    echo "✓ Updated .htaccess with error logging configuration<br>";
} else {
    echo "✓ Error logging already configured in .htaccess<br>";
}

// Create a php.ini file for additional error logging
$phpIniContent = "; Custom PHP Configuration for Error Logging
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = " . __DIR__ . "/logs/error_log
error_reporting = E_ALL
";

file_put_contents('php.ini', $phpIniContent);
echo "✓ Created php.ini with error logging settings<br>";

// Test error logging
error_log("Test error log entry from enable-error-logging.php - " . date('Y-m-d H:i:s'));
echo "✓ Test error logged<br>";

echo "<br><hr><br>";
echo "<h2>Error Logging Enabled!</h2>";
echo "<p>Errors will now be logged to:</p>";
echo "<ul>";
echo "<li><strong>logs/error_log</strong> - PHP errors</li>";
echo "<li><strong>logs/error.log</strong> - Application errors (via error handler)</li>";
echo "</ul>";

echo "<h3>View Error Logs:</h3>";
echo "<p><a href='view-error-log.php'>View Application Error Log</a></p>";
echo "<p><a href='view-php-errors.php'>View PHP Error Log</a> (create this file)</p>";

echo "<h3>Check if it's working:</h3>";
echo "<p>1. Visit any page on your site</p>";
echo "<p>2. Check logs/error_log for any errors</p>";
echo "<p>3. If errors occur, they will be logged automatically</p>";

echo "<h3>Files Created/Modified:</h3>";
echo "<ul>";
echo "<li>logs/error_log - PHP error log file</li>";
echo "<li>php.ini - PHP configuration</li>";
echo "<li>.htaccess - Updated with error logging directives</li>";
echo "</ul>";
?>
