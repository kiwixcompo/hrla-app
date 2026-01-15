<!-- Site Settings Section -->
<div id="site-settingsSection" class="content-section">
    <div class="section-header">
        <h2><i class="fas fa-paint-brush"></i> Website Customization</h2>
        <p>Customize your website's appearance and content</p>
    </div>

    <!-- Settings Tabs -->
    <div class="settings-tabs">
        <button class="settings-tab active" data-tab="colors">
            <i class="fas fa-palette"></i> Colors & Branding
        </button>
        <button class="settings-tab" data-tab="hero">
            <i class="fas fa-home"></i> Hero Section
        </button>
        <button class="settings-tab" data-tab="features">
            <i class="fas fa-star"></i> Features
        </button>
        <button class="settings-tab" data-tab="about">
            <i class="fas fa-info-circle"></i> About
        </button>
        <button class="settings-tab" data-tab="contact">
            <i class="fas fa-envelope"></i> Contact
        </button>
    </div>

    <!-- Colors Tab -->
    <div class="settings-tab-content active" id="colorsTab">
        <div class="card">
            <div class="card-header">
                <h3>Brand Colors</h3>
                <p>Customize your website's color scheme</p>
            </div>
            <div class="card-body">
                <form id="colorsForm" class="settings-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="category" value="colors">
                    
                    <?php
                    $colorSettings = getSiteSettingsByCategory('colors');
                    foreach ($colorSettings as $setting):
                    ?>
                    <div class="form-group color-picker-group">
                        <label for="<?php echo $setting['setting_key']; ?>">
                            <?php echo htmlspecialchars($setting['label']); ?>
                        </label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="<?php echo $setting['setting_key']; ?>" 
                                name="<?php echo $setting['setting_key']; ?>" 
                                value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                                class="color-input"
                            >
                            <input 
                                type="text" 
                                value="<?php echo htmlspecialchars($setting['setting_value']); ?>" 
                                class="color-text-input"
                                readonly
                            >
                            <button type="button" class="btn-reset-color" data-default="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Colors
                        </button>
                        <button type="button" class="btn btn-secondary" id="previewColors">
                            <i class="fas fa-eye"></i> Preview Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hero Tab -->
    <div class="settings-tab-content" id="heroTab">
        <div class="card">
            <div class="card-header">
                <h3>Hero Section Content</h3>
                <p>Edit the main headline and features on your homepage</p>
            </div>
            <div class="card-body">
                <form id="heroForm" class="settings-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="category" value="hero">
                    
                    <?php
                    $heroSettings = getSiteSettingsByCategory('hero');
                    foreach ($heroSettings as $setting):
                    ?>
                    <div class="form-group">
                        <label for="<?php echo $setting['setting_key']; ?>">
                            <?php echo htmlspecialchars($setting['label']); ?>
                        </label>
                        <?php if ($setting['setting_type'] === 'textarea'): ?>
                        <textarea 
                            id="<?php echo $setting['setting_key']; ?>" 
                            name="<?php echo $setting['setting_key']; ?>"
                            rows="3"
                            class="form-control"
                        ><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>
                        <?php else: ?>
                        <input 
                            type="text" 
                            id="<?php echo $setting['setting_key']; ?>" 
                            name="<?php echo $setting['setting_key']; ?>" 
                            value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                            class="form-control"
                        >
                        <?php endif; ?>
                        <small class="form-text">Character count: <span class="char-count">0</span></small>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Hero Content
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Features Tab -->
    <div class="settings-tab-content" id="featuresTab">
        <div class="card">
            <div class="card-header">
                <h3>Features Section</h3>
                <p>Edit the features displayed on your homepage</p>
            </div>
            <div class="card-body">
                <form id="featuresForm" class="settings-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="category" value="features">
                    
                    <?php
                    $featureSettings = getSiteSettingsByCategory('features');
                    foreach ($featureSettings as $setting):
                    ?>
                    <div class="form-group">
                        <label for="<?php echo $setting['setting_key']; ?>">
                            <?php echo htmlspecialchars($setting['label']); ?>
                        </label>
                        <input 
                            type="text" 
                            id="<?php echo $setting['setting_key']; ?>" 
                            name="<?php echo $setting['setting_key']; ?>" 
                            value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                            class="form-control"
                        >
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Features
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- About Tab -->
    <div class="settings-tab-content" id="aboutTab">
        <div class="card">
            <div class="card-header">
                <h3>About Section</h3>
                <p>Edit your about section content</p>
            </div>
            <div class="card-body">
                <form id="aboutForm" class="settings-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="category" value="about">
                    
                    <?php
                    $aboutSettings = getSiteSettingsByCategory('about');
                    foreach ($aboutSettings as $setting):
                    ?>
                    <div class="form-group">
                        <label for="<?php echo $setting['setting_key']; ?>">
                            <?php echo htmlspecialchars($setting['label']); ?>
                        </label>
                        <?php if ($setting['setting_type'] === 'textarea'): ?>
                        <textarea 
                            id="<?php echo $setting['setting_key']; ?>" 
                            name="<?php echo $setting['setting_key']; ?>"
                            rows="6"
                            class="form-control"
                        ><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>
                        <?php else: ?>
                        <input 
                            type="text" 
                            id="<?php echo $setting['setting_key']; ?>" 
                            name="<?php echo $setting['setting_key']; ?>" 
                            value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                            class="form-control"
                        >
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save About Content
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Contact Tab -->
    <div class="settings-tab-content" id="contactTab">
        <div class="card">
            <div class="card-header">
                <h3>Contact Information</h3>
                <p>Update your contact details</p>
            </div>
            <div class="card-body">
                <form id="contactForm" class="settings-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="category" value="contact">
                    
                    <?php
                    $contactSettings = getSiteSettingsByCategory('contact');
                    foreach ($contactSettings as $setting):
                    ?>
                    <div class="form-group">
                        <label for="<?php echo $setting['setting_key']; ?>">
                            <?php echo htmlspecialchars($setting['label']); ?>
                        </label>
                        <input 
                            type="<?php echo $setting['setting_type']; ?>" 
                            id="<?php echo $setting['setting_key']; ?>" 
                            name="<?php echo $setting['setting_key']; ?>" 
                            value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                            class="form-control"
                        >
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Contact Info
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.settings-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2rem;
    border-bottom: 2px solid #e9ecef;
    flex-wrap: wrap;
}

