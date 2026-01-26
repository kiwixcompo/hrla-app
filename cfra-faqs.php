<?php
/**
 * CFRA FAQs Page
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'config/site-settings.php';
require_once 'includes/auth.php';

$auth = getAuth();

$pageTitle = 'CFRA FAQs - HRLA | HR Leave Assist';
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
    <meta name="description" content="Comprehensive CFRA FAQs covering California Family Rights Act eligibility, benefits, job protection, and more.">
    <meta name="keywords" content="CFRA FAQ, California Family Rights Act, CFRA eligibility, California leave laws">
    
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

    <!-- CFRA FAQs Content -->
    <div class="faq-page">
        <div class="container">
            <div class="faq-page-header">
                <h1>CFRA FAQs</h1>
                <p>Frequently Asked Questions about the California Family Rights Act</p>
            </div>

            <div class="faq-accordion">
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>What does CFRA mean and what does it cover?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>CFRA stands for the California Family Rights Act. It provides eligible employees with up to 12 weeks of job-protected leave for their own serious health condition, to care for a qualifying family member, or to bond with a new child. CFRA protects the employee's job but does not provide pay.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>Is CFRA paid or unpaid leave?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>CFRA itself is unpaid. However, employees may receive partial wage replacement through California programs such as Paid Family Leave (PFL) or State Disability Insurance (SDI), depending on the reason for the leave and eligibility for those programs.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>Who qualifies for CFRA in California?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>An employee may qualify for CFRA if they work for a California employer with 5 or more employees, have worked for the employer for at least 12 months, and have worked 1,250 hours in the 12 months before the leave starts.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>How many weeks of CFRA can an employee take?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Eligible employees can take up to 12 workweeks of CFRA leave in a 12-month period. Employers choose how the 12-month period is measured, such as a calendar year, rolling year, or another approved method.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>What is considered a serious health condition under CFRA?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>A serious health condition under CFRA generally includes an illness, injury, impairment, or mental or physical condition requiring inpatient care or ongoing treatment by a healthcare provider. CFRA also covers care for qualifying family members with serious health conditions.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>Can CFRA be taken intermittently or part-time?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Yes. CFRA may be taken intermittently or on a reduced schedule when medically necessary or when caring for a covered family member. Employers may require medical certification to support intermittent or reduced-schedule leave.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>What documentation is required for CFRA leave?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Employers may request medical certification or supporting documentation, depending on the reason for CFRA leave. Employees are typically given a set deadline to return completed forms. Employers cannot require a medical diagnosis to be disclosed.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>Does CFRA protect an employee's job?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Yes. CFRA provides job protection, meaning an employee must generally be reinstated to the same or a comparable position at the end of approved CFRA leave, as long as they return to work on time and remain eligible.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>What happens when CFRA leave runs out?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>When CFRA leave ends, the employee is expected to return to work. If the employee cannot return, the employer may need to evaluate other leave options or reasonable accommodation obligations under disability or state employment laws.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>Can an employer fire an employee after CFRA ends?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>CFRA protection ends after the 12-week entitlement is exhausted. However, an employer cannot automatically terminate an employee without considering other legal obligations, such as disability accommodation, anti-retaliation rules, and consistent application of company policy.</p>
                    </div>
                </div>
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

    <script src="assets/js/mobile-menu.js"></script>
    <script>
        // FAQ Accordion functionality
        document.querySelectorAll('.faq-question').forEach(button => {
            button.addEventListener('click', function() {
                const faqItem = this.parentElement;
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                
                // Close all other FAQ items
                document.querySelectorAll('.faq-item').forEach(item => {
                    if (item !== faqItem) {
                        item.classList.remove('active');
                        item.querySelector('.faq-question').setAttribute('aria-expanded', 'false');
                    }
                });
                
                // Toggle current FAQ item
                if (isExpanded) {
                    faqItem.classList.remove('active');
                    this.setAttribute('aria-expanded', 'false');
                } else {
                    faqItem.classList.add('active');
                    this.setAttribute('aria-expanded', 'true');
                }
            });
        });
    </script>
</body>
</html>