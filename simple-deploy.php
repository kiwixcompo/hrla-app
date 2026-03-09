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
        chmod("$webRoot/$file", 0644); // Set proper permissions
        echo "✓ Copied $file\n";
    } else {
        echo "⚠ File not found: $file\n";
    }
}

// Copy folders
foreach ($folders as $folder) {
    if (is_dir("$repoPath/$folder")) {
        shell_exec("cp -r $repoPath/$folder $webRoot/");
        echo "✓ Copied folder $folder/\n";
    }
}

// Set proper permissions on directories
echo "\nSetting directory permissions...\n";
chmod("$webRoot/admin", 0755);
chmod("$webRoot/api", 0755);
chmod("$webRoot/assets", 0755);
chmod("$webRoot/config", 0755);
chmod("$webRoot/includes", 0755);
echo "✓ Permissions set\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "✓ DEPLOYMENT COMPLETE!\n";
echo "\nIMPORTANT NEXT STEPS:\n";
echo "1. Visit https://www.hrleaveassist.com/ to verify it works\n";
echo "2. Clear your browser cache (Ctrl+F5) if needed\n";
echo "3. Check that .htaccess file exists in public_html\n";
echo "4. If still showing directory listing, contact hosting support\n";
echo "</pre>";
?>
