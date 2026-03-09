<?php
/**
 * Test Resend Verification Functionality
 */

require_once 'config/app.php';
require_once 'includes/auth.php';

// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Resend Verification Test</h1>";
echo "<pre>";

// Test email to use
$testEmail = "test@example.com";

echo "=== Testing Resend Verification ===\n";
echo "Test Email: $testEmail\n\n";

// Check if PHPMailer is available
echo "1. Checking PHPMailer installation...\n";
if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    echo "   ✓ PHPMailer is installed\n\n";
} else {
    echo "   ✗ PHPMailer is NOT installed\n";
    echo "   Run: composer install OR visit install-phpmailer.php\n\n";
}

// Check SMTP configuration
echo "2. Checking SMTP configuration...\n";
echo "   Host: " . SMTP_HOST . "\n";
echo "   Port: " . SMTP_PORT . "\n";
echo "   Username: " . SMTP_USERNAME . "\n";
echo "   Password: " . (SMTP_PASSWORD ? "Set (" . strlen(SMTP_PASSWORD) . " chars)" : "NOT SET") . "\n";
echo "   Encryption: " . SMTP_ENCRYPTION . "\n\n";

// Check database connection
echo "3. Checking database connection...\n";
try {
    $db = getDB();
    echo "   ✓ Database connected\n\n";
    
    // Check for pending verifications
    echo "4. Checking for pending verifications...\n";
    $sql = "SELECT email, first_name, created_at, expires_at FROM pending_verifications WHERE expires_at > NOW() LIMIT 5";
    $pending = $db->fetchAll($sql);
    
    if ($pending) {
        echo "   Found " . count($pending) . " pending verification(s):\n";
        foreach ($pending as $p) {
            echo "   - " . $p['email'] . " (created: " . $p['created_at'] . ")\n";
        }
    } else {
        echo "   No pending verifications found\n";
    }
    echo "\n";
    
} catch (Exception $e) {
    echo "   ✗ Database error: " . $e->getMessage() . "\n\n";
}

// Test the resend function
echo "5. Testing resend verification function...\n";
try {
    $auth = getAuth();
    
    // Check if test email has pending verification
    $sql = "SELECT * FROM pending_verifications WHERE email = ? AND expires_at > NOW()";
    $pending = $db->fetch($sql, [$testEmail]);
    
    if ($pending) {
        echo "   Found pending verification for $testEmail\n";
        echo "   Attempting to resend...\n";
        
        $result = $auth->resendVerification($testEmail);
        
        if ($result['success']) {
            echo "   ✓ SUCCESS: " . $result['message'] . "\n";
            if (isset($result['verification_link'])) {
                echo "   Verification Link: " . $result['verification_link'] . "\n";
            }
        } else {
            echo "   ✗ FAILED: " . $result['error'] . "\n";
        }
    } else {
        echo "   No pending verification found for $testEmail\n";
        echo "   To test, first register with this email address\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Exception: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n";
echo "=== Test Complete ===\n";
echo "Check logs/" . date('Y-m-d') . ".log for detailed error messages\n";

echo "</pre>";

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>If PHPMailer is not installed, visit <a href='install-phpmailer.php'>install-phpmailer.php</a></li>";
echo "<li>Test SMTP connection at <a href='test-email.php'>test-email.php</a></li>";
echo "<li>Try registering a new account to test the full flow</li>";
echo "<li>Check the logs directory for detailed error messages</li>";
echo "</ol>";
?>
