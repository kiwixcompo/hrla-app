<?php
/**
 * California Leave Assistant
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'includes/auth.php';

$auth = getAuth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$hasAccess = $auth->hasAccess();

// Check if user has expired access
$now = time();
$trialExpiry = $user['trial_expiry'] ? strtotime($user['trial_expiry']) : null;
$subscriptionExpiry = $user['subscription_expiry'] ? strtotime($user['subscription_expiry']) : null;

$accessStatus = 'expired';
$accessExpiry = null;

if ($user['is_admin']) {
    $accessStatus = 'admin';
} elseif (($subscriptionExpiry && $subscriptionExpiry > $now) || in_array($user['access_level'], ['subscribed', 'organization'])) {
    $accessStatus = 'subscribed';
    $accessExpiry = $subscriptionExpiry;
} elseif ($trialExpiry && $trialExpiry > $now) {
    $accessStatus = 'trial';
    $accessExpiry = $trialExpiry;
}

// Redirect expired users to subscription page
if ($accessStatus === 'expired') {
    header('Location: ' . appUrl('subscription.php?expired=1'));
    exit;
}

$pageTitle = 'California Leave Assistant - HR Leave Assistant';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="styles.css?v=1.2">
    <link rel="icon" type="image/png" href="hrla_logo.png">

    <style>
        /* --- BRAND COLORS --- */
        :root {
            --hrla-blue: #0322D8;
            --hrla-dark-blue: #1800AD;
            --hrla-green: #3DB20B;
            --hrla-black: #000000;
        }

        /* Override button colors */
        .btn-primary {
            background-color: var(--hrla-blue) !important;
            border-color: var(--hrla-blue) !important;
            color: white !important;
        }
        .btn-primary:hover {
            background-color: var(--hrla-dark-blue) !important;
            border-color: var(--hrla-dark-blue) !important;
        }

        /* Logo matches dashboard */
        .nav-logo {
            max-height: 60px !important;
            height: auto !important;
            width: auto !important;
            cursor: pointer;
        }

        /* Trial/subscription badge */
        .trial-badge {
            background-color: #0322D8;
            color: white;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .subscription-badge {
            background-color: #3DB20B;
            color: white;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
        }

        /* Tighten app-nav menu gap */
        .app-nav .nav-menu { gap: 8px !important; }

        /* --- STANDARD PAGE SCROLL LAYOUT --- */
        html, body {
            height: 100%;
            margin: 0;
            background-color: #f3f4f6;
        }

        .page {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .app-nav {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.5rem 0;
        }

        .tool-container {
            padding: 0 20px 40px 20px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            flex: 1;
        }

        .tool-header {
            padding: 40px 0 30px;
            text-align: center;
        }
        .tool-header h1 { font-size: 2rem; margin-bottom: 10px; color: var(--hrla-black); font-weight: 800; }
        .tool-header p { color: #6b7280; margin-bottom: 15px; font-size: 1.1rem; }
        .tool-warning { color: var(--hrla-blue); font-size: 0.95rem; font-weight: 600; }

        .tool-workspace {
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }

        /* PANELS */
        .input-panel, .output-panel {
            flex: 1;
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            min-height: 500px;
        }

        .panel-header { margin-bottom: 15px; }
        .panel-header label { font-weight: 700; color: #111; font-size: 1rem; }

        /* Input Resize */
        #californiaInput {
            width: 100%;
            padding: 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            min-height: 150px;
            resize: vertical;
            font-family: 'Inter', sans-serif;
        }

        .followup-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #f3f4f6;
        }
        
        #californiaFollowup {
            width: 100%;
            padding: 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            min-height: 100px;
            resize: vertical;
            font-family: 'Inter', sans-serif;
            margin-top: 10px;
        }

        .followup-actions, .panel-actions {
            margin-top: 20px;
            text-align: right;
        }

        /* Output Scroll */
        .output-actions { float: right; }
        .output-actions .btn { font-size: 0.85rem; padding: 6px 12px; }

        .response-output {
            background-color: #f9fafb;
            border-radius: 8px;
            border: 1px solid #f3f4f6;
            padding: 20px;
            font-size: 1rem;
            line-height: 1.7;
            color: #1f2937;
            min-height: 300px;
            flex: 1;
            max-height: 800px; 
            overflow-y: auto;
        }

        .response-output::-webkit-scrollbar { width: 8px; }
        .response-output::-webkit-scrollbar-track { background: #f1f1f1; }
        .response-output::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }

        @media (max-width: 992px) {
            .tool-workspace { flex-direction: column; }
            .input-panel, .output-panel { min-height: auto; }
        }

        @media (max-width: 768px) {
            .nav-logo { max-height: 36px !important; }
            /* Override global mobile nav-menu hide */
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
                overflow: visible !important;
            }
            .user-profile-btn .user-name { display: none; }
            .hide-mobile { display: none; }
            .trial-badge, .subscription-badge {
                font-size: 0.7rem !important;
                padding: 3px 6px !important;
                max-width: 90px;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .btn-success {
                padding: 5px 8px !important;
                font-size: 0.8rem !important;
            }
            .nav-container { overflow: visible; }
            .app-nav { overflow: visible; }
            .tool-container { padding: 0 12px 30px 12px; }
            .tool-header { padding: 20px 0 16px; }
            .tool-header h1 { font-size: 1.4rem; }
            .tool-header p { font-size: 0.95rem; }
            .input-panel, .output-panel { padding: 16px; width: 100%; box-sizing: border-box; }
            #californiaInput, #californiaFollowup { min-height: 80px; font-size: 0.95rem; }
            .response-output { min-height: 150px; max-height: none; font-size: 0.95rem; }
        }

        /* User profile dropdown */
        .user-profile-menu { position: relative; }
        .user-profile-btn {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 12px; background: white;
            border: 1px solid #e5e7eb; border-radius: 50px;
            cursor: pointer; font-family: 'Inter', sans-serif;
            font-size: 0.9rem; font-weight: 500; color: #374151;
        }
        .user-profile-btn:hover { background: #f3f4f6; }
        .user-avatar-circle {
            width: 30px; height: 30px; border-radius: 50%;
            background: #0322D8; color: white;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem; font-weight: 700; flex-shrink: 0;
        }
        .user-profile-btn .chevron { font-size: 0.7rem; color: #9ca3af; transition: transform 0.2s; }
        .user-profile-menu.open .chevron { transform: rotate(180deg); }
        .user-dropdown {
            display: none; position: absolute; top: calc(100% + 8px); right: 0;
            min-width: 210px; background: white; border: 1px solid #e5e7eb;
            border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            z-index: 9999; overflow: hidden;
        }
        .user-profile-menu.open .user-dropdown { display: block; }
        .user-dropdown-header { padding: 14px 16px; border-bottom: 1px solid #f3f4f6; }
        .user-dropdown-header .full-name { font-weight: 600; font-size: 0.95rem; color: #111827; }
        .user-dropdown-header .email { font-size: 0.8rem; color: #6b7280; margin-top: 2px; }
        .user-dropdown-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 16px; color: #374151; text-decoration: none; font-size: 0.9rem;
        }
        .user-dropdown-item:hover { background: #f9fafb; color: #111827; text-decoration: none; }
        .user-dropdown-item i { width: 16px; color: #6b7280; font-size: 0.85rem; }
        .user-dropdown-item.logout { border-top: 1px solid #f3f4f6; color: #dc2626; }
        .user-dropdown-item.logout i { color: #dc2626; }
        .user-dropdown-item.logout:hover { background: #fef2f2; }
        @media (max-width: 768px) { .user-profile-btn .user-name { display: none; } }
    </style>
</head>
<body>
    <div id="californiaPage" class="page">
        <nav class="app-nav">
            <div class="nav-container">
                <div class="nav-brand">
                    <a href="<?php echo appUrl('dashboard.php'); ?>">
                        <img src="dashboard_logo.png" alt="HRLA" class="nav-logo">
                    </a>
                </div>
                <div class="nav-menu">
                    <?php if ($accessStatus === 'trial'): ?>
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
        
        <div class="tool-container">
            <div class="tool-header">
                <h1>California Leave Assistant</h1>
                <p>Generate professional, compliant responses employee's FMLA, CFRA, PDL leave questions</p>
                <div class="tool-warning">
                    <i class="fas fa-shield-alt"></i>
                    <span>Do not enter SSNs, medical records, DOBs, or sensitive personal data</span>
                </div>
            </div>
            
            <div class="tool-workspace">
                <div class="input-panel">
                    <div class="panel-header">
                        <label id="californiaInputLabel">Copy and Paste Employee Email or type questions below</label>
                    </div>
                    
                    <textarea id="californiaInput"></textarea>
                    
                    <div id="californiaFollowupSection" class="followup-section" style="display: none;">
                        <div class="followup-header">
                            <label>Enter follow-up Question (optional)</label>
                        </div>
                        <textarea id="californiaFollowup" placeholder="Enter follow-up questions here..."></textarea>
                        <div class="followup-actions">
                            <button id="californiaFollowupSubmit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                                Submit Follow Up
                            </button>
                        </div>
                    </div>
                    
                    <div class="panel-actions" id="californiaGenerateActions">
                        <button id="californiaSubmit" class="btn btn-primary">
                            <i class="fas fa-magic"></i>
                            Generate Response
                        </button>
                    </div>
                </div>
                
                <div class="output-panel">
                    <div class="panel-header">
                        <label>HRLA Generated Response:</label>
                        <div class="output-actions">
                            <button id="californiaCopy" class="btn btn-secondary">
                                <i class="fas fa-copy"></i>
                                Copy
                            </button>
                        </div>
                    </div>
                    
                    <div id="californiaOutput" class="response-output"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const californiaInput = document.getElementById('californiaInput');
        const californiaOutput = document.getElementById('californiaOutput');
        const californiaSubmit = document.getElementById('californiaSubmit');
        const californiaGenerateActions = document.getElementById('californiaGenerateActions');
        const californiaCopy = document.getElementById('californiaCopy');
        const californiaFollowupSection = document.getElementById('californiaFollowupSection');
        const californiaFollowup = document.getElementById('californiaFollowup');
        const californiaFollowupSubmit = document.getElementById('californiaFollowupSubmit');

        californiaSubmit.addEventListener('click', async function() {
            const input = californiaInput.value.trim();
            if (!input) { alert('Please enter a question or email to analyze.'); return; }

            californiaSubmit.disabled = true;
            californiaSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            californiaOutput.innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin"></i> Analyzing your request...</p>';

            try {
                const response = await fetch('<?php echo appUrl('api/ai.php'); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tool_name: 'california', input_text: input })
                });
                const data = await response.json();

                if (data.success) {
                    californiaOutput.innerHTML = data.response;
                    californiaGenerateActions.style.display = 'none';
                    californiaFollowupSection.style.display = 'block';
                } else {
                    californiaOutput.innerHTML = `<p style="color: #ef4444; padding: 1rem;">Error: ${data.error || 'Failed to generate response'}</p>`;
                }
            } catch (error) {
                californiaOutput.innerHTML = `<p style="color: #ef4444; padding: 1rem;">Error: ${error.message}</p>`;
            } finally {
                californiaSubmit.disabled = false;
                californiaSubmit.innerHTML = '<i class="fas fa-magic"></i> Generate Response';
            }
        });

        californiaCopy.addEventListener('click', function() {
            const text = californiaOutput.innerText;
            navigator.clipboard.writeText(text).then(() => {
                const originalText = californiaCopy.innerHTML;
                californiaCopy.innerHTML = '<i class="fas fa-check"></i> Copied!';
                setTimeout(() => { californiaCopy.innerHTML = originalText; }, 2000);
            });
        });

        californiaFollowupSubmit.addEventListener('click', async function() {
            const followup = californiaFollowup.value.trim();
            if (!followup) { alert('Please enter a follow-up question.'); return; }

            californiaFollowupSubmit.disabled = true;
            californiaFollowupSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            // Add a loading indicator below the existing response
            const loadingDiv = document.createElement('div');
            loadingDiv.id = 'followupLoading';
            loadingDiv.style.cssText = 'margin-top:16px;padding:12px;background:#f0f4ff;border-radius:8px;color:#6b7280;';
            loadingDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing follow-up...';
            californiaOutput.appendChild(loadingDiv);

            try {
                const response = await fetch('<?php echo appUrl('api/ai.php'); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tool_name: 'california', input_text: followup })
                });
                const data = await response.json();

                const loader = document.getElementById('followupLoading');
                if (loader) loader.remove();

                if (data.success) {
                    const divider = document.createElement('hr');
                    divider.style.cssText = 'margin:16px 0;border:none;border-top:1px solid #e5e7eb;';
                    const followupLabel = document.createElement('p');
                    followupLabel.style.cssText = 'font-weight:600;color:#0322D8;margin-bottom:8px;font-size:0.9rem;';
                    followupLabel.textContent = 'Follow-up Answer:';
                    const followupAnswer = document.createElement('div');
                    followupAnswer.innerHTML = data.response;
                    californiaOutput.appendChild(divider);
                    californiaOutput.appendChild(followupLabel);
                    californiaOutput.appendChild(followupAnswer);
                    californiaFollowup.value = '';
                    californiaOutput.scrollTop = californiaOutput.scrollHeight;
                } else {
                    const errDiv = document.createElement('p');
                    errDiv.style.cssText = 'color:#ef4444;padding:8px 0;';
                    errDiv.textContent = 'Error: ' + (data.error || 'Failed to process follow-up');
                    californiaOutput.appendChild(errDiv);
                }
            } catch (error) {
                const loader = document.getElementById('followupLoading');
                if (loader) loader.remove();
                const errDiv = document.createElement('p');
                errDiv.style.cssText = 'color:#ef4444;padding:8px 0;';
                errDiv.textContent = 'Error: ' + error.message;
                californiaOutput.appendChild(errDiv);
            } finally {
                californiaFollowupSubmit.disabled = false;
                californiaFollowupSubmit.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Follow Up';
            }
        });

        // Countdown timer
        const timerElement = document.getElementById('trialTimer');
        if (timerElement) {
            const expiryTimestamp = parseInt(timerElement.dataset.expiry);
            function updateTimer() {
                const now = Math.floor(Date.now() / 1000);
                const remaining = expiryTimestamp - now;
                if (remaining <= 0) { document.getElementById('timeRemaining').textContent = 'Expired'; return; }
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