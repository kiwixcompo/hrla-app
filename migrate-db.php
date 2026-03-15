<?php
/**
 * Database Migration Script
 * Run once on the live server to create missing tables / add missing columns.
 * DELETE or restrict access to this file after running.
 */

// Basic protection - require a secret key in the URL
$secret = $_GET['key'] ?? '';
if ($secret !== 'hrla-migrate-2026') {
    http_response_code(403);
    die('Access denied. Use ?key=hrla-migrate-2026');
}

require_once 'config/app.php';

header('Content-Type: text/plain');

$db  = getDB();
$pdo = $db->getPdo();
$log = [];

function run($pdo, $sql, &$log) {
    try {
        $pdo->exec($sql);
        $log[] = "✅ OK: " . trim(substr($sql, 0, 80));
    } catch (PDOException $e) {
        $log[] = "⚠️  SKIP/ERR: " . $e->getMessage();
    }
}

// 1. Create password_resets table if missing
run($pdo, "
    CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        reset_token VARCHAR(255) UNIQUE NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        used_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_reset_token (reset_token),
        INDEX idx_expires_at (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", $log);

// 2. Add verification_code column to pending_verifications if missing
run($pdo, "ALTER TABLE pending_verifications ADD COLUMN IF NOT EXISTS verification_code VARCHAR(10) DEFAULT NULL", $log);
run($pdo, "ALTER TABLE pending_verifications ADD COLUMN IF NOT EXISTS verification_expires DATETIME DEFAULT NULL", $log);

// 3. Add index on verification_code if missing (ignore error if already exists)
run($pdo, "ALTER TABLE pending_verifications ADD INDEX idx_verification_code (verification_code)", $log);

echo implode("\n", $log) . "\n\nDone. Delete or restrict this file now.\n";
?>
