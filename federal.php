<?php
/**
 * Federal Leave Assistant
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'includes/auth.php';

$auth = getAuth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$hasAccess = $auth->hasAccess();

$pageTitle = 'Federal Leave Assistant - HR Leave Assistant';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="styles.css?v=1.2">
    <link rel="icon" type="image/png" href="hrla_logo.png">

    <style>
        /* --- BRAND COLORS --- */
        :root {
            --hrla-blue: #0322D8;
            --hrla-dark-blue: #1800AD;
            --hrla-green: #3DB20B;
            --hrla-black: #000000;
        }

        /* Override button colors */
        .btn-primary {
            background-color: var(--hrla-blue) !important;
            border-color: var(--hrla-blue) !important;
            color: white !important;
        }
        .btn-primary:hover {
            background-color: var(--hrla-dark-blue) !important;
            border-color: var(--hrla-dark-blue) !important;
        }

        /* Large Logo Style */
        .nav-logo-large {
            max-height: 55px;
            width: auto;
            margin-right: 15px;
        }
        
        .nav-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: #333;
        }

        /* --- STANDARD PAGE SCROLL LAYOUT --- */
        html, body {
            height: 100%;
            margin: 0;
            background-color: #f3f4f6;
            /* Default scroll behavior allowed */
        }

        .page {
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Allow page to grow */
        }

        /* Nav */
        .app-nav {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.5rem 0;
        }

        /* Main Container */
        .tool-container {
            padding: 0 20px 40px 20px;
            max-width: 1400px; /* Constrain width for readability */
            margin: 0 auto;
            width: 100%;
            flex: 1;
        }

        .tool-header {
            padding: 40px 0 30px;
            text-align: center;
        }
        .tool-header h1 { font-size: 2rem; margin-bottom: 10px; color: var(--hrla-black); font-weight: 800; }
        .tool-header p { color: #6b7280; margin-bottom: 15px; font-size: 1.1rem; }
        .tool-warning { color: var(--hrla-blue); font-size: 0.95rem; font-weight: 600; }

        /* Workspace Grid */
        .tool-workspace {
            display: flex;
            gap: 30px;
            align-items: flex-start; /* Align tops */
        }

        /* PANELS */
        .input-panel, .output-panel {
            flex: 1;
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            min-height: 500px; /* Ensure they look substantial */
        }

        /* --- INPUT STYLES --- */
        .panel-header { margin-bottom: 15px; }
        .panel-header label { font-weight: 700; color: #111; font-size: 1rem; }

        /* Textarea grows automatically but has min-height */
        #federalInput {
            width: 100%;
            padding: 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            min-height: 150px; /* Good starting size */
            resize: vertical;  /* Allow user to stretch if needed */
            font-family: 'Inter', sans-serif;
        }

        /* Follow-up Section */
        .followup-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #f3f4f6;
        }
        
        #federalFollowup {
            width: 100%;
            padding: 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            min-height: 100px;
            resize: vertical;
            font-family: 'Inter', sans-serif;
            margin-top: 10px;
        }

        .followup-actions, .panel-actions {
            margin-top: 20px;
            text-align: right;
        }

        /* --- OUTPUT STYLES --- */
        .output-actions { float: right; }
        .output-actions .btn { font-size: 0.85rem; padding: 6px 12px; }

        /* Response Box */
        .response-output {
            background-color: #f9fafb;
            border-radius: 8px;
            border: 1px solid #f3f4f6;
            padding: 20px;
            font-size: 1rem;
            line-height: 1.7;
            color: #1f2937;
            min-height: 300px; /* Ensures box is visible */
            flex: 1; /* Fills available space in panel */
            
            /* Logic: If text is huge, scroll internally. 
               If page height allows, just grow. */
            max-height: 800px; 
            overflow-y: auto;
        }

        /* Scrollbar Styling */
        .response-output::-webkit-scrollbar { width: 8px; }
        .response-output::-webkit-scrollbar-track { background: #f1f1f1; }
        .response-output::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }

        /* Responsive */
        @media (max-width: 992px) {
            .tool-workspace { flex-direction: column; }
            .input-panel, .output-panel { min-height: auto; }
        }
    </style>
