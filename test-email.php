<?php
/**
 * Simple Email Test Script for Namecheap Hosting
 * Visit: https://www.hrleaveassist.com/test-email.php
 * 
 * This script tests if your email configuration is working
 * before setting up the Node.js application.
 */

// Configuration
$from_email = 'askhrla@hrleaveassist.com';
$from_name = 'HR Leave Assistant';
$test_recipient = 'talk2char@gmail.com'; // Change this to your test email

// Get test email from URL parameter if provided
if (isset($_GET['email']) && filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) {
    $test_recipient = $_GET['email'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Test - HR Leave Assistant</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #0023F5 0%, #0322D8 100%); color: white; padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .status { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="email"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #0023F5; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0322D8; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏛️ HR Leave Assistant</h1>
        <p>Email Configuration Test</p>
    </div>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <?php
        $recipient = $_POST['email'];
        
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            echo '<div class="status error">❌ Invalid email address provided.</div>';
        } else {
            // Email content
            $subject = 'HR Leave Assistant - Email Configuration Test';
            $message = "
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Email Test Successful</title>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f8f9fa; }
                    .container { max-width: 600px; margin: 20px auto; padding: 0; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    .header { background: linear-gradient(135deg, #0023F5 0%, #0322D8 100%); color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0; }
                    .content { background: #ffffff; padding: 30px 20px; border-radius: 0 0 8px 8px; }
                    .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 14px; color: #666666; }
                    .success-box { background: #d4edda; color: #155724; padding: 20px; border-radius: 5px; border-left: 4px solid #28a745; margin: 20px 0; }
                    .info-box { background: #e3f2fd; padding: 15px; border-radius: 5px; margin-top: 20px; border-left: 4px solid #0023F5; }
                    .company-info { margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1 style='margin: 0; font-size: 24px;'>🏛️ HR Leave Assistant</h1>
                        <p style='margin: 10px 0 0 0; font-size: 16px;'>Email Configuration Test</p>
                    </div>
                    <div class='content'>
                        <div class='success-box'>
                            <h2 style='margin: 0 0 10px 0; color: #155724;'>✅ Email Test Successful!</h2>
                            <p style='margin: 0;'>Your email configuration is working correctly and this message was delivered successfully.</p>
                        </div>
                        
                        <h3 style='color: #333; margin-top: 30px;'>Test Details:</h3>
                        <ul style='color: #555;'>
                            <li><strong>From:</strong> {$from_email}</li>
                            <li><strong>Test Time:</strong> " . date('Y-m-d H:i:s T') . "</li>
                            <li><strong>Server:</strong> " . $_SERVER['HTTP_HOST'] . "</li>
                            <li><strong>PHP Version:</strong> " . phpversion() . "</li>
                        </ul>
                        
                        <div class='info-box'>
                            <h4 style='margin: 0 0 10px 0; color: #0c5460;'>Next Steps for Production:</h4>
                            <ol style='margin: 10px 0 0 20px; color: #0c5460;'>
                                <li>Set EMAIL_PASS environment variable in cPanel</li>
                                <li>Deploy your Node.js application</li>
                                <li>Test user registration with email verification</li>
                                <li>Monitor email delivery and spam folder placement</li>
                            </ol>
                        </div>
                        
                        <div class='company-info'>
                            <p style='margin: 0;'><strong>About HR Leave Assistant:</strong></p>
                            <p style='margin: 5px 0 0 0;'>Professional HR compliance tools for managing Federal FMLA and California leave laws. Ensuring accurate, compliant responses to employee leave requests.</p>
                        </div>
                    </div>
                    <div class='footer'>
                        <p style='margin: 0;'>This is a test email from HR Leave Assistant</p>
                        <p style='margin: 5px 0;'><a href='https://www.hrleaveassist.com' style='color: #0023F5;'>www.hrleaveassist.com</a></p>
                        <p style='margin: 10px 0 0 0; font-size: 12px;'>© <?php echo date('Y'); ?> HR Leave Assistant. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            // Plain text version
            $text_message = "
HR Leave Assistant - Email Configuration Test

EMAIL TEST SUCCESSFUL!

Your email configuration is working correctly and this message was delivered successfully.

Test Details:
- From: {$from_email}
- Test Time: " . date('Y-m-d H:i:s T') . "
- Server: " . $_SERVER['HTTP_HOST'] . "
- PHP Version: " . phpversion() . "

Next Steps for Production:
1. Set EMAIL_PASS environment variable in cPanel
2. Deploy your Node.js application  
3. Test user registration with email verification
4. Monitor email delivery and spam folder placement

About HR Leave Assistant:
Professional HR compliance tools for managing Federal FMLA and California leave laws. 
Ensuring accurate, compliant responses to employee leave requests.

---
This is a test email from HR Leave Assistant
Website: https://www.hrleaveassist.com
© " . date('Y') . " HR Leave Assistant. All rights reserved.
            ";
            
            // Anti-spam email headers
            $headers = array(
                'MIME-Version' => '1.0',
                'Content-Type' => 'text/html; charset=UTF-8',
                'From' => "HR Leave Assistant <{$from_email}>",
                'Reply-To' => $from_email,
                'Return-Path' => $from_email,
                'X-Mailer' => 'HR Leave Assistant v1.0',
                'X-Priority' => '3',
                'X-MSMail-Priority' => 'Normal',
                'Importance' => 'Normal',
                'List-Unsubscribe' => "<mailto:{$from_email}?subject=Unsubscribe>",
                'X-Auto-Response-Suppress' => 'OOF, DR, RN, NRN, AutoReply',
                'Message-ID' => '<' . time() . '.' . uniqid() . '@hrleaveassist.com>',
                'Date' => date('r'),
                'Content-Language' => 'en-US',
                'Organization' => 'HR Leave Assistant',
                'X-Sender' => $from_email,
                'X-Originating-IP' => '[' . ($_SERVER['SERVER_ADDR'] ?? '127.0.0.1') . ']',
                'X-Source-Dir' => 'hrleaveassist.com'
            );
            
            // Convert headers array to string
            $header_string = '';
            foreach ($headers as $key => $value) {
                $header_string .= "{$key}: {$value}\r\n";
            }
            
            // Send email
            $sent = mail($recipient, $subject, $message, $header_string);
            
            if ($sent) {
                echo '<div class="status success">✅ <strong>Email sent successfully!</strong><br>';
                echo "Test email sent to: <strong>{$recipient}</strong><br>";
                echo "From: <strong>{$from_email}</strong><br>";
                echo "Time: " . date('Y-m-d H:i:s T') . "</div>";
                
                echo '<div class="status info">📧 <strong>Check your inbox!</strong><br>';
                echo "The test email should arrive within a few minutes. Don't forget to check your spam folder.</div>";
            } else {
                echo '<div class="status error">❌ <strong>Failed to send email.</strong><br>';
                echo "This could be due to:<br>";
                echo "• Email account not properly configured in cPanel<br>";
                echo "• SMTP restrictions on your hosting<br>";
                echo "• Invalid sender email address<br>";
                echo "• Server mail function disabled</div>";
                
                echo '<div class="status info">💡 <strong>Troubleshooting:</strong><br>';
                echo "1. Verify askhrla@hrleaveassist.com exists in cPanel Email Accounts<br>";
                echo "2. Check cPanel Email Deliverability settings<br>";
                echo "3. Contact Namecheap support if issues persist</div>";
            }
        }
        ?>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="email">Test Email Address:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($test_recipient); ?>" required>
            <small>Enter the email address where you want to receive the test email</small>
        </div>
        
        <button type="submit">📧 Send Test Email</button>
    </form>

    <div class="status info">
        <h3>📋 Email Configuration Checklist:</h3>
        <ul>
            <li><strong>Email Account:</strong> askhrla@hrleaveassist.com should exist in cPanel</li>
            <li><strong>SMTP Settings:</strong> mail.hrleaveassist.com (port 587 or 465)</li>
            <li><strong>Environment Variable:</strong> EMAIL_PASS should be set with email password</li>
            <li><strong>Domain:</strong> www.hrleaveassist.com should be properly configured</li>
        </ul>
    </div>

    <div class="status info">
        <h3>🔧 Server Information:</h3>
        <div class="code">
            <strong>Server:</strong> <?php echo $_SERVER['HTTP_HOST']; ?><br>
            <strong>PHP Version:</strong> <?php echo phpversion(); ?><br>
            <strong>Mail Function:</strong> <?php echo function_exists('mail') ? '✅ Available' : '❌ Not Available'; ?><br>
            <strong>Test URL:</strong> https://www.hrleaveassist.com/test-email.php<br>
            <strong>From Email:</strong> <?php echo $from_email; ?>
        </div>
    </div>

    <div class="status info">
        <h3>🚀 Next Steps:</h3>
        <ol>
            <li><strong>Test this email functionality</strong> by sending a test email above</li>
            <li><strong>Set EMAIL_PASS environment variable</strong> in cPanel with your email password</li>
            <li><strong>Deploy your Node.js application</strong> with the updated email configuration</li>
            <li><strong>Test user registration</strong> to ensure verification emails work</li>
            <li><strong>Remove this test file</strong> after confirming everything works</li>
        </ol>
    </div>

</body>
</html>