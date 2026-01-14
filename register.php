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
                $showVerificationMessage = true;
                $registeredEmail = $email;
                $success = $result['message'];
            } else {
                $error = $result['error'];
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
    <link rel="icon" type="image/png" href="hrla_logo.png">
</head>
<body>
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
                            <label for="email">Work email</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                required 
                                placeholder="name@company.com"
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
                                placeholder="Enter access code if you have one"
                            >
                            <small class="form-hint">If you have an access code, enter it here for extended access</small>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="termsAccepted" required>
                            <label for="termsAccepted">I agree to the <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a></label>
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
</body>
</html>
