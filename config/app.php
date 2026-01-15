<?php
/**
 * Application Configuration
 * HR Leave Assistant - PHP/MySQL Version
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (disable in production)
$isProduction = ($_SERVER['HTTP_HOST'] ?? '') !== 'localhost';

if ($isProduction) {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Application constants
define('APP_NAME', 'HR Leave Assistant');
define('APP_VERSION', '2.0.0');
define('APP_URL', $isProduction ? 'https://www.hrleaveassist.com' : 'http://localhost/leave_assistant');
define('APP_EMAIL', 'askhrla@hrleaveassist.com');
define('APP_SUPPORT_EMAIL', 'askhrla@hrleaveassist.com');

// Security settings
define('SESSION_LIFETIME', 24 * 60 * 60); // 24 hours
define('CSRF_TOKEN_LIFETIME', 60 * 60); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 15 * 60); // 15 minutes

// Trial settings
define('TRIAL_DURATION_HOURS', 24);
define('TRIAL_DURATION_SECONDS', TRIAL_DURATION_HOURS * 60 * 60);

// Subscription settings
define('MONTHLY_FEE', 29.00);
define('ANNUAL_FEE', 290.00); // 10 months price
define('ORGANIZATION_FEE', 99.00);

// API settings
define('OPENAI_MODEL', 'gpt-4o-mini');
define('OPENAI_MAX_TOKENS', 1000);
define('OPENAI_TEMPERATURE', 0.3);

// Email settings
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'mail.hrleaveassist.com');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 587);
define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? 'askhrla@hrleaveassist.com');

// Load local config if it exists (not in git)
if (file_exists(__DIR__ . '/local.php')) {
    require_once __DIR__ . '/local.php';
}

define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? (defined('SMTP_PASSWORD_LOCAL') ? SMTP_PASSWORD_LOCAL : ''));
define('SMTP_ENCRYPTION', 'tls');

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Rate limiting
define('API_RATE_LIMIT', 60); // requests per minute
define('EMAIL_RATE_LIMIT', 5); // emails per hour per user

// Pagination
define('USERS_PER_PAGE', 25);
define('CONVERSATIONS_PER_PAGE', 50);
define('LOGS_PER_PAGE', 100);

// Application paths
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('TEMPLATES_PATH', ROOT_PATH . '/templates');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('LOGS_PATH', ROOT_PATH . '/logs');

// Create logs directory if it doesn't exist
if (!is_dir(LOGS_PATH)) {
    mkdir(LOGS_PATH, 0755, true);
}

// Timezone
date_default_timezone_set('UTC');

/**
 * Application configuration array
 */
$config = [
    'app' => [
        'name' => APP_NAME,
        'version' => APP_VERSION,
        'url' => APP_URL,
        'email' => APP_EMAIL,
        'support_email' => APP_SUPPORT_EMAIL,
        'is_production' => $isProduction
    ],
    
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_NAME'] ?? 'hrla_database',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'charset' => 'utf8mb4'
    ],
    
    'email' => [
        'host' => SMTP_HOST,
        'port' => SMTP_PORT,
        'username' => SMTP_USERNAME,
        'password' => SMTP_PASSWORD,
        'encryption' => SMTP_ENCRYPTION,
        'from_name' => APP_NAME,
        'from_email' => APP_EMAIL
    ],
    
    'security' => [
        'session_lifetime' => SESSION_LIFETIME,
        'csrf_token_lifetime' => CSRF_TOKEN_LIFETIME,
        'password_min_length' => PASSWORD_MIN_LENGTH,
        'max_login_attempts' => MAX_LOGIN_ATTEMPTS,
        'login_lockout_time' => LOGIN_LOCKOUT_TIME
    ],
    
    'subscription' => [
        'trial_duration' => TRIAL_DURATION_SECONDS,
        'monthly_fee' => MONTHLY_FEE,
        'annual_fee' => ANNUAL_FEE,
        'organization_fee' => ORGANIZATION_FEE
    ],
    
    'api' => [
        'openai_model' => OPENAI_MODEL,
        'openai_max_tokens' => OPENAI_MAX_TOKENS,
        'openai_temperature' => OPENAI_TEMPERATURE,
        'rate_limit' => API_RATE_LIMIT
    ],
    
    'pagination' => [
        'users_per_page' => USERS_PER_PAGE,
        'conversations_per_page' => CONVERSATIONS_PER_PAGE,
        'logs_per_page' => LOGS_PER_PAGE
    ]
];

/**
 * Get configuration value
 */
function config($key, $default = null) {
    global $config;
    
    $keys = explode('.', $key);
    $value = $config;
    
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }
    
    return $value;
}

/**
 * Get application URL
 */
function appUrl($path = '') {
    $url = rtrim(APP_URL, '/');
    if ($path) {
        $url .= '/' . ltrim($path, '/');
    }
    return $url;
}

/**
 * Get asset URL
 */
function asset($path) {
    return appUrl('assets/' . ltrim($path, '/'));
}

/**
 * Redirect to URL
 */
function redirect($url, $statusCode = 302) {
    if (!headers_sent()) {
        header("Location: $url", true, $statusCode);
        exit;
    }
}

/**
 * Redirect back with message
 */
function redirectBack($message = '', $type = 'info') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    
    $referer = $_SERVER['HTTP_REFERER'] ?? appUrl();
    redirect($referer);
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    $message = $_SESSION['flash_message'] ?? '';
    $type = $_SESSION['flash_type'] ?? 'info';
    
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    
    return $message ? ['message' => $message, 'type' => $type] : null;
}

/**
 * Set flash message
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Check if request is AJAX
 */
function isAjax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = explode(',', $ip)[0];
            }
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Get user agent
 */
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}

/**
 * Log message to file
 */
function logMessage($message, $level = 'info', $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $ip = getClientIP();
    $userAgent = getUserAgent();
    
    $logEntry = [
        'timestamp' => $timestamp,
        'level' => $level,
        'message' => $message,
        'ip' => $ip,
        'user_agent' => $userAgent,
        'context' => $context
    ];
    
    $logLine = json_encode($logEntry) . PHP_EOL;
    
    $logFile = LOGS_PATH . '/' . date('Y-m-d') . '.log';
    file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    
    // Also log to PHP error log for critical errors
    if (in_array($level, ['error', 'critical', 'emergency'])) {
        error_log("[$level] $message");
    }
}

/**
 * Generate random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token']) || 
        !isset($_SESSION['csrf_token_time']) || 
        (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_LIFETIME) {
        
        $_SESSION['csrf_token'] = generateRandomString(32);
        $_SESSION['csrf_token_time'] = time();
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && 
           isset($_SESSION['csrf_token_time']) &&
           (time() - $_SESSION['csrf_token_time']) <= CSRF_TOKEN_LIFETIME &&
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = 'USD') {
    return '$' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'M j, Y g:i A') {
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    return $date->format($format);
}

/**
 * Time ago format
 */
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    
    return floor($time/31536000) . ' years ago';
}

// Load database configuration
require_once CONFIG_PATH . '/database.php';

// Auto-initialize database if tables don't exist
try {
    $db = getDB();
    // Check if users table exists - if not, initialize
    if (!$db->tableExists('users')) {
        error_log("Database tables not found - auto-initializing...");
        initializeDatabase();
    }
} catch (Exception $e) {
    error_log("Database check failed: " . $e->getMessage());
    // Don't show error to user, just log it
}
?>