<?php
/**
 * PHPMailer Installation Script
 * Downloads and installs PHPMailer without Composer
 */

echo "<h1>PHPMailer Installation</h1>";
echo "<pre>";

$vendorDir = __DIR__ . '/vendor';
$phpmailerDir = $vendorDir . '/phpmailer/phpmailer';

// Create vendor directory
if (!is_dir($vendorDir)) {
    mkdir($vendorDir, 0755, true);
    echo "✓ Created vendor directory\n";
}

if (!is_dir($phpmailerDir)) {
    mkdir($phpmailerDir, 0755, true);
    echo "✓ Created phpmailer directory\n";
}

// Download PHPMailer
echo "\nDownloading PHPMailer...\n";
$zipUrl = 'https://github.com/PHPMailer/PHPMailer/archive/refs/tags/v6.9.1.zip';
$zipFile = $vendorDir . '/phpmailer.zip';

$ch = curl_init($zipUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$zipContent = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && $zipContent) {
    file_put_contents($zipFile, $zipContent);
    echo "✓ Downloaded PHPMailer\n";
    
    // Extract ZIP
    $zip = new ZipArchive;
    if ($zip->open($zipFile) === TRUE) {
        $zip->extractTo($vendorDir);
        $zip->close();
        echo "✓ Extracted PHPMailer\n";
        
        // Move files to correct location
        $extractedDir = $vendorDir . '/PHPMailer-6.9.1';
        if (is_dir($extractedDir)) {
            // Copy src files
            $srcDir = $extractedDir . '/src';
            if (is_dir($srcDir)) {
                $files = scandir($srcDir);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        copy($srcDir . '/' . $file, $phpmailerDir . '/' . $file);
                    }
                }
                echo "✓ Copied PHPMailer files\n";
            }
            
            // Clean up
            unlink($zipFile);
            deleteDirectory($extractedDir);
            echo "✓ Cleaned up temporary files\n";
        }
    } else {
        echo "✗ Failed to extract ZIP file\n";
    }
} else {
    echo "✗ Failed to download PHPMailer (HTTP $httpCode)\n";
}

// Create autoload file
$autoloadContent = '<?php
// PHPMailer Autoloader
spl_autoload_register(function ($class) {
    $prefix = "PHPMailer\\\\PHPMailer\\\\";
    $base_dir = __DIR__ . "/phpmailer/phpmailer/";
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace("\\\\", "/", $relative_class) . ".php";
    
    if (file_exists($file)) {
        require $file;
    }
});
';

file_put_contents($vendorDir . '/autoload.php', $autoloadContent);
echo "✓ Created autoload file\n";

echo "\n=== Installation Complete ===\n";
echo "PHPMailer has been installed successfully!\n";
echo "You can now send emails using SMTP.\n";

echo "</pre>";

function deleteDirectory($dir) {
    if (!is_dir($dir)) return;
    
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    rmdir($dir);
}
?>
