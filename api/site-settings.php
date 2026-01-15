<?php
/**
 * Site Settings API
 * Handles saving and retrieving site customization settings
 */

require_once '../config/app.php';
require_once '../config/site-settings.php';
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

if ($requestMethod === 'POST') {
    $action = $input['action'] ?? '';
    $csrfToken = $input['csrf_token'] ?? '';
    
    // Verify CSRF token
    if (!verifyCSRFToken($csrfToken)) {
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }
    
    if ($action === 'save_settings') {
        saveSettings($db, $input, $user);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}

if ($requestMethod === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'get_settings') {
        getSettings($db);
    } elseif ($action === 'get_category') {
        $category = $_GET['category'] ?? '';
        getCategorySettings($db, $category);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}

/**
 * Save settings
 */
function saveSettings($db, $input, $user) {
    try {
        $category = $input['category'] ?? '';
        $updated = 0;
        
        foreach ($input as $key => $value) {
            // Skip non-setting fields
            if (in_array($key, ['action', 'csrf_token', 'category'])) {
                continue;
            }
            
            // Update setting
            if (updateSiteSetting($key, $value, $user['id'])) {
                $updated++;
            }
        }
        
        // Log the change
        logMessage("Site settings updated: $category category, $updated settings changed", 'info', [
            'user_id' => $user['id'],
            'category' => $category,
            'count' => $updated
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => "$updated settings updated successfully",
            'updated_count' => $updated
        ]);
        
    } catch (Exception $e) {
        error_log("Failed to save settings: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Failed to save settings: ' . $e->getMessage()]);
    }
}

/**
 * Get all settings
 */
function getSettings($db) {
    try {
        $settings = getAllSiteSettings();
        echo json_encode(['success' => true, 'settings' => $settings]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Get settings by category
 */
function getCategorySettings($db, $category) {
    try {
        $settings = getSiteSettingsByCategory($category);
        echo json_encode(['success' => true, 'settings' => $settings]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