</head>
<body>
    <div id="federalPage" class="page">
        <nav class="app-nav">
            <div class="nav-container">
                <div class="nav-brand">
                    <a href="<?php echo appUrl('dashboard.php'); ?>" class="btn btn-ghost">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <img src="federal_logo.png" alt="Federal Leave Assistant" class="nav-logo-large">
                    <span class="nav-title">Federal Leave Assistant</span>
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
                <h1>Federal Leave Assistant</h1>
                <p>Generate professional, compliant responses to employee's FMLA leave questions</p>
                <div class="tool-warning">
                    <i class="fas fa-shield-alt"></i>
                    <span>Do not enter SSNs, medical records, DOBs, or sensitive personal data</span>
                </div>
            </div>
            
            <div class="tool-workspace">
                <div class="input-panel">
                    <div class="panel-header">
                        <label id="federalInputLabel">Copy and Paste Employee Email or type questions below</label>
                    </div>
                    
                    <textarea id="federalInput"></textarea>
                    
                    <div id="federalFollowupSection" class="followup-section" style="display: none;">
                        <div class="followup-header">
                            <label>Enter follow-up Question (optional)</label>
                        </div>
                        <textarea id="federalFollowup" placeholder="Enter follow-up questions here..."></textarea>
                        <div class="followup-actions">
                            <button id="federalFollowupSubmit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                                Submit Follow Up
                            </button>
                        </div>
                    </div>
                    
                    <div class="panel-actions">
                        <button id="federalSubmit" class="btn btn-primary">
                            <i class="fas fa-magic"></i>
                            Generate Response
                        </button>
                    </div>
                </div>
                
                <div class="output-panel">
                    <div class="panel-header">
                        <label>HRLA Generated Response:</label>
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
        const federalInput = document.getElementById('federalInput');
        const federalOutput = document.getElementById('federalOutput');
        const federalSubmit = document.getElementById('federalSubmit');
        const federalCopy = document.getElementById('federalCopy');
        const federalRegenerate = document.getElementById('federalRegenerate');
        const federalFollowupSection = document.getElementById('federalFollowupSection');
        const federalFollowup = document.getElementById('federalFollowup');
        const federalFollowupSubmit = document.getElementById('federalFollowupSubmit');

        federalSubmit.addEventListener('click', async function() {
            const input = federalInput.value.trim();
            if (!input) { alert('Please enter a question or email to analyze.'); return; }

            federalSubmit.disabled = true;
            federalSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            federalOutput.innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin"></i> Analyzing your request...</p>';

            try {
                const response = await fetch('<?php echo appUrl('api/ai.php'); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tool_name: 'federal', input_text: input })
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

        federalCopy.addEventListener('click', function() {
            const text = federalOutput.innerText;
            navigator.clipboard.writeText(text).then(() => {
                const originalText = federalCopy.innerHTML;
                federalCopy.innerHTML = '<i class="fas fa-check"></i> Copied!';
                setTimeout(() => { federalCopy.innerHTML = originalText; }, 2000);
            });
        });

        federalRegenerate.addEventListener('click', function() { federalSubmit.click(); });

        federalFollowupSubmit.addEventListener('click', async function() {
            const followup = federalFollowup.value.trim();
            if (!followup) { alert('Please enter a follow-up question.'); return; }

            federalFollowupSubmit.disabled = true;
            federalFollowupSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            federalOutput.innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin"></i> Processing follow-up...</p>';

            try {
                const response = await fetch('<?php echo appUrl('api/ai.php'); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tool_name: 'federal', input_text: federalInput.value + '\n\nFollow-up: ' + followup })
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
                federalFollowupSubmit.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Follow Up';
            }
        });
    </script>
</body>
</html>