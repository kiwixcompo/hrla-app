<?php
/**
 * User Dashboard
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'includes/auth.php';
require_once 'includes/content.php';

$auth = getAuth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$hasAccess = $auth->hasAccess();

initContentSystem();

// Calculate trial/subscription status
$now = time();
$trialExpiry = $user['trial_expiry'] ? strtotime($user['trial_expiry']) : null;
$subscriptionExpiry = $user['subscription_expiry'] ? strtotime($user['subscription_expiry']) : null;

$accessStatus = 'expired';
$accessExpiry = null;
$timeRemaining = 0;

if ($user['is_admin']) {
    $accessStatus = 'admin';
} elseif (($subscriptionExpiry && $subscriptionExpiry > $now) || in_array($user['access_level'], ['subscribed', 'organization'])) {
    $accessStatus = 'subscribed';
    $accessExpiry = $subscriptionExpiry;
    $timeRemaining = $subscriptionExpiry ? $subscriptionExpiry - $now : 0;
} elseif ($trialExpiry && $trialExpiry > $now) {
    $accessStatus = 'trial';
    $accessExpiry = $trialExpiry;
    $timeRemaining = $trialExpiry - $now;
}

// Redirect expired users to subscription page
if ($accessStatus === 'expired' && !$user['is_admin']) {
    header('Location: ' . appUrl('subscription.php?expired=1'));
    exit;
}

$pageTitle = 'Dashboard - HR Leave Assistant';
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
    
    <link rel="stylesheet" href="styles.css?v=<?php echo filemtime(__DIR__ . '/styles.css'); ?>">
    <link rel="icon" type="image/png" href="hrla_logo.png">
    
    <style>
        :root {
            --hrla-blue: #0322D8;
            --hrla-green: #3DB20B;
        }
        
        /* Change trial timer background to blue */
        .trial-badge {
            background-color: var(--hrla-blue) !important;
            color: white !important;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .subscription-badge {
            background-color: var(--hrla-green) !important;
            color: white !important;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
        }
        
        /* Dashboard logo styling */
        .nav-logo {
            max-height: 60px !important;
            width: auto !important;
            height: auto !important;
        }
        
        /* Simple button styling for tool selection */
        .tools-grid {
            display: flex;
            flex-direction: row;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .tool-section {
            flex: 1;
        }
        
        .tool-button {
            display: block;
            padding: 20px 30px;
            border: 2px solid var(--hrla-blue);
            border-radius: 8px;
            background: white;
            color: var(--hrla-blue);
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .tool-button:hover {
            background: var(--hrla-blue);
            color: white;
            text-decoration: none;
        }
        
        .limitation-text {
            margin-top: 15px;
            font-size: 0.9rem;
            color: #666;
            line-height: 1.4;
        }
        
        .limitation-text strong {
            color: #333;
        }

        .limitation-text ul {
            margin: 8px 0 8px 20px;
            padding: 0;
        }

        .limitation-text ul li {
            margin-bottom: 4px;
        }
        
        /* Responsive design for mobile */
        @media (max-width: 768px) {
            .tools-grid {
                flex-direction: column;
            }
            /* Override global mobile nav-menu hide — keep profile dropdown visible */
            .app-nav .nav-menu {
                position: static !important;
                transform: none !important;
                opacity: 1 !important;
                visibility: visible !important;
                flex-direction: row !important;
                background: transparent !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
                gap: 6px !important;
                flex-wrap: nowrap !important;
                align-items: center !important;
            }
            .user-profile-btn .user-name {
                display: none;
            }
            /* Compact trial badge on mobile */
            .trial-badge, .subscription-badge {
                font-size: 0.7rem !important;
                padding: 3px 6px !important;
                max-width: 90px;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            /* Compact upgrade button on mobile */
            .hide-mobile {
                display: none;
            }
            .btn-success {
                padding: 6px 10px !important;
                font-size: 0.8rem !important;
            }
            /* Compact admin panel button on mobile */
            .btn-ghost span {
                display: none;
            }
        }

        /* User profile dropdown — scoped overrides */
        .user-profile-menu {
            position: relative;
        }
        .user-profile-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 50px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            color: #374151;
        }
        .user-profile-btn:hover { background: #f3f4f6; }
        .user-avatar-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #0322D8;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .user-profile-btn .chevron {
            font-size: 0.7rem;
            color: #9ca3af;
            transition: transform 0.2s;
        }
        .user-profile-menu.open .chevron { transform: rotate(180deg); }
        .user-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 210px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            z-index: 9999;
            overflow: hidden;
        }
        .user-profile-menu.open .user-dropdown { display: block; }
        .user-dropdown-header {
            padding: 14px 16px;
            border-bottom: 1px solid #f3f4f6;
        }
        .user-dropdown-header .full-name { font-weight: 600; font-size: 0.95rem; color: #111827; }
        .user-dropdown-header .email { font-size: 0.8rem; color: #6b7280; margin-top: 2px; }
        .user-dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            color: #374151;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .user-dropdown-item:hover { background: #f9fafb; color: #111827; text-decoration: none; }
        .user-dropdown-item i { width: 16px; color: #6b7280; font-size: 0.85rem; }
        .user-dropdown-item.logout { border-top: 1px solid #f3f4f6; color: #dc2626; }
        .user-dropdown-item.logout i { color: #dc2626; }
        .user-dropdown-item.logout:hover { background: #fef2f2; }
        @media (max-width: 768px) {
            .user-profile-btn .user-name { display: none; }
        }
    </style>
</head>
<body>
    <!-- Dashboard -->
    <div id="dashboard" class="page">
        <nav class="app-nav">
            <div class="nav-container">
                <div class="nav-brand">
                    <a href="<?php echo appUrl('dashboard.php'); ?>">
                        <img src="dashboard_logo.png" alt="HRLA Dashboard" class="nav-logo">
                    </a>
                </div>
                <div class="nav-menu">
                    <?php if ($user['is_admin']): ?>
                        <a href="<?php echo appUrl('admin/index.php'); ?>" class="btn btn-ghost">
                            <i class="fas fa-user-shield"></i>
                            <span>Admin Panel</span>
                        </a>
                    <?php elseif ($accessStatus === 'trial'): ?>
                        <div id="trialTimer" class="trial-badge" data-expiry="<?php echo $accessExpiry; ?>">
                            Trial: <span id="timeRemaining">...</span>
                        </div>
                        <a href="<?php echo appUrl('subscription.php'); ?>" class="btn btn-success">
                            <i class="fas fa-crown"></i>
                            <span class="hide-mobile">Upgrade</span>
                        </a>
                    <?php elseif ($accessStatus === 'subscribed' && $accessExpiry): ?>
                        <div id="trialTimer" class="subscription-badge" data-expiry="<?php echo $accessExpiry; ?>">
                            <span id="timeRemaining">...</span>
                        </div>
                    <?php endif; ?>

                    <?php
                        $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
                    ?>
                    <div class="user-profile-menu" id="userProfileMenu">
                        <button class="user-profile-btn" id="userProfileBtn" type="button">
                            <div class="user-avatar-circle"><?php echo htmlspecialchars($initials); ?></div>
                            <span class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                            <i class="fas fa-chevron-down chevron"></i>
                        </button>
                        <div class="user-dropdown">
                            <div class="user-dropdown-header">
                                <div class="full-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                <div class="email"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                            <a href="<?php echo appUrl('settings.php'); ?>" class="user-dropdown-item">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            <a href="<?php echo appUrl('logout.php'); ?>" class="user-dropdown-item logout">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><?php echo htmlspecialchars(getContent('dashboard_welcome_heading', 'Welcome back,')); ?> <span id="userWelcomeName"><?php echo htmlspecialchars($user['first_name']); ?></span></h1>
                <p><?php echo htmlspecialchars(getContent('dashboard_welcome_subheading', 'Choose a compliance tool to generate professional leave responses')); ?></p>
            </div>
            
            <div class="tools-grid">
                <div class="tool-section">
                    <a href="<?php echo appUrl('federal.php'); ?>" class="tool-button" id="federalTool">
                        <?php echo htmlspecialchars(getContent('dashboard_federal_tool_label', 'Federal Leave Assistant')); ?>
                    </a>
                    <div class="limitation-text">
                        <strong><?php echo htmlspecialchars(getContent('dashboard_federal_disclaimer_heading', 'Federal-Specific Limitations')); ?></strong><br>
                        <?php echo htmlspecialchars(getContent('dashboard_federal_disclaimer_intro', 'Focuses employment laws, including but not limited to the Family and Medical Leave Act (FMLA) and the Americans with Disabilities Act (ADA). Responses are limited to federal law.')); ?>
                        <br><br>
                        <strong>HRLA:</strong>
                        <ul>
                            <li><?php echo htmlspecialchars(getContent('dashboard_federal_disclaimer_bullet_1', 'Does not account for state or local leave laws that may provide additional or different protections')); ?></li>
                            <li><?php echo htmlspecialchars(getContent('dashboard_federal_disclaimer_bullet_2', 'Does not evaluate collective bargaining agreements, institutional policies, or employer-specific practices')); ?></li>
                            <li><?php echo htmlspecialchars(getContent('dashboard_federal_disclaimer_bullet_3', 'Does not implement and act upon legal advice such as the ADA interactive process or individualized eligibility determinations')); ?></li>
                        </ul>
                        <?php echo htmlspecialchars(getContent('dashboard_federal_disclaimer_footer', 'Users are responsible for confirming current federal requirements and seeking legal advice when appropriate.')); ?>
                    </div>
                </div>
                
                <div class="tool-section">
                    <a href="<?php echo appUrl('california.php'); ?>" class="tool-button" id="californiaTool">
                        <?php echo htmlspecialchars(getContent('dashboard_california_tool_label', 'California Leave Assistant')); ?>
                    </a>
                    <div class="limitation-text">
                        <strong><?php echo htmlspecialchars(getContent('dashboard_california_disclaimer_heading', 'California-Specific Limitations')); ?></strong><br>
                        <?php echo htmlspecialchars(getContent('dashboard_california_disclaimer_intro', 'California employment laws, including but not limited to the California Family Rights Act (CFRA), Pregnancy Disability Leave (PDL), and related state-specific employment and housing Act (FEHA). Responses are limited to California law.')); ?>
                        <br><br>
                        <strong>HRLA:</strong>
                        <ul>
                            <li><?php echo htmlspecialchars(getContent('dashboard_california_disclaimer_bullet_1', 'Does not account for local city/county leave laws, announcement provisions, or other local provisions')); ?></li>
                            <li><?php echo htmlspecialchars(getContent('dashboard_california_disclaimer_bullet_2', 'Does not evaluate collective bargaining agreements, institutional policies, or employer-specific practices')); ?></li>
                            <li><?php echo htmlspecialchars(getContent('dashboard_california_disclaimer_bullet_3', 'Does not implement required employee obligations such as the interactive process')); ?></li>
                        </ul>
                        <?php echo htmlspecialchars(getContent('dashboard_california_disclaimer_footer', 'Users are responsible for confirming current legal requirements and seeking legal advice when appropriate.')); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Countdown timer
        const timerElement = document.getElementById('trialTimer');
        if (timerElement) {
            const expiryTimestamp = parseInt(timerElement.dataset.expiry);
            
            function updateTimer() {
                const now = Math.floor(Date.now() / 1000);
                const remaining = expiryTimestamp - now;
                
                if (remaining <= 0) {
                    document.getElementById('timeRemaining').textContent = 'Expired';
                    return;
                }
                
                const days = Math.floor(remaining / 86400);
                const hours = Math.floor((remaining % 86400) / 3600);
                const minutes = Math.floor((remaining % 3600) / 60);
                const seconds = remaining % 60;
                const isMobile = window.innerWidth <= 768;
                
                let t;
                if (days > 0) {
                    t = isMobile ? `${days}d ${hours}h` : `${days}d ${hours}h ${minutes}m`;
                } else if (hours > 0) {
                    t = isMobile ? `${hours}h ${minutes}m` : `${hours}h ${minutes}m ${seconds}s`;
                } else {
                    t = `${minutes}m ${seconds}s`;
                }
                
                document.getElementById('timeRemaining').textContent = t;
            }
            
            updateTimer();
            setInterval(updateTimer, 1000);
        }

        // User profile dropdown toggle
        const profileMenu = document.getElementById('userProfileMenu');
        const profileBtn = document.getElementById('userProfileBtn');
        if (profileBtn) {
            profileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                profileMenu.classList.toggle('open');
            });
            document.addEventListener('click', function() {
                profileMenu.classList.remove('open');
            });
        }
    </script>
</body>
</html>
