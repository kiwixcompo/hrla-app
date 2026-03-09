<?php
/**
 * PHPMailer Installation Script
 * Downloads and installs PHPMailer without Composer
 */

set_time_limit(120);
ini_set('max_execution_time', 120);

echo "<!DOCTYPE html><html><head><title>PHPMailer Installation</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:20px auto;padding:20px;background:#f8f9fa;}";
echo ".success{background:#d4edda;color:#155724;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #28a745;}";
echo ".error{background:#f8d7da;color:#721c24;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #dc3545;}";
echo ".info{background:#d1ecf1;color:#0c5460;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #17a2b8;}";
echo "pre{background:#f8f9fa;border:1px solid #dee2e6;padding:15px;border-radius:5px;overflow-x:auto;}</style></head><body>";
echo "<h1>PHPMailer Installation</h1>";

$vendorDir = __DIR__ . '/vendor';
$phpmailerDir = $vendorDir . '/phpmailer/phpmailer';

// Create vendor directory
if (!is_dir($vendorDir)) {
    if (!mkdir($vendorDir, 0755, true)) {
        echo "<div class='error'>❌ Failed to create vendor directory</div></body></html>";
        exit;
    }
    echo "<div class='success'>✓ Created vendor directory</div>";
}

if (!is_dir($phpmailerDir)) {
    if (!mkdir($phpmailerDir, 0755, true)) {
        echo "<div class='error'>❌ Failed to create phpmailer directory</div></body></html>";
        exit;
    }
    echo "<div class='success'>✓ Created phpmailer directory</div>";
}

// Download PHPMailer files directly from GitHub
echo "<div class='info'>📦 Downloading PHPMailer files from GitHub...</div>";

$files = [
    'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/v6.9.1/src/PHPMailer.php',
    'SMTP.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/v6.9.1/src/SMTP.php',
    'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/v6.9.1/src/Exception.php',
    'POP3.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/v6.9.1/src/POP3.php',
    'OAuth.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/v6.9.1/src/OAuth.php'
];

$success_count = 0;
$failed_files = [];

foreach ($files as $filename => $url) {
    echo "<div class='info'>Downloading $filename...</div>";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'User-Agent: PHPMailer-Installer/1.0',
            'timeout' => 30
        ]
    ]);
    
    $content = @file_get_contents($url, false, $context);
    
    if ($content !== false && strlen($content) > 100) {
        $target_file = $phpmailerDir . '/' . $filename;
        if (file_put_contents($target_file, $content)) {
            echo "<div class='success'>✓ Downloaded $filename (" . number_format(strlen($content)) . " bytes)</div>";
            $success_count++;
        } else {
            echo "<div class='error'>❌ Failed to save $filename</div>";
            $failed_files[] = $filename;
        }
    } else {
        echo "<div class='error'>❌ Failed to download $filename from GitHub</div>";
        $failed_files[] = $filename;
    }
    
    flush();
    if (ob_get_level()) ob_flush();
}

// Create autoload file
$autoloadContent = '<?php
/**
 * Autoloader for PHPMailer
 */

spl_autoload_register(function ($class) {
    // PHPMailer namespace
    $prefix = \'PHPMailer\\\\PHPMailer\\\\\';
    $base_dir = __DIR__ . \'/phpmailer/phpmailer/\';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace(\'\\\\\', \'/\', $relative_class) . \'.php\';
    
    if (file_exists($file)) {
        require $file;
    }
});
';

if (file_put_contents($vendorDir . '/autoload.php', $autoloadContent)) {
    echo "<div class='success'>✓ Created autoload file</div>";
    $success_count++;
} else {
    echo "<div class='error'>❌ Failed to create autoload file</div>";
}

echo "<hr>";

if ($success_count >= 5 && empty($failed_files)) {
    echo "<div class='success'><h2>✅ Installation Complete!</h2>";
    echo "<p>PHPMailer has been installed successfully!</p>";
    echo "<p>You can now send emails using SMTP.</p></div>";
    
    echo "<div class='info'><h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Visit <a href='test-email.php'>test-email.php</a> to verify installation</li>";
    echo "<li>Try the 'Resend Verification Email' button on the registration page</li>";
    echo "<li>Check logs/ directory for any error messages</li>";
    echo "</ol></div>";
} else {
    echo "<div class='error'><h2>❌ Installation Incomplete</h2>";
    echo "<p>Some files failed to download:</p><ul>";
    foreach ($failed_files as $file) {
        echo "<li>$file</li>";
    }
    echo "</ul>";
    echo "<p>Please try again or contact your hosting provider.</p></div>";
}

// Show installed files
echo "<div class='info'><h3>📁 Installed Files:</h3><pre>";
if (is_dir($phpmailerDir)) {
    $installed = scandir($phpmailerDir);
    foreach ($installed as $file) {
        if ($file !== '.' && $file !== '..') {
            $size = filesize($phpmailerDir . '/' . $file);
            echo "$file (" . number_format($size) . " bytes)\n";
        }
    }
} else {
    echo "Directory not found";
}
echo "</pre></div>";

echo "<div style='text-align:center;margin-top:20px;'>";
echo "<a href='test-email.php' style='display:inline-block;padding:10px 20px;background:#0023F5;color:white;text-decoration:none;border-radius:5px;margin:5px;'>Test Email Configuration</a>";
echo "<a href='/' style='display:inline-block;padding:10px 20px;background:#28a745;color:white;text-decoration:none;border-radius:5px;margin:5px;'>Go to Website</a>";
echo "</div>";

echo "</body></html>";
?>
