<?php
echo "Testing content system...\n";

// Test file access
$contentFile = 'data/content.json';
if (file_exists($contentFile)) {
    echo "✓ Content file exists\n";
    $content = json_decode(file_get_contents($contentFile), true);
    if ($content) {
        echo "✓ Content file is valid JSON\n";
        echo "Hero title: " . ($content['hero_title'] ?? 'Not found') . "\n";
    } else {
        echo "✗ Content file is not valid JSON\n";
    }
} else {
    echo "✗ Content file does not exist\n";
}

// Test function directly
function getContentDirect($key, $default = '') {
    $contentFile = 'data/content.json';
    
    if (file_exists($contentFile)) {
        try {
            $content = json_decode(file_get_contents($contentFile), true);
            return $content[$key] ?? $default;
        } catch (Exception $e) {
            return $default;
        }
    }
    
    return $default;
}

echo "Direct function test: " . getContentDirect('hero_title', 'Default') . "\n";
?>