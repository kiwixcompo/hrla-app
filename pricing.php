<?php
/**
 * Pricing Page
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'config/site-settings.php';
require_once 'includes/auth.php';
require_once 'includes/content.php';

// Initialize content system
initContentSystem();

$auth = getAuth();

$pageTitle = 'Pricing - HRLA | HR Leave Assist';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <meta name="description" content="HR Leave Assist pricing plans. Choose from Free Trial, Monthly, Annual, or Organization plans for HR leave response generation.">
    <meta name="keywords" content="HR leave assistant pricing, FMLA compliance pricing, HR tools cost">
    
    <link rel="icon" type="image/svg+xml" href="hrla-logo-new-fixed.svg">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="styles.css?v=1.2">
    <link rel="stylesheet" href="assets/css/main.css?v=<?php echo time(); ?>">

    <style>
        :root {
            --hrla-blue: <?php echo getContent('color_primary', '#0322D8'); ?>;
            --hrla-dark-blue: <?php echo getContent('color_dark_blue', '#1800AD'); ?>;
            --hrla-green: <?php echo getContent('color_secondary', '#3DB20B'); ?>;
            --hrla-black: #000000;
        }

        /* --- STYLES COPIED FROM INDEX FOR CONSISTENCY --- */
        .pricing-section {
            padding: 60px 0 80px;
            background-color: #f8f9fa;
        }

        .pricing-page-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .pricing-page-header h1 {
            font-size: 2.5rem;
            color: var(--hrla-black);
            margin-bottom: 15px;
            font-weight: 800;
        }

        .pricing-page-header p {
            font-size: 1.2rem;
            color: #666;
        }

        /* The Grid System */
        .pricing-grid-detailed {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .pricing-card-detailed {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 30px;
            flex: 1;
            min-width: 250px;
            max-width: 300px;
            display: flex;
            flex-direction: column;
            position: relative;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .pricing-card-detailed:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        }

        .pricing-card-detailed.featured {
            border: 2px solid var(--hrla-blue);
            transform: scale(1.02);
            z-index: 1;
        }

        .pricing-card-detailed.featured:hover {
            transform: scale(1.02) translateY(-5px);
        }

        .pricing-badge {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--hrla-blue);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .pricing-header h3 {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            color: #111;
        }

        .pricing-description p {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 20px;
            min-height: 40px;
        }

        .pricing-features ul {
            list-style: none;
            padding: 0;
            margin-bottom: 25px;
            text-align: left;
        }

        .pricing-features li {
            font-size: 0.9rem;
            color: #444;
            margin-bottom: 8px;
            display: flex;
            align-items: baseline;
        }

        .pricing-best-for {
            background: #f2f4fe;
            padding: 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            color: #333;
            margin-bottom: 20px;
            margin-top: auto;
        }
        
        .pricing-best-for strong {
            display: inline;
            margin-bottom: 0;
            color: #000;
        }

        .btn-primary {
            background-color: var(--hrla-blue);
            border-color: var(--hrla-blue);
            font-weight: 700;
            padding: 10px 20px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            display: inline-block;
            transition: 0.2s;
        }

        .btn-primary:hover {
            background-color: var(--hrla-dark-blue);
            border-color: var(--hrla-dark-blue);
        }

        .btn-block {
            display: block;
            width: 100%;
            text-align: center;
        }
        
        /* Navigation (Copied for isolated view) */
        .nav-logo-symbol {
            font-weight: 900;
            font-size: 1.5rem;
        }
        .nav-hr { color: var(--hrla-blue); }
        .nav-la { color: var(--hrla-green); }
        
        /* Mobile Menu Styles */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--hrla-blue);
            cursor: pointer;
            padding: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block !important;
                z-index: 1001;
            }
            
            .nav-menu {
                position: fixed;
                top: 70px;
                left: 0;
                right: 0;
                background: white !important;
                border-bottom: 1px solid #e0e0e0;
                padding: 1rem 1.5rem;
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                z-index: 1000;
            }
            
            .nav-menu.mobile-open {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
                background: white !important;
            }
            
            .nav-menu .nav-link {
                margin: 0.5rem 0;
                text-align: center;
                display: block;
                width: 100%;
                padding: 0.75rem 1rem;
                border-radius: 6px;
                color: #666 !important;
                background: transparent !important;
                text-decoration: none;
                font-weight: 500;
                transition: all 0.2s ease;
            }
            
            .nav-menu .nav-link:hover,
            .nav-menu .nav-link.active {
                color: var(--hrla-blue) !important;
                background: #f8f9fa !important;
            }
            
            .nav-menu .btn {
                margin: 0.5rem 0;
                text-align: center;
                display: block;
                width: 100%;
                padding: 0.75rem 1rem;
                border-radius: 6px;
                text-decoration: none;
                font-weight: 600;
                transition: all 0.2s ease;
            }
            
            .nav-menu .btn-outline {
                background: white !important;
                color: var(--hrla-blue) !important;
                border: 2px solid var(--hrla-blue) !important;
            }
            
            .nav-menu .btn-outline:hover {
                background: #f8f9fa !important;
            }
            
            .nav-menu .btn-primary {
                background: var(--hrla-blue) !important;
                color: white !important;
                border: 2px solid var(--hrla-blue) !important;
            }
            
            .nav-menu .btn-primary:hover {
                background: var(--hrla-dark-blue) !important;
                border-color: var(--hrla-dark-blue) !important;
            }
        }
    </style>
