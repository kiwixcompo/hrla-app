<?php
/**
 * SMTP Connection Test
 * DELETE after use.
 */
$secret = $_GET['key'] ?? '';
if ($secret !== 'hrla-smtp-test') {
    http_response_code(403);
    die('Access denied. Use ?key=hrla-smtp-test');
}

require_once 'config/app.php';

$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    die('❌ vendor/autoload.php not found. Run: composer install');
}
require_once $autoload;

if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    die('❌ PHPMailer class not found after autoload.');
}

header('Content-Type: text/plain');

echo "SMTP Settings in use:\n";
echo "  Host:     " . SMTP_HOST . "\n";
echo "  Port:     " . SMTP_PORT . "\n";
echo "  Username: " . SMTP_USERNAME . "\n";
echo "  Password: " . (SMTP_PASSWORD ? str_repeat('*', strlen(SMTP_PASSWORD)) : '(empty!)') . "\n\n";

$sendTo = $_GET['to'] ?? '';
if (empty($sendTo)) {
    die("Add ?to=your@email.com to also send a test email.\nOtherwise the credentials check above is all you get.\n");
}

try {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ],
    ];
    $mail->SMTPDebug  = 2; // verbose output
    $mail->Debugoutput = function($str, $level) { echo $str . "\n"; };

    $mail->setFrom(SMTP_USERNAME, 'HR Leave Assistant');
    $mail->addAddress($sendTo);
    $mail->Subject = 'SMTP Test - HR Leave Assistant';
    $mail->Body    = 'This is a test email to confirm SMTP is working correctly.';

    $mail->send();
    echo "\n✅ Email sent successfully to $sendTo\n";
} catch (\PHPMailer\PHPMailer\Exception $e) {
    echo "\n❌ Failed: " . $e->getMessage() . "\n";
}
?>
