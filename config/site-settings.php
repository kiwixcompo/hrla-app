<?php
/**
 * Site Settings Management
 * Allows admin to customize website content and styling
 */

/**
 * Create site_settings table
 */
function createSiteSettingsTable($db) {
    $sql = "CREATE TABLE IF NOT EXISTS site_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        setting_type ENUM('text', 'textarea', 'color', 'number', 'url', 'email') DEFAULT 'text',
        category VARCHAR(50) NOT NULL,
        label VARCHAR(255) NOT NULL,
        description TEXT,
        display_order INT DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by INT,
        INDEX idx_category (category),
        INDEX idx_key (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->query($sql);
}

/**
 * Initialize default site settings
 */
function initializeSiteSettings($db) {
    $defaultSettings = [
        // Colors
        ['key' => 'color_primary', 'value' => '#0023F5', 'type' => 'color', 'category' => 'colors', 'label' => 'Primary Color (Blue)', 'order' => 1],
        ['key' => 'color_secondary', 'value' => '#4FCD1A', 'type' => 'color', 'category' => 'colors', 'label' => 'Secondary Color (Green)', 'order' => 2],
        ['key' => 'color_text', 'value' => '#333333', 'type' => 'color', 'category' => 'colors', 'label' => 'Text Color', 'order' => 3],
        ['key' => 'color_background', 'value' => '#FFFFFF', 'type' => 'color', 'category' => 'colors', 'label' => 'Background Color', 'order' => 4],
        
        // Hero Section
        ['key' => 'hero_title', 'value' => 'Answer Employee Leave Questions', 'type' => 'text', 'category' => 'hero', 'label' => 'Hero Title', 'order' => 1],
        ['key' => 'hero_highlight', 'value' => 'accurately and consistently', 'type' => 'text', 'category' => 'hero', 'label' => 'Hero Highlight Text', 'order' => 2],
        ['key' => 'hero_subtitle', 'value' => 'Designed for compliant HR Leave Decision-Making', 'type' => 'text', 'category' => 'hero', 'label' => 'Hero Subtitle', 'order' => 3],
        ['key' => 'hero_feature_1', 'value' => 'Built by HR for HR professionals. Aligned with FMLA, PDL, ADA, & CFRA', 'type' => 'text', 'category' => 'hero', 'label' => 'Hero Feature 1', 'order' => 4],
        ['key' => 'hero_feature_2', 'value' => 'Respond to employee leave questions clearly and consistently', 'type' => 'text', 'category' => 'hero', 'label' => 'Hero Feature 2', 'order' => 5],
        ['key' => 'hero_feature_3', 'value' => 'Align responses with applicable federal and state leave requirements', 'type' => 'text', 'category' => 'hero', 'label' => 'Hero Feature 3', 'order' => 6],
        ['key' => 'hero_feature_4', 'value' => 'Reduce compliance risk and unnecessary rework', 'type' => 'text', 'category' => 'hero', 'label' => 'Hero Feature 4', 'order' => 7],
        ['key' => 'hero_cta_primary', 'value' => 'Try HR Leave Assist', 'type' => 'text', 'category' => 'hero', 'label' => 'Primary CTA Button Text', 'order' => 8],
        ['key' => 'hero_cta_secondary', 'value' => 'Watch Demo', 'type' => 'text', 'category' => 'hero', 'label' => 'Secondary CTA Button Text', 'order' => 9],
        
        // Features Section
        ['key' => 'features_title', 'value' => 'How HR Leave Assist Supports Your Leave Process', 'type' => 'text', 'category' => 'features', 'label' => 'Features Section Title', 'order' => 1],
        ['key' => 'feature_1_title', 'value' => 'Built to Support Leave Compliance', 'type' => 'text', 'category' => 'features', 'label' => 'Feature 1 Title', 'order' => 2],
        ['key' => 'feature_2_title', 'value' => 'Respond to Leave Questions Faster', 'type' => 'text', 'category' => 'features', 'label' => 'Feature 2 Title', 'order' => 3],
        ['key' => 'feature_3_title', 'value' => 'Navigate Federal & California Leave Laws', 'type' => 'text', 'category' => 'features', 'label' => 'Feature 3 Title', 'order' => 4],
        ['key' => 'feature_4_title', 'value' => 'Designed for Busy HR Teams', 'type' => 'text', 'category' => 'features', 'label' => 'Feature 4 Title', 'order' => 5],
        ['key' => 'feature_5_title', 'value' => 'Empowers HR-Led Decision-Making', 'type' => 'text', 'category' => 'features', 'label' => 'Feature 5 Title', 'order' => 6],
        
        // About Section
        ['key' => 'about_title', 'value' => 'About HR Leave Assist', 'type' => 'text', 'category' => 'about', 'label' => 'About Section Title', 'order' => 1],
        ['key' => 'about_content', 'value' => 'HR Leave Assist is designed by HR professionals for HR professionals...', 'type' => 'textarea', 'category' => 'about', 'label' => 'About Content', 'order' => 2],
        
        // Contact
        ['key' => 'contact_email', 'value' => 'askhrla@hrleaveassist.com', 'type' => 'email', 'category' => 'contact', 'label' => 'Contact Email', 'order' => 1],
        ['key' => 'support_email', 'value' => 'askhrla@hrleaveassist.com', 'type' => 'email', 'category' => 'contact', 'label' => 'Support Email', 'order' => 2],
    ];
    
    foreach ($defaultSettings as $setting) {
        $existing = $db->fetch("SELECT id FROM site_settings WHERE setting_key = ?", [$setting['key']]);
        
        if (!$existing) {
            $sql = "INSERT INTO site_settings (setting_key, setting_value, setting_type, category, label, display_order) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $db->query($sql, [
                $setting['key'],
                $setting['value'],
                $setting['type'],
                $setting['category'],
                $setting['label'],
                $setting['order']
            ]);
        }
    }
}

