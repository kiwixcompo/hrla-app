<?php
/**
 * Test Admin Content Management
 */

require_once 'config/app.php';
require_once 'includes/content.php';

echo "<h1>Admin Content Management Test</h1>";

// Test saving content to file
echo "<h2>Testing Content Save</h2>";

$testContent = [
    'pricing_monthly_title' => 'Monthly Test — $29',
    'pricing_annual_title' => 'Annual Test — $290',
    'hero_title' => 'Test Hero Title Updated'
];

foreach ($testContent as $key => $value) {
    $success = saveContentToFile($key, $value);
    if ($success) {
        echo "<p style='color: green;'>✓ Saved $key: $value</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to save $key</p>";
    }
}

echo "<h2>Testing Content Retrieval</h2>";

foreach ($testContent as $key => $expectedValue) {
    $actualValue = getContent($key, 'DEFAULT');
    if ($actualValue === $expectedValue) {
        echo "<p style='color: green;'>✓ Retrieved $key: $actualValue</p>";
    } else {
        echo "<p style='color: red;'>✗ Expected '$expectedValue' but got '$actualValue' for $key</p>";
    }
}

echo "<h2>Content File Contents</h2>";
$contentFile = 'data/content.json';
if (file_exists($contentFile)) {
    $content = json_decode(file_get_contents($contentFile), true);
    echo "<pre>" . json_encode($content, JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "<p style='color: red;'>Content file not found</p>";
}

echo "<p><a href='index.php'>Test Homepage</a></p>";
echo "<p><a href='pricing.php'>Test Pricing Page</a></p>";
echo "<p><a href='admin/index.php'>Go to Admin Panel</a></p>";
?>