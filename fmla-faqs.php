<?php
/**
 * FMLA FAQs Page
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'config/site-settings.php';
require_once 'includes/auth.php';

$auth = getAuth();

$pageTitle = 'FMLA FAQs - HRLA | HR Leave Assist';
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
    <meta name="description" content="Comprehensive FMLA FAQs covering eligibility, duration, job protection, and more. Get answers to common Family and Medical Leave Act questions.">
    <meta name="keywords" content="FMLA FAQ, Family Medical Leave Act, FMLA eligibility, FMLA questions">
    
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

    <!-- FMLA FAQs Content -->
    <div class="faq-page">
        <div class="container">
            <div class="faq-page-header">
                <h1>FMLA FAQs</h1>
                <p>Frequently Asked Questions about the Family and Medical Leave Act</p>
            </div>

            <div class="faq-accordion">
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>What is FMLA and how does it work?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>The Family and Medical Leave Act (FMLA) is a federal law that allows eligible employees to take up to 12 weeks of unpaid, job-protected leave in a 12-month period for certain medical or family reasons. During FMLA leave, group health benefits generally continue, and employees may be entitled to return to the same or an equivalent position.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>How much does FMLA pay?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>FMLA itself does not provide paid leave. It is a job-protection law, not a wage-replacement program. Employees may receive pay during FMLA leave through accrued sick or vacation time, employer policies, or state wage-replacement programs, depending on eligibility and how different benefits coordinate.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>Who is eligible for FMLA?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>To be eligible for FMLA, an employee generally must work for a covered employer, have at least 12 months of service, and have worked at least 1,250 hours during the prior 12 months. Eligibility is determined based on federal criteria and may vary depending on the employer's size and worksite location.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>How long can you be on FMLA?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Eligible employees may take up to 12 workweeks of FMLA leave within a defined 12-month period. The method used to measure the 12-month period can vary by employer. In some cases, leave may be taken all at once or spread out over time, depending on the qualifying reason.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>What qualifies as a serious health condition under FMLA?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>A serious health condition under FMLA generally involves an illness, injury, impairment, or physical or mental condition that requires inpatient care or ongoing treatment by a healthcare provider. This may include chronic conditions, periods of incapacity, or conditions requiring continuing medical supervision, depending on the circumstances.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>Can FMLA be taken intermittently?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>FMLA does not have to be taken all at one time. Eligible employees may take FMLA intermittently or on a reduced work schedule when medically necessary or otherwise permitted. This means leave can be taken in separate blocks of time, such as a few hours or days at a time, depending on the qualifying reason and documentation.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>What proof is required for FMLA?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Employers may request medical certification or other documentation to support the need for FMLA leave. This typically includes information from a healthcare provider confirming that a qualifying condition exists. Specific documentation requirements and deadlines can vary, and incomplete or delayed paperwork may affect how leave is designated.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>Is FMLA job protected?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>FMLA is generally a job-protected leave, meaning employees are typically entitled to return to the same or an equivalent position after leave ends. An equivalent position usually has similar pay, benefits, and working conditions. Job protection may depend on continued eligibility and whether the employee would have remained employed regardless of taking leave.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>What happens when FMLA ends?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>When FMLA leave is exhausted, job protection under FMLA ends. At that point, other considerations may apply, such as employer leave policies, disability accommodations, or state laws. The next steps depend on the employee's ability to return to work and the specific circumstances involved.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <h3>Can an employee be terminated after FMLA ends?</h3>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </button>
                    <div class="faq-answer">
                        <p>An employee is not automatically protected from termination once FMLA leave ends. Employment decisions after FMLA may depend on factors such as job requirements, business needs, and whether other protections apply. Termination decisions should be based on legitimate, non-retaliatory reasons and evaluated on a case-by-case basis.</p>
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