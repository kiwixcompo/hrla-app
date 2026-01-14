<?php
/**
 * User Settings Page
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'includes/auth.php';

$auth = getAuth();
$auth->requireAuth();

$user = $auth->getCurrentUser();

$pageTitle = 'Settings - HR Leave Assistant';
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
    <div id="settings" class="page">
        <nav class="app-nav">
            <div class="nav-container">
                <div class="nav-brand">
                    <img src="hrla_logo.png" alt="HRLA" class="nav-logo">
                    <span class="nav-title">Settings</span>
                </div>
                <div class="nav-menu">
                    <a href="<?php echo appUrl('dashboard.php'); ?>" class="btn btn-ghost">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back</span>
                    </a>
                    <a href="<?php echo appUrl('logout.php'); ?>" class="btn btn-ghost">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </nav>
        
        <div class="settings-container">
            <div class="settings-header">
                <h1>Account Settings</h1>
                <p>Manage your account preferences and information</p>
            </div>
            
            <div class="settings-content">
                <!-- Profile Settings -->
                <div class="settings-section">
                    <h3>Profile Information</h3>
                    <form id="profileForm" class="settings-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name</label>
                                <input type="text" id="firstName" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name</label>
                                <input type="text" id="lastName" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
                
                <!-- Password Change -->
                <div class="settings-section">
                    <h3>Change Password</h3>
                    <form id="passwordForm" class="settings-form">
                        <div class="form-group">
                            <label for="currentPassword">Current Password</label>
                            <input type="password" id="currentPassword" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label for="newPassword">New Password</label>
                            <input type="password" id="newPassword" name="new_password" required minlength="8">
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword">Confirm New Password</label>
                            <input type="password" id="confirmPassword" name="confirm_password" required minlength="8">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </form>
                </div>
                
                <!-- Account Information -->
                <div class="settings-section">
                    <h3>Account Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Account Status</label>
                            <span class="status-badge status-<?php echo $user['access_level']; ?>">
                                <?php echo ucfirst($user['access_level']); ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <label>Email Verified</label>
                            <span class="status-badge <?php echo $user['email_verified'] ? 'status-verified' : 'status-unverified'; ?>">
                                <?php echo $user['email_verified'] ? 'Verified' : 'Not Verified'; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <label>Member Since</label>
                            <span><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                        </div>
                        <?php if ($user['trial_expiry']): ?>
                        <div class="info-item">
                            <label>Trial Expires</label>
                            <span><?php echo date('F j, Y g:i A', strtotime($user['trial_expiry'])); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Profile form submission
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {
                action: 'update_profile',
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                email: formData.get('email'),
                csrf_token: '<?php echo generateCSRFToken(); ?>'
            };
            
            fetch('<?php echo appUrl('api/user.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Profile updated successfully');
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to update profile'));
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        });

        // Password form submission
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const newPassword = formData.get('new_password');
            const confirmPassword = formData.get('confirm_password');
            
            if (newPassword !== confirmPassword) {
                alert('New passwords do not match');
                return;
            }
            
            const data = {
                action: 'change_password',
                current_password: formData.get('current_password'),
                new_password: newPassword,
                csrf_token: '<?php echo generateCSRFToken(); ?>'
            };
            
            fetch('<?php echo appUrl('api/user.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Password updated successfully');
                    this.reset();
                } else {
                    alert('Error: ' + (data.error || 'Failed to update password'));
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        });
    </script>
</body>
</html>
