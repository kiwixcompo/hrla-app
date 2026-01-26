<?php
/**
 * Authentication API Endpoints
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once '../config/app.php';
require_once '../includes/auth.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Handle CORS if needed
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exit(0);
}

try {
    $auth = getAuth();
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'forgot_password':
            handleForgotPassword($auth);
            break;
            
        case 'reset_password':
            handleResetPassword($auth);
            break;
            
        case 'verify_reset_token':
            handleVerifyResetToken($auth);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    logMessage("Auth API error: " . $e->getMessage(), 'error', [
        'action' => $action ?? 'unknown',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Request failed. Please try again.'
    ]);
}

/**
 * Handle forgot password request
 */
function handleForgotPassword($auth) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }
    
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid CSRF token');
    }
    
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        echo json_encode([
            'success' => false,
            'error' => 'Email address is required'
        ]);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'error' => 'Please enter a valid email address'
        ]);
        return;
    }
    
    $result = $auth->requestPasswordReset($email);
    echo json_encode($result);
}

/**
 * Handle password reset
 */
function handleResetPassword($auth) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }
    
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid CSRF token');
    }
    
    $token = $_POST['token'] ?? '';
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($token)) {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid reset token'
        ]);
        return;
    }
    
    if (empty($newPassword)) {
        echo json_encode([
            'success' => false,
            'error' => 'Password is required'
        ]);
        return;
    }
    
    if (strlen($newPassword) < 8) {
        echo json_encode([
            'success' => false,
            'error' => 'Password must be at least 8 characters long'
        ]);
        return;
    }
    
    if ($newPassword !== $confirmPassword) {
        echo json_encode([
            'success' => false,
            'error' => 'Passwords do not match'
        ]);
        return;
    }
    
    $result = $auth->resetPassword($token, $newPassword);
    echo json_encode($result);
}

/**
 * Verify reset token validity
 */
function handleVerifyResetToken($auth) {
    $token = $_GET['token'] ?? '';
    
    if (empty($token)) {
        echo json_encode([
            'success' => false,
            'error' => 'Token is required'
        ]);
        return;
    }
    
    try {
        $db = getDB();
        $sql = "SELECT email, expires_at FROM password_resets WHERE reset_token = ? AND expires_at > NOW() AND used_at IS NULL";
        $resetRequest = $db->fetch($sql, [$token]);
        
        if ($resetRequest) {
            echo json_encode([
                'success' => true,
                'valid' => true,
                'email' => $resetRequest['email'],
                'expires_at' => $resetRequest['expires_at']
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'valid' => false,
                'error' => 'Invalid or expired reset token'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Unable to verify token'
        ]);
    }
}
?>