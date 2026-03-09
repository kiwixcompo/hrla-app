<?php
/**
 * Email Testing Script
 * Test SMTP configuration and email sending
 */

require_once 'config/app.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Email Configuration Test</h1>";
echo "<pre>";

echo "=== SMTP Configuration ===\n";
echo "SMTP Host: " . SMTP_HOST . "\n";
echo "SMTP Port: " . SMTP_PORT . "\n";
echo "SMTP Username: " . SMTP_USERNAME . "\n";
echo "SMTP Password: " . (SMTP_PASSWORD ? str_repeat('*', strlen(SMTP_PASSWORD)) : 'NOT SET') . "\n";
echo "SMTP Encryption: " . SMTP_ENCRYPTION . "\n";
echo "\n";

echo "=== Testing Native PHP mail() ===\n";
$to = "test@example.com";
$subject = "Test Email from HR Leave Assistant";
$message = "This is a test email.";
$headers = "From: " . APP_EMAIL . "\r\n";
$headers .= "Reply-To: " . APP_SUPPORT_EMAIL . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$result = @mail($to, $subject, $message, $headers);
if ($result) {
    echo "✓ mail() function returned TRUE\n";
} else {
    echo "✗ mail() function returned FALSE\n";
    $error = error_get_last();
    if ($error) {
        echo "Error: " . $error['message'] . "\n";
    }
}
echo "\n";

echo "=== Testing SMTP Connection ===\n";
$socket = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);
if ($socket) {
    echo "✓ Successfully connected to " . SMTP_HOST . ":" . SMTP_PORT . "\n";
    $response = fgets($socket);
    echo "Server response: " . $response;
    fclose($socket);
} else {
    echo "✗ Failed to connect to " . SMTP_HOST . ":" . SMTP_PORT . "\n";
    echo "Error: $errstr ($errno)\n";
}
echo "\n";

echo "=== Checking for PHPMailer ===\n";
if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    echo "✓ PHPMailer is installed\n";
} else {
    echo "✗ PHPMailer is NOT installed\n";
    echo "To install PHPMailer, run: composer require phpmailer/phpmailer\n";
}
echo "\n";

echo "=== Server Information ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "OS: " . PHP_OS . "\n";
echo "sendmail_path: " . ini_get('sendmail_path') . "\n";
echo "SMTP (php.ini): " . ini_get('SMTP') . "\n";
echo "smtp_port (php.ini): " . ini_get('smtp_port') . "\n";

echo "</pre>";

echo "<h2>Recommendation</h2>";
echo "<p>Since native mail() is not working, you need to install PHPMailer for SMTP support.</p>";
echo "<p>Run this command in your project directory:</p>";
echo "<code style='background: #f0f0f0; padding: 10px; display: block;'>composer require phpmailer/phpmailer</code>";
?>
