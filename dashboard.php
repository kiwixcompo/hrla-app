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
} elseif ($subscriptionExpiry && $subscriptionExpiry > $now) {
    $accessStatus = 'subscribed';
    $accessExpiry = $subscriptionExpiry;
    $timeRemaining = $subscriptionExpiry - $now;
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
    
    <link rel="stylesheet" href="styles.css?v=1.2">
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
        }
        
        /* Dashboard logo styling - make it bigger */
        .nav-logo {
            max-height: 100px !important;
            width: auto !important;
            height: 100px !important;
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
        }
    </style>
</head>
<body>
    <!-- Dashboard -->
    <div id="dashboard" class="page">
        <nav class="app-nav">
            <div class="nav-container">
                <div class="nav-brand">
                    <img src="dashboard_logo.png" alt="HRLA Dashboard" class="nav-logo">
                </div>
                <div class="nav-menu">
                    <?php if ($user['is_admin']): ?>
                        <a href="<?php echo appUrl('admin/index.php'); ?>" class="btn btn-ghost">
                            <i class="fas fa-user-shield"></i>
                            <span>Admin Panel</span>
                        </a>
                    <?php elseif ($accessStatus === 'trial'): ?>
                        <div id="trialTimer" class="trial-badge" data-expiry="<?php echo $accessExpiry; ?>">
                            Trial: <span id="timeRemaining">Calculating...</span>
                        </div>
                        <a href="<?php echo appUrl('subscription.php'); ?>" class="btn btn-success">
                            <i class="fas fa-crown"></i>
                            <span>Upgrade</span>
                        </a>
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
        // Countdown timer with seconds
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
                
                let timeString = '';
                if (days > 0) {
                    timeString = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                } else if (hours > 0) {
                    timeString = `${hours}h ${minutes}m ${seconds}s`;
                } else if (minutes > 0) {
                    timeString = `${minutes}m ${seconds}s`;
                } else {
                    timeString = `${seconds}s`;
                }
                
                document.getElementById('timeRemaining').textContent = timeString;
            }
            
            updateTimer();
            setInterval(updateTimer, 1000);
        }
    </script>
</body>
</html>
