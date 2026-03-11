<?php
/**
 * Check which files exist on the server
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>File Check</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { color: #0322D8; }
        .ok { color: #3DB20B; }
        .error { color: #d32f2f; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table td { padding: 10px; border-bottom: 1px solid #ddd; }
        table td:first-child { font-weight: bold; width: 200px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 File Existence Check</h1>
        
        <h2>Current Directory</h2>
        <p><strong>Script Location:</strong> <?php echo __DIR__; ?></p>
        <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></p>
        
        <h2>Critical Files</h2>
        <table>
            <?php
            $files = [
                'index.php',
                'test.php',
                'login.php',
                'register.php',
                '.htaccess',
                'config/app.php',
                'includes/auth.php',
                'data/users.json'
            ];
            
            foreach ($files as $file) {
                $exists = file_exists($file);
                $status = $exists ? "<span class='ok'>✓ EXISTS</span>" : "<span class='error'>✗ MISSING</span>";
                
                echo "<tr>";
                echo "<td>{$file}</td>";
                echo "<td>{$status}";
                
                if ($exists) {
                    if (is_file($file)) {
                        $size = filesize($file);
                        $perms = substr(sprintf('%o', fileperms($file)), -4);
                        echo " ({$perms}, " . number_format($size) . " bytes)";
                    } else {
                        echo " (directory)";
                    }
                }
                
                echo "</td>";
                echo "</tr>";
            }
            ?>
        </table>
        
        <h2>All Files in Current Directory</h2>
        <table>
            <tr>
                <td><strong>Name</strong></td>
                <td><strong>Type</strong></td>
                <td><strong>Size</strong></td>
            </tr>
            <?php
            $items = scandir('.');
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                
                $type = is_dir($item) ? 'Directory' : 'File';
                $size = is_file($item) ? number_format(filesize($item)) . ' bytes' : '-';
                
                echo "<tr>";
                echo "<td>{$item}</td>";
                echo "<td>{$type}</td>";
                echo "<td>{$size}</td>";
                echo "</tr>";
            }
            ?>
        </table>
        
        <h2>What This Means</h2>
        <?php if (file_exists('index.php')): ?>
            <p class="ok">✓ index.php exists! The problem might be with .htaccess configuration.</p>
        <?php else: ?>
            <p class="error">✗ index.php is MISSING! This is why you see directory listing.</p>
            <p><strong>Solution:</strong> Deploy your files to the server or check deployment path.</p>
        <?php endif; ?>
    </div>
</body>
</html>
