<?php
/**
 * Login Page
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'includes/auth.php';

$auth = getAuth();

// Redirect if already logged in
if ($auth->isAuthenticated()) {
    $user = $auth->getCurrentUser();
    if ($user['is_admin']) {
        redirect(appUrl('admin/index.php'));
    } else {
        redirect(appUrl('dashboard.php'));
    }
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);
        
        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password.';
        } else {
            $result = $auth->login($email, $password, $rememberMe);
            
            if ($result['success']) {
                $user = $result['user'];
                if ($user['is_admin']) {
                    redirect(appUrl('admin/index.php'));
                } else {
                    redirect(appUrl('dashboard.php'));
                }
            } else {
                $error = $result['error'];
            }
        }
    }
}

// Handle verification success message
if (isset($_GET['verified']) && $_GET['verified'] === 'true') {
    $success = 'Email verified successfully! You can now login.';
}

$pageTitle = 'Login - HR Leave Assistant';
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
        
        /* Login Logo Styling */
        .auth-logo {
            max-height: 150px;
            width: auto;
        }
        
        .login-logo-text {
            font-family: 'Arial Black', 'Segoe UI Black', Impact, sans-serif;
            font-size: 5rem;
            font-weight: 900;
            text-transform: uppercase;
            line-height: 1;
            letter-spacing: -3px;
            margin-bottom: 20px;
        }
        
        .login-hr {
            color: var(--hrla-blue);
        }
        
        .login-la {
            color: var(--hrla-green);
        }
    </style>
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
                <a href="<?php echo appUrl('register.php'); ?>" class="btn btn-primary">Get started for free</a>
            </div>
            <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle mobile menu" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <div id="loginPage" class="page">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <!-- Image logo placeholder -->
                    <img src="login_logo.png" alt="HRLA" class="auth-logo" onload="this.style.display='block'; document.querySelector('.login-logo-text').style.display='none';" onerror="this.style.display='none'; document.querySelector('.login-logo-text').style.display='block';">
                    
                    <!-- CSS text logo (fallback and default) -->
                    <div class="login-logo-text">
                        <span class="login-hr">HR</span><span class="login-la">LA</span>
                    </div>
                    
                    <h2>Welcome back</h2>
                    <p>Sign in to your HRLA account</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-error" style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border-left: 4px solid #dc2626;">
                        <span class="alert-icon">❌</span>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success" style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border-left: 4px solid #10b981;">
                        <span class="alert-icon">✅</span>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="auth-form" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            required 
                            autocomplete="email"
                            placeholder="Enter your email"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            autocomplete="current-password"
                            placeholder="Enter your password"
                        >
                    </div>
                    
                    <div class="form-check" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem;">
                        <input type="checkbox" name="remember_me" id="remember_me" style="width: auto; margin: 0;">
                        <label for="remember_me" style="margin: 0; font-weight: 500; font-size: 0.875rem;">Remember me for 30 days</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block" id="loginBtn">
                        Sign In
                    </button>
                </form>
                
                <div class="auth-footer">
                    <p>Don't have an account? <a href="<?php echo appUrl('register.php'); ?>" id="showRegister">Sign up for free</a></p>
                    <p><a href="<?php echo appUrl('index.php'); ?>" id="backToHomepageFromLogin">← Back to Homepage</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/mobile-menu.js"></script>
</body>
</html>