<?php
/**
 * Test Content Management System
 */

require_once 'config/app.php';
require_once 'includes/content.php';

// Initialize content system
$initialized = initContentSystem();

echo "<h1>Content Management System Test</h1>";

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

echo "<p><a href='admin/index.php'>Go to Admin Panel</a></p>";
echo "<p><a href='index.php'>Go to Homepage</a></p>";
?>