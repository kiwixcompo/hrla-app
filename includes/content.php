<?php
/**
 * Content Management Helper Functions
 * HR Leave Assistant - Content Management System
 */

/**
 * Get content value by key
 */
function getContent($key, $default = '') {
    try {
        $db = getDB();
        $result = $db->fetch("SELECT content_value FROM site_content WHERE content_key = ? AND is_active = 1", [$key]);
        return $result ? $result['content_value'] : $default;
    } catch (Exception $e) {
        error_log("Error getting content for key '$key': " . $e->getMessage());
        
        // Try file-based fallback
        $fileContent = getContentFromFile($key, $default);
        if ($fileContent !== $default) {
            return $fileContent;
        }
        
        return $default;
    }
}

/**
 * File-based content fallback
 */
function getContentFromFile($key, $default = '') {
    $contentFile = __DIR__ . '/../data/content.json';
    
    if (file_exists($contentFile)) {
        try {
            $content = json_decode(file_get_contents($contentFile), true);
            return $content[$key] ?? $default;
        } catch (Exception $e) {
            error_log("Error reading content file: " . $e->getMessage());
        }
    }
    
    return $default;
}

/**
 * Save content to file (fallback)
 */
function saveContentToFile($key, $value) {
    $contentFile = __DIR__ . '/../data/content.json';
    $content = [];
    
    // Load existing content
    if (file_exists($contentFile)) {
        try {
            $content = json_decode(file_get_contents($contentFile), true) ?: [];
        } catch (Exception $e) {
            error_log("Error reading existing content file: " . $e->getMessage());
        }
    }
    
    // Update content
    $content[$key] = $value;
    
    // Save content
    try {
        if (!is_dir(dirname($contentFile))) {
            mkdir(dirname($contentFile), 0755, true);
        }
        file_put_contents($contentFile, json_encode($content, JSON_PRETTY_PRINT));
        return true;
    } catch (Exception $e) {
        error_log("Error saving content to file: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all content for a category
 */
function getContentByCategory($category) {
    try {
        $db = getDB();
        $results = $db->fetchAll("SELECT content_key, content_value FROM site_content WHERE category = ? AND is_active = 1 ORDER BY sort_order ASC", [$category]);
        
        $content = [];
        foreach ($results as $row) {
            $content[$row['content_key']] = $row['content_value'];
        }
        
        return $content;
    } catch (Exception $e) {
        error_log("Error getting content for category '$category': " . $e->getMessage());
        return [];
    }
}

/**
 * Check if content table exists and create if needed
 */
function ensureContentTable() {
    try {
        $db = getDB();
        
        // Check if table exists
        $tableExists = $db->fetch("SHOW TABLES LIKE 'site_content'");
        
        if (!$tableExists) {
            // Create table with default content
            $createTableSql = "CREATE TABLE IF NOT EXISTS site_content (
                id INT AUTO_INCREMENT PRIMARY KEY,
                content_key VARCHAR(100) UNIQUE NOT NULL,
                content_value TEXT NOT NULL,
                content_type ENUM('text', 'textarea', 'url', 'number', 'color') DEFAULT 'text',
                category VARCHAR(50) NOT NULL,
                label VARCHAR(255) NOT NULL,
                description TEXT,
                sort_order INT DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                updated_by INT,
                INDEX idx_category (category),
                INDEX idx_content_key (content_key),
                INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $db->query($createTableSql);
            
            // Insert default content
            insertDefaultContent($db);
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Error ensuring content table: " . $e->getMessage());
        // Don't fail if database is not available - file system will work
        return true;
    }
}

/**
 * Insert default content values
 */
function insertDefaultContent($db) {
    $defaultContent = [
        // Hero Section
        ['hero_title', 'Generates HR-Ready Responses To Employee Leave Questions', 'textarea', 'hero', 'Hero Title', 'Main headline on homepage', 1],
        ['hero_subtitle', 'AI-powered HR leave response generator for federal and California leave questions. Draft clear, compliant employee communications aligned with FMLA, CFRA, PDL, and ADA.', 'textarea', 'hero', 'Hero Subtitle', 'Subtitle text below main headline', 2],
        ['hero_feature_1', 'Built by HR for HR professionals', 'text', 'hero', 'Feature 1', 'First feature bullet point', 3],
        ['hero_feature_2', 'Drafts employee-ready responses', 'text', 'hero', 'Feature 2', 'Second feature bullet point', 4],
        ['hero_feature_3', 'Aligned with FMLA, PDL, ADA, and CFRA', 'text', 'hero', 'Feature 3', 'Third feature bullet point', 5],
        ['hero_feature_4', 'Supports consistent HR decision-making', 'text', 'hero', 'Feature 4', 'Fourth feature bullet point', 6],
        ['hero_feature_5', 'Helps reduce compliance risk', 'text', 'hero', 'Feature 5', 'Fifth feature bullet point', 7],
        ['hero_cta_primary', 'Try HR Leave Assist', 'text', 'hero', 'Primary CTA Button', 'Text for main call-to-action button', 8],
        ['hero_cta_secondary', 'See How It Works', 'text', 'hero', 'Secondary CTA Button', 'Text for secondary button', 9],
        
        // Video Settings
        ['video_url', 'https://youtu.be/mCncgWhvKnQ', 'url', 'video', 'YouTube Video URL', 'YouTube video link for "How It Works" modal', 1],
        
        // Features Section
        ['features_title', 'Supporting Your Leave Process', 'text', 'features', 'Features Section Title', 'Main title for features section', 1],
        ['features_subtitle', 'Every Step of the Way', 'text', 'features', 'Features Section Subtitle', 'Subtitle for features section', 2],
        ['feature_1', 'Built to Support Leave Compliance', 'text', 'features', 'Feature 1', 'First feature item', 3],
        ['feature_2', 'Respond to Leave Questions Faster', 'text', 'features', 'Feature 2', 'Second feature item', 4],
        ['feature_3', 'Navigate Federal & California Leave Laws', 'text', 'features', 'Feature 3', 'Third feature item', 5],
        ['feature_4', 'Designed for Busy HR Teams', 'text', 'features', 'Feature 4', 'Fourth feature item', 6],
        ['feature_5', 'Empowers HR-Led Decision-Making', 'text', 'features', 'Feature 5', 'Fifth feature item', 7],
        ['feature_6', 'Supports Consistent Leave Administration', 'text', 'features', 'Feature 6', 'Sixth feature item', 8],
        
        // Colors
        ['color_primary', '#0322D8', 'color', 'colors', 'Primary Blue', 'Main blue color used throughout site', 1],
        ['color_secondary', '#3DB20B', 'color', 'colors', 'Secondary Green', 'Green color used for accents', 2],
        ['color_dark_blue', '#1800AD', 'color', 'colors', 'Dark Blue', 'Darker blue for hover states', 3],
        ['color_red', '#FF0000', 'color', 'colors', 'Red', 'Red color for CTA section', 4],
    ];
    
    $insertSql = "INSERT IGNORE INTO site_content (content_key, content_value, content_type, category, label, description, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    foreach ($defaultContent as $content) {
        $db->query($insertSql, $content);
    }
}

/**
 * Get YouTube video embed URL from various YouTube URL formats
 */
function getYouTubeEmbedUrl($url) {
    if (empty($url)) {
        return '';
    }
    
    $videoId = '';
    
    // Extract video ID from different YouTube URL formats
    if (strpos($url, 'youtu.be/') !== false) {
        $videoId = explode('youtu.be/', $url)[1];
        $videoId = explode('?', $videoId)[0]; // Remove query parameters
    } elseif (strpos($url, 'youtube.com/watch?v=') !== false) {
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $videoId = $query['v'] ?? '';
    } elseif (strpos($url, 'youtube.com/embed/') !== false) {
        $videoId = explode('youtube.com/embed/', $url)[1];
        $videoId = explode('?', $videoId)[0]; // Remove query parameters
    }
    
    return $videoId ? "https://www.youtube.com/embed/{$videoId}" : '';
}

/**
 * Initialize content system
 */
function initContentSystem() {
    try {
        return ensureContentTable();
    } catch (Exception $e) {
        error_log("Content system initialization failed: " . $e->getMessage());
        // Even if database fails, we can still use file-based system
        return true;
    }
}
?>