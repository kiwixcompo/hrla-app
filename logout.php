<?php
/**
 * Logout Handler
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'includes/auth.php';

$auth = getAuth();

// Perform logout
$result = $auth->logout();

// Clear any flash messages
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// Redirect to login page
redirect(appUrl('login.php'));
?>