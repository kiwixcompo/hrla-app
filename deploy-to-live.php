<?php
/**
 * Deployment Script - Pull Latest Changes from GitHub
 * Run this file on your live server to update from GitHub
 * 
 * Access: https://yoursite.com/deploy-to-live.php?key=YOUR_SECRET_KEY
 */

// Security: Set a secret key to prevent unauthorized deployments
define('DEPLOY_SECRET_KEY', 'hrla_deploy_2026_secure'); // Change this to something unique!

// Check if secret key is provided and correct
$providedKey = $_GET['key'] ?? '';
if ($providedKey !== DEPLOY_SECRET_KEY) {
    http_response_code(403);
    die('Access Denied: Invalid deployment key');
}

// Configuration - UPDATE THESE PATHS FOR YOUR SERVER
$repoPath = '/home/hrledkhw/repositories/hrla-app'; // Git repository location
$webRoot = '/home/hrledkhw/public_html'; // Live website location
$gitBranch = 'main'; // or 'master' depending on your branch name
$logFile = $webRoot . '/deployment.log';

// Function to log messages
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    echo "<p>$message</p>";
    flush();
}

// Start deployment
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRLA Deployment</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #00ff00;
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }
        h1 {
            color: #00ff00;
            border-bottom: 2px solid #00ff00;
            padding-bottom: 10px;
        }
        p {
            margin: 5px 0;
            padding: 5px;
            background: #2d2d2d;
            border-left: 3px solid #00ff00;
        }
        .success {
            color: #00ff00;
            font-weight: bold;
        }
        .error {
            color: #ff0000;
            font-weight: bold;
        }
        .warning {
            color: #ffaa00;
        }
        pre {
            background: #2d2d2d;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <h1>🚀 HRLA Deployment Script</h1>
    <p>Starting deployment process...</p>

<?php

// Verify repository path exists
if (!is_dir($repoPath)) {
    logMessage("<span class='error'>✗ ERROR: Repository path does not exist: $repoPath</span>");
    logMessage("Please update the \$repoPath variable in this script");
    exit(1);
}

// Verify web root exists
if (!is_dir($webRoot)) {
    logMessage("<span class='error'>✗ ERROR: Web root path does not exist: $webRoot</span>");
    logMessage("Please update the \$webRoot variable in this script");
    exit(1);
}

logMessage("✓ Repository path: $repoPath");
logMessage("✓ Web root path: $webRoot");

// Change to repository directory
chdir($repoPath);
logMessage("✓ Changed to repository directory");

// Check if git is available
exec('git --version 2>&1', $output, $returnCode);
if ($returnCode !== 0) {
    logMessage("<span class='error'>✗ ERROR: Git is not available on this server</span>");
    logMessage("Please contact your hosting provider to enable Git");
    exit(1);
}
logMessage("✓ Git is available: " . implode(' ', $output));
$output = [];

// Verify this is a git repository
exec('git rev-parse --git-dir 2>&1', $output, $returnCode);
if ($returnCode !== 0) {
    logMessage("<span class='error'>✗ ERROR: $repoPath is not a git repository</span>");
    logMessage("<pre>" . implode("\n", $output) . "</pre>");
    exit(1);
}
logMessage("✓ Confirmed git repository");
$output = [];

// Fetch latest changes from remote
logMessage("Fetching latest changes from GitHub...");
exec('git fetch --all 2>&1', $output, $returnCode);
if ($returnCode !== 0) {
    logMessage("<span class='error'>✗ ERROR: Failed to fetch from remote</span>");
    logMessage("<pre>" . implode("\n", $output) . "</pre>");
    exit(1);
}
logMessage("✓ Successfully fetched from remote");
$output = [];

// Show current commit
exec('git rev-parse HEAD 2>&1', $currentCommit);
$currentCommitShort = substr($currentCommit[0], 0, 8);
logMessage("Current commit: $currentCommitShort");

// Reset to match remote exactly (this will overwrite local changes)
logMessage("Resetting to match remote repository...");
exec("git reset --hard origin/$gitBranch 2>&1", $output, $returnCode);
if ($returnCode !== 0) {
    logMessage("<span class='error'>✗ ERROR: Failed to reset to remote</span>");
    logMessage("<pre>" . implode("\n", $output) . "</pre>");
    exit(1);
}
logMessage("✓ Successfully reset to origin/$gitBranch");
$output = [];

// Show new commit
exec('git rev-parse HEAD 2>&1', $newCommit);
$newCommitShort = substr($newCommit[0], 0, 8);
logMessage("New commit: $newCommitShort");

// Clean up any untracked files
logMessage("Cleaning up untracked files...");
exec('git clean -fd 2>&1', $output, $returnCode);
logMessage("✓ Cleanup complete");
$output = [];

// Copy files from repository to web root (if they're different locations)
if ($repoPath !== $webRoot) {
    logMessage("Syncing files from repository to web root...");
    
    // Use rsync if available, otherwise use cp
    exec('which rsync 2>&1', $output, $returnCode);
    if ($returnCode === 0) {
        // rsync is available
        $rsyncCmd = "rsync -av --delete --exclude='.git' --exclude='deployment.log' --exclude='data/' --exclude='logs/' '$repoPath/' '$webRoot/' 2>&1";
        exec($rsyncCmd, $output, $returnCode);
        if ($returnCode === 0) {
            logMessage("✓ Files synced using rsync");
        } else {
            logMessage("<span class='error'>✗ rsync failed, trying cp...</span>");
            exec("cp -rf $repoPath/* $webRoot/ 2>&1", $output, $returnCode);
            logMessage("✓ Files copied using cp");
        }
    } else {
        // Use cp
        exec("cp -rf $repoPath/* $webRoot/ 2>&1", $output, $returnCode);
        logMessage("✓ Files copied using cp");
    }
    $output = [];
} else {
    logMessage("<span class='warning'>Repository and web root are the same - no file sync needed</span>");
}

// Show what changed
logMessage("Getting list of changed files...");
exec("git diff --name-only " . $currentCommit[0] . " " . $newCommit[0] . " 2>&1", $changedFiles);
if (!empty($changedFiles)) {
    logMessage("<span class='success'>Files updated:</span>");
    echo "<pre>";
    foreach ($changedFiles as $file) {
        echo "  • $file\n";
    }
    echo "</pre>";
} else {
    logMessage("<span class='warning'>No files were changed (already up to date)</span>");
}

// Clear PHP opcache if available
if (function_exists('opcache_reset')) {
    opcache_reset();
    logMessage("✓ PHP opcache cleared");
}

// Show deployment summary
logMessage("<span class='success'>========================================</span>");
logMessage("<span class='success'>✓ DEPLOYMENT SUCCESSFUL!</span>");
logMessage("<span class='success'>========================================</span>");
logMessage("Your live site has been updated to match GitHub");
logMessage("Repository: $repoPath");
logMessage("Web Root: $webRoot");
logMessage("Branch: $gitBranch");
logMessage("Commit: $newCommitShort");
logMessage("");
logMessage("<strong>IMPORTANT: Clear your browser cache to see changes!</strong>");
logMessage("Press Ctrl+F5 or Cmd+Shift+R to hard refresh");

?>

    <p style="margin-top: 30px; color: #ffaa00;">
        <strong>Security Note:</strong> For production use, delete this file after deployment 
        or move it outside the web root.
    </p>
    
    <p style="margin-top: 10px;">
        <a href="/" style="color: #00ff00;">← Back to Home</a> | 
        <a href="deployment.log" style="color: #00ff00;" target="_blank">View Full Log</a>
    </p>
</body>
</html>