/**
 * Get site setting value
 */
function getSiteSetting($key, $default = '') {
    try {
        $db = getDB();
        $setting = $db->fetch("SELECT setting_value FROM site_settings WHERE setting_key = ?", [$key]);
        return $setting ? $setting['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Get all settings by category
 */
function getSiteSettingsByCategory($category) {
    try {
        $db = getDB();
        return $db->fetchAll("SELECT * FROM site_settings WHERE category = ? ORDER BY display_order", [$category]);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get all settings grouped by category
 */
function getAllSiteSettings() {
    try {
        $db = getDB();
        $settings = $db->fetchAll("SELECT * FROM site_settings ORDER BY category, display_order");
        
        $grouped = [];
        foreach ($settings as $setting) {
            $grouped[$setting['category']][] = $setting;
        }
        
        return $grouped;
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Update site setting
 */
function updateSiteSetting($key, $value, $userId) {
    try {
        $db = getDB();
        $sql = "UPDATE site_settings SET setting_value = ?, updated_by = ?, updated_at = NOW() WHERE setting_key = ?";
        $db->query($sql, [$value, $userId, $key]);
        return true;
    } catch (Exception $e) {
        error_log("Failed to update site setting: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate CSS from color settings
 */
function generateCustomCSS() {
    $primaryColor = getSiteSetting('color_primary', '#0023F5');
    $secondaryColor = getSiteSetting('color_secondary', '#4FCD1A');
    $textColor = getSiteSetting('color_text', '#333333');
    $backgroundColor = getSiteSetting('color_background', '#FFFFFF');
    
    return "
    :root {
        --color-primary: {$primaryColor};
        --color-secondary: {$secondaryColor};
        --color-text: {$textColor};
        --color-background: {$backgroundColor};
    }
    
    .btn-primary {
        background-color: {$primaryColor} !important;
    }
    
    .btn-success {
        background-color: {$secondaryColor} !important;
    }
    
    .hero-highlight {
        color: {$secondaryColor} !important;
    }
    
    .features-checklist .fa-check-circle {
        color: {$secondaryColor} !important;
    }
    ";
}
?>
