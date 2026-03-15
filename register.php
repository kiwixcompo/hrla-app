<?php
/**
 * Registration Page
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'includes/auth.php';

$auth = getAuth();

// Redirect if already logged in
if ($auth->isAuthenticated()) {
    redirect(appUrl('dashboard.php'));
}

$error = '';
$success = '';
$showVerificationMessage = false;
$registeredEmail = '';
$pendingEmail = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $firstName = sanitize($_POST['first_name'] ?? '');
        $lastName = sanitize($_POST['last_name'] ?? '');
        $accessCode = sanitize($_POST['access_code'] ?? '');
        
        // Validation
        if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
            $error = 'Please fill in all required fields.';
        } elseif (!isValidEmail($email)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
            $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            $result = $auth->register($email, $password, $firstName, $lastName, $accessCode);
            
            if ($result['success']) {
                redirect(appUrl('verify.php?email=' . urlencode($email)));
            } else {
                $error = $result['error'];
                if (isset($result['pending_email'])) {
                    $pendingEmail = $result['pending_email'];
                }
            }
        }
    }
}

$pageTitle = 'Register - HR Leave Assistant';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="styles.css?v=1.2">
    <link rel="stylesheet" href="assets/css/main.css?v=<?php echo time(); ?>">
    <link rel="icon" type="image/png" href="hrla_logo.png">
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

    <style>
        :root {
            --hrla-blue: #0322D8;
            --hrla-green: #3DB20B;
        }
        
        .nav-logo-symbol {
            font-family: 'Arial Black', 'Segoe UI Black', Impact, sans-serif;
            font-size: 1.8rem;
            font-weight: 900;
            text-transform: uppercase;
            line-height: 1;
            letter-spacing: -2px;
        }
        
        .nav-hr {
            color: var(--hrla-blue);
        }
        
        .nav-la {
            color: var(--hrla-green);
        }
    </style>

    <?php if ($showVerificationMessage): ?>
        <!-- Verification Page -->
        <div id="verificationPage" class="page">
            <div class="auth-container">
                <div class="auth-card text-center">
                    <div class="verification-icon">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <h2>Check your email</h2>
                    <p>We've sent a verification link to <strong><?php echo htmlspecialchars($registeredEmail); ?></strong>. Please check your inbox and click the link to verify your account.</p>
                    
                    <div class="verification-actions" style="margin-top: 2rem;">
                        <a href="<?php echo appUrl('login.php'); ?>" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to Sign In
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Register Page -->
        <div id="registerPage" class="page">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-header">
                        <h2>Get started for free</h2>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error" style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border-left: 4px solid #dc2626;">
                            <span class="alert-icon">❌</span>
                            <?php echo htmlspecialchars($error); ?>
                            <?php if ($pendingEmail): ?>
                                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #fca5a5;">
                                    <button type="button" id="resendVerificationBtn" class="btn btn-primary" style="width: 100%; background: #0322D8;">
                                        <i class="fas fa-envelope"></i> Resend Verification Email
                                    </button>
                                    <div id="resendMessage" style="margin-top: 0.5rem; display: none;"></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="auth-form" id="registerForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First name</label>
                                <input 
                                    type="text" 
                                    id="first_name" 
                                    name="first_name" 
                                    value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                                    required 
                                    placeholder="First name"
                                >
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last name</label>
                                <input 
                                    type="text" 
                                    id="last_name" 
                                    name="last_name" 
                                    value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                                    required 
                                    placeholder="Last name"
                                >
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                required 
                                placeholder="name@example.com"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                placeholder="Create a password"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm password</label>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                required 
                                placeholder="Confirm your password"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="access_code">Access Code (Optional)</label>
                            <input 
                                type="text" 
                                id="access_code" 
                                name="access_code" 
                                value="<?php echo htmlspecialchars($_POST['access_code'] ?? ''); ?>"
                                placeholder="Enter access code"
                            >
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="termsAccepted" required>
                            <label for="termsAccepted">I agree to the <a href="product-scope.php">Terms of Use</a> and <a href="privacy-policy.php">Privacy Policy</a></label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                    </form>
                    
                    <div class="auth-footer">
                        <p>Already have an account? <a href="<?php echo appUrl('login.php'); ?>" id="showLogin">Sign in</a></p>
                        <p><a href="<?php echo appUrl('index.php'); ?>" id="backToHomepageFromRegister">← Back to Homepage</a></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <script src="assets/js/mobile-menu.js"></script>
    
    <?php if ($pendingEmail): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const resendBtn = document.getElementById('resendVerificationBtn');
            const resendMessage = document.getElementById('resendMessage');
            const pendingEmail = <?php echo json_encode($pendingEmail); ?>;
            
            if (resendBtn) {
                resendBtn.addEventListener('click', async function() {
                    resendBtn.disabled = true;
                    resendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                    resendMessage.style.display = 'none';
                    
                    try {
                        const response = await fetch('api/auth.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                action: 'resend_verification',
                                email: pendingEmail,
                                csrf_token: '<?php echo generateCSRFToken(); ?>'
                            })
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            resendMessage.style.display = 'block';
                            resendMessage.style.background = '#d1fae5';
                            resendMessage.style.color = '#065f46';
                            resendMessage.style.padding = '0.75rem';
                            resendMessage.style.borderRadius = '0.375rem';
                            resendMessage.style.borderLeft = '4px solid #10b981';
                            
                            let messageHtml = '<i class="fas fa-check-circle"></i> ' + result.message;
                            
                            // Show verification link in development mode
                            if (result.verification_link) {
                                messageHtml += '<div style="margin-top: 1rem; padding: 1rem; background: #fff; border-radius: 0.375rem; border: 1px solid #10b981;">';
                                messageHtml += '<strong style="color: #065f46;">Development Mode - Verification Link:</strong><br>';
                                messageHtml += '<a href="' + result.verification_link + '" style="color: #0322D8; word-break: break-all; font-size: 0.875rem;">' + result.verification_link + '</a>';
                                messageHtml += '<div style="margin-top: 0.5rem; font-size: 0.875rem; color: #666;">Click the link above to verify your account</div>';
                                messageHtml += '</div>';
                            }
                            
                            resendMessage.innerHTML = messageHtml;
                            resendBtn.innerHTML = '<i class="fas fa-check"></i> Email Sent!';
                            
                            setTimeout(() => {
                                resendBtn.disabled = false;
                                resendBtn.innerHTML = '<i class="fas fa-envelope"></i> Resend Verification Email';
                            }, 5000);
                        } else {
                            resendMessage.style.display = 'block';
                            resendMessage.style.background = '#fee2e2';
                            resendMessage.style.color = '#991b1b';
                            resendMessage.style.padding = '0.75rem';
                            resendMessage.style.borderRadius = '0.375rem';
                            resendMessage.style.borderLeft = '4px solid #dc2626';
                            resendMessage.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + result.error;
                            resendBtn.disabled = false;
                            resendBtn.innerHTML = '<i class="fas fa-envelope"></i> Resend Verification Email';
                        }
                    } catch (error) {
                        console.error('Resend verification error:', error);
                        resendMessage.style.display = 'block';
                        resendMessage.style.background = '#fee2e2';
                        resendMessage.style.color = '#991b1b';
                        resendMessage.style.padding = '0.75rem';
                        resendMessage.style.borderRadius = '0.375rem';
                        resendMessage.style.borderLeft = '4px solid #dc2626';
                        resendMessage.innerHTML = '<i class="fas fa-exclamation-circle"></i> Failed to send email. Please try again.';
                        resendBtn.disabled = false;
                        resendBtn.innerHTML = '<i class="fas fa-envelope"></i> Resend Verification Email';
                    }
                });
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
