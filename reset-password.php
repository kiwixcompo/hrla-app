<?php
/**
 * Reset Password Page
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'includes/auth.php';

$auth = getAuth();
$error = '';
$success = '';
$token = $_GET['token'] ?? '';

// Redirect if already logged in
if ($auth->isAuthenticated()) {
    redirect(appUrl('dashboard.php'));
}

// Validate token
if (empty($token)) {
    $error = 'Invalid password reset link. Please request a new password reset.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $newPassword = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($newPassword)) {
            $error = 'Please enter a new password.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            $result = $auth->resetPassword($token, $newPassword);
            
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['error'];
            }
        }
    }
}

$pageTitle = 'Reset Password - HRLA | HR Leave Assist';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Cache Control -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Create a new password for your HR Leave Assistant account.">
    <meta name="keywords" content="reset password, new password, HR Leave Assistant">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="hrla-logo-new-fixed.svg">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="styles.css?v=1.2">
    <link rel="stylesheet" href="assets/css/main.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- Navigation -->
    <nav class="landing-nav" role="navigation" aria-label="Main navigation">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="<?php echo appUrl('index.php'); ?>">
                    <div class="nav-logo-symbol">
                        <span class="nav-hr">HR</span><span class="nav-la">LA</span>
                    </div>
                </a>
            </div>
            <div class="nav-menu" role="menubar">
                <a href="quick-start.php" class="nav-link" role="menuitem">Quick Start Guide</a>
                <a href="<?php echo appUrl('index.php#how-it-works'); ?>" class="nav-link" role="menuitem">How it Works</a>
                <a href="pricing.php" class="nav-link" role="menuitem">Pricing</a>
                <a href="<?php echo appUrl('index.php#faqs'); ?>" class="nav-link" role="menuitem">FAQs</a>
                <a href="<?php echo appUrl('index.php#contact'); ?>" class="nav-link" role="menuitem">Contact</a>
                <a href="<?php echo appUrl('login.php'); ?>" class="btn btn-outline">Sign In</a>
                <a href="<?php echo appUrl('register.php'); ?>" class="btn btn-primary">GET STARTED FOR FREE</a>
            </div>
            <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle mobile menu" aria-expanded="false">
                <span class="hamburger-icon">≡</span>
            </button>
        </div>
    </nav>

    <div id="resetPasswordPage" class="page">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h2>Create New Password</h2>
                    <p>Enter your new password below.</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-error" style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border-left: 4px solid #dc2626;">
                        <span class="alert-icon">❌</span>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php if (strpos($error, 'Invalid') !== false || strpos($error, 'expired') !== false): ?>
                        <div class="auth-footer">
                            <p><a href="<?php echo appUrl('forgot-password.php'); ?>">Request a new password reset</a></p>
                            <p><a href="<?php echo appUrl('login.php'); ?>">← Back to Login</a></p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success" style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border-left: 4px solid #10b981;">
                        <span class="alert-icon">✅</span>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                    <div class="auth-footer">
                        <p><a href="<?php echo appUrl('login.php'); ?>" class="btn btn-primary btn-block">Sign In Now</a></p>
                        <p><a href="<?php echo appUrl('index.php'); ?>">← Back to Homepage</a></p>
                    </div>
                <?php elseif (!$error || (strpos($error, 'Invalid') === false && strpos($error, 'expired') === false)): ?>
                    <form method="POST" class="auth-form" id="resetPasswordForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                minlength="8"
                                autocomplete="new-password"
                                placeholder="Enter your new password"
                            >
                            <small class="form-text">Password must be at least 8 characters long</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                required 
                                minlength="8"
                                autocomplete="new-password"
                                placeholder="Confirm your new password"
                            >
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block" id="resetBtn">
                            Update Password
                        </button>
                    </form>
                    
                    <div class="auth-footer">
                        <p><a href="<?php echo appUrl('login.php'); ?>">← Back to Login</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="assets/js/mobile-menu.js"></script>
    <script>
        // Password confirmation validation
        document.getElementById('confirm_password')?.addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>