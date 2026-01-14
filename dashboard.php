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
</head>
<body>
    <!-- Dashboard -->
    <div id="dashboard" class="page">
        <nav class="app-nav">
            <div class="nav-container">
                <div class="nav-brand">
                    <img src="hrla_logo.png" alt="HRLA" class="nav-logo">
                    <span class="nav-title">Leave Assistant</span>
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
                <a href="<?php echo appUrl('federal.php'); ?>" class="tool-card" id="federalTool">
                    <div class="tool-icon federal">
                        <i class="fas fa-flag-usa"></i>
                    </div>
                    <div class="tool-content">
                        <h3>Federal FMLA</h3>
                        <p>Generate compliant responses for Federal Family & Medical Leave Act inquiries and requests.</p>
                        <div class="tool-features">
                            <span class="feature-tag">FMLA Compliant</span>
                            <span class="feature-tag">AI Powered</span>
                        </div>
                    </div>
                    <div class="tool-action">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
                
                <a href="<?php echo appUrl('california.php'); ?>" class="tool-card" id="californiaTool">
                    <div class="tool-icon california">
                        <i class="fas fa-sun"></i>
                    </div>
                    <div class="tool-content">
                        <h3>California Leaves</h3>
                        <p>Navigate CFRA, PDL, and FMLA interactions specifically for California employees.</p>
                        <div class="tool-features">
                            <span class="feature-tag">Multi-Law</span>
                            <span class="feature-tag">CA Specific</span>
                        </div>
                    </div>
                    <div class="tool-action">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
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