</head>
<body>
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
                <a href="pricing.php" class="nav-link active" role="menuitem">Pricing</a>
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

    <div class="pricing-page pricing-section">
        <div class="container">
            <div class="pricing-page-header">
                <h1><?php echo htmlspecialchars(getContent('pricing_title', 'Pricing Plans')); ?></h1>
                <p>Choose the plan that works best for your HR team</p>
            </div>

            <div class="pricing-grid-detailed">
                <div class="pricing-card-detailed">
                    <div class="pricing-header">
                        <h3><?php echo htmlspecialchars(getContent('pricing_free_title', 'Free Trial — $0')); ?></h3>
                    </div>
                    <div class="pricing-description">
                        <p>Evaluate how HR Leave Assist supports your individual HR workflow</p>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li>• Trial access to HR Leave Assist</li>
                            <li>• Guidance aligned to laws</li>
                            <li>• AI-assisted draft responses</li>
                            <li>• Up to 20 questions in trial</li>
                            <li>• No payment required</li>
                        </ul>
                    </div>
                    <div class="pricing-best-for">
                        <strong>Best for:</strong> <?php echo htmlspecialchars(getContent('pricing_free_description', 'HR professionals who want to test the tool with real-world scenarios before subscribing.')); ?>
                    </div>
                    <div class="pricing-action">
                        <a href="<?php echo appUrl('register.php'); ?>" class="btn btn-primary btn-block">
                            Start Free Trial
                        </a>
                    </div>
                </div>
                
                <div class="pricing-card-detailed featured">
                    <div class="pricing-badge">Most Popular</div>
                    <div class="pricing-header">
                        <h3><?php echo htmlspecialchars(getContent('pricing_monthly_title', 'Monthly — $29')); ?></h3>
                    </div>
                    <div class="pricing-description">
                        <p>For individual HR professionals managing ongoing leave questions</p>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li>• Full individual access</li>
                            <li>• Federal & California laws</li>
                            <li>• Draft clear responses</li>
                            <li>• Unlimited questions</li>
                            <li>• Cancel anytime</li>
                        </ul>
                    </div>
                    <div class="pricing-best-for">
                        <strong>Best for:</strong> <?php echo htmlspecialchars(getContent('pricing_monthly_description', 'Individual HR professionals who regularly respond to employee leave inquiries.')); ?>
                    </div>
                    <div class="pricing-action">
                        <a href="<?php echo appUrl('register.php'); ?>" class="btn btn-primary btn-block">
                            Subscribe Monthly
                        </a>
                    </div>
                </div>
                
                <div class="pricing-card-detailed">
                    <div class="pricing-header">
                        <h3><?php echo htmlspecialchars(getContent('pricing_annual_title', 'Annual — $290')); ?></h3>
                        <div style="color: var(--hrla-green); font-size: 0.9rem; font-weight: bold;"><?php echo htmlspecialchars(getContent('pricing_annual_subtitle', '(2 months free)')); ?></div>
                    </div>
                    <div class="pricing-description">
                        <p>Consistent, uninterrupted access for individual professional use</p>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li>• Everything in Monthly</li>
                            <li>• 12 months continuous access</li>
                            <li>• Unlimited questions</li>
                            <li>• Predictable annual billing</li>
                        </ul>
                    </div>
                    <div class="pricing-best-for">
                        <strong>Best for:</strong> <?php echo htmlspecialchars(getContent('pricing_annual_description', 'Individual HR professionals who rely on HR Leave Assist as part of their regular, year-round workflow.')); ?>
                    </div>
                    <div class="pricing-action">
                        <a href="<?php echo appUrl('register.php'); ?>" class="btn btn-primary btn-block">
                            Subscribe Annually
                        </a>
                    </div>
                </div>
                
                <div class="pricing-card-detailed">
                    <div class="pricing-header">
                        <h3><?php echo htmlspecialchars(getContent('pricing_org_title', 'Organization — $580 / yr')); ?></h3>
                    </div>
                    <div class="pricing-description">
                        <p>Shared annual access for up to 5 HR professionals</p>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li>• Up to <strong>5 named HR users</strong></li>
                            <li>• 12 months access per user</li>
                            <li>• Unlimited questions per user</li>
                            <li>• Centralized annual billing</li>
                        </ul>
                    </div>
                    <div class="pricing-best-for">
                        <strong>Best for:</strong> <?php echo htmlspecialchars(getContent('pricing_org_description', 'Small HR teams of 2 to 5 who regularly respond to employee leave questions and want consistent, shared access.')); ?>
                    </div>
                    <div class="pricing-action">
                        <a href="<?php echo appUrl('register.php'); ?>" class="btn btn-primary btn-block">
                            Subscribe Organization
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="landing-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <img src="footer_logo.png" alt="HRLA Logo" class="footer-logo-img">
                    <p><?php echo htmlspecialchars(getContent('footer_description', 'A leave-support tool built by HR, for HR, to help apply consistent, compliance-aligned responses to employee leave questions.')); ?></p>
                </div>
                
                <div class="footer-legal-links">
                    <a href="product-scope.php">Terms of Use</a>
                    <a href="privacy-policy.php">Privacy Policy</a>
                    <a href="product-scope.php">Cancellation & Refund Policy</a>
                    <a href="product-scope.php">User Access & Licensing</a>
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