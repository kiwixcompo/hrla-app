<?php
/**
 * Federal FMLA Assistant
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'includes/auth.php';

$auth = getAuth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$hasAccess = $auth->hasAccess();

$pageTitle = 'Federal FMLA Assistant - HR Leave Assistant';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="styles.css?v=1.2">
    <link rel="icon" type="image/png" href="hrla_logo.png">
</head>
<body>
    <!-- Federal Tool Page -->
    <div id="federalPage" class="page">
        <nav class="app-nav">
            <div class="nav-container">
                <div class="nav-brand">
                    <a href="<?php echo appUrl('dashboard.php'); ?>" class="btn btn-ghost">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <img src="hrla_logo.png" alt="HRLA" class="nav-logo">
                    <span class="nav-title">Federal FMLA Assistant</span>
                </div>
                <div class="nav-menu">
                    <a href="<?php echo appUrl('settings.php'); ?>" class="btn btn-ghost">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                    <a href="<?php echo appUrl('logout.php'); ?>" class="btn btn-ghost">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </nav>
        
        <div class="tool-container">
            <div class="tool-header">
                <h1>Federal FMLA Assistant</h1>
                <p>Generate professional, compliant responses for Federal FMLA requests</p>
                <div class="tool-warning">
                    <i class="fas fa-shield-alt"></i>
                    <span>Do not enter SSNs, medical records, DOBs, or sensitive personal data</span>
                </div>
            </div>
            
            <div class="tool-workspace">
                <div class="input-panel">
                    <div class="panel-header">
                        <label id="federalInputLabel">Enter your Quick Question or Email:</label>
                    </div>
                    <textarea id="federalInput" placeholder="Enter your Quick Question or paste the employee's email below to get started."></textarea>
                    
                    <!-- Follow-up Section (initially hidden) -->
                    <div id="federalFollowupSection" class="followup-section" style="display: none;">
                        <div class="followup-header">
                            <label>Follow-up Question or Additional Information:</label>
                        </div>
                        <textarea id="federalFollowup" placeholder="Add any follow-up questions or additional context here..."></textarea>
                        <div class="followup-actions">
                            <button id="federalFollowupSubmit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                                Submit Follow-up
                            </button>
                        </div>
                    </div>
                    
                    <div class="panel-actions">
                        <button id="federalClear" class="btn btn-outline">
                            <i class="fas fa-trash"></i>
                            Clear
                        </button>
                        <button id="federalSubmit" class="btn btn-primary">
                            <i class="fas fa-magic"></i>
                            Generate Response
                        </button>
                    </div>
                </div>
                
                <div class="output-panel">
                    <div class="panel-header">
                        <label>AI Generated Response:</label>
                        <div class="output-actions">
                            <button id="federalRegenerate" class="btn btn-secondary" style="display: none;">
                                <i class="fas fa-redo"></i>
                                Regenerate
                            </button>
                            <button id="federalCopy" class="btn btn-secondary">
                                <i class="fas fa-copy"></i>
                                Copy
                            </button>
                        </div>
                    </div>
                    <div id="federalOutput" class="response-output">
                        <p style="color: #9ca3af; text-align: center; padding: 2rem;">
                            Your AI-generated response will appear here...
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Federal FMLA Assistant functionality
        const federalInput = document.getElementById('federalInput');
        const federalOutput = document.getElementById('federalOutput');
        const federalSubmit = document.getElementById('federalSubmit');
        const federalClear = document.getElementById('federalClear');
        const federalCopy = document.getElementById('federalCopy');
        const federalRegenerate = document.getElementById('federalRegenerate');
        const federalFollowupSection = document.getElementById('federalFollowupSection');
        const federalFollowup = document.getElementById('federalFollowup');
        const federalFollowupSubmit = document.getElementById('federalFollowupSubmit');

        // Generate Response
        federalSubmit.addEventListener('click', async function() {
            const input = federalInput.value.trim();
            
            if (!input) {
                alert('Please enter a question or email to analyze.');
                return;
            }

            // Show loading state
            federalSubmit.disabled = true;
            federalSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            federalOutput.innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin"></i> Analyzing your request...</p>';

            try {
                const response = await fetch('<?php echo appUrl('api/ai.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        tool_name: 'federal',
                        input_text: input
                    })
                });

                const data = await response.json();

                if (data.success) {
                    federalOutput.innerHTML = data.response;
                    federalRegenerate.style.display = 'inline-flex';
                    federalFollowupSection.style.display = 'block';
                } else {
                    federalOutput.innerHTML = `<p style="color: #ef4444; padding: 1rem;">Error: ${data.error || 'Failed to generate response'}</p>`;
                }
            } catch (error) {
                federalOutput.innerHTML = `<p style="color: #ef4444; padding: 1rem;">Error: ${error.message}</p>`;
            } finally {
                federalSubmit.disabled = false;
                federalSubmit.innerHTML = '<i class="fas fa-magic"></i> Generate Response';
            }
        });

        // Clear
        federalClear.addEventListener('click', function() {
            federalInput.value = '';
            federalOutput.innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 2rem;">Your AI-generated response will appear here...</p>';
            federalRegenerate.style.display = 'none';
            federalFollowupSection.style.display = 'none';
            federalFollowup.value = '';
        });

        // Copy to clipboard
        federalCopy.addEventListener('click', function() {
            const text = federalOutput.innerText;
            navigator.clipboard.writeText(text).then(() => {
                const originalText = federalCopy.innerHTML;
                federalCopy.innerHTML = '<i class="fas fa-check"></i> Copied!';
                setTimeout(() => {
                    federalCopy.innerHTML = originalText;
                }, 2000);
            });
        });

        // Regenerate
        federalRegenerate.addEventListener('click', function() {
            federalSubmit.click();
        });

        // Follow-up submit
        federalFollowupSubmit.addEventListener('click', async function() {
            const followup = federalFollowup.value.trim();
            
            if (!followup) {
                alert('Please enter a follow-up question.');
                return;
            }

            // Show loading state
            federalFollowupSubmit.disabled = true;
            federalFollowupSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            federalOutput.innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin"></i> Processing follow-up...</p>';

            try {
                const response = await fetch('<?php echo appUrl('api/ai.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        tool_name: 'federal',
                        input_text: federalInput.value + '\n\nFollow-up: ' + followup
                    })
                });

                const data = await response.json();

                if (data.success) {
                    federalOutput.innerHTML = data.response;
                    federalFollowup.value = '';
                } else {
                    federalOutput.innerHTML = `<p style="color: #ef4444; padding: 1rem;">Error: ${data.error || 'Failed to generate response'}</p>`;
                }
            } catch (error) {
                federalOutput.innerHTML = `<p style="color: #ef4444; padding: 1rem;">Error: ${error.message}</p>`;
            } finally {
                federalFollowupSubmit.disabled = false;
                federalFollowupSubmit.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Follow-up';
            }
        });
    </script>
</body>
</html>
