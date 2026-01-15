<?php
/**
 * Home Page / Landing Page
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'config/site-settings.php';
require_once 'includes/auth.php';

$auth = getAuth();

// Redirect authenticated users to their dashboard
if ($auth->isAuthenticated()) {
    $user = $auth->getCurrentUser();
    if ($user['is_admin']) {
        redirect(appUrl('admin/index.php'));
    } else {
        redirect(appUrl('dashboard.php'));
    }
}

$pageTitle = 'HRLA - HR Leave Assist | Professional Leave Management';
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
    <meta name="description" content="AI-powered Federal FMLA and California leave compliance responses. Generate professional, compliant HR communications in seconds. Built by HR expert with 25+ years experience.">
    <meta name="keywords" content="HR leave assistant, FMLA compliance, California leave laws, HR tools, employee relations, leave management">
    <meta name="author" content="HRLA - HR Leave Assist">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="HRLA - HR Leave Assist | Professional Leave Management">
    <meta property="og:description" content="AI-powered Federal FMLA and California leave compliance responses. Generate professional, compliant HR communications in seconds.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://hrleaveassist.com">
    <meta property="og:image" content="hrla-logo-new-fixed.svg">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="hrla-logo-new-fixed.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="hrla_logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="hrla_logo.png">
    <link rel="apple-touch-icon" href="hrla_logo.png">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="styles.css?v=1.2">
    <link rel="stylesheet" href="assets/css/custom.php?v=<?php echo time(); ?>">
</head>
<body>
    <!-- Skip Links for Accessibility -->
    <a href="#main-content" class="sr-only">Skip to main content</a>
    <a href="#navigation" class="sr-only">Skip to navigation</a>

    <!-- Landing Page -->
    <div id="landingPage" class="page">
        <!-- Navigation -->
        <nav class="landing-nav" role="navigation" aria-label="Main navigation" id="navigation">
            <div class="nav-container">
                <div class="nav-brand">
                    <img src="hrla_logo.png" alt="HRLA - HR Leave Assist" class="brand-logo">
                </div>
                <div class="nav-menu" role="menubar">
                    <a href="#features" class="nav-link" role="menuitem">Features</a>
                    <a href="#how-it-works" class="nav-link" role="menuitem">How it Works</a>
                    <a href="#pricing" class="nav-link" role="menuitem">Pricing</a>
                    <a href="#faqs" class="nav-link" role="menuitem">FAQs</a>
                    <a href="#" class="nav-link" role="menuitem">Support</a>
                    <a href="<?php echo appUrl('login.php'); ?>" class="btn btn-outline">Sign In</a>
                    <a href="<?php echo appUrl('register.php'); ?>" class="btn btn-primary">Get started for free</a>
                </div>
                <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle mobile menu" aria-expanded="false">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero-section" id="main-content">
            <div class="hero-container">
                <div class="hero-content">
                    <h1 class="hero-title">
                        <?php echo htmlspecialchars(getSiteSetting('hero_title', 'Answer Employee Leave Questions')); ?> 
                        <span class="hero-highlight"><?php echo htmlspecialchars(getSiteSetting('hero_highlight', 'accurately and consistently')); ?></span>
                    </h1>
                    <p class="hero-subtitle">
                        <?php echo htmlspecialchars(getSiteSetting('hero_subtitle', 'Designed for compliant HR Leave Decision-Making')); ?>
                    </p>
                    <div class="hero-features">
                        <div class="hero-feature">
                            <i class="fas fa-check"></i>
                            <span><?php echo htmlspecialchars(getSiteSetting('hero_feature_1', 'Built by HR for HR professionals. Aligned with FMLA, PDL, ADA, & CFRA')); ?></span>
                        </div>
                        <div class="hero-feature">
                            <i class="fas fa-check"></i>
                            <span><?php echo htmlspecialchars(getSiteSetting('hero_feature_2', 'Respond to employee leave questions clearly and consistently')); ?></span>
                        </div>
                        <div class="hero-feature">
                            <i class="fas fa-check"></i>
                            <span><?php echo htmlspecialchars(getSiteSetting('hero_feature_3', 'Align responses with applicable federal and state leave requirements')); ?></span>
                        </div>
                        <div class="hero-feature">
                            <i class="fas fa-check"></i>
                            <span><?php echo htmlspecialchars(getSiteSetting('hero_feature_4', 'Reduce compliance risk and unnecessary rework')); ?></span>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <a href="<?php echo appUrl('register.php'); ?>" class="btn btn-primary btn-large">
                            <?php echo htmlspecialchars(getSiteSetting('hero_cta_primary', 'Try HR Leave Assist')); ?>
                        </a>
                        <a href="#how-it-works" class="btn btn-success btn-large">
                            <?php echo htmlspecialchars(getSiteSetting('hero_cta_secondary', 'Watch Demo')); ?>
                        </a>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="hero-card" role="img" aria-label="HRLA Response Generator Demo">
                        <div class="card-header">
                            <span class="card-title">HRLA Response Generator</span>
                        </div>
                        <div class="card-content">
                            <div class="input-demo">
                                <label>Employee Leave Question</label>
                                <div class="demo-text" role="textbox" aria-readonly="true">"I need to take leave for my newborn..."</div>
                            </div>
                            <div class="output-demo">
                                <label>HR Response</label>
                                <div class="demo-response" role="textbox" aria-readonly="true">
                                    <p>Congratulations! Based on the information you provided you may be eligible for job-protected leave under the Family Medical Leave Act (FMLA)</p>
                                </div>
                            </div>
                            <div class="demo-note">
                                <em>HR reviews and edits all responses before sending</em>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="features-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title"><?php echo htmlspecialchars(getSiteSetting('features_title', 'How HR Leave Assist Supports Your Leave Process')); ?></h2>
                </div>
                <div class="features-checklist">
                    <div class="checklist-item">
                        <i class="fas fa-check-circle"></i>
                        <h3><?php echo htmlspecialchars(getSiteSetting('feature_1_title', 'Built to Support Leave Compliance')); ?></h3>
                    </div>
                    <div class="checklist-item">
                        <i class="fas fa-check-circle"></i>
                        <h3><?php echo htmlspecialchars(getSiteSetting('feature_2_title', 'Respond to Leave Questions Faster')); ?></h3>
                    </div>
                    <div class="checklist-item">
                        <i class="fas fa-check-circle"></i>
                        <h3><?php echo htmlspecialchars(getSiteSetting('feature_3_title', 'Navigate Federal & California Leave Laws')); ?></h3>
                    </div>
                    <div class="checklist-item">
                        <i class="fas fa-check-circle"></i>
                        <h3><?php echo htmlspecialchars(getSiteSetting('feature_4_title', 'Designed for Busy HR Teams')); ?></h3>
                    </div>
                    <div class="checklist-item">
                        <i class="fas fa-check-circle"></i>
                        <h3><?php echo htmlspecialchars(getSiteSetting('feature_5_title', 'Empowers HR-Led Decision-Making')); ?></h3>
                    </div>
                    <div class="checklist-item">
                        <i class="fas fa-check-circle"></i>
                        <h3>Supports Consistent Leave Administration</h3>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section id="how-it-works" class="how-it-works-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">How HR Leave Assist Works</h2>
                </div>
                <div class="steps-container">
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h3>Paste Employee Question or Email</h3>
                            <p>Copy and paste the employee's leave question or email into the system.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h3>Analysis & Draft Response</h3>
                            <p>The tool analyzes the email and prepares an employee-ready draft aligned with applicable leave requirements.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h3>Review Generated Response & Send</h3>
                            <p>Copy and paste the employee's leave question or email into the system.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Me Section -->
        <section class="about-section">
            <div class="container">
                <div class="about-content">
                    <div class="about-header">
                        <h2>About Me</h2>
                    </div>
                    <div class="about-main">
                        <div class="about-image">
                            <img src="about-headshot.jpg" alt="HR Professional - 25+ Years Experience" class="headshot">
                        </div>
                        <div class="about-text">
                            <p>After more than 25 years working in Employee Relations, Leave of Absence administration, and Risk Management, I found myself answering the same complex leave questions again and again—each one requiring speed, accuracy, and careful judgment. I wanted a more efficient way to respond without starting from scratch every time.</p>
                            
                            <p>I initially built HR Leave Assist for my own use, to help organize information, apply applicable leave requirements, and draft clear, employee-ready responses while preserving thoughtful HR review. As the tool evolved, it became clear how valuable this approach could be for other HR professionals facing similar challenges.</p>
                            
                            <p>HR Leave Assist is now available to support your work. It's designed to reinforce consistency, reduce uncertainty, and help you navigate sensitive leave conversations with care—while keeping HR fully in control of every decision.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="pricing-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Pricing</h2>
                </div>
                <div class="pricing-grid">
                    <div class="pricing-card">
                        <div class="pricing-header">
                            <h3>Free Trial — $0</h3>
                        </div>
                        <div class="pricing-description">
                            <p>Evaluate how HR Leave Assist supports your individual HR workflow</p>
                        </div>
                        <div class="pricing-features">
                            <ul>
                                <li>• Trial access to HR Leave Assist</li>
                                <li>• Guidance aligned to Federal and California leave laws</li>
                                <li>• AI-assisted draft responses for employee leave questions</li>
                                <li>• Up to 20 questions during the trial period</li>
                                <li>• No payment required</li>
                            </ul>
                        </div>
                        <div class="pricing-best-for">
                            <strong>Best for:</strong><br>
                            HR professionals who want to test the tool with real-world scenarios before subscribing.
                        </div>
                        <div class="pricing-action">
                            <a href="<?php echo appUrl('register.php'); ?>" class="btn btn-primary btn-block">
                                Start Free Trial
                            </a>
                        </div>
                    </div>
                    
                    <div class="pricing-card featured">
                        <div class="pricing-badge">Most Popular</div>
                        <div class="pricing-header">
                            <h3>Monthly — $29 / month</h3>
                        </div>
                        <div class="pricing-description">
                            <p>For individual HR professionals managing ongoing leave questions</p>
                        </div>
                        <div class="pricing-features">
                            <ul>
                                <li>• Full individual access to HR Leave Assist</li>
                                <li>• Guidance aligned to Federal and California leave laws</li>
                                <li>• Draft clear, employee-ready responses for leave scenarios</li>
                                <li>• Unlimited questions, including follow-ups</li>
                                <li>• Cancel anytime</li>
                            </ul>
                        </div>
                        <div class="pricing-best-for">
                            <strong>Best for:</strong><br>
                            Individual HR professionals who regularly respond to employee leave inquiries.
                        </div>
                        <div class="pricing-action">
                            <a href="<?php echo appUrl('register.php'); ?>" class="btn btn-primary btn-block">
                                Subscribe Monthly
                            </a>
                        </div>
                    </div>
                    
                    <div class="pricing-card">
                        <div class="pricing-header">
                            <h3>Annual — $290 / yr</h3>
                        </div>
                        <div class="pricing-description">
                            <p>Consistent, uninterrupted access for individual professional use</p>
                        </div>
                        <div class="pricing-features">
                            <ul>
                                <li>• Everything included in Monthly</li>
                                <li>• 12 months of continuous individual access</li>
                                <li>• Unlimited questions throughout the year</li>
                                <li>• Predictable annual billing</li>
                            </ul>
                        </div>
                        <div class="pricing-best-for">
                            <strong>Best for:</strong><br>
                            HR professionals who rely on HR Leave Assist as part of their regular, year-round workflow.
                        </div>
                        <div class="pricing-action">
                            <a href="<?php echo appUrl('register.php'); ?>" class="btn btn-primary btn-block">
                                Subscribe Annually
                            </a>
                        </div>
                    </div>
                    
                    <div class="pricing-card">
                        <div class="pricing-header">
                            <h3>Organization — $580 / yr</h3>
                        </div>
                        <div class="pricing-description">
                            <p>Shared annual access for up to 5 HR professionals</p>
                        </div>
                        <div class="pricing-features">
                            <ul>
                                <li>• Everything included in Monthly</li>
                                <li>• <strong>Up to 5</strong> named HR users under one organization</li>
                                <li>• 12 months of continuous access for each licensed user</li>
                                <li>• Unlimited questions per licensed user throughout the year</li>
                                <li>• Centralized annual billing</li>
                            </ul>
                        </div>
                        <div class="pricing-best-for">
                            <strong>Best for:</strong><br>
                            Small HR teams or departments where multiple HR professionals regularly respond to employee leave questions and want consistent, shared access.
                        </div>
                        <div class="pricing-action">
                            <a href="<?php echo appUrl('register.php'); ?>" class="btn btn-primary btn-block">
                                Subscribe Organization
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQs Section -->
        <section id="faqs" class="faqs-section">
            <div class="container">
                <h1 class="faqs-title">FMLA Questions & Answers</h1>
                
                <div class="faq-accordion">
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false">
                            <h2>What is FMLA and how does it work?</h2>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>The Family and Medical Leave Act (FMLA) is a federal law that allows eligible employees to take up to 12 weeks of unpaid, job-protected leave in a 12-month period for certain medical or family reasons. During FMLA leave, group health benefits generally continue, and employees may be entitled to return to the same or an equivalent position.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false">
                            <h2>How much does FMLA pay?</h2>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>FMLA itself does not provide paid leave. It is a job-protection law, not a wage-replacement program. Employees may receive pay during FMLA leave through accrued sick or vacation time, employer policies, or state wage-replacement programs, depending on eligibility and how different benefits coordinate.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false">
                            <h2>Who is eligible for FMLA?</h2>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>To be eligible for FMLA, an employee generally must work for a covered employer, have at least 12 months of service, and have worked at least 1,250 hours during the prior 12 months. Eligibility is determined based on federal criteria and may vary depending on the employer's size and worksite location.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false">
                            <h2>How long can you be on FMLA?</h2>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Eligible employees may take up to 12 workweeks of FMLA leave within a defined 12-month period. The method used to measure the 12-month period can vary by employer. In some cases, leave may be taken all at once or spread out over time, depending on the qualifying reason.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false">
                            <h2>What qualifies as a serious health condition under FMLA?</h2>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>A serious health condition under FMLA generally involves an illness, injury, impairment, or physical or mental condition that requires inpatient care or ongoing treatment by a healthcare provider. This may include chronic conditions, periods of incapacity, or conditions requiring continuing medical supervision, depending on the circumstances.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false">
                            <h2>Can FMLA be taken intermittently?</h2>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>FMLA does not have to be taken all at one time. Eligible employees may take FMLA intermittently or on a reduced work schedule when medically necessary or otherwise permitted. This means leave can be taken in separate blocks of time, such as a few hours or days at a time, depending on the qualifying reason and documentation.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false">
                            <h2>What proof is required for FMLA?</h2>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Employers may request medical certification or other documentation to support the need for FMLA leave. This typically includes information from a healthcare provider confirming that a qualifying condition exists. Specific documentation requirements and deadlines can vary, and incomplete or delayed paperwork may affect how leave is designated.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false">
                            <h2>Is FMLA job protected?</h2>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>FMLA is generally a job-protected leave, meaning employees are typically entitled to return to the same or an equivalent position after leave ends. An equivalent position usually has similar pay, benefits, and working conditions. Job protection may depend on continued eligibility and whether the employee would have remained employed regardless of taking leave.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false">
                            <h2>What happens when FMLA ends?</h2>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>When FMLA leave is exhausted, job protection under FMLA ends. At that point, other considerations may apply, such as employer leave policies, disability accommodations, or state laws. The next steps depend on the employee's ability to return to work and the specific circumstances involved.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false">
                            <h2>Can an employee be terminated after FMLA ends?</h2>
                            <i class="fas fa-chevron-down faq-icon"></i>
                        </button>
                        <div class="faq-answer">
                            <p>An employee is not automatically protected from termination once FMLA leave ends. Employment decisions after FMLA may depend on factors such as job requirements, business needs, and whether other protections apply. Termination decisions should be based on legitimate, non-retaliatory reasons and evaluated on a case-by-case basis.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2>Simple to Start, Easy to Use</h2>
                    <a href="<?php echo appUrl('register.php'); ?>" class="btn btn-primary btn-large">
                        Get started for free
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="landing-footer">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-brand">
                        <img src="hrla_logo.png" alt="HRLA" class="footer-logo">
                        <p>Professional HR leave guidance, designed to support accurate and consistent responses.</p>
                    </div>
                    <div class="footer-links">
                        <div class="footer-column">
                            <h4>Product</h4>
                            <a href="#features">Features</a>
                            <a href="#pricing">Pricing</a>
                            <a href="#how-it-works">How it Works</a>
                            <a href="#" data-modal="quickStartModal">Quick Start Guide</a>
                        </div>
                        <div class="footer-column">
                            <h4>Support</h4>
                            <a href="#" data-modal="productScopeModal">Product Scope & Use</a>
                            <a href="#" data-modal="helpCenterModal">Help Center</a>
                            <a href="#" data-modal="contactModal">Contact Us</a>
                        </div>
                        <div class="footer-column">
                            <h4>Legal</h4>
                            <a href="#" data-modal="termsModal">Terms of Use</a>
                            <a href="#" data-modal="privacyModal">Privacy Policy</a>
                            <a href="#" data-modal="refundModal">Cancellation & Refund Policy</a>
                            <a href="#" data-modal="licensingModal">User Access & Licensing</a>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom">
                    <div class="footer-bottom-content">
                        <p>&copy; <span id="currentYear"><?php echo date('Y'); ?></span> HR Leave Assistant. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Modals -->
    
    <!-- Terms of Use Modal -->
    <div id="termsModal" class="info-modal">
        <div class="info-modal-content">
            <button class="info-modal-close" aria-label="Close modal">&times;</button>
            <div class="info-modal-body">
                <h1>Terms of Use</h1>
                <p><strong>Effective Date:</strong> January 2026</p>
                
                <h2>1. Purpose of the Service</h2>
                <p>HR Leave Assist ("HRLA") is an informational, decision-support tool designed to assist human resources professionals in understanding employee leave, accommodation, and interactive process frameworks under <strong>applicable federal and California law</strong>.</p>
                <p>HRLA provides <strong>general informational guidance only</strong>. It is not a substitute for professional judgment, legal counsel, medical certification, or determinations made by regulatory agencies.</p>
                
                <h2>2. No Legal, Medical, or Compliance Advice</h2>
                <p>HRLA does <strong>not</strong> provide:</p>
                <ul>
                    <li>Legal advice</li>
                    <li>Medical advice</li>
                    <li>Final eligibility determinations</li>
                    <li>Binding interpretations of statutes or regulations</li>
                    <li>Guarantees of compliance</li>
                </ul>
                <p>Use of HRLA does not create an attorney-client relationship, a medical provider-patient relationship, or any fiduciary duty. All employment decisions—including leave approval, accommodation determinations, interactive process outcomes, and return-to-work decisions—remain the sole responsibility of the employer.</p>
                
                <h2>3. California-Specific Limitations</h2>
                <p>California employment laws, including but not limited to the California Family Rights Act (CFRA), Pregnancy Disability Leave (PDL), the Fair Employment and Housing Act (FEHA), and related regulations, are <strong>fact-specific, enforcement-driven, and subject to change</strong>.</p>
                <p>HRLA:</p>
                <ul>
                    <li>Does not account for local ordinances, enforcement positions, or court interpretations</li>
                    <li>Does not evaluate collective bargaining agreements, institutional policies, or employer-specific practices</li>
                    <li>Does not replace required employer obligations such as the interactive process</li>
                </ul>
                <p>Users are responsible for confirming current legal requirements and seeking legal advice when appropriate.</p>
                
                <h2>4. Federal Law Scope</h2>
                <p>For federal subscriptions, HRLA provides general informational guidance related to federal leave and accommodation frameworks, including the Family and Medical Leave Act (FMLA) and the Americans with Disabilities Act (ADA).</p>
                <p>Federal guidance is subject to eligibility thresholds, factual nuance, and evolving interpretation. HRLA does not guarantee applicability to any specific employment situation.</p>
                
                <h2>5. Accuracy, Currency, and "As-Of" Information</h2>
                <p>HRLA reflects laws and guidance <strong>generally in effect as of the date displayed within the tool</strong>.</p>
                <p>Employment laws and agency guidance may change without notice. HRLA does not represent that information is always current, complete, or applicable to every factual scenario.</p>
                
                <h2>6. Jurisdictional Scope</h2>
                <p>HRLA responses are limited to the jurisdiction selected by the user (California or federal). HRLA does not provide:</p>
                <ul>
                    <li>Multi-state comparisons</li>
                    <li>Conflict-of-law analysis</li>
                    <li>Guidance outside the selected jurisdiction</li>
                </ul>
                
                <h2>7. User Responsibilities</h2>
                <p>Users agree that they will:</p>
                <ul>
                    <li>Use HRLA for internal informational purposes only</li>
                    <li>Exercise independent professional judgment</li>
                    <li>Verify current laws and organizational policies</li>
                    <li>Refrain from entering sensitive personal data, including but not limited to:
                        <ul>
                            <li>Social Security numbers</li>
                            <li>Medical records or diagnoses</li>
                            <li>Dates of birth</li>
                        </ul>
                    </li>
                </ul>
                
                <h2>8. Subscription, Access, and Use</h2>
                <p>Access to HRLA is provided on a subscription basis. HRLA reserves the right to suspend or terminate access for:</p>
                <ul>
                    <li>Non-payment</li>
                    <li>Misuse</li>
                    <li>Excessive or automated usage</li>
                    <li>Violation of these Terms</li>
                </ul>
                
                <h2>9. Limitation of Liability</h2>
                <p>To the extent permitted by applicable law, HRLA and its operators disclaim liability for decisions, actions, penalties, claims, or damages arising from use of the service. HRLA is provided <strong>"as is" and "as available,"</strong> without warranties of any kind, express or implied.</p>
                
                <h2>10. Changes to the Service</h2>
                <p>HRLA may modify content, features, or availability at any time. Continued use constitutes acceptance of updated Terms.</p>
            </div>
        </div>
    </div>

    <!-- Privacy Policy Modal -->
    <div id="privacyModal" class="info-modal">
        <div class="info-modal-content">
            <button class="info-modal-close" aria-label="Close modal">&times;</button>
            <div class="info-modal-body">
                <h1>Privacy Policy</h1>
                <p><strong>Effective Date:</strong> January 2026</p>
                
                <h2>Information We Collect</h2>
                <p>We collect minimal information necessary to provide the HR Leave Assist service:</p>
                <ul>
                    <li><strong>Account Information:</strong> Name, email address, and organization details</li>
                    <li><strong>Usage Data:</strong> How you interact with the tool (features used, frequency)</li>
                    <li><strong>Payment Information:</strong> Processed securely through third-party payment processors</li>
                </ul>
                
                <h2>What We Do NOT Collect</h2>
                <p>We do not collect, store, or retain:</p>
                <ul>
                    <li>Employee personal information entered into the tool</li>
                    <li>Medical records or diagnoses</li>
                    <li>Social Security numbers</li>
                    <li>Specific leave requests or case details</li>
                </ul>
                
                <h2>How We Use Information</h2>
                <p>Information is used solely to:</p>
                <ul>
                    <li>Provide and improve the service</li>
                    <li>Process payments and manage subscriptions</li>
                    <li>Send service-related communications</li>
                    <li>Ensure security and prevent misuse</li>
                </ul>
                
                <h2>Data Security</h2>
                <p>We implement industry-standard security measures to protect your information. However, no method of transmission over the internet is 100% secure.</p>
                
                <h2>Third-Party Services</h2>
                <p>We use trusted third-party services for:</p>
                <ul>
                    <li>Payment processing (PayPal, Stripe)</li>
                    <li>Email communications</li>
                    <li>Hosting and infrastructure</li>
                </ul>
                
                <h2>Your Rights</h2>
                <p>You have the right to:</p>
                <ul>
                    <li>Access your account information</li>
                    <li>Request corrections or updates</li>
                    <li>Cancel your subscription at any time</li>
                    <li>Request account deletion</li>
                </ul>
                
                <h2>Contact Us</h2>
                <p>For privacy-related questions, contact us at: <a href="mailto:talk2char@gmail.com">talk2char@gmail.com</a></p>
            </div>
        </div>
    </div>

    <!-- Cancellation & Refund Policy Modal -->
    <div id="refundModal" class="info-modal">
        <div class="info-modal-content">
            <button class="info-modal-close" aria-label="Close modal">&times;</button>
            <div class="info-modal-body">
                <h1>Cancellation & Refund Policy</h1>
                
                <h2>No Refunds</h2>
                <p>All purchases are <strong>non-refundable</strong>. Because this product provides immediate access to a licensed digital HR decision-support tool, <strong>no refunds or credits are issued</strong> for any reason, including partial use, non-use, or change of mind.</p>
                
                <h2>Monthly Subscriptions</h2>
                <p>If you are on a <strong>monthly subscription</strong>, you may cancel at any time.</p>
                <ul>
                    <li>Cancellation stops <strong>future billing</strong></li>
                    <li>Access continues through the <strong>end of the current billing period</strong></li>
                    <li>No refunds or prorated credits are provided for the current month</li>
                </ul>
                
                <h2>Annual Subscriptions</h2>
                <p>If you are on an <strong>annual subscription with installment payments</strong>, you may cancel future payments at any time.</p>
                <ul>
                    <li>Cancellation stops <strong>remaining scheduled payments</strong></li>
                    <li>No refunds or credits are issued for payments already made</li>
                    <li>Access continues through the period already paid for</li>
                </ul>
                
                <h2>Immediate Access Disclaimer</h2>
                <p>Access to the tool is granted immediately upon purchase. As a result, all sales are considered final once access has been provided.</p>
                
                <h2>Wrong Version Purchases</h2>
                <p>You are responsible for selecting the correct version (Federal, California, Academic, etc.). If you believe you selected the wrong version, contact us promptly. Requests will be reviewed at our discretion. No refunds are guaranteed.</p>
                
                <h2>Responsibility & Use</h2>
                <p>This tool supports HR drafting and decision support only. All final decisions, compliance obligations, and use of outputs remain the responsibility of the purchaser.</p>
                
                <div class="policy-summary">
                    <h3>Summary</h3>
                    <ul>
                        <li>✔️ Cancel anytime</li>
                        <li>✔️ Stop future payments</li>
                        <li>✖️ No refunds</li>
                        <li>✖️ No credits or proration</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- User Access & Licensing Modal -->
    <div id="licensingModal" class="info-modal">
        <div class="info-modal-content">
            <button class="info-modal-close" aria-label="Close modal">&times;</button>
            <div class="info-modal-body">
                <h1>User Access, Licensing & Data Use</h1>
                
                <h3>What is this tool?</h3>
                <p>This is an internal HR support tool that helps HR teams draft clear, consistent responses to employee leave and accommodation questions. It does not make decisions, send emails, or replace HR judgment. It helps HR respond faster and more consistently.</p>
                
                <h3>Who is allowed to use it?</h3>
                <p>Only authorized HR staff or designated administrators within the organization may use this tool. It is not for employees and should not be shared outside the organization.</p>
                
                <h3>What does it help with?</h3>
                <p>The tool helps HR:</p>
                <ul>
                    <li>Draft responses to employee leave questions</li>
                    <li>Outline next steps in leave or accommodation situations</li>
                    <li>Keep explanations clear, consistent, and professional</li>
                    <li>Reduce the time spent rewriting the same answers</li>
                    <li><strong>HR reviews and approves everything before it is used.</strong></li>
                </ul>
                
                <h3>What does it NOT do?</h3>
                <p>This tool does not:</p>
                <ul>
                    <li>Approve or deny leave</li>
                    <li>Decide eligibility</li>
                    <li>Calculate pay or benefits</li>
                    <li>Store employee records</li>
                    <li>Send messages to employees</li>
                    <li>Replace HR systems or legal review</li>
                </ul>
                <p><em>Think of it as a smart drafting assistant, not an automated decision-maker.</em></p>
                
                <h3>Can employees use it directly?</h3>
                <p><strong>No.</strong> Employees never interact with this tool directly. All use happens behind the scenes by HR.</p>
                
                <h3>Does it store or remember employee data?</h3>
                <p><strong>No.</strong> The tool does not save, remember, or build profiles on employees. Information entered is used only to generate a response in the moment.</p>
                
                <h3>Is this a system of record?</h3>
                <p><strong>No.</strong> It does not replace HR systems, payroll, benefits platforms, or document storage systems. All official records remain where they are today.</p>
                
                <h3>Is it secure for internal use?</h3>
                <p><strong>Yes.</strong> The tool is designed for internal business use only. Organizations control:</p>
                <ul>
                    <li>Who has access</li>
                    <li>What information is entered</li>
                    <li>How outputs are used</li>
                    <li>HR remains responsible for final review and communication.</li>
                </ul>
                
                <h3>Can access be limited or removed?</h3>
                <p><strong>Yes.</strong> Organizations decide who can use the tool and can remove access at any time.</p>
                
                <h3>Can it be used across departments or locations?</h3>
                <p><strong>Yes</strong>—within the same organization, as long as the correct version is used for the applicable rules or location.</p>
                
                <div class="policy-summary">
                    <h3>Summary</h3>
                    <ul>
                        <li>✔️ Helps HR draft responses faster</li>
                        <li>✔️ Improves consistency and clarity</li>
                        <li>✔️ Keeps decisions with people—not software</li>
                        <li>✖️ Does not make decisions</li>
                        <li>✖️ Does not store data</li>
                        <li>✖️ Not for employee self-service</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Start Guide Modal -->
    <div id="quickStartModal" class="info-modal">
        <div class="info-modal-content">
            <button class="info-modal-close" aria-label="Close modal">&times;</button>
            <div class="info-modal-body">
                <h1>Leave Assistant — Quick Start Guide</h1>
                <p class="subtitle">For HR Professionals</p>
                
                <h3>What This Tool Does</h3>
                <p>The Leave Assistant drafts <strong>employee-ready HR responses</strong> to leave and accommodation questions. It saves drafting time while keeping <strong>HR in control of the final message</strong>.</p>
                
                <h2>How to Use (Fast)</h2>
                
                <div class="step-guide">
                    <h3>1️⃣ Enter the Question or Email</h3>
                    <ul>
                        <li>Copy/paste the employee's message or type your leave question.</li>
                        <li>Use the employee's wording whenever possible.</li>
                    </ul>
                    
                    <h3>2️⃣ Review the Draft Response</h3>
                    <p>The assistant generates a response:</p>
                    <ul>
                        <li><strong>Written</strong> from HR to the employee</li>
                        <li><strong>Clear, professional,</strong> and employee-friendly</li>
                        <li><strong>Focused</strong> on job-protected leave, next steps, and expectations</li>
                    </ul>
                    
                    <h3>3️⃣ Verify the Details</h3>
                    <p>Before using:</p>
                    <ul>
                        <li>Confirm dates, leave type, and eligibility assumptions</li>
                        <li>Check alignment with your policies and CBAs</li>
                        <li>Ensure the response fits your organization's process</li>
                        <li><strong>HR remains the final authority.</strong></li>
                    </ul>
                    
                    <h3>4️⃣ Edit as Needed</h3>
                    <p>Make light edits to:</p>
                    <ul>
                        <li>Add internal contacts or department names</li>
                        <li>Adjust tone or length</li>
                        <li>Remove sections that don't apply</li>
                        <li><strong>Editing is expected.</strong></li>
                    </ul>
                    
                    <h3>5️⃣ Send or Save</h3>
                    <p>Use the final response to:</p>
                    <ul>
                        <li>Reply to the employee</li>
                        <li>Save to the employee file</li>
                        <li>Maintain consistent HR documentation</li>
                    </ul>
                </div>
                
                <h3>Optional: Refine the Response</h3>
                <p>For complex situations, you can:</p>
                <ul>
                    <li>Add missing facts and regenerate</li>
                    <li>Ask a follow-up question</li>
                    <li>Request a shorter or more direct version</li>
                    <li>Stay within the same jurisdiction and leave context.</li>
                </ul>
                
                <div class="feature-grid">
                    <div class="feature-box">
                        <h3>What the Leave Assistant Does</h3>
                        <ul>
                            <li>✔️ Drafts employee-ready HR responses</li>
                            <li>✔️ Explains leave concepts clearly</li>
                            <li>✔️ Supports accommodation and return-to-work discussions</li>
                            <li>✔️ Improves consistency and efficiency</li>
                        </ul>
                    </div>
                    <div class="feature-box">
                        <h3>What It Does Not Do</h3>
                        <ul>
                            <li>❌ Make final eligibility decisions</li>
                            <li>❌ Provide legal advice</li>
                            <li>❌ Calculate pay or benefits</li>
                            <li>❌ Create forms, policies, or documents</li>
                        </ul>
                    </div>
                </div>
                
                <div class="best-practice">
                    <h3>Best Practice</h3>
                    <p>Use the Leave Assistant as your <strong>first-draft HR partner</strong> — fast, consistent, and supportive — with <strong>HR always in control</strong>.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Scope & Use Modal -->
    <div id="productScopeModal" class="info-modal">
        <div class="info-modal-content">
            <button class="info-modal-close" aria-label="Close modal">&times;</button>
            <div class="info-modal-body">
                <h1>What HR Leave Assist Does / Does Not Do</h1>
                
                <div class="comparison-table">
                    <div class="comparison-column">
                        <h2>HRLA Does</h2>
                        <ul>
                            <li>Provides clear, practical guidance on employee leave and accommodation questions under <strong>California and Federal law</strong></li>
                            <li>Translates complex leave frameworks (e.g., CFRA, PDL, FMLA, ADA) into understandable, HR-ready explanations</li>
                            <li>Helps HR professionals understand how different leave and accommodation rules may apply to a specific scenario</li>
                            <li>Supports consistent, compliant communication with employees</li>
                            <li>Identifies when the <strong>interactive process</strong> or accommodation considerations may be required</li>
                            <li>Flags overlapping or concurrent leave considerations</li>
                            <li>Uses jurisdiction-specific logic based on the <strong>selected state or federal scope</strong></li>
                            <li>Encourages appropriate follow-up questions when information is missing or unclear</li>
                            <li>Reflects laws generally in effect <strong>as of the date shown in the tool</strong></li>
                            <li>Helps HR teams respond more quickly and confidently to routine leave questions</li>
                        </ul>
                    </div>
                    
                    <div class="comparison-column">
                        <h2>HRLA Does Not</h2>
                        <ul>
                            <li>Provide legal advice or medical advice</li>
                            <li>Make final eligibility or compliance determinations</li>
                            <li>Replace legal counsel, medical certification, or agency determinations</li>
                            <li>Draft policies, handbooks, or formal legal documents</li>
                            <li>Conduct or document the interactive process on your behalf</li>
                            <li>Guarantee compliance or risk-free outcomes</li>
                            <li>Compare laws across states or provide multi-state analysis</li>
                            <li>Assume facts, make medical judgments, or interpret diagnoses</li>
                            <li>Monitor or automatically update for real-time law changes</li>
                            <li>Store or manage employee medical records or sensitive personal data</li>
                        </ul>
                    </div>
                </div>
                
                <div class="summary-box">
                    <h3>Plain-Language Summary for Buyers</h3>
                    <p>HRLA helps you understand the rules and apply them thoughtfully. You remain responsible for the final decision.</p>
                    
                    <h3>Micro-Version</h3>
                    <p>HRLA is a decision-support tool—not legal advice. It helps HR professionals understand leave and accommodation frameworks so they can respond consistently and confidently, while retaining responsibility for final decisions.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Center Modal -->
    <div id="helpCenterModal" class="info-modal">
        <div class="info-modal-content">
            <button class="info-modal-close" aria-label="Close modal">&times;</button>
            <div class="info-modal-body">
                <h1>Help Center</h1>
                
                <h2>Getting Started</h2>
                <p>New to HR Leave Assist? Check out our <a href="#" data-modal="quickStartModal" class="inline-modal-link">Quick Start Guide</a> for a fast overview of how to use the tool effectively.</p>
                
                <h2>Common Questions</h2>
                
                <h3>How do I access the tool?</h3>
                <p>After registering and verifying your email, log in to access the Federal FMLA and California Leave assistants from your dashboard.</p>
                
                <h3>What information should I enter?</h3>
                <p>Copy and paste the employee's leave question or email. The more context you provide, the better the response will be. Avoid entering sensitive personal information like Social Security numbers or detailed medical diagnoses.</p>
                
                <h3>Can I edit the responses?</h3>
                <p>Yes! All responses are meant to be reviewed and edited by HR before use. You maintain full control over the final message.</p>
                
                <h3>How do I cancel my subscription?</h3>
                <p>You can cancel anytime from your account settings. See our <a href="#" data-modal="refundModal" class="inline-modal-link">Cancellation & Refund Policy</a> for details.</p>
                
                <h3>Is my data secure?</h3>
                <p>Yes. We use industry-standard security measures. The tool does not store employee information you enter. See our <a href="#" data-modal="privacyModal" class="inline-modal-link">Privacy Policy</a> for more information.</p>
                
                <h2>Technical Support</h2>
                <p>If you're experiencing technical issues or have questions not covered here, please contact us:</p>
                <ul>
                    <li><strong>Email:</strong> <a href="mailto:talk2char@gmail.com">talk2char@gmail.com</a></li>
                    <li><strong>Response Time:</strong> Within 1-2 business days</li>
                </ul>
                
                <h2>Additional Resources</h2>
                <ul>
                    <li><a href="#" data-modal="productScopeModal" class="inline-modal-link">Product Scope & Use</a></li>
                    <li><a href="#" data-modal="termsModal" class="inline-modal-link">Terms of Use</a></li>
                    <li><a href="#" data-modal="licensingModal" class="inline-modal-link">User Access & Licensing</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Contact Us Modal -->
    <div id="contactModal" class="info-modal">
        <div class="info-modal-content">
            <button class="info-modal-close" aria-label="Close modal">&times;</button>
            <div class="info-modal-body">
                <h1>Contact Us</h1>
                
                <p>We're here to help! Whether you have questions about the tool, need technical support, or want to discuss organizational licensing, we'd love to hear from you.</p>
                
                <h2>Email Support</h2>
                <div class="contact-box">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h3>General Inquiries & Support</h3>
                        <p><a href="mailto:talk2char@gmail.com">talk2char@gmail.com</a></p>
                        <p class="response-time">Response time: 1-2 business days</p>
                    </div>
                </div>
                
                <h2>What to Include in Your Message</h2>
                <p>To help us assist you quickly, please include:</p>
                <ul>
                    <li>Your name and organization (if applicable)</li>
                    <li>Your account email address</li>
                    <li>A brief description of your question or issue</li>
                    <li>Any relevant screenshots (for technical issues)</li>
                </ul>
                
                <h2>Common Inquiries</h2>
                <div class="inquiry-grid">
                    <div class="inquiry-item">
                        <h3><i class="fas fa-question-circle"></i> Product Questions</h3>
                        <p>Features, capabilities, and how the tool works</p>
                    </div>
                    <div class="inquiry-item">
                        <h3><i class="fas fa-wrench"></i> Technical Support</h3>
                        <p>Login issues, bugs, or technical difficulties</p>
                    </div>
                    <div class="inquiry-item">
                        <h3><i class="fas fa-building"></i> Organization Licensing</h3>
                        <p>Multi-user access and team subscriptions</p>
                    </div>
                    <div class="inquiry-item">
                        <h3><i class="fas fa-credit-card"></i> Billing & Subscriptions</h3>
                        <p>Payment questions and account management</p>
                    </div>
                </div>
                
                <h2>Before You Contact Us</h2>
                <p>You might find your answer faster in these resources:</p>
                <ul>
                    <li><a href="#" data-modal="helpCenterModal" class="inline-modal-link">Help Center</a> - Common questions and troubleshooting</li>
                    <li><a href="#" data-modal="quickStartModal" class="inline-modal-link">Quick Start Guide</a> - How to use the tool</li>
                    <li><a href="#faqs" class="inline-modal-link">FAQs</a> - Frequently asked questions about FMLA</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuToggle')?.addEventListener('click', function() {
            const navMenu = document.querySelector('.nav-menu');
            navMenu.classList.toggle('mobile-open');
            this.setAttribute('aria-expanded', navMenu.classList.contains('mobile-open'));
        });

        // FAQ Accordion
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

        // Modal functionality
        const modals = document.querySelectorAll('.info-modal');
        const modalTriggers = document.querySelectorAll('[data-modal]');
        const modalCloses = document.querySelectorAll('.info-modal-close');

        // Open modal
        modalTriggers.forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                const modalId = this.getAttribute('data-modal');
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            });
        });

        // Close modal
        modalCloses.forEach(closeBtn => {
            closeBtn.addEventListener('click', function() {
                const modal = this.closest('.info-modal');
                modal.classList.remove('active');
                document.body.style.overflow = '';
            });
        });

        // Close modal when clicking outside
        modals.forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                modals.forEach(modal => {
                    if (modal.classList.contains('active')) {
                        modal.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            }
        });

        // Handle inline modal links (links within modals that open other modals)
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('inline-modal-link')) {
                e.preventDefault();
                const currentModal = e.target.closest('.info-modal');
                const targetModalId = e.target.getAttribute('data-modal');
                
                if (currentModal && targetModalId) {
                    // Close current modal
                    currentModal.classList.remove('active');
                    
                    // Open target modal
                    const targetModal = document.getElementById(targetModalId);
                    if (targetModal) {
                        setTimeout(() => {
                            targetModal.classList.add('active');
                        }, 300);
                    }
                }
            }
        });
    </script>
</body>
</html>
