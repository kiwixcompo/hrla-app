<?php
/**
 * Admin API Endpoints
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once '../config/app.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

$auth = getAuth();

// Check if user is authenticated and is admin
if (!$auth->isAuthenticated() || !$auth->getCurrentUser()['is_admin']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$db = getDB();
$user = $auth->getCurrentUser();

// Get request data
$requestMethod = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Handle GET requests
if ($requestMethod === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_user':
            getUser($db, $_GET);
            break;
            
        case 'export_users':
            exportUsers($db);
            break;
            
        case 'export_data':
            exportData($db);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}

// Handle POST requests
if ($requestMethod === 'POST') {
    // Check for form data or JSON
    $action = $input['action'] ?? $_POST['action'] ?? '';
    $csrfToken = $input['csrf_token'] ?? $_POST['csrf_token'] ?? '';
    
    // Verify CSRF token
    if (!verifyCSRFToken($csrfToken)) {
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }
    
    switch ($action) {
        case 'save_ai_instructions':
            saveAIInstructions($db, $input, $user);
            break;
            
        case 'update_user':
            updateUser($db, $input);
            break;
            
        case 'delete_user':
            deleteUser($db, $input);
            break;
            
        case 'generate_access_code':
            generateAccessCode($db, $user);
            break;
            
        case 'delete_access_code':
            deleteAccessCode($db, $input);
            break;
            
        case 'save_api_key':
            saveApiKey($db, $user);
            break;
            
        case 'test_api_key':
            testApiKey($db);
            break;
            
        case 'optimize_database':
            optimizeDatabase($db);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}

/**
 * Generate Access Code
 */
function generateAccessCode($db, $user) {
    $codeLength = (int)($_POST['code_length'] ?? 8);
    $duration = (int)($_POST['duration'] ?? 30);
    $durationType = $_POST['duration_type'] ?? 'months';
    $description = $_POST['description'] ?? '';
    
    // Generate random code
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < $codeLength; $i++) {
        $code .= $characters[random_int(0, strlen($characters) - 1)];
    }
    
    // Calculate expiry
    $expiresAt = null;
    if ($durationType === 'days') {
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$duration} days"));
    } else {
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$duration} months"));
    }
    
    try {
        $sql = "INSERT INTO access_codes (code, description, duration, duration_type, created_by, expires_at) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $db->query($sql, [$code, $description, $duration, $durationType, $user['id'], $expiresAt]);
        
        // Redirect back to admin page
        header('Location: ' . appUrl('admin/index.php?tab=access-codes&success=code_generated'));
        exit;
    } catch (Exception $e) {
        header('Location: ' . appUrl('admin/index.php?tab=access-codes&error=failed_to_generate'));
        exit;
    }
}

/**
 * Delete Access Code
 */
function deleteAccessCode($db, $input) {
    $id = (int)($input['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid access code ID']);
        return;
    }
    
    try {
        $db->query("DELETE FROM access_codes WHERE id = ?", [$id]);
        echo json_encode(['success' => true, 'message' => 'Access code deleted successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Save API Key
 */
function saveApiKey($db, $user) {
    $apiKey = $_POST['openai_key'] ?? '';
    
    if (empty($apiKey) || $apiKey === '••••••••••••••••') {
        header('Location: ' . appUrl('admin/index.php?tab=api-settings&error=invalid_key'));
        exit;
    }
    
    try {
        // Check if API config exists
        $existing = $db->fetch("SELECT id FROM api_config WHERE is_active = 1 LIMIT 1");
        
        if ($existing) {
            // Update existing
            $db->query("UPDATE api_config SET openai_key = ?, updated_by = ?, updated_at = NOW() WHERE id = ?", 
                [$apiKey, $user['id'], $existing['id']]);
        } else {
            // Insert new
            $db->query("INSERT INTO api_config (openai_key, updated_by) VALUES (?, ?)", 
                [$apiKey, $user['id']]);
        }
        
        header('Location: ' . appUrl('admin/index.php?tab=api-settings&success=key_saved'));
        exit;
    } catch (Exception $e) {
        header('Location: ' . appUrl('admin/index.php?tab=api-settings&error=failed_to_save'));
        exit;
    }
}

/**
 * Test API Key
 */
function testApiKey($db) {
    $apiConfig = $db->fetch("SELECT openai_key FROM api_config WHERE is_active = 1 LIMIT 1");
    
    if (!$apiConfig) {
        echo json_encode(['success' => false, 'error' => 'No API key configured']);
        return;
    }
    
    $apiKey = $apiConfig['openai_key'];
    
    // SSL options - disable verification for local development only
    $isProduction = config('app.is_production');
    $sslVerify = $isProduction;
    
    // Test the API key with a simple request
    $ch = curl_init('https://api.openai.com/v1/models');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $sslVerify);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $sslVerify ? 2 : 0);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo json_encode(['success' => false, 'error' => 'Connection error: ' . $error]);
        return;
    }
    
    if ($httpCode === 200) {
        echo json_encode(['success' => true, 'message' => 'API key is valid']);
    } else {
        $errorData = json_decode($response, true);
        $errorMsg = $errorData['error']['message'] ?? 'API key is invalid or expired';
        echo json_encode(['success' => false, 'error' => $errorMsg]);
    }
}

