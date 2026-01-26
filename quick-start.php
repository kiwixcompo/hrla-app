<?php
/**
 * Quick Start Guide Page
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'config/site-settings.php';
require_once 'includes/auth.php';

$auth = getAuth();

$pageTitle = 'Quick Start Guide - HRLA | HR Leave Assist';
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
    <meta name="description" content="Quick Start Guide for HR Leave Assist - Learn how to use HRLA effectively for HR leave management.">
    <meta name="keywords" content="HR Leave Assist guide, HRLA tutorial, HR tools guide">
    
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
                <a href="quick-start.php" class="nav-link active" role="menuitem">Quick Start Guide</a>
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

    <!-- Quick Start Guide Content -->
    <div class="content-page">
        <div class="container">
            <div class="content-header">
                <h1>Quick Start Guide</h1>
            </div>

            <div class="content-body">
                <section class="content-section">
                    <h2>What Is HR Leave Assist (HRLA)?</h2>
                    <p>HRLA is a support tool for HR professionals who respond to employee leave questions. It helps organize applicable leave considerations and draft employee-ready responses—without starting from scratch. You remain responsible for all decisions.</p>
                </section>

                <section class="content-section">
                    <h2>Step 1: Paste the Question</h2>
                    <p>Paste one of the following:</p>
                    <ul>
                        <li>An employee's email or message</li>
                        <li>A summarized leave scenario</li>
                        <li>Your own HR follow-up question</li>
                    </ul>
                    <p>You may paste only the message body. "To," "From," and "Subject" lines are not required.</p>
                </section>

                <section class="content-section">
                    <h2>Step 2: Review the Draft</h2>
                    <p>HRLA reviews the scenario using applicable leave frameworks, including:</p>
                    <ul>
                        <li>FMLA</li>
                        <li>PDL (when medically applicable)</li>
                        <li>ADA / accommodation considerations</li>
                        <li>CFRA (California version)</li>
                    </ul>
                    <p>The draft may outline potential coverage, next steps, and documentation reminders.</p>
                </section>

                <section class="content-section">
                    <h2>Step 3: Edit Before Sending</h2>
                    <p>Before sending any response:</p>
                    <ul>
                        <li>Review for accuracy</li>
                        <li>Adjust tone and details</li>
                        <li>Confirm alignment with internal policies</li>
                    </ul>
                    <p>HRLA supports your judgment—it does not replace it.</p>
                </section>

                <section class="content-section">
                    <h2>Best Practices</h2>
                    <ul>
                        <li>Share only relevant information</li>
                        <li>Avoid unnecessary personal identifiers</li>
                        <li>Use HRLA to support consistency and efficiency</li>
                        <li>Confirm final decisions against current law and policy</li>
                    </ul>
                </section>

                <section class="content-section">
                    <h2>What HRLA Does Not Do</h2>
                    <ul>
                        <li>Does not provide legal advice</li>
                        <li>Is not a recordkeeping system</li>
                        <li>Does not replace the interactive process</li>
                        <li>Does not evaluate CBAs, local ordinances, or employer-specific rules</li>
                    </ul>
                </section>

                <section class="content-section">
                    <h2>When to Use HRLA</h2>
                    <p>Use HRLA to:</p>
                    <ul>
                        <li>Draft first-pass leave responses</li>
                        <li>Prepare follow-up communications</li>
                        <li>Check consistency across similar scenarios</li>
                    </ul>
                    <p>HRLA is your starting point—fast, structured, and HR-controlled.</p>
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
                    <p>A leave-support tool built by HR for HR to help apply consistent compliance-aligned responses to employee leave questions.</p>
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
    
    <script src="assets/js/mobile-menu.js"></script>
</body>
</html>