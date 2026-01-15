<?php
/**
 * Local Server Configuration Template
 * 
 * INSTRUCTIONS:
 * 1. Copy this file to config/local.php on your server
 * 2. Update all values with your actual credentials
 * 3. DO NOT commit config/local.php to git (it's in .gitignore)
 */

// Default admin password (used when creating admin user)
define('DEFAULT_ADMIN_PASSWORD', 'Password@123');

// SMTP Email Password
define('SMTP_PASSWORD_LOCAL', 'your-smtp-password-here');

// Database Configuration
// Replace these with your actual cPanel database credentials
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_NAME'] = 'hrledkhw_hrla_database';  // Format: cpanel_username_database_name
$_ENV['DB_USER'] = 'hrledkhw_hrla_user';      // Format: cpanel_username_db_user
$_ENV['DB_PASS'] = 'your-database-password-here';

// SMTP Configuration (optional - only if different from defaults)
// $_ENV['SMTP_HOST'] = 'mail.hrleaveassist.com';
// $_ENV['SMTP_PORT'] = 587;
// $_ENV['SMTP_USERNAME'] = 'askhrla@hrleaveassist.com';
// $_ENV['SMTP_PASSWORD'] = 'your-smtp-password';

// Enable error display for debugging (remove in production)
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
?>
