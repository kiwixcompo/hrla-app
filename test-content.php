<?php
/**
 * Test Content Management System
 */

require_once 'config/app.php';
require_once 'includes/content.php';

echo "<h1>Content Management System Test</h1>";

try {
    // Initialize content system
    $initialized = initContentSystem();
    
    if ($initialized) {
        echo "<p style='color: green;'>✓ Content system initialized successfully</p>";
        
        // Test getting content
        $heroTitle = getContent('hero_title', 'Default Title');
        $videoUrl = getContent('video_url', 'https://youtu.be/mCncgWhvKnQ');
        $primaryColor = getContent('color_primary', '#0322D8');
        
        echo "<h2>Sample Content:</h2>";
        echo "<p><strong>Hero Title:</strong> " . htmlspecialchars($heroTitle) . "</p>";
        echo "<p><strong>Video URL:</strong> " . htmlspecialchars($videoUrl) . "</p>";
        echo "<p><strong>Primary Color:</strong> <span style='color: $primaryColor;'>$primaryColor</span></p>";
        
        // Test YouTube embed URL conversion
        $embedUrl = getYouTubeEmbedUrl($videoUrl);
        echo "<p><strong>Embed URL:</strong> " . htmlspecialchars($embedUrl) . "</p>";
        
        if ($embedUrl) {
            echo "<h3>Video Preview:</h3>";
            echo "<iframe width='560' height='315' src='$embedUrl' frameborder='0' allowfullscreen></iframe>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Failed to initialize content system</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    // Try direct file access
    echo "<h2>Testing File-Based Content:</h2>";
    $contentFile = 'data/content.json';
    if (file_exists($contentFile)) {
        echo "<p style='color: green;'>✓ Content file exists</p>";
        $content = json_decode(file_get_contents($contentFile), true);
        if ($content) {
            echo "<p style='color: green;'>✓ Content file is valid JSON</p>";
            echo "<p><strong>Sample content:</strong> " . htmlspecialchars($content['hero_title'] ?? 'Not found') . "</p>";
        } else {
            echo "<p style='color: red;'>✗ Content file is not valid JSON</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Content file does not exist</p>";
    }
}

echo "<p><a href='admin/index.php'>Go to Admin Panel</a></p>";
echo "<p><a href='index.php'>Go to Homepage</a></p>";
?>