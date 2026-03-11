<?php
/**
 * Server Diagnostic Check
 * Simple check that doesn't require any includes
 */

// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Server Check</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { color: #0322D8; }
        .check { padding: 15px; margin: 10px 0; border-left: 4px solid #ddd; background: #f9f9f9; }
        .check.ok { border-left-color: #3DB20B; }
        .check.error { border-left-color: #d32f2f; }
        .check.warning { border-left-color: #ff9800; }
        .status { font-weight: bold; font-size: 18px; }
        .ok { color: #3DB20B; }
        .error { color: #d32f2f; }
        .warning { color: #ff9800; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table td { padding: 8px; border-bottom: 1px solid #ddd; }
        table td:first-child { font-weight: bold; width: 200px; }
        .code { background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; overflow-x: auto; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Server Diagnostic Check</h1>
        
        <?php
        $allOk = true;
        
        // Check 1: PHP is working
        echo "<div class='check ok'>";
        echo "<div class='status ok'>✓ PHP is Working</div>";
        echo "<table>";
        echo "<tr><td>PHP Version:</td><td>" . phpversion() . "</td></tr>";
        echo "<tr><td>Server Software:</td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</td></tr>";
        echo "<tr><td>Document Root:</td><td>" . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</td></tr>";
        echo "<tr><td>Script Path:</td><td>" . __FILE__ . "</td></tr>";
        echo "</table>";
        echo "</div>";
        
        // Check 2: index.php exists
        echo "<div class='check " . (file_exists('index.php') ? 'ok' : 'error') . "'>";
        if (file_exists('index.php')) {
            echo "<div class='status ok'>✓ index.php Found</div>";
            $perms = substr(sprintf('%o', fileperms('index.php')), -4);
            $size = filesize('index.php');
            echo "<table>";
            echo "<tr><td>Permissions:</td><td>{$perms}</td></tr>";
            echo "<tr><td>Size:</td><td>" . number_format($size) . " bytes</td></tr>";
            echo "<tr><td>Readable:</td><td>" . (is_readable('index.php') ? 'Yes' : 'No') . "</td></tr>";
            echo "</table>";
        } else {
            echo "<div class='status error'>✗ index.php NOT Found</div>";
            echo "<p>The main index.php file is missing from the root directory!</p>";
            $allOk = false;
        }
        echo "</div>";
        
        // Check 3: .htaccess exists and is readable
        echo "<div class='check " . (file_exists('.htaccess') && is_readable('.htaccess') ? 'ok' : 'error') . "'>";
        if (file_exists('.htaccess')) {
            if (is_readable('.htaccess')) {
                echo "<div class='status ok'>✓ .htaccess is Readable</div>";
                $perms = substr(sprintf('%o', fileperms('.htaccess')), -4);
                $size = filesize('.htaccess');
                echo "<table>";
                echo "<tr><td>Permissions:</td><td>{$perms} " . ($perms == '0644' ? '(Correct)' : '(Should be 0644)') . "</td></tr>";
                echo "<tr><td>Size:</td><td>" . number_format($size) . " bytes</td></tr>";
                echo "<tr><td>Readable:</td><td>Yes</td></tr>";
                if (function_exists('posix_getpwuid')) {
                    $owner = posix_getpwuid(fileowner('.htaccess'));
                    echo "<tr><td>Owner:</td><td>" . $owner['name'] . "</td></tr>";
                }
                echo "</table>";
                
                if ($perms != '0644') {
                    echo "<p class='warning'>⚠ Permissions should be 0644. Run fix-permissions.php to fix this.</p>";
                    $allOk = false;
                }
            } else {
                echo "<div class='status error'>✗ .htaccess is NOT Readable</div>";
                echo "<p>The .htaccess file exists but cannot be read by the web server.</p>";
                echo "<p><strong>Fix:</strong> Set permissions to 644</p>";
                $allOk = false;
            }
        } else {
            echo "<div class='status error'>✗ .htaccess NOT Found</div>";
            echo "<p>The .htaccess file is missing!</p>";
            $allOk = false;
        }
        echo "</div>";
        
        // Check 4: Required directories
        $requiredDirs = ['config', 'includes', 'data', 'logs', 'assets', 'api'];
        $dirStatus = true;
        foreach ($requiredDirs as $dir) {
            if (!is_dir($dir)) {
                $dirStatus = false;
                break;
            }
        }
        
        echo "<div class='check " . ($dirStatus ? 'ok' : 'error') . "'>";
        if ($dirStatus) {
            echo "<div class='status ok'>✓ Required Directories Found</div>";
            echo "<table>";
            foreach ($requiredDirs as $dir) {
                $perms = substr(sprintf('%o', fileperms($dir)), -4);
                $writable = is_writable($dir);
                echo "<tr><td>{$dir}/</td><td>{$perms} " . ($writable ? '(writable)' : '(not writable)') . "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='status error'>✗ Some Directories Missing</div>";
            foreach ($requiredDirs as $dir) {
                if (!is_dir($dir)) {
                    echo "<p>Missing: {$dir}/</p>";
                }
            }
            $allOk = false;
        }
        echo "</div>";
        
        // Check 5: Writable directories
        $writableDirs = ['logs', 'data'];
        $writeStatus = true;
        foreach ($writableDirs as $dir) {
            if (!is_dir($dir) || !is_writable($dir)) {
                $writeStatus = false;
                break;
            }
        }
        
        echo "<div class='check " . ($writeStatus ? 'ok' : 'warning') . "'>";
        if ($writeStatus) {
            echo "<div class='status ok'>✓ Writable Directories OK</div>";
            echo "<table>";
            foreach ($writableDirs as $dir) {
                $perms = substr(sprintf('%o', fileperms($dir)), -4);
                echo "<tr><td>{$dir}/</td><td>{$perms} (writable)</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='status warning'>⚠ Some Directories Not Writable</div>";
            foreach ($writableDirs as $dir) {
                if (is_dir($dir)) {
                    $perms = substr(sprintf('%o', fileperms($dir)), -4);
                    $writable = is_writable($dir);
                    echo "<p>{$dir}/: {$perms} " . ($writable ? '(OK)' : '(NOT WRITABLE - should be 777)') . "</p>";
                } else {
                    echo "<p>{$dir}/: Missing</p>";
                }
            }
        }
        echo "</div>";
        
        // Check 6: PHP Extensions
        $requiredExts = ['pdo', 'pdo_sqlite', 'json', 'mbstring', 'curl'];
        $extStatus = true;
        foreach ($requiredExts as $ext) {
            if (!extension_loaded($ext)) {
                $extStatus = false;
                break;
            }
        }
        
        echo "<div class='check " . ($extStatus ? 'ok' : 'error') . "'>";
        if ($extStatus) {
            echo "<div class='status ok'>✓ PHP Extensions OK</div>";
            echo "<table>";
            foreach ($requiredExts as $ext) {
                echo "<tr><td>{$ext}</td><td>Loaded</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='status error'>✗ Missing PHP Extensions</div>";
            foreach ($requiredExts as $ext) {
                $loaded = extension_loaded($ext);
                echo "<p>{$ext}: " . ($loaded ? 'OK' : 'MISSING') . "</p>";
            }
            $allOk = false;
        }
        echo "</div>";
        
        // Summary
        echo "<div class='check " . ($allOk ? 'ok' : 'error') . "'>";
        if ($allOk) {
            echo "<div class='status ok'>✓ All Checks Passed!</div>";
            echo "<p>Your server is configured correctly. If you're still seeing directory listing:</p>";
            echo "<ol>";
            echo "<li>Clear your browser cache (Ctrl+F5)</li>";
            echo "<li>Try accessing: <a href='index.php'>index.php</a> directly</li>";
            echo "<li>Check if .htaccess is being processed by Apache</li>";
            echo "</ol>";
        } else {
            echo "<div class='status error'>✗ Issues Found</div>";
            echo "<p><strong>Next Steps:</strong></p>";
            echo "<ol>";
            echo "<li>Run <a href='fix-permissions.php'>fix-permissions.php</a> to fix permission issues</li>";
            echo "<li>Or manually fix permissions via cPanel File Manager</li>";
            echo "<li>See <a href='DEPLOYMENT-FIX-GUIDE.md'>DEPLOYMENT-FIX-GUIDE.md</a> for detailed instructions</li>";
            echo "</ol>";
        }
        echo "</div>";
        
        // Quick actions
        echo "<div class='check'>";
        echo "<h3>Quick Actions</h3>";
        echo "<p><a href='fix-permissions.php' style='display:inline-block; padding:10px 20px; background:#0322D8; color:white; text-decoration:none; border-radius:4px; margin:5px;'>Fix Permissions</a></p>";
        echo "<p><a href='index.php' style='display:inline-block; padding:10px 20px; background:#3DB20B; color:white; text-decoration:none; border-radius:4px; margin:5px;'>Go to Homepage</a></p>";
        echo "</div>";
        ?>
    </div>
</body>
</html>
