<?php
/**
 * Authentication System
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once __DIR__ . '/../config/app.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Register new user
     */
    public function register($email, $password, $firstName, $lastName, $accessCode = null) {
        try {
            // Validate input
            if (!$this->validateRegistrationInput($email, $password, $firstName, $lastName)) {
                return ['success' => false, 'error' => 'Invalid input data'];
            }
            
            // Check for duplicate email
            if ($this->emailExists($email)) {
                return ['success' => false, 'error' => 'This email address is already registered. Please use a different email or try logging in.'];
            }
            
            // Check pending verifications
            if ($this->hasPendingVerification($email)) {
                return ['success' => false, 'error' => 'This email address already has a pending verification. Please check your email or try resending the verification.'];
            }
            
            // Validate access code if provided
            $accessCodeData = null;
            if ($accessCode) {
                $accessCodeData = $this->validateAccessCode($accessCode);
                if (!$accessCodeData) {
                    return ['success' => false, 'error' => 'Invalid access code'];
                }
            }
            
            // Generate verification token
            $verificationToken = generateRandomString(64);
            
            // Calculate trial expiry
            $trialExpiry = date('Y-m-d H:i:s', time() + TRIAL_DURATION_SECONDS);
            $accessLevel = 'trial';
            
            // Apply access code benefits
            if ($accessCodeData) {
                $duration = $accessCodeData['duration'];
                $durationType = $accessCodeData['duration_type'];
                
                if ($durationType === 'months') {
                    $trialExpiry = date('Y-m-d H:i:s', strtotime("+{$duration} months"));
                } else {
                    $trialExpiry = date('Y-m-d H:i:s', strtotime("+{$duration} days"));
                }
                $accessLevel = 'extended';
                
                // Update access code usage
                $this->updateAccessCodeUsage($accessCodeData['id']);
            }
            
            // Hash password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Store in pending verifications
            $sql = "INSERT INTO pending_verifications (email, first_name, last_name, password_hash, verification_token, access_code, trial_expiry, access_level, expires_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $expiresAt = date('Y-m-d H:i:s', time() + (24 * 60 * 60)); // 24 hours
            
            $this->db->query($sql, [
                $email,
                $firstName,
                $lastName,
                $passwordHash,
                $verificationToken,
                $accessCode,
                $trialExpiry,
                $accessLevel,
                $expiresAt
            ]);
            
            // Send verification email
            $this->sendVerificationEmail($email, $firstName, $verificationToken, $accessCodeData);
            
            logMessage("User registration initiated", 'info', [
                'email' => $email,
                'access_code_used' => !!$accessCode,
                'trial_expiry' => $trialExpiry
            ]);
            
            return [
                'success' => true,
                'message' => 'Registration successful. Please check your email for verification.',
                'verification_token' => $verificationToken
            ];
            
        } catch (Exception $e) {
            logMessage("Registration failed: " . $e->getMessage(), 'error', ['email' => $email]);
            return ['success' => false, 'error' => 'Registration failed. Please try again.'];
        }
    }
    
    /**
     * Request password reset
     */
    public function requestPasswordReset($email) {
        try {
            // Check if user exists and is verified
            $user = $this->getUserByEmail($email);
            
            if (!$user) {
                // Don't reveal if email exists or not for security
                return ['success' => true, 'message' => 'If this email is registered, you will receive a password reset link.'];
            }
            
            if (!$user['email_verified']) {
                return ['success' => false, 'error' => 'Please verify your email address first before resetting your password.'];
            }
            
            // Check for existing reset requests (rate limiting)
            $existingReset = $this->db->fetch(
                "SELECT * FROM password_resets WHERE email = ? AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1",
                [$email]
            );
            
            if ($existingReset) {
                $timeLeft = strtotime($existingReset['expires_at']) - time();
                if ($timeLeft > 3300) { // More than 55 minutes left (allow new request in last 5 minutes)
                    return ['success' => false, 'error' => 'A password reset email was already sent. Please check your email or wait before requesting another.'];
                }
            }
            
            // Generate reset token
            $resetToken = generateRandomString(64);
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour
            
            // Store reset request
            $sql = "INSERT INTO password_resets (email, reset_token, expires_at) VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE reset_token = VALUES(reset_token), expires_at = VALUES(expires_at), created_at = NOW()";
            
            $this->db->query($sql, [$email, $resetToken, $expiresAt]);
            
            // Send reset email
            $this->sendPasswordResetEmail($email, $user['first_name'], $resetToken);
            
            logMessage("Password reset requested", 'info', [
                'email' => $email,
                'user_id' => $user['id']
            ]);
            
            return [
                'success' => true,
                'message' => 'Password reset instructions have been sent to your email address.'
            ];
            
        } catch (Exception $e) {
            logMessage("Password reset request failed: " . $e->getMessage(), 'error', ['email' => $email]);
            return ['success' => false, 'error' => 'Unable to process password reset request. Please try again.'];
        }
    }
    
    /**
     * Reset password with token
     */
    public function resetPassword($token, $newPassword) {
        try {
            // Validate new password
            if (strlen($newPassword) < 8) {
                return ['success' => false, 'error' => 'Password must be at least 8 characters long'];
            }
            
            // Get reset request
            $sql = "SELECT * FROM password_resets WHERE reset_token = ? AND expires_at > NOW() AND used_at IS NULL";
            $resetRequest = $this->db->fetch($sql, [$token]);
            
            if (!$resetRequest) {
                return ['success' => false, 'error' => 'Invalid or expired password reset token'];
            }
            
            // Get user
            $user = $this->getUserByEmail($resetRequest['email']);
            if (!$user) {
                return ['success' => false, 'error' => 'User account not found'];
            }
            
            $this->db->beginTransaction();
            
            // Update password
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->db->query("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?", [$passwordHash, $user['id']]);
            
            // Mark reset token as used
            $this->db->query("UPDATE password_resets SET used_at = NOW() WHERE id = ?", [$resetRequest['id']]);
            
            // Invalidate all existing sessions for security
            $this->db->query("DELETE FROM user_sessions WHERE user_id = ?", [$user['id']]);
            
            $this->db->commit();
            
            logMessage("Password reset completed", 'info', [
                'email' => $resetRequest['email'],
                'user_id' => $user['id']
            ]);
            
            return [
                'success' => true,
                'message' => 'Password reset successfully. You can now login with your new password.',
                'email' => $resetRequest['email']
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            logMessage("Password reset failed: " . $e->getMessage(), 'error', ['token' => $token]);
            return ['success' => false, 'error' => 'Password reset failed. Please try again.'];
        }
    }
    
    /**
     * Send password reset email
     */
    private function sendPasswordResetEmail($email, $firstName, $resetToken) {
        try {
            require_once __DIR__ . '/email_templates.php';
            $emailTemplates = new EmailTemplates();
            
            $resetLink = appUrl("reset-password.php?token=" . urlencode($resetToken));
            
            return $emailTemplates->sendPasswordResetEmail($email, $firstName, $resetLink);
            
        } catch (Exception $e) {
            logMessage("Failed to send password reset email: " . $e->getMessage(), 'error', [
                'email' => $email,
                'token' => $resetToken
            ]);
            return false;
        }
    }
    public function verifyEmail($token) {
        try {
            // Get pending verification
            $sql = "SELECT * FROM pending_verifications WHERE verification_token = ? AND expires_at > NOW()";
            $pending = $this->db->fetch($sql, [$token]);
            
            if (!$pending) {
                return ['success' => false, 'error' => 'Invalid or expired verification token'];
            }
            
            // Create verified user
            $this->db->beginTransaction();
            
            $userSql = "INSERT INTO users (email, password_hash, first_name, last_name, email_verified, trial_started, trial_expiry, access_level) 
                        VALUES (?, ?, ?, ?, 1, NOW(), ?, ?)";
            
            $this->db->query($userSql, [
                $pending['email'],
                $pending['password_hash'],
                $pending['first_name'],
                $pending['last_name'],
                $pending['trial_expiry'],
                $pending['access_level']
            ]);
            
            // Remove from pending
            $this->db->query("DELETE FROM pending_verifications WHERE id = ?", [$pending['id']]);
            
            $this->db->commit();
            
            logMessage("Email verified successfully", 'info', [
                'email' => $pending['email'],
                'trial_expiry' => $pending['trial_expiry']
            ]);
            
            return [
                'success' => true,
                'message' => 'Email verified successfully. You can now login.',
                'email' => $pending['email']
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            logMessage("Email verification failed: " . $e->getMessage(), 'error', ['token' => $token]);
            return ['success' => false, 'error' => 'Verification failed. Please try again.'];
        }
    }
    
    /**
     * Login user
     */
    public function login($email, $password, $rememberMe = false) {
        try {
            // Check rate limiting
            if ($this->isLoginRateLimited($email)) {
                return ['success' => false, 'error' => 'Too many login attempts. Please try again later.'];
            }
            
            // Get user
            $user = $this->getUserByEmail($email);
            
            if (!$user || !password_verify($password, $user['password_hash'])) {
                $this->recordFailedLogin($email);
                return ['success' => false, 'error' => 'Invalid email or password'];
            }
            
            if (!$user['email_verified']) {
                return ['success' => false, 'error' => 'Please verify your email before logging in'];
            }
            
            // Create session
            $sessionToken = $this->createSession($user['id'], $rememberMe);
            
            // Update last login
            $this->db->query("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
            
            // Clear failed login attempts
            $this->clearFailedLogins($email);
            
            logMessage("User logged in successfully", 'info', [
                'user_id' => $user['id'],
                'email' => $email,
                'remember_me' => $rememberMe
            ]);
            
            return [
                'success' => true,
                'user' => $this->formatUserData($user),
                'session_token' => $sessionToken
            ];
            
        } catch (Exception $e) {
            logMessage("Login failed: " . $e->getMessage(), 'error', ['email' => $email]);
            return ['success' => false, 'error' => 'Login failed. Please try again.'];
        }
    }
    
    /**
     * Logout user
     */
    public function logout($sessionToken = null) {
        try {
            if (!$sessionToken) {
                $sessionToken = $_SESSION['session_token'] ?? null;
            }
            
            if ($sessionToken) {
                // Remove session from database
                $this->db->query("DELETE FROM user_sessions WHERE session_token = ?", [$sessionToken]);
                
                logMessage("User logged out", 'info', ['session_token' => substr($sessionToken, 0, 8) . '...']);
            }
            
            // Clear PHP session
            session_destroy();
            
            return ['success' => true, 'message' => 'Logged out successfully'];
            
        } catch (Exception $e) {
            logMessage("Logout failed: " . $e->getMessage(), 'error');
            return ['success' => false, 'error' => 'Logout failed'];
        }
    }
    
    /**
     * Get current authenticated user
     */
    public function getCurrentUser() {
        $sessionToken = $_SESSION['session_token'] ?? null;
        
        if (!$sessionToken) {
            return null;
        }
        
        return $this->getUserBySessionToken($sessionToken);
    }
    
    /**
     * Check if user is authenticated
     */
    public function isAuthenticated() {
        return $this->getCurrentUser() !== null;
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin() {
        $user = $this->getCurrentUser();
        return $user && $user['is_admin'];
    }
    
    /**
     * Check if user has access (trial or subscription)
     */
    public function hasAccess() {
        $user = $this->getCurrentUser();
        
        if (!$user) return false;
        if ($user['is_admin']) return true;
        
        $now = time();
        
        // Check subscription
        if ($user['subscription_expiry'] && strtotime($user['subscription_expiry']) > $now) {
            return true;
        }
        
        // Check trial
        if ($user['trial_expiry'] && strtotime($user['trial_expiry']) > $now) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Require authentication
     */
    public function requireAuth() {
        if (!$this->isAuthenticated()) {
            if (isAjax()) {
                http_response_code(401);
                echo json_encode(['error' => 'Authentication required']);
                exit;
            } else {
                redirect(appUrl('login.php'));
            }
        }
    }
    
    /**
     * Require admin access
     */
    public function requireAdmin() {
        $this->requireAuth();
        
        if (!$this->isAdmin()) {
            if (isAjax()) {
                http_response_code(403);
                echo json_encode(['error' => 'Admin access required']);
                exit;
            } else {
                redirect(appUrl('dashboard.php'));
            }
        }
    }
    
    /**
     * Require active access (trial or subscription)
     */
    public function requireAccess() {
        $this->requireAuth();
        
        if (!$this->hasAccess()) {
            if (isAjax()) {
                http_response_code(403);
                echo json_encode(['error' => 'Active subscription required']);
                exit;
            } else {
                redirect(appUrl('subscription.php'));
            }
        }
    }
    
    // Private helper methods
    
    private function validateRegistrationInput($email, $password, $firstName, $lastName) {
        return isValidEmail($email) &&
               strlen($password) >= PASSWORD_MIN_LENGTH &&
               strlen(trim($firstName)) >= 2 &&
               strlen(trim($lastName)) >= 2;
    }
    
    private function emailExists($email) {
        $sql = "SELECT id FROM users WHERE email = ?";
        return $this->db->fetch($sql, [$email]) !== false;
    }
    
    private function hasPendingVerification($email) {
        $sql = "SELECT id FROM pending_verifications WHERE email = ? AND expires_at > NOW()";
        return $this->db->fetch($sql, [$email]) !== false;
    }
    
    private function validateAccessCode($code) {
        $sql = "SELECT * FROM access_codes WHERE code = ? AND is_active = 1 AND (max_uses IS NULL OR current_uses < max_uses)";
        return $this->db->fetch($sql, [$code]);
    }
    
    private function updateAccessCodeUsage($codeId) {
        $sql = "UPDATE access_codes SET current_uses = current_uses + 1 WHERE id = ?";
        $this->db->query($sql, [$codeId]);
    }
    
    private function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        return $this->db->fetch($sql, [$email]);
    }
    
    private function getUserBySessionToken($sessionToken) {
        $sql = "SELECT u.* FROM users u 
                JOIN user_sessions s ON u.id = s.user_id 
                WHERE s.session_token = ? AND s.expires_at > NOW()";
        return $this->db->fetch($sql, [$sessionToken]);
    }
    
    private function createSession($userId, $rememberMe = false) {
        $sessionToken = generateRandomString(64);
        $expiresAt = date('Y-m-d H:i:s', time() + ($rememberMe ? 30 * 24 * 60 * 60 : SESSION_LIFETIME));
        
        $sql = "INSERT INTO user_sessions (user_id, session_token, expires_at, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $userId,
            $sessionToken,
            $expiresAt,
            getClientIP(),
            getUserAgent()
        ]);
        
        // Store in PHP session
        $_SESSION['session_token'] = $sessionToken;
        $_SESSION['user_id'] = $userId;
        
        return $sessionToken;
    }
    
    private function formatUserData($user) {
        return [
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'is_admin' => (bool)$user['is_admin'],
            'email_verified' => (bool)$user['email_verified'],
            'trial_expiry' => $user['trial_expiry'],
            'subscription_expiry' => $user['subscription_expiry'],
            'access_level' => $user['access_level'],
            'created_at' => $user['created_at'],
            'last_login' => $user['last_login']
        ];
    }
    
    private function isLoginRateLimited($email) {
        // Simple rate limiting - can be enhanced with Redis/Memcached
        $key = 'login_attempts_' . md5($email);
        $attempts = $_SESSION[$key] ?? 0;
        $lastAttempt = $_SESSION[$key . '_time'] ?? 0;
        
        // Reset if lockout time has passed
        if (time() - $lastAttempt > LOGIN_LOCKOUT_TIME) {
            unset($_SESSION[$key], $_SESSION[$key . '_time']);
            return false;
        }
        
        return $attempts >= MAX_LOGIN_ATTEMPTS;
    }
    
    private function recordFailedLogin($email) {
        $key = 'login_attempts_' . md5($email);
        $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
        $_SESSION[$key . '_time'] = time();
        
        logMessage("Failed login attempt", 'warning', [
            'email' => $email,
            'attempts' => $_SESSION[$key],
            'ip' => getClientIP()
        ]);
    }
    
    private function clearFailedLogins($email) {
        $key = 'login_attempts_' . md5($email);
        unset($_SESSION[$key], $_SESSION[$key . '_time']);
    }
    
    private function sendVerificationEmail($email, $firstName, $token, $accessCodeData = null) {
        require_once INCLUDES_PATH . '/email_templates.php';
        
        $verificationLink = appUrl("verify.php?token=$token");
        
        $emailTemplate = new EmailTemplates();
        $emailTemplate->sendVerificationEmail($email, $firstName, $verificationLink, $accessCodeData);
    }
}

// Global auth instance
$auth = null;

/**
 * Get auth instance
 */
function getAuth() {
    global $auth;
    if ($auth === null) {
        $auth = new Auth();
    }
    return $auth;
}

/**
 * Helper functions for templates
 */
function currentUser() {
    return getAuth()->getCurrentUser();
}

function isLoggedIn() {
    return getAuth()->isAuthenticated();
}

function isAdmin() {
    return getAuth()->isAdmin();
}

function hasAccess() {
    return getAuth()->hasAccess();
}

function requireAuth() {
    return getAuth()->requireAuth();
}

function requireAdmin() {
    return getAuth()->requireAdmin();
}

function requireAccess() {
    return getAuth()->requireAccess();
}
?>