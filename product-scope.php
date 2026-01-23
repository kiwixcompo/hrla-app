<?php
/**
 * Product Scope & Use Page
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'config/site-settings.php';
require_once 'includes/auth.php';

$auth = getAuth();

$pageTitle = 'Product Scope & Use - HRLA | HR Leave Assist';
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
    <meta name="description" content="Learn about HR Leave Assist product scope, terms of use, and proper usage guidelines for HR professionals.">
    <meta name="keywords" content="HR Leave Assist scope, product use, HR tools, terms of use">
    
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
                <a href="<?php echo appUrl('register.php'); ?>" class="btn btn-primary">Get started for free</a>
            </div>
        </div>
    </nav>

    <!-- Product Scope Content -->
    <div class="content-page">
        <div class="container">
            <div class="content-header">
                <h1>Product Scope & Use</h1>
            </div>

            <div class="content-body">
                <section class="content-section">
                    <h2>▶ Product Scope & Use</h2>
                    <p>HR Leave Assist (HRLA) is a support tool for HR professionals that helps organize leave-related information and draft employee-ready responses. It highlights potential leave considerations, next steps, and documentation based on the information provided by the user.</p>
                    <p>HRLA does not make final determinations, provide legal advice, or replace professional HR judgment. All outputs must be reviewed, edited, and approved by HR before use.</p>
                </section>

                <section class="content-section">
                    <h2>▶ Terms of Use</h2>
                    <p>HR Leave Assist is intended for internal HR business use only. You remain fully responsible for how generated content is interpreted, modified, and applied.</p>
                    <p>The tool should not be used as the sole basis for compliance decisions, employment actions, or legal conclusions.</p>
                </section>

                <section class="content-section">
                    <h2>▶ Cancellation & Refund Policy</h2>
                    <p>Subscriptions may be canceled at any time.</p>
                    <p>Monthly subscriptions remain active through the end of the current billing period.</p>
                    <p>Annual subscriptions may be canceled to prevent future charges; payments already made are non-refundable.</p>
                    <p>No refunds are provided for partial use, unused time, or early cancellation.</p>
                </section>

                <section class="content-section">
                    <h2>▶ Wrong Version Purchased</h2>
                    <p>You are responsible for selecting the correct version (Federal, California, Academic, etc.). If you believe you selected the wrong version, contact us promptly. Requests will be reviewed at our discretion. No refunds are guaranteed.</p>
                </section>

                <section class="content-section">
                    <h2>▶ User Access & Licensing</h2>
                    <p>Each subscription grants immediate access upon purchase. As a result, all sales are considered final once access has been provided.</p>
                    <p>Access is internal professional use only by the licensed user(s). Access may not be shared, transferred, resold, or used to provide services to third parties. Organization plans are limited to the number of HR professionals specified at purchase. Access may be suspended if usage exceeds the licensed scope.</p>
                </section>

                <section class="content-section">
                    <h2>▶ Disclaimer</h2>
                    <p>HR Leave Assist provides drafting support only, and the user assumes all responsibility for reviewing, validating, and applying any information generated by the tool.</p>
                </section>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <img src="footer_logo.png" alt="HRLA Logo" class="footer-logo-img">
                    <p>A leave-support tool built by HR, for HR, to help apply consistent, compliance-aligned responses to employee leave questions.</p>
                </div>
                
                <div class="footer-legal-links">
                    <a href="#" data-modal="termsModal">Terms of Use</a>
                    <a href="privacy-policy.php">Privacy Policy</a>
                    <a href="#" data-modal="refundModal">Cancellation & Refund Policy</a>
                    <a href="#" data-modal="licensingModal">User Access & Licensing</a>
                    <a href="product-scope.php">Product Scope & Use</a>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; <span id="currentYear"><?php echo date('Y'); ?></span> HR Leave Assistant. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>