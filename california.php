<?php
/**
 * California Leave Assistant
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'includes/auth.php';

$auth = getAuth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$hasAccess = $auth->hasAccess();

$pageTitle = 'California Leave Assistant - HR Leave Assistant';
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
    <!-- California Tool Page -->
    <div id="californiaPage" class="page">
        <nav class="app-nav">
            <div class="nav-container">
                <div class="nav-brand">
                    <a href="<?php echo appUrl('dashboard.php'); ?>" class="btn btn-ghost">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <img src="hrla_logo.png" alt="HRLA" class="nav-logo">
                    <span class="nav-title">California Compliance</span>
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
                <h1>California Leave Assistant</h1>
                <p>Navigate CFRA, PDL, and FMLA interactions for California employees</p>
                <div class="tool-warning">
                    <i class="fas fa-shield-alt"></i>
                    <span>Do not enter SSNs, medical records, DOBs, or sensitive personal data</span>
                </div>
            </div>
            
            <div class="tool-workspace">
                <div class="input-panel">
                    <div class="panel-header">
                        <label id="californiaInputLabel">Enter your Quick Question or Email:</label>
                    </div>
                    <textarea id="californiaInput" placeholder="Enter your Quick Question or paste the employee's email below to get started."></textarea>
                    
                    <!-- Follow-up Section (initially hidden) -->
                    <div id="californiaFollowupSection" class="followup-section" style="display: none;">
                        <div class="followup-header">
                            <label>Follow-up Question or Additional Information:</label>
                        </div>
                        <textarea id="californiaFollowup" placeholder="Add any follow-up questions or additional context here..."></textarea>
                        <div class="followup-actions">
                            <button id="californiaFollowupSubmit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                                Submit Follow-up
                            </button>
                        </div>
                    </div>
                    
                    <div class="panel-actions">
                        <button id="californiaClear" class="btn btn-outline">
                            <i class="fas fa-trash"></i>
                            Clear
                        </button>
                        <button id="californiaSubmit" class="btn btn-primary">
                            <i class="fas fa-magic"></i>
                            Generate Response
                        </button>
                    </div>
                </div>
                
                <div class="output-panel">
                    <div class="panel-header">
                        <label>AI Generated Response:</label>
                        <div class="output-actions">
                            <button id="californiaRegenerate" class="btn btn-secondary" style="display: none;">
                                <i class="fas fa-redo"></i>
                                Regenerate
                            </button>
                            <button id="californiaCopy" class="btn btn-secondary">
                                <i class="fas fa-copy"></i>
                                Copy
                            </button>
                        </div>
                    </div>
                    <div id="californiaOutput" class="response-output">
                        <p style="color: #9ca3af; text-align: center; padding: 2rem;">
                            Your AI-generated response will appear here...
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // California Leave Assistant functionality
        const californiaInput = document.getElementById('californiaInput');
        const californiaOutput = document.getElementById('californiaOutput');
        const californiaSubmit = document.getElementById('californiaSubmit');
        const californiaClear = document.getElementById('californiaClear');
        const californiaCopy = document.getElementById('californiaCopy');
        const californiaRegenerate = document.getElementById('californiaRegenerate');
        const californiaFollowupSection = document.getElementById('californiaFollowupSection');
        const californiaFollowup = document.getElementById('californiaFollowup');
        const californiaFollowupSubmit = document.getElementById('californiaFollowupSubmit');

        // Generate Response
        californiaSubmit.addEventListener('click', async function() {
            const input = californiaInput.value.trim();
            
            if (!input) {
                alert('Please enter a question or email to analyze.');
                return;
            }

            // Show loading state
            californiaSubmit.disabled = true;
            californiaSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            californiaOutput.innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin"></i> Analyzing your request...</p>';

            try {
                const response = await fetch('<?php echo appUrl('api/ai.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        tool_name: 'california',
                        input_text: input
                    })
                });

                const data = await response.json();

                if (data.success) {
                    californiaOutput.innerHTML = data.response;
                    californiaRegenerate.style.display = 'inline-flex';
                    californiaFollowupSection.style.display = 'block';
                } else {
                    californiaOutput.innerHTML = `<p style="color: #ef4444; padding: 1rem;">Error: ${data.error || 'Failed to generate response'}</p>`;
                }
            } catch (error) {
                californiaOutput.innerHTML = `<p style="color: #ef4444; padding: 1rem;">Error: ${error.message}</p>`;
            } finally {
                californiaSubmit.disabled = false;
                californiaSubmit.innerHTML = '<i class="fas fa-magic"></i> Generate Response';
            }
        });

        // Clear
        californiaClear.addEventListener('click', function() {
            californiaInput.value = '';
            californiaOutput.innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 2rem;">Your AI-generated response will appear here...</p>';
            californiaRegenerate.style.display = 'none';
            californiaFollowupSection.style.display = 'none';
            californiaFollowup.value = '';
        });

        // Copy to clipboard
        californiaCopy.addEventListener('click', function() {
            const text = californiaOutput.innerText;
            navigator.clipboard.writeText(text).then(() => {
                const originalText = californiaCopy.innerHTML;
                californiaCopy.innerHTML = '<i class="fas fa-check"></i> Copied!';
                setTimeout(() => {
                    californiaCopy.innerHTML = originalText;
                }, 2000);
            });
        });

        // Regenerate
        californiaRegenerate.addEventListener('click', function() {
            californiaSubmit.click();
        });

        // Follow-up submit
        californiaFollowupSubmit.addEventListener('click', async function() {
            const followup = californiaFollowup.value.trim();
            
            if (!followup) {
                alert('Please enter a follow-up question.');
                return;
            }

            // Show loading state
            californiaFollowupSubmit.disabled = true;
            californiaFollowupSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            californiaOutput.innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin"></i> Processing follow-up...</p>';

            try {
                const response = await fetch('<?php echo appUrl('api/ai.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        tool_name: 'california',
                        input_text: californiaInput.value + '\n\nFollow-up: ' + followup
                    })
                });

                const data = await response.json();

                if (data.success) {
                    californiaOutput.innerHTML = data.response;
                    californiaFollowup.value = '';
                } else {
                    californiaOutput.innerHTML = `<p style="color: #ef4444; padding: 1rem;">Error: ${data.error || 'Failed to generate response'}</p>`;
                }
            } catch (error) {
                californiaOutput.innerHTML = `<p style="color: #ef4444; padding: 1rem;">Error: ${error.message}</p>`;
            } finally {
                californiaFollowupSubmit.disabled = false;
                californiaFollowupSubmit.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Follow-up';
            }
        });
    </script>
</body>
</html>
