<?php
/**
 * View PHP Error Log
 * Simple viewer for logs/error_log
 */

// Basic authentication
session_start();
if (!isset($_SESSION['error_log_auth'])) {
    if (isset($_POST['password'])) {
        if ($_POST['password'] === 'changeme123') {
            $_SESSION['error_log_auth'] = true;
        } else {
            $error = 'Invalid password';
        }
    }
    
    if (!isset($_SESSION['error_log_auth'])) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>PHP Error Log - Login</title>
            <style>
                body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 50px; }
                .login-box { max-width: 400px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h1 { margin: 0 0 20px 0; color: #333; }
                input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
                button { width: 100%; padding: 12px; background: #0322D8; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
                button:hover { background: #1800AD; }
                .error { color: #d32f2f; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class="login-box">
                <h1>PHP Error Log</h1>
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="post">
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Login</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['error_log_auth']);
    header('Location: view-php-errors.php');
    exit;
}

// Get log file
$logFile = __DIR__ . '/logs/error_log';
$lines = isset($_GET['lines']) ? (int)$_GET['lines'] : 100;
$lines = max(10, min(1000, $lines));

// Read log file
$logContent = '';
$fileSize = 0;
$lastModified = 'Never';

if (file_exists($logFile)) {
    $fileSize = filesize($logFile);
    $lastModified = date('Y-m-d H:i:s', filemtime($logFile));
    
    // Read last N lines
    $file = new SplFileObject($logFile, 'r');
    $file->seek(PHP_INT_MAX);
    $totalLines = $file->key() + 1;
    
    $startLine = max(0, $totalLines - $lines);
    $file->seek($startLine);
    
    $logLines = [];
    while (!$file->eof()) {
        $logLines[] = $file->fgets();
    }
    
    $logContent = implode('', $logLines);
} else {
    $logContent = "No PHP error log found. Run enable-error-logging.php first.";
}

// Clear log
if (isset($_POST['clear_log']) && file_exists($logFile)) {
    file_put_contents($logFile, '');
    header('Location: view-php-errors.php');
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Error Log</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #1e1e1e; color: #d4d4d4; }
        
        .header {
            background: #252526;
            border-bottom: 1px solid #3e3e42;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .header h1 {
            font-size: 18px;
            color: #fff;
            font-family: Arial, sans-serif;
        }
        
        .header-info {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
            font-size: 12px;
            color: #858585;
        }
        
        .controls {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-family: Arial, sans-serif;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #0e639c;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1177bb;
        }
        
        .btn-danger {
            background: #d32f2f;
            color: white;
        }
        
        .btn-danger:hover {
            background: #f44336;
        }
        
        .btn-secondary {
            background: #3e3e42;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #505050;
        }
        
        .log-container {
            padding: 20px;
            overflow-x: auto;
        }
        
        .log-content {
            background: #1e1e1e;
            padding: 20px;
            border: 1px solid #3e3e42;
            border-radius: 4px;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 13px;
            line-height: 1.6;
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #858585;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .controls {
                width: 100%;
            }
            
            .btn {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔍 PHP Error Log</h1>
        <div class="header-info">
            <span>Size: <?php echo number_format($fileSize / 1024, 2); ?> KB</span>
            <span>Last Modified: <?php echo $lastModified; ?></span>
            <span>Showing: Last <?php echo $lines; ?> lines</span>
        </div>
        <div class="controls">
            <a href="?lines=50" class="btn btn-secondary">50 lines</a>
            <a href="?lines=100" class="btn btn-secondary">100 lines</a>
            <a href="?lines=500" class="btn btn-secondary">500 lines</a>
            <button onclick="location.reload()" class="btn btn-primary">Refresh</button>
            <form method="post" style="display: inline;" onsubmit="return confirm('Clear the log?')">
                <button type="submit" name="clear_log" class="btn btn-danger">Clear</button>
            </form>
            <a href="view-error-log.php" class="btn btn-secondary">App Errors</a>
            <a href="?logout" class="btn btn-secondary">Logout</a>
        </div>
    </div>
    
    <div class="log-container">
        <?php if (empty(trim($logContent)) || strpos($logContent, 'No PHP error') === 0): ?>
            <div class="empty-state">
                <div>📋</div>
                <p>No PHP errors logged yet.</p>
                <p>Run <a href="enable-error-logging.php" style="color: #4ec9b0;">enable-error-logging.php</a> to set up error logging.</p>
            </div>
        <?php else: ?>
            <div class="log-content"><?php echo htmlspecialchars($logContent); ?></div>
        <?php endif; ?>
    </div>
    
    <script>
        window.addEventListener('load', function() {
            const logContent = document.querySelector('.log-content');
            if (logContent) {
                logContent.scrollTop = logContent.scrollHeight;
            }
        });
    </script>
</body>
</html>
