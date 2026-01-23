<?php
/**
 * Privacy Policy Page
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'config/site-settings.php';
require_once 'includes/auth.php';

$auth = getAuth();

$pageTitle = 'Privacy Policy - HRLA | HR Leave Assist';
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
    <meta name="description" content="HR Leave Assist Privacy Policy - Learn how we collect, use, and protect your information.">
    <meta name="keywords" content="privacy policy, data protection, HR Leave Assist privacy">
    
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

    <!-- Privacy Policy Content -->
    <div class="content-page">
        <div class="container">
            <div class="content-header">
                <h1>Privacy Policy</h1>
            </div>

            <div class="content-body">
                <section class="content-section">
                    <h2>What information does HR Leave Assist collect?</h2>
                    <p>HR Leave Assist (HRLA) collects only limited information necessary to provide licensed access to the tool, such as:</p>
                    <ul>
                        <li>Account and contact information</li>
                        <li>Subscription and billing status</li>
                        <li>Content you voluntarily paste into the tool for drafting purposes</li>
                    </ul>
                    <p>HRLA is designed to minimize data collection.</p>
                </section>

                <section class="content-section">
                    <h2>What information should not be entered?</h2>
                    <p>HRLA is not intended for sensitive personal identifiers. Do not enter:</p>
                    <ul>
                        <li>Social Security numbers</li>
                        <li>Dates of birth</li>
                        <li>Medical records</li>
                        <li>Personnel files</li>
                        <li>Government identification numbers</li>
                    </ul>
                    <p>Users are responsible for following their organization's internal data-handling and confidentiality standards.</p>
                </section>

                <section class="content-section">
                    <h2>How is my information used?</h2>
                    <p>Information is used only to:</p>
                    <ul>
                        <li>Provide licensed access to HR Leave Assist</li>
                        <li>Generate draft, informational HR responses</li>
                        <li>Manage subscriptions and access controls</li>
                        <li>Maintain service functionality and security</li>
                        <li>Respond to support inquiries</li>
                    </ul>
                    <p>HRLA does not use user content for advertising, training, or marketing.</p>
                </section>

                <section class="content-section">
                    <h2>Is HR Leave Assist a recordkeeping system?</h2>
                    <p>No. HRLA is not a recordkeeping, case-management, or document-storage system.</p>
                    <p>Content entered into HRLA is used solely to generate draft responses and is not intended to replace employer systems of record.</p>
                </section>

                <section class="content-section">
                    <h2>How does AI use my input?</h2>
                    <p>HRLA uses AI to generate informational draft responses based on user-provided text.</p>
                    <p>All outputs:</p>
                    <ul>
                        <li>Are for internal business use only</li>
                        <li>Must be reviewed, edited, and approved by an HR professional</li>
                        <li>Do not constitute legal advice or employment decisions</li>
                    </ul>
                </section>

                <section class="content-section">
                    <h2>Is my information shared or sold?</h2>
                    <p>No. HRLA does not sell or rent user data.</p>
                    <p>Limited information may be shared only with:</p>
                    <ul>
                        <li>Service providers required to operate the platform (e.g., payment or hosting services)</li>
                        <li>Legal or regulatory authorities when required by law</li>
                    </ul>
                </section>

                <section class="content-section">
                    <h2>How is my information protected?</h2>
                    <p>Reasonable administrative and technical safeguards are used to protect information.</p>
                    <p>No system can be guaranteed to be completely secure. Users should avoid entering unnecessary sensitive data.</p>
                </section>

                <section class="content-section">
                    <h2>Who is responsible for compliance decisions?</h2>
                    <p>The user and their organization remain fully responsible for:</p>
                    <ul>
                        <li>Leave determinations</li>
                        <li>Legal compliance</li>
                        <li>Final communications with employees</li>
                        <li>Recordkeeping and documentation</li>
                    </ul>
                    <p>HRLA supports HR judgment but does not replace it.</p>
                </section>

                <section class="content-section">
                    <h2>Is HR Leave Assist for children?</h2>
                    <p>No. HRLA is intended for professional use only and is not designed for individuals under 18.</p>
                </section>

                <section class="content-section">
                    <h2>Will this policy change?</h2>
                    <p>This Privacy Policy may be updated periodically. Any changes will be posted on this page with an updated "Last Updated" date.</p>
                </section>

                <section class="content-section">
                    <h2>How can I contact you?</h2>
                    <p>For privacy questions, contact:</p>
                    <p>HR Leave Assist<br>
                    <a href="mailto:askhrla@hrleaveassist.com">askhrla@hrleaveassist.com</a></p>
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