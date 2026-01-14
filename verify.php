<?php
/**
 * Email Verification Page
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'includes/auth.php';

$auth = getAuth();
$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$verified = false;

if (empty($token)) {
    $error = 'Missing verification token. Please check your email for the correct link.';
} else {
    $result = $auth->verifyEmail($token);
    
    if ($result['success']) {
        $success = $result['message'];
        $verified = true;
        
        // Auto-redirect to login after 3 seconds
        header("refresh:3;url=" . appUrl('login.php?verified=true'));
    } else {
        $error = $result['error'];
    }
}

$pageTitle = 'Email Verification - HR Leave Assistant';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
    <link rel="icon" type="image/svg+xml" href="<?php echo asset('images/favicon.svg'); ?>">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo">
                    <h1>🏛️ HR Leave Assistant</h1>
                    <p>Professional HR Compliance Tool</p>
                </div>
            </div>
            
            <div class="auth-content">
                <?php if ($verified): ?>
                    <!-- Success Message -->
                    <div class="verification-success">
                        <div class="success-icon">✅</div>
                        <h2>Email Verified Successfully!</h2>
                        
                        <div class="alert alert-success">
                            <span class="alert-icon">🎉</span>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                        
                        <div class="verification-next-steps">
                            <h3>What's Next?</h3>
                            <ul>
                                <li>Your account is now active</li>
                                <li>Your 24-hour free trial has started</li>
                                <li>You can now sign in and start using HR Leave Assistant</li>
                            </ul>
                        </div>
                        
                        <div class="auto-redirect">
                            <p>You will be automatically redirected to the sign-in page in <span id="countdown">3</span> seconds...</p>
                        </div>
                        
                        <div class="verification-actions">
                            <a href="<?php echo appUrl('login.php?verified=true'); ?>" class="btn btn-primary btn-full">
                                Continue to Sign In
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Error Message -->
                    <div class="verification-error">
                        <div class="error-icon">❌</div>
                        <h2>Verification Failed</h2>
                        
                        <div class="alert alert-error">
                            <span class="alert-icon">⚠️</span>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                        
                        <div class="verification-help">
                            <h3>What can you do?</h3>
                            <ul>
                                <li>Check if you clicked the correct link from your email</li>
                                <li>Make sure the verification link hasn't expired (valid for 24 hours)</li>
                                <li>Try registering again if the link is too old</li>
                                <li>Contact support if you continue having issues</li>
                            </ul>
                        </div>
                        
                        <div class="verification-actions">
                            <a href="<?php echo appUrl('register.php'); ?>" class="btn btn-primary">
                                Try Registration Again
                            </a>
                            <a href="<?php echo appUrl('login.php'); ?>" class="btn btn-outline">
                                Go to Sign In
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="auth-footer">
                    <p>
                        Need help? Contact us at 
                        <a href="mailto:<?php echo config('app.support_email'); ?>">
                            <?php echo config('app.support_email'); ?>
                        </a>
                    </p>
                    <p>
                        <a href="<?php echo appUrl(); ?>">← Back to Home</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Countdown timer for auto-redirect
            const countdownElement = document.getElementById('countdown');
            if (countdownElement) {
                let seconds = 3;
                
                const timer = setInterval(function() {
                    seconds--;
                    countdownElement.textContent = seconds;
                    
                    if (seconds <= 0) {
                        clearInterval(timer);
                        window.location.href = '<?php echo appUrl('login.php?verified=true'); ?>';
                    }
                }, 1000);
            }
        });
    </script>
</body>
</html>