/**
 * Optimize Database
 */
function optimizeDatabase($db) {
    try {
        $tables = ['users', 'access_codes', 'api_config', 'user_sessions', 'conversations', 'transactions', 'pending_verifications', 'system_logs'];
        
        foreach ($tables as $table) {
            $db->query("OPTIMIZE TABLE $table");
        }
        
        echo json_encode(['success' => true, 'message' => 'Database optimized successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Export Users
 */
function exportUsers($db) {
    try {
        $users = $db->fetchAll("SELECT id, email, first_name, last_name, is_admin, email_verified, access_level, created_at, last_login FROM users ORDER BY created_at DESC");
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, ['ID', 'Email', 'First Name', 'Last Name', 'Admin', 'Verified', 'Access Level', 'Created', 'Last Login']);
        
        // Add data
        foreach ($users as $user) {
            fputcsv($output, [
                $user['id'],
                $user['email'],
                $user['first_name'],
                $user['last_name'],
                $user['is_admin'] ? 'Yes' : 'No',
                $user['email_verified'] ? 'Yes' : 'No',
                $user['access_level'],
                $user['created_at'],
                $user['last_login'] ?? 'Never'
            ]);
        }
        
        fclose($output);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Export All Data
 */
function exportData($db) {
    try {
        $data = [
            'users' => $db->fetchAll("SELECT * FROM users"),
            'access_codes' => $db->fetchAll("SELECT * FROM access_codes"),
            'conversations' => $db->fetchAll("SELECT * FROM conversations"),
            'transactions' => $db->fetchAll("SELECT * FROM transactions"),
            'export_date' => date('Y-m-d H:i:s')
        ];
        
        // Set headers for JSON download
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="database_export_' . date('Y-m-d') . '.json"');
        
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Save AI Instructions
 */
function saveAIInstructions($db, $input, $user) {
    try {
        $toolName = $input['tool_name'] ?? '';
        $customInstructions = trim($input['custom_instructions'] ?? '');
        $isActive = (int)($input['is_active'] ?? 1);
        
        if (!in_array($toolName, ['federal', 'california'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid tool name']);
            return;
        }
        
        // Check if instructions exist
        $existing = $db->fetch("SELECT id FROM ai_instructions WHERE tool_name = ?", [$toolName]);
        
        if ($existing) {
            // Update existing
            $sql = "UPDATE ai_instructions 
                    SET custom_instructions = ?, 
                        is_active = ?, 
                        updated_by = ?,
                        updated_at = NOW()
                    WHERE tool_name = ?";
            
            $db->query($sql, [$customInstructions, $isActive, $user['id'], $toolName]);
        } else {
            // Insert new
            $sql = "INSERT INTO ai_instructions (tool_name, custom_instructions, is_active, updated_by) 
                    VALUES (?, ?, ?, ?)";
            
            $db->query($sql, [$toolName, $customInstructions, $isActive, $user['id']]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Instructions saved successfully']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Get User
 */
function getUser($db, $params) {
    try {
        $userId = (int)($params['id'] ?? 0);
        
        if ($userId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
            return;
        }
        
        $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'User not found']);
            return;
        }
        
        // Remove sensitive data
        unset($user['password_hash']);
        unset($user['verification_token']);
        
        echo json_encode(['success' => true, 'user' => $user]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Update User
 */
function updateUser($db, $input) {
    try {
        $userId = (int)($input['user_id'] ?? 0);
        $firstName = trim($input['first_name'] ?? '');
        $lastName = trim($input['last_name'] ?? '');
        $email = trim($input['email'] ?? '');
        $accessLevel = $input['access_level'] ?? 'trial';
        $emailVerified = (int)($input['email_verified'] ?? 0);
        
        if ($userId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
            return;
        }
        
        if (empty($firstName) || empty($lastName) || empty($email)) {
            echo json_encode(['success' => false, 'error' => 'All fields are required']);
            return;
        }
        
        if (!isValidEmail($email)) {
            echo json_encode(['success' => false, 'error' => 'Invalid email address']);
            return;
        }
        
        // Check if email is already taken by another user
        $existingUser = $db->fetch("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $userId]);
        if ($existingUser) {
            echo json_encode(['success' => false, 'error' => 'Email already in use']);
            return;
        }
        
        $sql = "UPDATE users SET 
                first_name = ?, 
                last_name = ?, 
                email = ?, 
                access_level = ?, 
                email_verified = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $db->query($sql, [$firstName, $lastName, $email, $accessLevel, $emailVerified, $userId]);
        
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Delete User
 */
function deleteUser($db, $input) {
    try {
        $userId = (int)($input['user_id'] ?? 0);
        
        if ($userId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
            return;
        }
        
        // Check if user is admin
        $user = $db->fetch("SELECT is_admin FROM users WHERE id = ?", [$userId]);
        if ($user && $user['is_admin']) {
            echo json_encode(['success' => false, 'error' => 'Cannot delete admin user']);
            return;
        }
        
        // Delete user
        $db->query("DELETE FROM users WHERE id = ?", [$userId]);
        
        // Also delete related data
        $db->query("DELETE FROM user_sessions WHERE user_id = ?", [$userId]);
        $db->query("DELETE FROM conversations WHERE user_id = ?", [$userId]);
        
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
