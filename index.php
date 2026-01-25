<?php
/**
 * Home Page / Landing Page
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'config/site-settings.php';
require_once 'includes/auth.php';
require_once 'includes/content.php';

// Initialize content system
initContentSystem();

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

$pageTitle = 'HRLA - HR Leave Assist | HR Leave Response Generator';
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
    
    <meta name="description" content="AI-powered HR leave response generator for federal and California leave questions. Draft clear, compliant employee communications aligned with FMLA, CFRA, PDL, and ADA. Built by HR expert with 25+ years experience.">
    <meta name="keywords" content="HR leave assistant, FMLA compliance, California leave laws, HR tools, employee relations, leave management">
    <meta name="author" content="HRLA - HR Leave Assist">
    
    <meta property="og:title" content="HRLA - HR Leave Assist | HR Leave Response Generator">
    <meta property="og:description" content="AI-powered HR leave response generator for federal and California leave questions. Draft clear, compliant employee communications aligned with FMLA, CFRA, PDL, and ADA.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://hrleaveassist.com">
    <meta property="og:image" content="hrla-logo-new-fixed.svg">
    
    <link rel="icon" type="image/svg+xml" href="hrla-logo-new-fixed.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="hrla_logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="hrla_logo.png">
    <link rel="apple-touch-icon" href="hrla_logo.png">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="styles.css?v=1.2">
    <link rel="stylesheet" href="assets/css/custom.php?v=<?php echo time(); ?>">

    <style>
        /* --- EXACT COLOR VARIABLES --- */
        :root {
            --hrla-blue: <?php echo getContent('color_primary', '#0322D8'); ?>;
            --hrla-dark-blue: <?php echo getContent('color_dark_blue', '#1800AD'); ?>;
            --hrla-green: <?php echo getContent('color_secondary', '#3DB20B'); ?>;
            --hrla-black: #000000;
            --hrla-red: <?php echo getContent('color_red', '#FF0000'); ?>; /* Standard Red */
        }

        /* --- NAVIGATION --- */
        .nav-brand { display: flex; align-items: center; }
        .nav-logo-img { max-height: 45px; width: auto; display: block; }

        /* --- HERO SECTION --- */
        .hero-section { background-color: #f2f4fe; padding: 80px 0; }
        .hero-brand-text { font-size: 3rem; font-weight: 900; text-transform: uppercase; margin-bottom: 20px; letter-spacing: -1px; line-height: 1; }
        .text-blue { color: var(--hrla-blue); }
        .text-green { color: var(--hrla-green); }
        
        .hero-title-custom { font-size: 2.5rem; line-height: 1.2; font-weight: 800; color: var(--hrla-black); margin-bottom: 30px; max-width: 100%; }
        
        .feature-list-custom { list-style: none; padding: 0; margin: 0 0 40px 0; display: block; text-align: left; }
        .feature-list-custom li { font-size: 1.15rem; color: #333; margin-bottom: 15px; display: flex; align-items: flex-start; font-weight: 400; }
        .checkmark-custom { color: var(--hrla-green); font-weight: bold; font-size: 1.4rem; margin-right: 15px; line-height: 1; }

        /* General Buttons */
        .btn-primary-custom { background-color: var(--hrla-blue); color: white; border: 2px solid var(--hrla-blue); padding: 16px 32px; font-weight: 700; font-size: 1.1rem; border-radius: 6px; text-decoration: none; display: inline-block; transition: all 0.2s; }
        .btn-primary-custom:hover { background-color: var(--hrla-dark-blue); border-color: var(--hrla-dark-blue); color: white; }
        .btn-secondary-custom { background-color: white; color: var(--hrla-blue); border: 2px solid var(--hrla-blue); padding: 16px 32px; font-weight: 700; font-size: 1.1rem; border-radius: 6px; text-decoration: none; display: inline-block; transition: all 0.2s; }
        .btn-secondary-custom:hover { background-color: #eef2ff; }
        .button-group-custom { display: flex; gap: 20px; justify-content: flex-start; }

        /* --- HERO CARD --- */
        .hero-visual { display: flex; justify-content: center; align-items: center; }
        .hero-card { background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; max-width: 500px; width: 100%; }
        .card-header { background: #f8f9fa; padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; align-items: center; }
        .card-dots { display: flex; gap: 6px; margin-right: 15px; }
        .dot { width: 10px; height: 10px; border-radius: 50%; }
        .dot.red { background: #ff5f56; }
        .dot.yellow { background: #ffbd2e; }
        .dot.green { background: #27c93f; }
        .card-title { font-weight: 700; font-size: 1rem; color: #333; letter-spacing: 0.5px; }
        .card-content { padding: 25px; }
        .input-demo, .output-demo { margin-bottom: 25px; }
        .input-demo label, .output-demo label { display: block; font-weight: 700; margin-bottom: 10px; color: #111; }
        .demo-text, .demo-response { font-size: 0.9rem; color: #666; margin-bottom: 8px; font-style: italic; border-left: none; padding-left: 0; }
        .demo-input, .demo-output { background: #f2f4fe; padding: 15px; border-radius: 0 8px 8px 0; font-size: 0.95rem; line-height: 1.5; color: #333; border-left: 4px solid var(--hrla-blue); }
        .demo-note { display: flex; align-items: center; justify-content: center; margin-top: 20px; font-size: 0.95rem; color: var(--hrla-blue); font-weight: 700; }
        .demo-note i { color: var(--hrla-green); margin-right: 10px; font-size: 1.3rem; }
        .demo-note em { font-style: normal; }

        /* --- WHAT IS HR LEAVE ASSIST SECTION --- */
        .what-is-section { 
            padding: 80px 0; 
            background: #fff; 
        }
        
        .video-content {
            display: flex;
            justify-content: center;
            align-items: center;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .video-wrapper {
            position: relative;
            width: 100%;
            height: 0;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .video-wrapper iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        /* Mobile video section inside hero */
        .what-is-mobile {
            padding: 40px 0;
            text-align: center;
        }
        
        .mobile-video-header {
            margin-bottom: 30px;
        }
        
        .mobile-video-header .section-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--hrla-black);
            margin: 0;
        }
        
        /* Mobile video section as separate section */
        .what-is-mobile-section {
            padding: 60px 0;
            background: #f8f9fa;
            text-align: center;
        }
        
        .what-is-mobile-section .mobile-video-header {
            margin-bottom: 40px;
        }
        
        .what-is-mobile-section .mobile-video-header .section-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--hrla-black);
            margin: 0;
        }
        
        /* Responsive visibility */
        .desktop-only {
            display: block;
        }
        
        .mobile-only {
            display: none;
        }
        
        @media (max-width: 991px) {
            .desktop-only {
                display: none;
            }
            
            .mobile-only {
                display: block;
            }
            
            .what-is-mobile .video-content {
                max-width: 100%;
                padding: 0 20px;
            }
            
            .what-is-mobile-section .video-content {
                max-width: 100%;
                padding: 0 20px;
            }
            
            .mobile-video-header .section-title {
                font-size: 1.8rem;
            }
        }
        
        @media (max-width: 768px) {
            .what-is-section {
                padding: 60px 0;
            }
            
            .what-is-mobile {
                padding: 30px 0;
            }
            
            .what-is-mobile-section {
                padding: 40px 0;
            }
            
            .what-is-mobile .video-content {
                padding: 0 15px;
            }
            
            .what-is-mobile-section .video-content {
                padding: 0 15px;
            }
            
            .mobile-video-header .section-title {
                font-size: 1.6rem;
            }
        }

        /* --- ABOUT SECTION --- */
        .about-section { padding: 80px 0; background: #fff; }
        .about-main { display: flex; flex-direction: column; gap: 40px; }
        .about-logo { flex: 0 0 auto; display: flex; flex-direction: column; align-items: flex-start; }
        .about-logo-img { max-width: 250px; height: auto; margin-bottom: 10px; }
        .about-logo-text { font-family: 'Inter', sans-serif; font-size: 2rem; font-weight: 800; color: var(--hrla-blue); margin: 0; line-height: 1.2; }
        .about-text { font-size: 1.1rem; line-height: 1.6; color: #333; }
        .about-text p { margin-bottom: 20px; }

        /* --- PRICING SECTION (MINIFIED) --- */
        .pricing-section { padding: 80px 0; background-color: #ffffff; }
        .pricing-grid-minified { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; max-width: 1200px; margin: 0 auto; }
        .pricing-card-minified { background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 25px; flex: 1; min-width: 240px; max-width: 280px; display: flex; flex-direction: column; justify-content: space-between; position: relative; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .pricing-card-minified:hover { transform: translateY(-5px); }
        .pricing-card-minified.featured { border: 2px solid var(--hrla-blue); transform: scale(1.03); z-index: 10; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .pricing-card-minified.featured:hover { transform: scale(1.03) translateY(-5px); }
        .pricing-badge { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: var(--hrla-green); color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; white-space: nowrap; }
        .pricing-header-mini { text-align: center; margin-bottom: 20px; }
        .pricing-header-mini h3 { font-size: 1.4rem; font-weight: 800; margin-bottom: 5px; color: #111; }
        .pricing-sub { font-size: 0.85rem; font-weight: 700; color: #111; }
        .pricing-best-for-mini { background: #f8f9fa; padding: 15px; border-radius: 8px; font-size: 0.85rem; line-height: 1.4; color: #444; margin-bottom: 20px; flex-grow: 1; }
        .pricing-best-for-mini strong { display: block; margin-bottom: 5px; color: #000; }
        .btn-block { display: block; width: 100%; text-align: center; }

        /* --- FAQ SELECTION SECTION --- */
        .faq-selection-section { padding: 80px 0; background-color: #f2f4fe; }
        .faq-selection-header { text-align: center; margin-bottom: 50px; }
        .faq-selection-grid { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; }
        .faq-selection-card { 
            background: white; 
            padding: 40px 30px; 
            border-radius: 12px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); 
            text-align: center; 
            flex: 1; 
            min-width: 280px; 
            max-width: 400px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .faq-selection-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .faq-card-icon { font-size: 3rem; color: var(--hrla-blue); margin-bottom: 20px; }
        .faq-selection-card h3 { font-size: 1.5rem; font-weight: 800; margin-bottom: 15px; color: #111; }
        .faq-selection-card p { color: #666; margin-bottom: 30px; line-height: 1.5; }

        /* --- CTA SECTION (UPDATED) --- */
        .cta-section { padding: 80px 0; background-color: white; text-align: center; }
        .cta-content { max-width: 800px; margin: 0 auto; }
        
        .cta-title-red {
            color: var(--hrla-red); /* RED TEXT */
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 25px;
            line-height: 1.2;
        }
        
        .btn-cta-blue {
            background-color: var(--hrla-blue); /* BLUE BACKGROUND */
            color: #ffffff; /* WHITE TEXT */
            padding: 18px 40px;
            font-size: 1.2rem;
            font-weight: 700;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            border: 2px solid var(--hrla-blue);
            transition: all 0.3s ease;
        }
        
        .btn-cta-blue:hover {
            background-color: var(--hrla-dark-blue);
            border-color: var(--hrla-dark-blue);
            color: #ffffff;
        }

        /* --- FOOTER --- */
        .landing-footer { background-color: #1a1a1a; color: #fff; padding: 60px 0 30px; }
        .footer-content { display: flex; justify-content: space-between; align-items: flex-start; gap: 60px; margin-bottom: 40px; }
        .footer-brand { display: flex; flex-direction: column; align-items: flex-start; max-width: 500px; text-align: left; }
        
        /* Footer Logo - CSS Text Style to match image */
        .footer-logo-text { 
            font-family: 'Arial Black', 'Segoe UI Black', Impact, sans-serif;
            font-size: 4rem;
            font-weight: 900;
            text-transform: uppercase;
            line-height: 1;
            letter-spacing: -3px;
            margin-bottom: 30px;
        }
        .footer-hr { color: #0022ff; }
        .footer-la { color: #4cbb17; }
        
        .footer-brand p { color: #ccc; line-height: 1.6; font-size: 1.1rem; margin: 0; }
        
        /* Footer Links Vertical List */
        .footer-legal-links { display: flex; flex-direction: column; align-items: flex-start; gap: 15px; }
        .footer-legal-links a { color: #aaa; text-decoration: none; transition: color 0.2s; font-size: 1rem; }
        .footer-legal-links a:hover { color: #fff; text-decoration: underline; }
        
        .footer-bottom { border-top: 1px solid #333; padding-top: 30px; text-align: center; color: #666; font-size: 0.85rem; }
        
        @media (max-width: 768px) {
            .footer-content { flex-direction: column; align-items: center; text-align: center; gap: 40px; }
            .footer-brand { align-items: center; text-align: center; }
            .footer-legal-links { align-items: center; }
            .footer-logo-text { font-size: 3rem; }
        }

        @media (min-width: 992px) {
            .hero-content { text-align: left; padding-right: 40px; }
            .about-main { flex-direction: row; align-items: flex-start; gap: 60px; }
            .about-logo { width: 30%; }
            .about-text { width: 70%; }
        }
    </style>
</head>
<body>
    <a href="#main-content" class="sr-only">Skip to main content</a>
    <a href="#navigation" class="sr-only">Skip to navigation</a>

    <div id="landingPage" class="page">
        <nav class="landing-nav" role="navigation" aria-label="Main navigation" id="navigation">
            <div class="nav-container">
                <div class="nav-brand">
                    <a href="index.php">
                        <img src="site_logo.png" alt="HRLA Logo" class="nav-logo-img">
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

        <section class="hero-section" id="main-content">
            <div class="hero-container">
                <div class="hero-content">
                    <div class="hero-brand-text">
                        <span class="text-blue">HR LEAVE</span> <span class="text-green">ASSIST</span>
                    </div>
                    <h1 class="hero-title-custom">
                        <?php echo htmlspecialchars(getContent('hero_title', 'Answer Employee Leave Questions With Consistent Compliance Information')); ?>
                    </h1>
                    <ul class="feature-list-custom">
                        <li><span class="checkmark-custom">✔</span> <?php echo htmlspecialchars(getContent('hero_feature_1', 'Built by HR for HR professionals')); ?></li>
                        <li><span class="checkmark-custom">✔</span> <?php echo htmlspecialchars(getContent('hero_feature_2', 'Drafts employee-ready responses')); ?></li>
                        <li><span class="checkmark-custom">✔</span> <?php echo htmlspecialchars(getContent('hero_feature_3', 'Aligned with FMLA, PDL, ADA, and CFRA')); ?></li>
                        <li><span class="checkmark-custom">✔</span> <?php echo htmlspecialchars(getContent('hero_feature_4', 'Supports consistent HR decision-making')); ?></li>
                        <li><span class="checkmark-custom">✔</span> <?php echo htmlspecialchars(getContent('hero_feature_5', 'Helps reduce compliance risk')); ?></li>
                    </ul>
                    <div class="button-group-custom">
                        <a href="<?php echo appUrl('register.php'); ?>" class="btn-primary-custom">
                            <?php echo htmlspecialchars(getContent('hero_cta_primary', 'Try HR Leave Assist')); ?>
                        </a>
                        <a href="#how-it-works" class="btn-secondary-custom">
                            <?php echo htmlspecialchars(getContent('hero_cta_secondary', 'See How It Works')); ?>
                        </a>
                    </div>
                </div>
                
                <div class="hero-visual">
                    <div class="hero-card" role="img" aria-label="HRLA Response Generator Demo">
                        <div class="card-header">
                            <div class="card-dots">
                                <span class="dot red"></span>
                                <span class="dot yellow"></span>
                                <span class="dot green"></span>
                            </div>
                            <span class="card-title">HRLA RESPONSE GENERATOR</span>
                        </div>
                        <div class="card-content">
                            <div class="input-demo">
                                <label>Employee Leave Question</label>
                                <div class="demo-text">Paste or summarize the employee's question or situation.</div>
                                <div class="demo-input">I need time off for a medical procedure, and I'm not sure what I need to do.</div>
                            </div>
                            <div class="output-demo">
                                <label>HR Response to Employee</label>
                                <div class="demo-response">HR Leave Assist generates a response here.</div>
                                <div class="demo-output">Thanks for letting us know. We can walk through this together. Time off for a medical procedure may be covered as job-protected leave under the Family and Medical Leave Act (FMLA)</div>
                            </div>
                            <div class="demo-note">
                                <i class="fas fa-shield-alt"></i>
                                <em>All responses are reviewed and finalized by HR Professional</em>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- What Is HR Leave Assist Section - Mobile Only -->
        <section class="what-is-mobile-section mobile-only">
            <div class="container">
                <div class="mobile-video-header">
                    <h2 class="section-title"><?php echo htmlspecialchars(getContent('video_section_title', 'What Is HR Leave Assist?')); ?></h2>
                </div>
                <div class="video-content">
                    <div class="video-wrapper">
                        <iframe 
                            width="100%" 
                            height="100%" 
                            src="<?php echo getYouTubeEmbedUrl(getContent('video_url', 'https://youtu.be/mCncgWhvKnQ')); ?>" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>
        </section>

        <!-- What Is HR Leave Assist Section - Desktop -->
        <section id="what-is-hrla" class="what-is-section desktop-only">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title"><?php echo htmlspecialchars(getContent('video_section_title', 'What Is HR Leave Assist?')); ?></h2>
                </div>
                <div class="video-content">
                    <div class="video-wrapper">
                        <iframe 
                            width="100%" 
                            height="100%" 
                            src="<?php echo getYouTubeEmbedUrl(getContent('video_url', 'https://youtu.be/mCncgWhvKnQ')); ?>" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="features-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title"><?php echo htmlspecialchars(getContent('features_title', 'Supporting Your Leave Process')); ?></h2>
                    <h3 class="section-subtitle"><?php echo htmlspecialchars(getContent('features_subtitle', 'Every Step of the Way')); ?></h3>
                </div>
                <div class="features-checklist">
                    <div class="checklist-item">
                        <i class="fas fa-check-circle"></i>
                        <h3><?php echo htmlspecialchars(getContent('feature_1', 'Built to Support Leave Compliance')); ?></h3>
                    </div>
                    <div class="checklist-item">
                        <i class="fas fa-check-circle"></i>
                        <h3><?php echo htmlspecialchars(getContent('feature_2', 'Respond to Leave Questions Faster')); ?></h3>
                    </div>
                    <div class="checklist-item">
                        <i class="fas fa-check-circle"></i>
                        <h3><?php echo htmlspecialchars(getContent('feature_3', 'Navigate Federal & California Leave Laws')); ?></h3>
                    </div>
                    <div class="checklist-item">
                        <i class="fas fa-check-circle"></i>
                        <h3><?php echo htmlspecialchars(getContent('feature_4', 'Designed for Busy HR Teams')); ?></h3>
                    </div>
                    <div class="checklist-item">
                        <i class="fas fa-check-circle"></i>
                        <h3><?php echo htmlspecialchars(getContent('feature_5', 'Empowers HR-Led Decision-Making')); ?></h3>
                    </div>
                    <div class="checklist-item">
                        <i class="fas fa-check-circle"></i>
                        <h3><?php echo htmlspecialchars(getContent('feature_6', 'Supports Consistent Leave Administration')); ?></h3>
                    </div>
                </div>
            </div>
        </section>

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
                            <p>Review, edit as needed, and send the response to your employee.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="about-section">
            <div class="container">
                <div class="about-content">
                    <div class="about-main">
                        <div class="about-logo">
                            <img src="about_tool_logo.png" alt="HRLA" class="about-logo-img">
                            <h2 class="about-logo-text">HR Leave Assist</h2>
                        </div>
                        <div class="about-text">
                            <p><strong style="color: var(--hrla-blue);">HR <span style="color: var(--hrla-green);">Leave Assist</span> (HRLA)</strong> is a support tool built for HR professionals who answer employee leave questions every day — especially those involving FMLA, CFRA, PDL, and ADA considerations.</p>
                            <p>Leave situations are rarely straightforward. They often involve overlapping leave laws, internal requirements, and personal circumstances—under real-time pressure. HRLA helps streamline that complexity by organizing applicable leave considerations and drafting clear, employee-ready responses, without starting from scratch.</p>
                            <p>Built by an HR professional with over 25 years of experience, HRLA is designed to support your judgment—not replace it. The tool reinforces consistency, reduces missed steps, and helps you respond with care, efficiency, and confidence.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="pricing" class="pricing-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Pricing</h2>
                </div>
                <div class="pricing-grid-minified">
                    <div class="pricing-card-minified">
                        <div class="pricing-header-mini">
                            <h3>Free Trial — $0</h3>
                        </div>
                        <div class="pricing-best-for-mini">
                            <strong>Best for:</strong>
                            HR professionals who want to test the tool with real-world scenarios before subscribing.
                        </div>
                        <div class="pricing-action">
                            <a href="<?php echo appUrl('register.php'); ?>" class="btn btn-primary btn-block">
                                Start Free Trial
                            </a>
                        </div>
                    </div>
                    
                    <div class="pricing-card-minified">
                        <div class="pricing-header-mini">
                            <h3>Monthly — $29</h3>
                        </div>
                        <div class="pricing-best-for-mini">
                            <strong>Best for:</strong>
                            Individual HR professionals who regularly respond to employee leave inquiries.
                        </div>
                        <div class="pricing-action">
                            <a href="<?php echo appUrl('register.php'); ?>" class="btn btn-primary btn-block">
                                Subscribe Monthly
                            </a>
                        </div>
                    </div>
                    
                    <div class="pricing-card-minified featured">
                        <div class="pricing-badge">Most Popular</div>
                        <div class="pricing-header-mini">
                            <h3>Annual — $290</h3>
                            <div class="pricing-sub">(2 months free)</div>
                        </div>
                        <div class="pricing-best-for-mini">
                            <strong>Best for:</strong>
                            Individual HR professionals who rely on HR Leave Assist as part of their regular, year-round workflow.
                        </div>
                        <div class="pricing-action">
                            <a href="<?php echo appUrl('register.php'); ?>" class="btn btn-primary btn-block">
                                Subscribe Annually
                            </a>
                        </div>
                    </div>
                    
                    <div class="pricing-card-minified">
                        <div class="pricing-header-mini">
                            <h3>Organization — $580 / yr</h3>
                        </div>
                        <div class="pricing-best-for-mini">
                            <strong>Best for:</strong>
                            Small HR teams of 2 to 5 who regularly respond to employee leave questions and want consistent, shared access.
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

        <section id="faqs" class="faq-selection-section">
            <div class="container">
                <div class="faq-selection-header">
                    <h2 class="section-title">Frequently Asked Questions</h2>
                    <p class="section-subtitle">Select a category to find answers about leave laws and regulations</p>
                </div>
                <div class="faq-selection-grid">
                    <div class="faq-selection-card">
                        <div class="faq-card-icon">
                            <i class="fas fa-flag-usa"></i>
                        </div>
                        <h3>FMLA FAQs</h3>
                        <p>Family and Medical Leave Act questions covering federal leave requirements, eligibility, and job protection.</p>
                        <a href="fmla-faqs.php" class="btn btn-primary btn-block">
                            View FMLA FAQs
                        </a>
                    </div>
                    <div class="faq-selection-card">
                        <div class="faq-card-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3>CFRA FAQs</h3>
                        <p>California Family Rights Act questions covering state-specific leave laws, benefits, and requirements.</p>
                        <a href="cfra-faqs.php" class="btn btn-primary btn-block">
                            View CFRA FAQs
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2 class="cta-title-red">Simple to Start - Easy to Use</h2>
                    <a href="<?php echo appUrl('register.php'); ?>" class="btn-cta-blue">
                        Get Started Now
                    </a>
                </div>
            </div>
        </section>

        <footer class="landing-footer">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-brand">
                        <div class="footer-logo-text">
                            <span class="footer-hr">HR</span><span class="footer-la">LA</span>
                        </div>
                        <p><?php echo htmlspecialchars(getContent('footer_description', 'A leave-support tool built by HR, for HR, to help apply consistent, compliance-aligned responses to employee leave questions.')); ?></p>
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
    </div>

    <script>
        document.getElementById('mobileMenuToggle')?.addEventListener('click', function() {
            const navMenu = document.querySelector('.nav-menu');
            navMenu.classList.toggle('mobile-open');
            this.setAttribute('aria-expanded', navMenu.classList.contains('mobile-open'));
        });
        // Modals functionality implied here...
    </script>
</body>
</html>