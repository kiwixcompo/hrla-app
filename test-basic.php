<?php
/**
 * Basic Test - Check if PHP is working
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>Basic Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .success { color: #3DB20B; font-weight: bold; font-size: 24px; }
        .info { color: #666; margin: 20px 0; }
    </style>
</head>
<body>
    <h1 class='success'>✓ PHP is working!</h1>
    <div class='info'>
        <p>PHP Version: " . phpversion() . "</p>
        <p>Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>
        <p>Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</p>
        <p>Current File: " . __FILE__ . "</p>
    </div>
    <p><a href='diagnose-local.php'>Run Full Diagnostics</a></p>
</body>
</html>";
?>
