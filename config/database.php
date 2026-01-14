<?php
/**
 * Database Configuration and Connection Class
 * HR Leave Assistant - PHP/MySQL Version
 */

class Database {
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $pdo;
    private $error;

    public function __construct() {
        // Load configuration from environment or defaults
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->dbname = $_ENV['DB_NAME'] ?? 'hrla_database';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASS'] ?? '';
        
        $this->connect();
    }

    /**
     * Create database connection
     */
    private function connect() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please check your configuration.");
        }
    }

    /**
     * Get PDO instance
     */
    public function getPdo() {
        return $this->pdo;
    }

    /**
     * Execute a prepared statement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query failed: " . $e->getMessage() . " SQL: " . $sql);
            throw new Exception("Database query failed: " . $e->getMessage());
        }
    }

    /**
     * Get single row
     */
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Get all rows
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get row count
     */
    public function rowCount($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollback();
    }

    /**
     * Check if table exists
     */
    public function tableExists($tableName) {
        $sql = "SHOW TABLES LIKE ?";
        $stmt = $this->query($sql, [$tableName]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Create database tables if they don't exist
     */
    public function createTables() {
        $tables = [
            'users' => "
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) UNIQUE NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    first_name VARCHAR(100) NOT NULL,
                    last_name VARCHAR(100) NOT NULL,
                    is_admin TINYINT(1) DEFAULT 0,
                    email_verified TINYINT(1) DEFAULT 0,
                    verification_token VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    trial_started TIMESTAMP NULL,
                    trial_expiry TIMESTAMP NULL,
                    subscription_expiry TIMESTAMP NULL,
                    access_level ENUM('trial', 'extended', 'subscribed', 'expired', 'administrator') DEFAULT 'trial',
                    last_login TIMESTAMP NULL,
                    INDEX idx_email (email),
                    INDEX idx_email_verified (email_verified),
                    INDEX idx_access_level (access_level)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            
            'access_codes' => "
                CREATE TABLE IF NOT EXISTS access_codes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    code VARCHAR(50) UNIQUE NOT NULL,
                    description TEXT,
                    duration INT NOT NULL,
                    duration_type ENUM('days', 'months') NOT NULL,
                    max_uses INT DEFAULT NULL,
                    current_uses INT DEFAULT 0,
                    is_active TINYINT(1) DEFAULT 1,
                    created_by INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    expires_at TIMESTAMP NULL,
                    INDEX idx_code (code),
                    INDEX idx_is_active (is_active)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            
            'api_config' => "
                CREATE TABLE IF NOT EXISTS api_config (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    openai_key VARCHAR(255) NOT NULL,
                    total_requests INT DEFAULT 0,
                    openai_requests INT DEFAULT 0,
                    is_active TINYINT(1) DEFAULT 1,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    updated_by INT NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            
            'ai_instructions' => "
                CREATE TABLE IF NOT EXISTS ai_instructions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    tool_name ENUM('federal', 'california') NOT NULL UNIQUE,
                    custom_instructions TEXT,
                    is_active TINYINT(1) DEFAULT 1,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    updated_by INT NOT NULL,
                    INDEX idx_tool_name (tool_name)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            
            'user_sessions' => "
                CREATE TABLE IF NOT EXISTS user_sessions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    session_token VARCHAR(255) UNIQUE NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    expires_at TIMESTAMP NOT NULL,
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    INDEX idx_session_token (session_token),
                    INDEX idx_user_id (user_id),
                    INDEX idx_expires_at (expires_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            
            'conversations' => "
                CREATE TABLE IF NOT EXISTS conversations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    tool_name ENUM('federal', 'california') NOT NULL,
                    input_text TEXT NOT NULL,
                    response_text TEXT NOT NULL,
                    provider VARCHAR(50) DEFAULT 'openai',
                    tokens_used INT DEFAULT 0,
                    cost DECIMAL(10, 4) DEFAULT 0.0000,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user_id (user_id),
                    INDEX idx_tool_name (tool_name),
                    INDEX idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            
            'transactions' => "
                CREATE TABLE IF NOT EXISTS transactions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    amount DECIMAL(10, 2) NOT NULL,
                    currency VARCHAR(3) DEFAULT 'USD',
                    payment_method ENUM('stripe', 'paypal') NOT NULL,
                    stripe_payment_id VARCHAR(255),
                    paypal_order_id VARCHAR(255),
                    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
                    subscription_period INT DEFAULT 30,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user_id (user_id),
                    INDEX idx_status (status),
                    INDEX idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            
            'pending_verifications' => "
                CREATE TABLE IF NOT EXISTS pending_verifications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) NOT NULL,
                    first_name VARCHAR(100) NOT NULL,
                    last_name VARCHAR(100) NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    verification_token VARCHAR(255) UNIQUE NOT NULL,
                    access_code VARCHAR(50),
                    trial_expiry TIMESTAMP NULL,
                    access_level VARCHAR(50) DEFAULT 'trial',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    expires_at TIMESTAMP NOT NULL,
                    INDEX idx_email (email),
                    INDEX idx_verification_token (verification_token),
                    INDEX idx_expires_at (expires_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            
            'system_logs' => "
                CREATE TABLE IF NOT EXISTS system_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    log_type ENUM('error', 'warning', 'info', 'security') NOT NULL,
                    message TEXT NOT NULL,
                    user_id INT NULL,
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    additional_data JSON,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_log_type (log_type),
                    INDEX idx_created_at (created_at),
                    INDEX idx_user_id (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            "
        ];

        try {
            $this->beginTransaction();
            
            foreach ($tables as $tableName => $sql) {
                $this->query($sql);
                error_log("Created table: $tableName");
            }
            
            // Create default admin user first (before API config needs it)
            $this->createDefaultAdmin();
            
            // Then create default API config (needs admin user ID)
            $this->createDefaultApiConfig();
            
            $this->commit();
            return true;
            
        } catch (Exception $e) {
            $this->rollback();
            error_log("Failed to create tables: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create default admin user
     */
    private function createDefaultAdmin() {
        try {
            $adminExists = $this->fetch("SELECT id FROM users WHERE email = ?", ['talk2char@gmail.com']);
            
            if (!$adminExists) {
                // Use default password from local config if available
                $defaultPassword = defined('DEFAULT_ADMIN_PASSWORD') ? DEFAULT_ADMIN_PASSWORD : 'ChangeMe123!';
                $passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);
                $trialExpiry = date('Y-m-d H:i:s', strtotime('+100 years'));
                
                $sql = "INSERT INTO users (email, password_hash, first_name, last_name, is_admin, email_verified, trial_started, trial_expiry, access_level) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, 'administrator')";
                
                $this->query($sql, [
                    'talk2char@gmail.com',
                    $passwordHash,
                    'Super',
                    'Admin',
                    1,
                    1,
                    $trialExpiry
                ]);
                
                error_log("Created default admin user");
            }
        } catch (Exception $e) {
            error_log("Failed to create default admin: " . $e->getMessage());
            throw $e; // This is critical, so throw
        }
    }

    /**
     * Create default API config entry
     */
    private function createDefaultApiConfig() {
        try {
            $configExists = $this->fetch("SELECT id FROM api_config WHERE id = 1");
            
            if (!$configExists) {
                // Get admin user ID
                $admin = $this->fetch("SELECT id FROM users WHERE email = ?", ['talk2char@gmail.com']);
                $adminId = $admin['id'] ?? 1;
                
                $sql = "INSERT INTO api_config (id, openai_key, total_requests, openai_requests, is_active, updated_by) 
                        VALUES (1, '', 0, 0, 1, ?)";
                
                $this->query($sql, [$adminId]);
                
                error_log("Created default API config entry");
            }
        } catch (Exception $e) {
            error_log("Failed to create default API config: " . $e->getMessage());
            // Don't throw - this is not critical for initial setup
        }
    }

    /**
     * Get database statistics
     */
    public function getStats() {
        $stats = [];
        
        $tables = ['users', 'access_codes', 'conversations', 'transactions', 'pending_verifications'];
        
        foreach ($tables as $table) {
            $result = $this->fetch("SELECT COUNT(*) as count FROM $table");
            $stats[$table] = $result['count'] ?? 0;
        }
        
        return $stats;
    }
}

// Global database instance
$db = null;

/**
 * Get database instance
 */
function getDB() {
    global $db;
    if ($db === null) {
        $db = new Database();
    }
    return $db;
}

/**
 * Initialize database (create tables if needed)
 */
function initializeDatabase() {
    try {
        $db = getDB();
        $db->createTables();
        return true;
    } catch (Exception $e) {
        error_log("Database initialization failed: " . $e->getMessage());
        return false;
    }
}
?>