.settings-tab {
    padding: 1rem 1.5rem;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 500;
    color: #6c757d;
    transition: all 0.3s;
}

.settings-tab:hover {
    color: #0023F5;
    background: #f8f9fa;
}

.settings-tab.active {
    color: #0023F5;
    border-bottom-color: #0023F5;
}

.settings-tab i {
    margin-right: 0.5rem;
}

.settings-tab-content {
    display: none;
}

.settings-tab-content.active {
    display: block;
}

.settings-form {
    max-width: 800px;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #0023F5;
    box-shadow: 0 0 0 3px rgba(0, 35, 245, 0.1);
}

.color-picker-group {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.color-input-wrapper {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.color-input {
    width: 80px;
    height: 50px;
    border: 2px solid #dee2e6;
    border-radius: 6px;
    cursor: pointer;
}

.color-text-input {
    flex: 1;
    padding: 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    font-family: monospace;
    font-size: 1rem;
}

.btn-reset-color {
    padding: 0.75rem 1rem;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-reset-color:hover {
    background: #5a6268;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #dee2e6;
}

.form-text {
    display: block;
    margin-top: 0.5rem;
    color: #6c757d;
    font-size: 0.875rem;
}

.char-count {
    font-weight: 600;
    color: #0023F5;
}

@media (max-width: 768px) {
    .settings-tabs {
        overflow-x: auto;
    }
    
    .settings-tab {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        white-space: nowrap;
    }
    
    .color-input-wrapper {
        flex-direction: column;
        align-items: stretch;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
// Settings tabs functionality
document.querySelectorAll('.settings-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const tabName = this.dataset.tab;
        
        // Update active tab
        document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        // Update active content
        document.querySelectorAll('.settings-tab-content').forEach(c => c.classList.remove('active'));
        document.getElementById(tabName + 'Tab').classList.add('active');
    });
});

// Color picker sync
document.querySelectorAll('.color-input').forEach(input => {
    input.addEventListener('input', function() {
        const textInput = this.parentElement.querySelector('.color-text-input');
        textInput.value = this.value.toUpperCase();
    });
});

// Character count
document.querySelectorAll('.form-control').forEach(input => {
    const updateCount = () => {
        const counter = input.parentElement.querySelector('.char-count');
        if (counter) {
            counter.textContent = input.value.length;
        }
    };
    
    updateCount();
    input.addEventListener('input', updateCount);
});

// Form submissions
['colors', 'hero', 'features', 'about', 'contact'].forEach(category => {
    const form = document.getElementById(category + 'Form');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await fetch('../api/site-settings.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'save_settings',
                        ...data
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Settings saved successfully!', 'success');
                    if (category === 'colors') {
                        // Reload to apply color changes
                        setTimeout(() => location.reload(), 1000);
                    }
                } else {
                    showNotification(result.error || 'Failed to save settings', 'error');
                }
            } catch (error) {
                showNotification('Error saving settings: ' + error.message, 'error');
            }
        });
    }
});

// Preview colors
document.getElementById('previewColors')?.addEventListener('click', function() {
    const form = document.getElementById('colorsForm');
    const formData = new FormData(form);
    
    // Apply colors temporarily
    document.documentElement.style.setProperty('--color-primary', formData.get('color_primary'));
    document.documentElement.style.setProperty('--color-secondary', formData.get('color_secondary'));
    
    showNotification('Preview applied! Save to make permanent.', 'info');
});

// Reset color buttons
document.querySelectorAll('.btn-reset-color').forEach(btn => {
    btn.addEventListener('click', function() {
        const defaultColor = this.dataset.default;
        const colorInput = this.parentElement.querySelector('.color-input');
        const textInput = this.parentElement.querySelector('.color-text-input');
        
        colorInput.value = defaultColor;
        textInput.value = defaultColor;
    });
});
</script>
