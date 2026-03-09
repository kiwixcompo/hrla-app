<?php
/**
 * Simple Deployment Script
 * Upload this file to your public_html folder and run it
 */

// Simple password protection
$password = 'deploy2026'; // Change this!
$inputPassword = $_GET['pass'] ?? '';

if ($inputPassword !== $password) {
    die('Access Denied. Usage: simple-deploy.php?pass=YOUR_PASSWORD');
}

echo "<h1>HRLA Simple Deployment</h1>";
echo "<pre>";

$repoPath = '/home/hrledkhw/repositories/hrla-app';
$webRoot = '/home/hrledkhw/public_html';

echo "Repository: $repoPath\n";
echo "Web Root: $webRoot\n";
echo str_repeat("=", 50) . "\n\n";

// Check if repo exists
if (!is_dir($repoPath)) {
    die("ERROR: Repository not found at $repoPath\n");
}

// Pull latest changes
echo "Pulling latest changes...\n";
chdir($repoPath);
$output = shell_exec('git pull origin main 2>&1');
echo $output . "\n";

// Copy files
echo "\nCopying files to web root...\n";
$files = [
    'index.php',
    'login.php',
    'register.php',
    'dashboard.php',
    'subscription.php',
    'settings.php',
    'federal.php',
    'california.php',
    'pricing.php',
    'logout.php',
    'verify.php',
    'reset-password.php',
    'forgot-password.php',
    'payment-callback.php',
    'quick-start.php',
    'privacy-policy.php',
    'product-scope.php',
    'cfra-faqs.php',
    'fmla-faqs.php',
    'styles.css',
    'mobile-responsive.css',
    '.htaccess'
];

$folders = [
    'admin',
    'api',
    'assets',
    'config',
    'includes',
    'texts'
];

// Copy individual files
foreach ($files as $file) {
    if (file_exists("$repoPath/$file")) {
        copy("$repoPath/$file", "$webRoot/$file");
        echo "✓ Copied $file\n";
    }
}

// Copy folders
foreach ($folders as $folder) {
    if (is_dir("$repoPath/$folder")) {
        shell_exec("cp -r $repoPath/$folder $webRoot/");
        echo "✓ Copied folder $folder/\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "✓ DEPLOYMENT COMPLETE!\n";
echo "\nClear your browser cache (Ctrl+F5) to see changes.\n";
echo "</pre>";
?>
