<?php
/**
 * Create Content Management Tables
 * HR Leave Assistant - Content Management System
 */

require_once 'config/app.php';

try {
    $db = getDB();
    
    // Create site_content table for managing all editable content
    $sql = "CREATE TABLE IF NOT EXISTS site_content (
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
    
    $db->query($sql);
    echo "✓ Created site_content table\n";
    
    // Insert default content values
    $defaultContent = [
        // Hero Section
        ['hero_title', 'Answer Employee Leave Questions With Consistent Compliance Information', 'textarea', 'hero', 'Hero Title', 'Main headline on homepage', 1],
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
        
        // How It Works Section
        ['how_it_works_title', 'How HR Leave Assist Works', 'text', 'how_it_works', 'How It Works Title', 'Title for how it works section', 1],
        ['step_1_title', 'Paste Employee Question or Email', 'text', 'how_it_works', 'Step 1 Title', 'Title for first step', 2],
        ['step_1_description', 'Copy and paste the employee\'s leave question or email into the system.', 'textarea', 'how_it_works', 'Step 1 Description', 'Description for first step', 3],
        ['step_2_title', 'Analysis & Draft Response', 'text', 'how_it_works', 'Step 2 Title', 'Title for second step', 4],
        ['step_2_description', 'The tool analyzes the email and prepares an employee-ready draft aligned with applicable leave requirements.', 'textarea', 'how_it_works', 'Step 2 Description', 'Description for second step', 5],
        ['step_3_title', 'Review Generated Response & Send', 'text', 'how_it_works', 'Step 3 Title', 'Title for third step', 6],
        ['step_3_description', 'Review, edit as needed, and send the response to your employee.', 'textarea', 'how_it_works', 'Step 3 Description', 'Description for third step', 7],
        
        // About Section
        ['about_title', 'HR Leave Assist', 'text', 'about', 'About Section Title', 'Title for about section', 1],
        ['about_paragraph_1', 'HR Leave Assist (HRLA) is a support tool built for HR professionals who answer employee leave questions every day — especially those involving FMLA, CFRA, PDL, and ADA considerations.', 'textarea', 'about', 'About Paragraph 1', 'First paragraph of about section', 2],
        ['about_paragraph_2', 'Leave situations are rarely straightforward. They often involve overlapping leave laws, internal requirements, and personal circumstances—under real-time pressure. HRLA helps streamline that complexity by organizing applicable leave considerations and drafting clear, employee-ready responses, without starting from scratch.', 'textarea', 'about', 'About Paragraph 2', 'Second paragraph of about section', 3],
        ['about_paragraph_3', 'Built by an HR professional with over 25 years of experience, HRLA is designed to support your judgment—not replace it. The tool reinforces consistency, reduces missed steps, and helps you respond with care, efficiency, and confidence.', 'textarea', 'about', 'About Paragraph 3', 'Third paragraph of about section', 4],
        
        // Pricing Section
        ['pricing_title', 'Pricing', 'text', 'pricing', 'Pricing Section Title', 'Title for pricing section', 1],
        ['pricing_free_title', 'Free Trial — $0', 'text', 'pricing', 'Free Trial Title', 'Title for free trial plan', 2],
        ['pricing_free_description', 'HR professionals who want to test the tool with real-world scenarios before subscribing.', 'textarea', 'pricing', 'Free Trial Description', 'Description for free trial plan', 3],
        ['pricing_monthly_title', 'Monthly — $29', 'text', 'pricing', 'Monthly Plan Title', 'Title for monthly plan', 4],
        ['pricing_monthly_description', 'Individual HR professionals who regularly respond to employee leave inquiries.', 'textarea', 'pricing', 'Monthly Plan Description', 'Description for monthly plan', 5],
        ['pricing_annual_title', 'Annual — $290', 'text', 'pricing', 'Annual Plan Title', 'Title for annual plan', 6],
        ['pricing_annual_subtitle', '(2 months free)', 'text', 'pricing', 'Annual Plan Subtitle', 'Subtitle for annual plan', 7],
        ['pricing_annual_description', 'Individual HR professionals who rely on HR Leave Assist as part of their regular, year-round workflow.', 'textarea', 'pricing', 'Annual Plan Description', 'Description for annual plan', 8],
        ['pricing_org_title', 'Organization — $580 / yr', 'text', 'pricing', 'Organization Plan Title', 'Title for organization plan', 9],
        ['pricing_org_description', 'Small HR teams of 2 to 5 who regularly respond to employee leave questions and want consistent, shared access.', 'textarea', 'pricing', 'Organization Plan Description', 'Description for organization plan', 10],
        
        // FAQ Section
        ['faq_title', 'Frequently Asked Questions', 'text', 'faq', 'FAQ Section Title', 'Title for FAQ section', 1],
        ['faq_subtitle', 'Select a category to find answers about leave laws and regulations', 'text', 'faq', 'FAQ Section Subtitle', 'Subtitle for FAQ section', 2],
        ['faq_fmla_title', 'FMLA FAQs', 'text', 'faq', 'FMLA FAQ Title', 'Title for FMLA FAQ card', 3],
        ['faq_fmla_description', 'Family and Medical Leave Act questions covering federal leave requirements, eligibility, and job protection.', 'textarea', 'faq', 'FMLA FAQ Description', 'Description for FMLA FAQ card', 4],
        ['faq_cfra_title', 'CFRA FAQs', 'text', 'faq', 'CFRA FAQ Title', 'Title for CFRA FAQ card', 5],
        ['faq_cfra_description', 'California Family Rights Act questions covering state-specific leave laws, benefits, and requirements.', 'textarea', 'faq', 'CFRA FAQ Description', 'Description for CFRA FAQ card', 6],
        
        // CTA Section
        ['cta_title', 'Simple to Start - Easy to Use', 'text', 'cta', 'CTA Section Title', 'Title for final call-to-action section', 1],
        ['cta_button_text', 'Get Started Now', 'text', 'cta', 'CTA Button Text', 'Text for final CTA button', 2],
        
        // Footer
        ['footer_description', 'A leave-support tool built by HR, for HR, to help apply consistent, compliance-aligned responses to employee leave questions.', 'textarea', 'footer', 'Footer Description', 'Description text in footer', 1],
        
        // Colors
        ['color_primary', '#0322D8', 'color', 'colors', 'Primary Blue', 'Main blue color used throughout site', 1],
        ['color_secondary', '#3DB20B', 'color', 'colors', 'Secondary Green', 'Green color used for accents', 2],
        ['color_dark_blue', '#1800AD', 'color', 'colors', 'Dark Blue', 'Darker blue for hover states', 3],
        ['color_red', '#FF0000', 'color', 'colors', 'Red', 'Red color for CTA section', 4],
    ];
    
    // Insert default content
    $insertSql = "INSERT IGNORE INTO site_content (content_key, content_value, content_type, category, label, description, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    foreach ($defaultContent as $content) {
        $db->query($insertSql, $content);
    }
    
    echo "✓ Inserted default content values\n";
    echo "✓ Content management system ready!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>