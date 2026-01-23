<?php
/**
 * User Dashboard
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'includes/auth.php';

$auth = getAuth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$hasAccess = $auth->hasAccess();

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
                        <span class="admin-badge">
                            <i class="fas fa-shield-alt"></i>
                            <span>Admin</span>
                        </span>
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
                    <a href="<?php echo appUrl('settings.php'); ?>" class="btn btn-ghost">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                    <a href="<?php echo appUrl('logout.php'); ?>" id="logoutBtn" class="btn btn-ghost">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </nav>
        
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1>Welcome back, <span id="userWelcomeName"><?php echo htmlspecialchars($user['first_name']); ?></span></h1>
                <p>Choose a compliance tool to generate professional leave responses</p>
            </div>
            
            <div class="tools-grid">
                <div class="tool-section">
                    <a href="<?php echo appUrl('federal.php'); ?>" class="tool-button" id="federalTool">
                        Federal Leave Assistant
                    </a>
                    <div class="limitation-text">
                        <strong>Federal-Specific Limitations</strong><br>
                        Focuses employment laws, including but not limited to the Family and Medical Leave Act (FMLA) and the Americans with Disabilities Act (ADA), and to state regulations are not covered within this version. Responses are limited to federal law.
                        <br><br>
                        <strong>HRLA:</strong><br>
                        • Does not account for state or local leave laws that may provide additional or different protections<br>
                        • Does not evaluate collective bargaining agreements, institutional policies, or employer-specific practices<br>
                        • Does not implement and act upon legal advice such as the ADA interactive process or individualized eligibility determinations<br>
                        <br>
                        Users are responsible for confirming current federal requirements and seeking legal advice when appropriate.
                    </div>
                </div>
                
                <div class="tool-section">
                    <a href="<?php echo appUrl('california.php'); ?>" class="tool-button" id="californiaTool">
                        California Leave Assistant
                    </a>
                    <div class="limitation-text">
                        <strong>California-Specific Limitations</strong><br>
                        California employment laws, including but not limited to the California Family Rights Act (CFRA), Pregnancy Disability Leave (PDL), and related state-specific employment and housing Act (FEHA), and related regulations are not covered within this version. Responses are limited to California law.
                        <br><br>
                        <strong>HRLA:</strong><br>
                        • Does not account for local city/county leave laws, announcement provisions, or other local provisions<br>
                        • Does not evaluate collective bargaining agreements, institutional policies, or employer-specific practices<br>
                        • Does not implement required employee obligations such as the interactive process<br>
                        <br>
                        Users are responsible for confirming current legal requirements and seeking legal advice when appropriate.
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
