<?php
/**
 * Admin Dashboard with Sidebar Navigation
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once '../config/app.php';
require_once '../includes/auth.php';

$auth = getAuth();
$auth->requireAuth();
$auth->requireAdmin();

$user = $auth->getCurrentUser();

// Get statistics
$db = getDB();
$stats = [
    'total_users' => $db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
    'verified_users' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE email_verified = 1")['count'] ?? 0,
    'subscribed_users' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE access_level = 'subscribed'")['count'] ?? 0,
    'trial_users' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE access_level = 'trial'")['count'] ?? 0,
];

$pageTitle = 'Admin Dashboard - HR Leave Assistant';
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
    
    <link rel="stylesheet" href="../styles.css?v=1.4">
    <link rel="stylesheet" href="../mobile-responsive.css?v=1.0">
    <link rel="stylesheet" href="../assets/css/admin.css?v=2.0">
    <link rel="stylesheet" href="../assets/css/admin-sidebar.css?v=1.0">
    <link rel="icon" type="image/png" href="../hrla_logo.png">
    
    <style>
        /* Content Management Styles */
        .content-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid #e9ecef;
            flex-wrap: wrap;
            overflow-x: auto;
        }

        .content-tab {
            padding: 1rem 1.5rem;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            color: #6c757d;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .content-tab:hover {
            color: #0023F5;
            background: #f8f9fa;
        }

        .content-tab.active {
            color: #0023F5;
            border-bottom-color: #0023F5;
        }

        .content-tab i {
            margin-right: 0.5rem;
        }

        .content-tab-content {
            display: none;
        }

        .content-tab-content.active {
            display: block;
        }

        .content-form {
            max-width: 800px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #0023F5;
            box-shadow: 0 0 0 3px rgba(0, 35, 245, 0.1);
        }

        .form-text {
            display: block;
            margin-top: 0.5rem;
            color: #6c757d;
            font-size: 0.875rem;
        }

        .char-count {
            font-weight: 600;
            color: #0023F5;
        }

        .feature-inputs input,
        .feature-items input {
            margin-bottom: 0.5rem;
        }

        .mb-2 {
            margin-bottom: 0.5rem !important;
        }

        .step-group,
        .plan-group,
        .faq-card-group {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .step-group h4,
        .plan-group h4,
        .faq-card-group h4 {
            margin-bottom: 1rem;
            color: #0023F5;
            font-size: 1.1rem;
        }

        .color-picker-group {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .color-input-wrapper {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .color-input {
            width: 80px;
            height: 50px;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            cursor: pointer;
        }

        .color-text-input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-family: monospace;
            font-size: 1rem;
        }

        .btn-reset-color {
            padding: 0.75rem 1rem;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-reset-color:hover {
            background: #5a6268;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #dee2e6;
        }

        .video-preview {
            margin-top: 2rem;
        }

        .video-embed {
            max-width: 560px;
            margin-top: 1rem;
        }

        .video-embed iframe {
            width: 100%;
            height: 315px;
            border-radius: 8px;
        }

        @media (max-width: 768px) {
            .content-tabs {
                overflow-x: auto;
            }
            
            .content-tab {
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
                white-space: nowrap;
            }
            
            .color-input-wrapper {
                flex-direction: column;
                align-items: stretch;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .video-embed iframe {
                height: 200px;
            }
        }
    </style>
</head>
<body class="admin-body">
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Admin Dashboard with Sidebar -->
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <img src="../hrla_logo.png" alt="HRLA" class="sidebar-logo">
                <h3>Admin Panel</h3>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="sidebar-user">
                <div class="user-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="user-info">
                    <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                    <p>Administrator</p>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="#" class="nav-item active" data-section="overview">
                    <i class="fas fa-chart-line"></i>
                    <span>Overview</span>
                </a>
                <a href="#" class="nav-item" data-section="users">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="#" class="nav-item" data-section="payments">
                    <i class="fas fa-credit-card"></i>
                    <span>Payments</span>
                </a>
                <a href="#" class="nav-item" data-section="email">
                    <i class="fas fa-envelope"></i>
                    <span>Email</span>
                </a>
                <a href="#" class="nav-item" data-section="system">
                    <i class="fas fa-cogs"></i>
                    <span>System</span>
                </a>
                <a href="#" class="nav-item" data-section="access-codes">
                    <i class="fas fa-key"></i>
                    <span>Access Codes</span>
                </a>
                <a href="#" class="nav-item" data-section="api-settings">
                    <i class="fas fa-code"></i>
                    <span>API Settings</span>
                </a>
                <a href="#" class="nav-item" data-section="ai-instructions">
                    <i class="fas fa-robot"></i>
                    <span>AI Instructions</span>
                </a>
                <a href="#" class="nav-item" data-section="content-management">
                    <i class="fas fa-edit"></i>
                    <span>Content Management</span>
                </a>
                <a href="#" class="nav-item" data-section="site-settings">
                    <i class="fas fa-paint-brush"></i>
                    <span>Site Settings</span>
                </a>
                <a href="#" class="nav-item" data-section="storage">
                    <i class="fas fa-database"></i>
                    <span>Storage</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="<?php echo appUrl('settings.php'); ?>" class="footer-link">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="<?php echo appUrl('logout.php'); ?>" class="footer-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <!-- Top Bar -->
            <div class="admin-topbar">
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title" id="pageTitle">Overview</h1>
                <div class="topbar-actions">
                    <a href="<?php echo appUrl('dashboard.php'); ?>" class="btn btn-ghost btn-sm">
                        <i class="fas fa-external-link-alt"></i>
                        <span class="hide-mobile">View Site</span>
                    </a>
                </div>
            </div>
            
            <!-- Content Area -->
            <div class="admin-content">
                
                <!-- Overview Section -->
                <div id="overviewSection" class="content-section active">
                    <div class="section-header">
                        <h2>Dashboard Overview</h2>
                        <p>Manage users, settings, and system configuration</p>
                    </div>
                    
                    <!-- Statistics Cards -->
                    <div class="stats-grid">
                        <div class="stat-card clickable-card" data-filter="all">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <h3 id="totalUsers"><?php echo $stats['total_users']; ?></h3>
                                <p>Total Users</p>
                            </div>
                        </div>
                        <div class="stat-card clickable-card" data-filter="verified">
                            <div class="stat-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="stat-content">
                                <h3 id="verifiedUsers"><?php echo $stats['verified_users']; ?></h3>
                                <p>Verified Users</p>
                            </div>
                        </div>
                        <div class="stat-card clickable-card" data-filter="subscribed">
                            <div class="stat-icon">
                                <i class="fas fa-crown"></i>
                            </div>
                            <div class="stat-content">
                                <h3 id="subscribedUsers"><?php echo $stats['subscribed_users']; ?></h3>
                                <p>Subscribed Users</p>
                            </div>
                        </div>
                        <div class="stat-card clickable-card" data-filter="trial">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-content">
                                <h3 id="trialUsers"><?php echo $stats['trial_users']; ?></h3>
                                <p>Trial Users</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Users Section -->
                <div id="usersSection" class="content-section">
                    <div class="section-header">
                        <h2>User Management</h2>
                        <p>View and manage all registered users</p>
                    </div>
                    
                    <div class="settings-section">
                        <div class="tab-header">
                            <div class="tab-controls">
                                <input type="text" id="userSearch" placeholder="Search users..." class="search-input">
                                <select id="userFilter" class="filter-select">
                                    <option value="all">All Users</option>
                                    <option value="verified">Verified</option>
                                    <option value="unverified">Unverified</option>
                                    <option value="subscribed">Subscribed</option>
                                    <option value="trial">Trial</option>
                                    <option value="expired">Expired</option>
                                </select>
                            </div>
                            <div class="tab-actions">
                                <button id="refreshUsers" class="btn btn-secondary">
                                    <i class="fas fa-sync"></i>
                                    Refresh
                                </button>
                                <button id="exportUsers" class="btn btn-primary">
                                    <i class="fas fa-download"></i>
                                    Export CSV
                                </button>
                            </div>
                        </div>
                        
                        <div class="users-table-container">
                            <table class="users-table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAllUsers"></th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Plan</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    <?php
                                    $users = $db->fetchAll("SELECT * FROM users ORDER BY created_at DESC LIMIT 50");
                                    foreach ($users as $u):
                                        $statusClass = $u['email_verified'] ? 'status-verified' : 'status-unverified';
                                        $statusText = $u['email_verified'] ? 'Verified' : 'Unverified';
                                        
                                        // Show Admin badge for admin users
                                        if ($u['is_admin'] || $u['access_level'] === 'administrator') {
                                            $planClass = 'status-admin';
                                            $planText = 'Admin';
                                        } else {
                                            $planClass = 'status-' . $u['access_level'];
                                            $planText = ucfirst($u['access_level']);
                                        }
                                    ?>
                                    <tr>
                                        <td><input type="checkbox" class="user-checkbox" value="<?php echo $u['id']; ?>"></td>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-name"><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                        <td><span class="status-badge <?php echo $planClass; ?>"><?php echo $planText; ?></span></td>
                                        <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-secondary" onclick="editUser(<?php echo $u['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if (!$u['is_admin']): ?>
                                            <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $u['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Settings Section -->
                <div id="paymentsSection" class="content-section">
                    <div class="section-header">
                        <h2>Payment Configuration</h2>
                        <p>Configure payment gateways and subscription settings</p>
                    </div>
                    
                    <div class="settings-section">
                        <h3>Payment Configuration</h3>
                        <form id="paymentSettingsForm">
                            <div class="form-group">
                                <label for="monthlyFee">Monthly Subscription Fee ($)</label>
                                <input type="number" id="monthlyFee" step="0.01" min="0" value="29.00" required>
                            </div>
                            <div class="form-group">
                                <label for="stripePublishableKey">Stripe Publishable Key</label>
                                <input type="text" id="stripePublishableKey" placeholder="pk_...">
                            </div>
                            <div class="form-group">
                                <label for="stripeSecretKey">Stripe Secret Key</label>
                                <input type="password" id="stripeSecretKey" placeholder="sk_...">
                            </div>
                            <div class="form-group">
                                <label for="paypalClientId">PayPal Client ID</label>
                                <input type="text" id="paypalClientId">
                            </div>
                            <div class="form-group">
                                <label for="paypalClientSecret">PayPal Client Secret</label>
                                <input type="password" id="paypalClientSecret">
                            </div>
                            <button type="submit" class="btn btn-primary">Save Payment Settings</button>
                        </form>
                    </div>
                </div>

                
                <!-- Email Settings Section -->
                <div id="emailSection" class="content-section">
                    <div class="section-header">
                        <h2>Email Configuration</h2>
                        <p>Configure SMTP settings for email notifications</p>
                    </div>
                    
                    <div class="settings-section">
                        <h3>SMTP Configuration</h3>
                        <form id="emailSettingsForm">
                            <div class="form-group">
                                <label for="smtpProvider">SMTP Provider</label>
                                <select id="smtpProvider">
                                    <option value="gmail">Gmail</option>
                                    <option value="outlook">Outlook</option>
                                    <option value="custom">Custom SMTP</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="smtpEmail">Email Address</label>
                                <input type="email" id="smtpEmail" required>
                            </div>
                            <div class="form-group">
                                <label for="smtpPassword">Email Password / App Password</label>
                                <input type="password" id="smtpPassword" required>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Save Email Settings</button>
                                <button type="button" id="testEmailBtn" class="btn btn-secondary">Send Test Email</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- System Settings Section -->
                <div id="systemSection" class="content-section">
                    <div class="section-header">
                        <h2>System Configuration</h2>
                        <p>Configure system-wide settings and preferences</p>
                    </div>
                    
                    <div class="settings-section">
                        <h3>System Configuration</h3>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="allowRegistration" checked>
                                Allow New User Registration
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="requireEmailVerification" checked>
                                Require Email Verification
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Access Codes Section -->
                <div id="access-codesSection" class="content-section">
                    <div class="section-header">
                        <h2>Access Code Management</h2>
                        <p>Generate and manage access codes for extended user access</p>
                    </div>
                    
                    <div class="settings-section">
                        <h3>Access Code Management</h3>
                        <p>Generate access codes that users can enter during registration for extended access periods.</p>
                        
                        <div class="access-code-generator">
                            <h4>Generate New Access Code</h4>
                            <form id="generateAccessCodeForm" method="POST" action="<?php echo appUrl('api/admin.php'); ?>">
                                <input type="hidden" name="action" value="generate_access_code">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="codeLength">Code Length</label>
                                        <select id="codeLength" name="code_length">
                                            <option value="6">6 characters</option>
                                            <option value="8" selected>8 characters</option>
                                            <option value="10">10 characters</option>
                                            <option value="12">12 characters</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="accessDuration">Duration</label>
                                        <input type="number" id="accessDuration" name="duration" min="1" max="365" value="30" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="durationType">Period</label>
                                        <select id="durationType" name="duration_type" required>
                                            <option value="days">Days</option>
                                            <option value="months" selected>Months</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="codeDescription">Description (Optional)</label>
                                    <input type="text" id="codeDescription" name="description" placeholder="e.g., Special promotion, Conference attendees">
                                </div>
                                <button type="submit" class="btn btn-primary">Generate Access Code</button>
                            </form>
                        </div>
                        
                        <div class="access-codes-list">
                            <h4>Active Access Codes</h4>
                            <div class="table-container">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Description</th>
                                            <th>Duration</th>
                                            <th>Uses</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="accessCodesTableBody">
                                        <?php
                                        $accessCodes = $db->fetchAll("SELECT * FROM access_codes WHERE is_active = 1 ORDER BY created_at DESC");
                                        if (empty($accessCodes)):
                                        ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No access codes generated yet</td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach ($accessCodes as $code): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($code['code']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($code['description'] ?? 'N/A'); ?></td>
                                                <td><?php echo $code['duration'] . ' ' . $code['duration_type']; ?></td>
                                                <td><?php echo $code['current_uses'] . ($code['max_uses'] ? '/' . $code['max_uses'] : ''); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($code['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteAccessCode(<?php echo $code['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- API Settings Section -->
                <div id="api-settingsSection" class="content-section">
                    <div class="section-header">
                        <h2>API Configuration</h2>
                        <p>Configure OpenAI API settings and monitor usage</p>
                    </div>
                    
                    <div class="settings-section">
                        <h3>OpenAI API Configuration</h3>
                        <?php
                        $apiConfig = $db->fetch("SELECT * FROM api_config WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
                        $hasApiKey = !empty($apiConfig) && !empty($apiConfig['openai_key']);
                        ?>
                        
                        <form id="apiSettingsForm" method="POST" action="<?php echo appUrl('api/admin.php'); ?>">
                            <input type="hidden" name="action" value="save_api_key">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="form-group">
                                <label for="openaiKey">OpenAI API Key</label>
                                <input 
                                    type="password" 
                                    id="openaiKey" 
                                    name="openai_key" 
                                    placeholder="sk-..." 
                                    value="<?php echo $hasApiKey ? '••••••••••••••••' : ''; ?>"
                                    required
                                >
                                <?php if ($hasApiKey): ?>
                                <small class="form-hint">Leave as is to keep current key, or enter new key to update</small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="api-status">
                                <div class="status-indicator">
                                    <?php if ($hasApiKey): ?>
                                    <i class="fas fa-circle text-success"></i>
                                    <span>API Key Configured</span>
                                    <?php else: ?>
                                    <i class="fas fa-circle text-danger"></i>
                                    <span>No API Key Configured</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Save API Key</button>
                                <?php if ($hasApiKey): ?>
                                <button type="button" id="testApiBtn" class="btn btn-secondary">Test API Key</button>
                                <?php endif; ?>
                            </div>
                        </form>
                        
                        <?php if ($hasApiKey): ?>
                        <div class="api-usage-stats">
                            <h4>API Usage Statistics</h4>
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="stat-value" id="totalApiRequests"><?php echo number_format($apiConfig['total_requests'] ?? 0); ?></div>
                                    <div class="stat-label">Total Requests</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value" id="openaiRequests"><?php echo number_format($apiConfig['openai_requests'] ?? 0); ?></div>
                                    <div class="stat-label">OpenAI Requests</div>
                                </div>
                            </div>
                            <p style="margin-top: 1rem; color: #6b7280; font-size: 0.875rem;">
                                Last updated: <?php echo $apiConfig ? date('M j, Y g:i A', strtotime($apiConfig['updated_at'])) : 'Never'; ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- AI Instructions Section -->
                <div id="ai-instructionsSection" class="content-section">
                    <div class="section-header">
                        <h2>AI Response Instructions</h2>
                        <p>Customize how the AI responds for each assistant</p>
                    </div>
                    
                    <div class="settings-section">
                        <h3>AI Response Instructions</h3>
                        <p>Customize how the AI responds for each assistant. These instructions will be added to the system prompt.</p>
                        
                        <?php
                        // Fetch current instructions
                        $federalInstructions = $db->fetch("SELECT * FROM ai_instructions WHERE tool_name = 'federal'");
                        $californiaInstructions = $db->fetch("SELECT * FROM ai_instructions WHERE tool_name = 'california'");
                        ?>
                        
                        <!-- Federal FMLA Instructions -->
                        <div class="instruction-section">
                            <h4><i class="fas fa-flag-usa"></i> Federal FMLA Assistant</h4>
                            <form id="federalInstructionsForm" class="instructions-form">
                                <input type="hidden" name="tool_name" value="federal">
                                <div class="form-group">
                                    <label for="federalInstructions">Custom Instructions</label>
                                    <textarea 
                                        id="federalInstructions" 
                                        name="custom_instructions" 
                                        rows="8"
                                        placeholder="Enter specific instructions for how the AI should respond to Federal FMLA requests. For example: tone, format, specific details to include, etc."
                                    ><?php echo htmlspecialchars($federalInstructions['custom_instructions'] ?? ''); ?></textarea>
                                    <small class="form-hint">
                                        These instructions will be added to the base Federal FMLA prompt. Leave empty to use default behavior.
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input 
                                            type="checkbox" 
                                            name="is_active" 
                                            <?php echo ($federalInstructions['is_active'] ?? 1) ? 'checked' : ''; ?>
                                        >
                                        Enable Custom Instructions
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Federal Instructions
                                </button>
                            </form>
                        </div>
                        
                        <!-- California Leave Instructions -->
                        <div class="instruction-section">
                            <h4><i class="fas fa-sun"></i> California Leave Assistant</h4>
                            <form id="californiaInstructionsForm" class="instructions-form">
                                <input type="hidden" name="tool_name" value="california">
                                <div class="form-group">
                                    <label for="californiaInstructions">Custom Instructions</label>
                                    <textarea 
                                        id="californiaInstructions" 
                                        name="custom_instructions" 
                                        rows="8"
                                        placeholder="Enter specific instructions for how the AI should respond to California leave requests. For example: tone, format, specific details to include, etc."
                                    ><?php echo htmlspecialchars($californiaInstructions['custom_instructions'] ?? ''); ?></textarea>
                                    <small class="form-hint">
                                        These instructions will be added to the base California leave prompt. Leave empty to use default behavior.
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input 
                                            type="checkbox" 
                                            name="is_active" 
                                            <?php echo ($californiaInstructions['is_active'] ?? 1) ? 'checked' : ''; ?>
                                        >
                                        Enable Custom Instructions
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save California Instructions
                                </button>
                            </form>
                        </div>
                        
                        <div class="instruction-tips">
                            <h4><i class="fas fa-lightbulb"></i> Tips for Effective Instructions</h4>
                            <ul>
                                <li>Be specific about the tone (formal, friendly, professional)</li>
                                <li>Specify any required sections or format</li>
                                <li>Mention specific laws or regulations to emphasize</li>
                                <li>Include any company-specific policies to reference</li>
                                <li>Specify length preferences (concise vs detailed)</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                
                <!-- Content Management Section -->
                <div id="content-managementSection" class="content-section">
                    <div class="section-header">
                        <h2><i class="fas fa-edit"></i> Content Management</h2>
                        <p>Edit all website content including text, images, and video links</p>
                    </div>

                    <!-- Content Management Tabs -->
                    <div class="content-tabs">
                        <button class="content-tab active" data-tab="hero">
                            <i class="fas fa-home"></i> Hero Section
                        </button>
                        <button class="content-tab" data-tab="video">
                            <i class="fas fa-video"></i> Video Settings
                        </button>
                        <button class="content-tab" data-tab="features">
                            <i class="fas fa-star"></i> Features
                        </button>
                        <button class="content-tab" data-tab="how_it_works">
                            <i class="fas fa-cogs"></i> How It Works
                        </button>
                        <button class="content-tab" data-tab="about">
                            <i class="fas fa-info-circle"></i> About
                        </button>
                        <button class="content-tab" data-tab="pricing">
                            <i class="fas fa-dollar-sign"></i> Pricing
                        </button>
                        <button class="content-tab" data-tab="faq">
                            <i class="fas fa-question-circle"></i> FAQ
                        </button>
                        <button class="content-tab" data-tab="cta">
                            <i class="fas fa-bullhorn"></i> Call to Action
                        </button>
                        <button class="content-tab" data-tab="footer">
                            <i class="fas fa-align-center"></i> Footer
                        </button>
                        <button class="content-tab" data-tab="colors">
                            <i class="fas fa-palette"></i> Colors
                        </button>
                    </div>

                    <!-- Hero Content Tab -->
                    <div class="content-tab-content active" id="heroContent">
                        <div class="card">
                            <div class="card-header">
                                <h3>Hero Section Content</h3>
                                <p>Edit the main headline and features on your homepage</p>
                            </div>
                            <div class="card-body">
                                <form id="heroContentForm" class="content-form">
                                    <div class="form-group">
                                        <label for="hero_title">Hero Title</label>
                                        <textarea id="hero_title" name="hero_title" rows="3" class="form-control" placeholder="Main headline on homepage">Answer Employee Leave Questions With Consistent Compliance Information</textarea>
                                        <small class="form-text">Character count: <span class="char-count">0</span></small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="hero_subtitle">Hero Subtitle</label>
                                        <textarea id="hero_subtitle" name="hero_subtitle" rows="3" class="form-control" placeholder="Subtitle text below main headline">AI-powered HR leave response generator for federal and California leave questions. Draft clear, compliant employee communications aligned with FMLA, CFRA, PDL, and ADA.</textarea>
                                        <small class="form-text">Character count: <span class="char-count">0</span></small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Feature Bullet Points</label>
                                        <div class="feature-inputs">
                                            <input type="text" name="hero_feature_1" class="form-control mb-2" placeholder="Feature 1" value="Built by HR for HR professionals">
                                            <input type="text" name="hero_feature_2" class="form-control mb-2" placeholder="Feature 2" value="Drafts employee-ready responses">
                                            <input type="text" name="hero_feature_3" class="form-control mb-2" placeholder="Feature 3" value="Aligned with FMLA, PDL, ADA, and CFRA">
                                            <input type="text" name="hero_feature_4" class="form-control mb-2" placeholder="Feature 4" value="Supports consistent HR decision-making">
                                            <input type="text" name="hero_feature_5" class="form-control mb-2" placeholder="Feature 5" value="Helps reduce compliance risk">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="hero_cta_primary">Primary Button Text</label>
                                        <input type="text" id="hero_cta_primary" name="hero_cta_primary" class="form-control" value="Try HR Leave Assist">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="hero_cta_secondary">Secondary Button Text</label>
                                        <input type="text" id="hero_cta_secondary" name="hero_cta_secondary" class="form-control" value="See How It Works">
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Save Hero Content
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="previewChanges('hero')">
                                            <i class="fas fa-eye"></i> Preview Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Video Settings Tab -->
                    <div class="content-tab-content" id="videoContent">
                        <div class="card">
                            <div class="card-header">
                                <h3>Video Settings</h3>
                                <p>Manage the YouTube video that plays in the "How It Works" modal</p>
                            </div>
                            <div class="card-body">
                                <form id="videoContentForm" class="content-form">
                                    <div class="form-group">
                                        <label for="video_url">YouTube Video URL</label>
                                        <input type="url" id="video_url" name="video_url" class="form-control" value="https://youtu.be/mCncgWhvKnQ" placeholder="https://youtu.be/VIDEO_ID">
                                        <small class="form-text">Enter the full YouTube URL (e.g., https://youtu.be/mCncgWhvKnQ)</small>
                                    </div>
                                    
                                    <div class="video-preview">
                                        <h4>Current Video Preview</h4>
                                        <div class="video-embed">
                                            <iframe width="100%" height="315" src="https://www.youtube.com/embed/mCncgWhvKnQ" frameborder="0" allowfullscreen></iframe>
                                        </div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Save Video Settings
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="updateVideoPreview()">
                                            <i class="fas fa-refresh"></i> Update Preview
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Features Tab -->
                    <div class="content-tab-content" id="featuresContent">
                        <div class="card">
                            <div class="card-header">
                                <h3>Features Section</h3>
                                <p>Edit the features displayed on your homepage</p>
                            </div>
                            <div class="card-body">
                                <form id="featuresContentForm" class="content-form">
                                    <div class="form-group">
                                        <label for="features_title">Features Section Title</label>
                                        <input type="text" id="features_title" name="features_title" class="form-control" value="Supporting Your Leave Process">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="features_subtitle">Features Section Subtitle</label>
                                        <input type="text" id="features_subtitle" name="features_subtitle" class="form-control" value="Every Step of the Way">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Feature Items</label>
                                        <div class="feature-items">
                                            <input type="text" name="feature_1" class="form-control mb-2" value="Built to Support Leave Compliance">
                                            <input type="text" name="feature_2" class="form-control mb-2" value="Respond to Leave Questions Faster">
                                            <input type="text" name="feature_3" class="form-control mb-2" value="Navigate Federal & California Leave Laws">
                                            <input type="text" name="feature_4" class="form-control mb-2" value="Designed for Busy HR Teams">
                                            <input type="text" name="feature_5" class="form-control mb-2" value="Empowers HR-Led Decision-Making">
                                            <input type="text" name="feature_6" class="form-control mb-2" value="Supports Consistent Leave Administration">
                                        </div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Save Features
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- How It Works Tab -->
                    <div class="content-tab-content" id="how_it_worksContent">
                        <div class="card">
                            <div class="card-header">
                                <h3>How It Works Section</h3>
                                <p>Edit the step-by-step process explanation</p>
                            </div>
                            <div class="card-body">
                                <form id="howItWorksContentForm" class="content-form">
                                    <div class="form-group">
                                        <label for="how_it_works_title">Section Title</label>
                                        <input type="text" id="how_it_works_title" name="how_it_works_title" class="form-control" value="How HR Leave Assist Works">
                                    </div>
                                    
                                    <div class="steps-editor">
                                        <div class="step-group">
                                            <h4>Step 1</h4>
                                            <div class="form-group">
                                                <label for="step_1_title">Step 1 Title</label>
                                                <input type="text" id="step_1_title" name="step_1_title" class="form-control" value="Paste Employee Question or Email">
                                            </div>
                                            <div class="form-group">
                                                <label for="step_1_description">Step 1 Description</label>
                                                <textarea id="step_1_description" name="step_1_description" rows="2" class="form-control">Copy and paste the employee's leave question or email into the system.</textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="step-group">
                                            <h4>Step 2</h4>
                                            <div class="form-group">
                                                <label for="step_2_title">Step 2 Title</label>
                                                <input type="text" id="step_2_title" name="step_2_title" class="form-control" value="Analysis & Draft Response">
                                            </div>
                                            <div class="form-group">
                                                <label for="step_2_description">Step 2 Description</label>
                                                <textarea id="step_2_description" name="step_2_description" rows="2" class="form-control">The tool analyzes the email and prepares an employee-ready draft aligned with applicable leave requirements.</textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="step-group">
                                            <h4>Step 3</h4>
                                            <div class="form-group">
                                                <label for="step_3_title">Step 3 Title</label>
                                                <input type="text" id="step_3_title" name="step_3_title" class="form-control" value="Review Generated Response & Send">
                                            </div>
                                            <div class="form-group">
                                                <label for="step_3_description">Step 3 Description</label>
                                                <textarea id="step_3_description" name="step_3_description" rows="2" class="form-control">Review, edit as needed, and send the response to your employee.</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Save How It Works
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- About Tab -->
                    <div class="content-tab-content" id="aboutContent">
                        <div class="card">
                            <div class="card-header">
                                <h3>About Section</h3>
                                <p>Edit your about section content</p>
                            </div>
                            <div class="card-body">
                                <form id="aboutContentForm" class="content-form">
                                    <div class="form-group">
                                        <label for="about_title">About Section Title</label>
                                        <input type="text" id="about_title" name="about_title" class="form-control" value="HR Leave Assist">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="about_paragraph_1">First Paragraph</label>
                                        <textarea id="about_paragraph_1" name="about_paragraph_1" rows="4" class="form-control">HR Leave Assist (HRLA) is a support tool built for HR professionals who answer employee leave questions every day — especially those involving FMLA, CFRA, PDL, and ADA considerations.</textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="about_paragraph_2">Second Paragraph</label>
                                        <textarea id="about_paragraph_2" name="about_paragraph_2" rows="4" class="form-control">Leave situations are rarely straightforward. They often involve overlapping leave laws, internal requirements, and personal circumstances—under real-time pressure. HRLA helps streamline that complexity by organizing applicable leave considerations and drafting clear, employee-ready responses, without starting from scratch.</textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="about_paragraph_3">Third Paragraph</label>
                                        <textarea id="about_paragraph_3" name="about_paragraph_3" rows="4" class="form-control">Built by an HR professional with over 25 years of experience, HRLA is designed to support your judgment—not replace it. The tool reinforces consistency, reduces missed steps, and helps you respond with care, efficiency, and confidence.</textarea>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Save About Content
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Tab -->
                    <div class="content-tab-content" id="pricingContent">
                        <div class="card">
                            <div class="card-header">
                                <h3>Pricing Section</h3>
                                <p>Edit pricing plans and descriptions</p>
                            </div>
                            <div class="card-body">
                                <form id="pricingContentForm" class="content-form">
                                    <div class="form-group">
                                        <label for="pricing_title">Pricing Section Title</label>
                                        <input type="text" id="pricing_title" name="pricing_title" class="form-control" value="Pricing">
                                    </div>
                                    
                                    <div class="pricing-plans">
                                        <div class="plan-group">
                                            <h4>Free Trial Plan</h4>
                                            <div class="form-group">
                                                <label for="pricing_free_title">Plan Title</label>
                                                <input type="text" id="pricing_free_title" name="pricing_free_title" class="form-control" value="Free Trial — $0">
                                            </div>
                                            <div class="form-group">
                                                <label for="pricing_free_description">Plan Description</label>
                                                <textarea id="pricing_free_description" name="pricing_free_description" rows="2" class="form-control">HR professionals who want to test the tool with real-world scenarios before subscribing.</textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="plan-group">
                                            <h4>Monthly Plan</h4>
                                            <div class="form-group">
                                                <label for="pricing_monthly_title">Plan Title</label>
                                                <input type="text" id="pricing_monthly_title" name="pricing_monthly_title" class="form-control" value="Monthly — $29">
                                            </div>
                                            <div class="form-group">
                                                <label for="pricing_monthly_description">Plan Description</label>
                                                <textarea id="pricing_monthly_description" name="pricing_monthly_description" rows="2" class="form-control">Individual HR professionals who regularly respond to employee leave inquiries.</textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="plan-group">
                                            <h4>Annual Plan</h4>
                                            <div class="form-group">
                                                <label for="pricing_annual_title">Plan Title</label>
                                                <input type="text" id="pricing_annual_title" name="pricing_annual_title" class="form-control" value="Annual — $290">
                                            </div>
                                            <div class="form-group">
                                                <label for="pricing_annual_subtitle">Plan Subtitle</label>
                                                <input type="text" id="pricing_annual_subtitle" name="pricing_annual_subtitle" class="form-control" value="(2 months free)">
                                            </div>
                                            <div class="form-group">
                                                <label for="pricing_annual_description">Plan Description</label>
                                                <textarea id="pricing_annual_description" name="pricing_annual_description" rows="2" class="form-control">Individual HR professionals who rely on HR Leave Assist as part of their regular, year-round workflow.</textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="plan-group">
                                            <h4>Organization Plan</h4>
                                            <div class="form-group">
                                                <label for="pricing_org_title">Plan Title</label>
                                                <input type="text" id="pricing_org_title" name="pricing_org_title" class="form-control" value="Organization — $580 / yr">
                                            </div>
                                            <div class="form-group">
                                                <label for="pricing_org_description">Plan Description</label>
                                                <textarea id="pricing_org_description" name="pricing_org_description" rows="2" class="form-control">Small HR teams of 2 to 5 who regularly respond to employee leave questions and want consistent, shared access.</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Save Pricing Content
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Tab -->
                    <div class="content-tab-content" id="faqContent">
                        <div class="card">
                            <div class="card-header">
                                <h3>FAQ Section</h3>
                                <p>Edit FAQ section titles and descriptions</p>
                            </div>
                            <div class="card-body">
                                <form id="faqContentForm" class="content-form">
                                    <div class="form-group">
                                        <label for="faq_title">FAQ Section Title</label>
                                        <input type="text" id="faq_title" name="faq_title" class="form-control" value="Frequently Asked Questions">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="faq_subtitle">FAQ Section Subtitle</label>
                                        <input type="text" id="faq_subtitle" name="faq_subtitle" class="form-control" value="Select a category to find answers about leave laws and regulations">
                                    </div>
                                    
                                    <div class="faq-cards">
                                        <div class="faq-card-group">
                                            <h4>FMLA FAQ Card</h4>
                                            <div class="form-group">
                                                <label for="faq_fmla_title">FMLA Card Title</label>
                                                <input type="text" id="faq_fmla_title" name="faq_fmla_title" class="form-control" value="FMLA FAQs">
                                            </div>
                                            <div class="form-group">
                                                <label for="faq_fmla_description">FMLA Card Description</label>
                                                <textarea id="faq_fmla_description" name="faq_fmla_description" rows="2" class="form-control">Family and Medical Leave Act questions covering federal leave requirements, eligibility, and job protection.</textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="faq-card-group">
                                            <h4>CFRA FAQ Card</h4>
                                            <div class="form-group">
                                                <label for="faq_cfra_title">CFRA Card Title</label>
                                                <input type="text" id="faq_cfra_title" name="faq_cfra_title" class="form-control" value="CFRA FAQs">
                                            </div>
                                            <div class="form-group">
                                                <label for="faq_cfra_description">CFRA Card Description</label>
                                                <textarea id="faq_cfra_description" name="faq_cfra_description" rows="2" class="form-control">California Family Rights Act questions covering state-specific leave laws, benefits, and requirements.</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Save FAQ Content
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- CTA Tab -->
                    <div class="content-tab-content" id="ctaContent">
                        <div class="card">
                            <div class="card-header">
                                <h3>Call to Action Section</h3>
                                <p>Edit the final call-to-action section</p>
                            </div>
                            <div class="card-body">
                                <form id="ctaContentForm" class="content-form">
                                    <div class="form-group">
                                        <label for="cta_title">CTA Title</label>
                                        <input type="text" id="cta_title" name="cta_title" class="form-control" value="Simple to Start - Easy to Use">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="cta_button_text">CTA Button Text</label>
                                        <input type="text" id="cta_button_text" name="cta_button_text" class="form-control" value="Get Started Now">
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Save CTA Content
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Tab -->
                    <div class="content-tab-content" id="footerContent">
                        <div class="card">
                            <div class="card-header">
                                <h3>Footer Content</h3>
                                <p>Edit footer description text</p>
                            </div>
                            <div class="card-body">
                                <form id="footerContentForm" class="content-form">
                                    <div class="form-group">
                                        <label for="footer_description">Footer Description</label>
                                        <textarea id="footer_description" name="footer_description" rows="3" class="form-control">A leave-support tool built by HR, for HR, to help apply consistent, compliance-aligned responses to employee leave questions.</textarea>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Save Footer Content
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Colors Tab -->
                    <div class="content-tab-content" id="colorsContent">
                        <div class="card">
                            <div class="card-header">
                                <h3>Website Colors</h3>
                                <p>Customize your website's color scheme</p>
                            </div>
                            <div class="card-body">
                                <form id="colorsContentForm" class="content-form">
                                    <div class="color-picker-group">
                                        <label for="color_primary">Primary Blue</label>
                                        <div class="color-input-wrapper">
                                            <input type="color" id="color_primary" name="color_primary" value="#0322D8" class="color-input">
                                            <input type="text" value="#0322D8" class="color-text-input" readonly>
                                            <button type="button" class="btn-reset-color" data-default="#0322D8">
                                                <i class="fas fa-undo"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="color-picker-group">
                                        <label for="color_secondary">Secondary Green</label>
                                        <div class="color-input-wrapper">
                                            <input type="color" id="color_secondary" name="color_secondary" value="#3DB20B" class="color-input">
                                            <input type="text" value="#3DB20B" class="color-text-input" readonly>
                                            <button type="button" class="btn-reset-color" data-default="#3DB20B">
                                                <i class="fas fa-undo"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="color-picker-group">
                                        <label for="color_dark_blue">Dark Blue</label>
                                        <div class="color-input-wrapper">
                                            <input type="color" id="color_dark_blue" name="color_dark_blue" value="#1800AD" class="color-input">
                                            <input type="text" value="#1800AD" class="color-text-input" readonly>
                                            <button type="button" class="btn-reset-color" data-default="#1800AD">
                                                <i class="fas fa-undo"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="color-picker-group">
                                        <label for="color_red">Red (CTA Section)</label>
                                        <div class="color-input-wrapper">
                                            <input type="color" id="color_red" name="color_red" value="#FF0000" class="color-input">
                                            <input type="text" value="#FF0000" class="color-text-input" readonly>
                                            <button type="button" class="btn-reset-color" data-default="#FF0000">
                                                <i class="fas fa-undo"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Save Colors
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="previewColors()">
                                            <i class="fas fa-eye"></i> Preview Colors
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php 
                // Include Site Settings Section
                require_once 'site-settings-section.php'; 
                ?>
                
                <!-- Storage Management Section -->
                <div id="storageSection" class="content-section">
                    <div class="section-header">
                        <h2>Storage Management</h2>
                        <p>Manage database and system storage</p>
                    </div>
                    
                    <div class="settings-section">
                        <h3>Storage Management</h3>
                        <p>Manage database and system storage</p>
                        
                        <div class="storage-stats">
                            <h4>Database Statistics</h4>
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="stat-value"><?php echo $stats['total_users']; ?></div>
                                    <div class="stat-label">Total Users</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value">0</div>
                                    <div class="stat-label">Conversations</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="storage-actions">
                            <h4>Database Operations</h4>
                            <div class="action-buttons">
                                <button class="btn btn-primary" onclick="exportData()">
                                    <i class="fas fa-download"></i>
                                    Export Database
                                </button>
                                <button class="btn btn-secondary" onclick="optimizeDatabase()">
                                    <i class="fas fa-cog"></i>
                                    Optimize Database
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </main>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit User</h3>
                <button class="modal-close" onclick="closeEditModal()">&times;</button>
            </div>
            <form id="editUserForm">
                <input type="hidden" id="editUserId" name="user_id">
                <div class="form-group">
                    <label for="editFirstName">First Name</label>
                    <input type="text" id="editFirstName" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="editLastName">Last Name</label>
                    <input type="text" id="editLastName" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="editEmail">Email</label>
                    <input type="email" id="editEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="editAccessLevel">Access Level</label>
                    <select id="editAccessLevel" name="access_level" required>
                        <option value="trial">Trial</option>
                        <option value="extended">Extended</option>
                        <option value="subscribed">Subscribed</option>
                        <option value="expired">Expired</option>
                        <option value="administrator">Administrator</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="editEmailVerified" name="email_verified">
                        Email Verified
                    </label>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>


    <script>
        // Notification System
        function showNotification(message, type = 'info') {
            // Remove any existing notifications
            const existingNotification = document.querySelector('.admin-notification');
            if (existingNotification) {
                existingNotification.remove();
            }
            
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `admin-notification notification-${type}`;
            
            // Set icon based on type
            let icon = 'fa-info-circle';
            if (type === 'success') icon = 'fa-check-circle';
            if (type === 'error') icon = 'fa-exclamation-circle';
            if (type === 'warning') icon = 'fa-exclamation-triangle';
            
            notification.innerHTML = `
                <i class="fas ${icon}"></i>
                <span>${message}</span>
                <button class="notification-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            // Add to page
            document.body.appendChild(notification);
            
            // Trigger animation
            setTimeout(() => notification.classList.add('show'), 10);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }
        
        // Sidebar Navigation
        const navItems = document.querySelectorAll('.nav-item');
        const sections = document.querySelectorAll('.content-section');
        const pageTitle = document.getElementById('pageTitle');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const adminSidebar = document.getElementById('adminSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        // Section navigation
        navItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const sectionName = this.dataset.section;
                
                // Update active nav item
                navItems.forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
                
                // Update active section
                sections.forEach(section => section.classList.remove('active'));
                const targetSection = document.getElementById(sectionName + 'Section');
                if (targetSection) {
                    targetSection.classList.add('active');
                }
                
                // Update page title
                const sectionTitle = this.querySelector('span').textContent;
                pageTitle.textContent = sectionTitle;
                
                // Close mobile sidebar
                if (window.innerWidth <= 1024) {
                    adminSidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });

        // Mobile menu toggle
        mobileMenuBtn.addEventListener('click', function() {
            adminSidebar.classList.add('mobile-open');
            sidebarOverlay.classList.add('active');
        });

        sidebarToggle.addEventListener('click', function() {
            adminSidebar.classList.remove('mobile-open');
            sidebarOverlay.classList.remove('active');
        });

        sidebarOverlay.addEventListener('click', function() {
            adminSidebar.classList.remove('mobile-open');
            sidebarOverlay.classList.remove('active');
        });

        // User management functions
        function editUser(id) {
            // Fetch user data
            fetch(`<?php echo appUrl('api/admin.php?action=get_user&id='); ?>${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const user = data.user;
                        document.getElementById('editUserId').value = user.id;
                        document.getElementById('editFirstName').value = user.first_name;
                        document.getElementById('editLastName').value = user.last_name;
                        document.getElementById('editEmail').value = user.email;
                        document.getElementById('editAccessLevel').value = user.access_level;
                        document.getElementById('editEmailVerified').checked = user.email_verified == 1;
                        
                        document.getElementById('editUserModal').style.display = 'flex';
                    } else {
                        alert('Error loading user data: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                });
        }

        function closeEditModal() {
            document.getElementById('editUserModal').style.display = 'none';
        }

        // Handle edit user form submission
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {
                action: 'update_user',
                user_id: formData.get('user_id'),
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                email: formData.get('email'),
                access_level: formData.get('access_level'),
                email_verified: document.getElementById('editEmailVerified').checked ? 1 : 0,
                csrf_token: '<?php echo generateCSRFToken(); ?>'
            };
            
            fetch('<?php echo appUrl('api/admin.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User updated successfully');
                    closeEditModal();
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to update user'));
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        });

        function deleteUser(id) {
            if (confirm('Are you sure you want to delete this user?')) {
                fetch('<?php echo appUrl('api/admin.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete_user',
                        user_id: id,
                        csrf_token: '<?php echo generateCSRFToken(); ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('User deleted successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.error || 'Failed to delete user'));
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                });
            }
        }

        // Access code management
        function deleteAccessCode(id) {
            if (confirm('Are you sure you want to delete this access code?')) {
                fetch('<?php echo appUrl('api/admin.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete_access_code',
                        id: id,
                        csrf_token: '<?php echo generateCSRFToken(); ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Access code deleted successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.error || 'Failed to delete access code'));
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                });
            }
        }

        // Test API Key
        const testApiBtn = document.getElementById('testApiBtn');
        if (testApiBtn) {
            testApiBtn.addEventListener('click', function() {
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
                
                fetch('<?php echo appUrl('api/admin.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'test_api_key',
                        csrf_token: '<?php echo generateCSRFToken(); ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('API Key is working correctly!');
                    } else {
                        alert('API Key test failed: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Error testing API key: ' + error.message);
                })
                .finally(() => {
                    this.disabled = false;
                    this.innerHTML = 'Test API Key';
                });
            });
        }

        // Export and optimize functions
        function exportData() {
            window.location.href = '<?php echo appUrl('api/admin.php?action=export_data'); ?>';
        }

        function optimizeDatabase() {
            if (confirm('This will optimize all database tables. Continue?')) {
                fetch('<?php echo appUrl('api/admin.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'optimize_database',
                        csrf_token: '<?php echo generateCSRFToken(); ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Database optimized successfully!');
                    } else {
                        alert('Error: ' + (data.error || 'Failed to optimize database'));
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                });
            }
        }

        // Refresh users
        document.getElementById('refreshUsers')?.addEventListener('click', function() {
            location.reload();
        });

        // Export users
        document.getElementById('exportUsers')?.addEventListener('click', function() {
            window.location.href = '<?php echo appUrl('api/admin.php?action=export_users'); ?>';
        });

        // User search and filter
        const userSearch = document.getElementById('userSearch');
        const userFilter = document.getElementById('userFilter');
        const usersTableBody = document.getElementById('usersTableBody');

        if (userSearch && userFilter && usersTableBody) {
            function filterUsers() {
                const searchTerm = userSearch.value.toLowerCase();
                const filterValue = userFilter.value;
                const rows = usersTableBody.querySelectorAll('tr');

                rows.forEach(row => {
                    const name = row.querySelector('.user-name')?.textContent.toLowerCase() || '';
                    const email = row.cells[2]?.textContent.toLowerCase() || '';
                    const status = row.querySelector('.status-badge')?.textContent.toLowerCase() || '';
                    
                    const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
                    const matchesFilter = filterValue === 'all' || status.includes(filterValue);

                    row.style.display = (matchesSearch && matchesFilter) ? '' : 'none';
                });
            }

            userSearch.addEventListener('input', filterUsers);
            userFilter.addEventListener('change', filterUsers);
        }

        // Stat card filtering
        document.querySelectorAll('.stat-card.clickable-card').forEach(card => {
            card.addEventListener('click', function() {
                const filter = this.dataset.filter;
                
                // Switch to users section
                const usersNavItem = document.querySelector('[data-section="users"]');
                if (usersNavItem) {
                    usersNavItem.click();
                }
                
                // Apply filter
                if (userFilter) {
                    setTimeout(() => {
                        userFilter.value = filter;
                        userFilter.dispatchEvent(new Event('change'));
                    }, 100);
                }
            });
        });

        // AI Instructions Forms
        document.getElementById('federalInstructionsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            saveAIInstructions(this, 'federal');
        });

        document.getElementById('californiaInstructionsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            saveAIInstructions(this, 'california');
        });

        function saveAIInstructions(form, toolName) {
            const formData = new FormData(form);
            const data = {
                action: 'save_ai_instructions',
                tool_name: formData.get('tool_name'),
                custom_instructions: formData.get('custom_instructions'),
                is_active: form.querySelector('input[name="is_active"]').checked ? 1 : 0,
                csrf_token: '<?php echo generateCSRFToken(); ?>'
            };

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            fetch('<?php echo appUrl('api/admin.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Instructions saved successfully!');
                } else {
                    alert('Error: ' + (data.error || 'Failed to save instructions'));
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        }

        // Success message function
        function showSuccessMessage(message) {
            // Create success message element
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.innerHTML = `
                <i class="fas fa-check-circle"></i>
                <span>${message}</span>
            `;
            
            // Add to page
            document.body.appendChild(successDiv);
            
            // Remove after 3 seconds
            setTimeout(() => {
                successDiv.remove();
            }, 3000);
        }

        // Update generate access code form to stay on section
        document.getElementById('generateAccessCodeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('<?php echo appUrl('api/admin.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Access code generated successfully!');
                    // Reload the page to show new code
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('Error: ' + (data.error || 'Failed to generate access code'));
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        });

        // Update API settings form to stay on section
        document.getElementById('apiSettingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('<?php echo appUrl('api/admin.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('API key saved successfully!');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('Error: ' + (data.error || 'Failed to save API key'));
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        });

        // Content Management System JavaScript
        
        // Content tabs functionality
        document.querySelectorAll('.content-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabName = this.dataset.tab;
                
                // Update active tab
                document.querySelectorAll('.content-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Update active content
                document.querySelectorAll('.content-tab-content').forEach(c => c.classList.remove('active'));
                const targetContent = document.getElementById(tabName + 'Content');
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            });
        });

        // Character count for textareas
        document.querySelectorAll('.content-form textarea, .content-form input[type="text"]').forEach(input => {
            const updateCount = () => {
                const counter = input.parentElement.querySelector('.char-count');
                if (counter) {
                    counter.textContent = input.value.length;
                }
            };
            
            updateCount();
            input.addEventListener('input', updateCount);
        });

        // Color picker sync
        document.querySelectorAll('.color-input').forEach(input => {
            input.addEventListener('input', function() {
                const textInput = this.parentElement.querySelector('.color-text-input');
                textInput.value = this.value.toUpperCase();
            });
        });

        // Reset color buttons
        document.querySelectorAll('.btn-reset-color').forEach(btn => {
            btn.addEventListener('click', function() {
                const defaultColor = this.dataset.default;
                const colorInput = this.parentElement.querySelector('.color-input');
                const textInput = this.parentElement.querySelector('.color-text-input');
                
                colorInput.value = defaultColor;
                textInput.value = defaultColor;
            });
        });

        // Content form submissions
        const contentForms = [
            'heroContentForm', 'videoContentForm', 'featuresContentForm', 
            'howItWorksContentForm', 'aboutContentForm', 'pricingContentForm',
            'faqContentForm', 'ctaContentForm', 'footerContentForm', 'colorsContentForm'
        ];

        contentForms.forEach(formId => {
            const form = document.getElementById(formId);
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const data = Object.fromEntries(formData);
                    
                    // Add action and category
                    data.action = 'save_content';
                    data.category = formId.replace('ContentForm', '').replace('_', '');
                    data.csrf_token = '<?php echo generateCSRFToken(); ?>';
                    
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                    
                    try {
                        const response = await fetch('<?php echo appUrl('api/admin.php'); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(data)
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            showNotification('Content saved successfully!', 'success');
                        } else {
                            showNotification(result.error || 'Failed to save content', 'error');
                        }
                    } catch (error) {
                        showNotification('Error saving content: ' + error.message, 'error');
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
            }
        });

        // Video preview update
        function updateVideoPreview() {
            const videoUrl = document.getElementById('video_url').value;
            const iframe = document.querySelector('.video-embed iframe');
            
            if (videoUrl && iframe) {
                // Extract video ID from YouTube URL
                let videoId = '';
                if (videoUrl.includes('youtu.be/')) {
                    videoId = videoUrl.split('youtu.be/')[1].split('?')[0];
                } else if (videoUrl.includes('youtube.com/watch?v=')) {
                    videoId = videoUrl.split('v=')[1].split('&')[0];
                }
                
                if (videoId) {
                    iframe.src = `https://www.youtube.com/embed/${videoId}`;
                    showNotification('Video preview updated!', 'success');
                } else {
                    showNotification('Invalid YouTube URL format', 'error');
                }
            }
        }

        // Preview changes function
        function previewChanges(section) {
            showNotification('Preview functionality will be available after saving changes', 'info');
        }

        // Preview colors function
        function previewColors() {
            const form = document.getElementById('colorsContentForm');
            const formData = new FormData(form);
            
            // Apply colors temporarily to admin panel
            document.documentElement.style.setProperty('--color-primary', formData.get('color_primary'));
            document.documentElement.style.setProperty('--color-secondary', formData.get('color_secondary'));
            document.documentElement.style.setProperty('--color-dark-blue', formData.get('color_dark_blue'));
            document.documentElement.style.setProperty('--color-red', formData.get('color_red'));
            
            showNotification('Color preview applied! Save to make permanent.', 'info');
        }
    </script>
</body>
</html>
