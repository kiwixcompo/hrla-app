<?php
/**
 * Subscription/Upgrade Page
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'includes/auth.php';
require_once 'includes/content.php';

// Initialize content system
initContentSystem();

$auth = getAuth();
$auth->requireAuth();

$user = $auth->getCurrentUser();

// Check if user is coming from expired access
$isExpired = isset($_GET['expired']) && $_GET['expired'] == '1';

$pageTitle = 'Upgrade - HR Leave Assistant';
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
    
    <link rel="stylesheet" href="styles.css?v=1.3">
    <link rel="stylesheet" href="mobile-responsive.css?v=1.0">
    <link rel="icon" type="image/png" href="hrla_logo.png">
    
    <style>
        body {
            background: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        
        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .subscription-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
            width: 100%;
            display: block;
        }
        
        /* Expiration Notice Styling */
        .expiration-notice {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            padding: 50px 40px;
            border-radius: 16px;
            text-align: center;
            margin-bottom: 50px;
            box-shadow: 0 8px 30px rgba(255, 107, 107, 0.25);
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
            width: 100%;
            display: block;
            clear: both;
        }
        
        .expiration-notice .notice-icon {
            font-size: 4rem;
            margin-bottom: 25px;
            opacity: 0.95;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .expiration-notice h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .expiration-notice p {
            font-size: 1.2rem;
            margin: 0;
            opacity: 0.95;
            line-height: 1.6;
        }
        
        /* Subscription Header */
        .subscription-header {
            text-align: center;
            margin-bottom: 50px;
            width: 100%;
            display: block;
            clear: both;
        }
        
        .subscription-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #111;
            margin-bottom: 15px;
        }
        
        .subscription-header p {
            font-size: 1.2rem;
            color: #666;
        }
        
        /* Pricing Grid */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .pricing-card {
            background: white;
            border-radius: 16px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 2px solid #e9ecef;
            position: relative;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .pricing-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        
        .pricing-card.featured {
            border-color: #0322D8;
            box-shadow: 0 8px 30px rgba(3, 34, 216, 0.15);
        }
        
        .pricing-badge {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: #0322D8;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .pricing-header h3 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #111;
            margin-bottom: 15px;
            margin-top: 10px;
        }
        
        .pricing-description {
            margin-bottom: 25px;
            min-height: 60px;
        }
        
        .pricing-description p {
            color: #666;
            font-size: 1rem;
            line-height: 1.5;
            margin: 0;
        }
        
        .pricing-features {
            flex-grow: 1;
            margin-bottom: 25px;
        }
        
        .pricing-features ul {
            list-style: none;
            padding: 0;
            text-align: left;
            margin: 0;
        }
        
        .pricing-features li {
            padding: 10px 0;
            color: #333;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .pricing-best-for {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: left;
            font-size: 0.9rem;
            line-height: 1.6;
            color: #555;
        }
        
        .pricing-best-for strong {
            color: #111;
            display: block;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        .btn-block {
            width: 100%;
            padding: 15px 30px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #0322D8;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1800AD;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(3, 34, 216, 0.3);
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .subscription-container {
                padding: 30px 15px;
            }
            
            .expiration-notice {
                padding: 35px 25px;
                margin-bottom: 40px;
            }
            
            .expiration-notice .notice-icon {
                font-size: 3rem;
                margin-bottom: 20px;
            }
            
            .expiration-notice h2 {
                font-size: 1.8rem;
                margin-bottom: 15px;
            }
            
            .expiration-notice p {
                font-size: 1rem;
            }
            
            .subscription-header h1 {
                font-size: 2rem;
            }
            
            .subscription-header p {
                font-size: 1rem;
            }
            
            .pricing-grid {
                grid-template-columns: 1fr;
                gap: 25px;
                padding: 0 10px;
            }
            
            .pricing-card {
                padding: 30px 20px;
            }
            
            .pricing-card.featured {
                transform: none;
            }
            
            .pricing-card:hover {
                transform: none;
            }
        }
    </style>
</head>
<body>
    <div id="subscription" class="page">
        <nav class="app-nav">
            <div class="nav-container">
                <div class="nav-brand">
                    <img src="subscription_logo.png" alt="HRLA Subscription" class="nav-logo">
                </div>
                <div class="nav-menu">
                    <?php if (!$isExpired): ?>
                    <a href="<?php echo appUrl('dashboard.php'); ?>" class="btn btn-ghost">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back</span>
                    </a>
                    <?php endif; ?>
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
        
        <div class="subscription-container">
            <?php if ($isExpired): ?>
            <div class="expiration-notice">
                <div class="notice-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h2>Your Trial Has Expired</h2>
                <p>Your trial period has ended. To continue using HR Leave Assist, please select a subscription plan below.</p>
            </div>
            <?php endif; ?>
            
            <div class="subscription-header">
                <h1>Choose Your Plan</h1>
                <p>Select the perfect plan for your HR leave compliance needs</p>
            </div>
            
            <!-- Pricing Cards -->
            <div class="pricing-grid">
                <!-- Free Trial -->
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Free Trial — $0</h3>
                    </div>
                    <div class="pricing-description">
                        <p>Evaluate how HR Leave Assist supports your individual HR workflow</p>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li>• Trial access to HR Leave Assist</li>
                            <li>• Guidance aligned to laws</li>
                            <li>• AI-assisted draft responses</li>
                            <li>• Up to 20 questions in trial</li>
                            <li>• No payment required</li>
                        </ul>
                    </div>
                    <div class="pricing-best-for">
                        <strong>Best for:</strong><br>
                        HR professionals who want to test the tool with real-world scenarios.
                    </div>
                    <button class="btn btn-primary btn-block" onclick="selectPlan('trial', 0)">
                        Start Free Trial
                    </button>
                </div>
                
                <!-- Monthly Plan -->
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3><?php echo htmlspecialchars(getContent('pricing_monthly_title', 'Monthly — $29')); ?></h3>
                    </div>
                    <div class="pricing-description">
                        <p>For individual HR professionals managing ongoing leave questions</p>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li>• Full individual access</li>
                            <li>• Federal & California laws</li>
                            <li>• Draft clear responses</li>
                            <li>• Unlimited questions</li>
                            <li>• Cancel anytime</li>
                        </ul>
                    </div>
                    <div class="pricing-best-for">
                        <strong>Best for:</strong><br>
                        <?php echo htmlspecialchars(getContent('pricing_monthly_description', 'Individual HR professionals who regularly respond to employee leave inquiries.')); ?>
                    </div>
                    <button class="btn btn-primary btn-block" onclick="selectPlan('monthly', <?php 
                        $monthlyTitle = getContent('pricing_monthly_title', 'Monthly — $29');
                        preg_match('/\$(\d+)/', $monthlyTitle, $matches);
                        echo isset($matches[1]) ? $matches[1] : 29;
                    ?>)">
                        Subscribe Monthly
                    </button>
                </div>
                
                <!-- Annual Plan (Most Popular) -->
                <div class="pricing-card featured">
                    <div class="pricing-badge">Most Popular</div>
                    <div class="pricing-header">
                        <h3><?php echo htmlspecialchars(getContent('pricing_annual_title', 'Annual — $290')); ?></h3>
                        <div class="pricing-save"><?php echo htmlspecialchars(getContent('pricing_annual_subtitle', '(2 months free)')); ?></div>
                    </div>
                    <div class="pricing-description">
                        <p>Consistent, uninterrupted access for individual professional use</p>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li>• Everything in Monthly</li>
                            <li>• 12 months continuous access</li>
                            <li>• Unlimited questions</li>
                            <li>• Predictable annual billing</li>
                        </ul>
                    </div>
                    <div class="pricing-best-for">
                        <strong>Best for:</strong><br>
                        <?php echo htmlspecialchars(getContent('pricing_annual_description', 'Individual HR professionals who rely on HR Leave Assist as part of their regular, year-round workflow.')); ?>
                    </div>
                    <button class="btn btn-primary btn-block" onclick="selectPlan('annual', <?php 
                        $annualTitle = getContent('pricing_annual_title', 'Annual — $290');
                        preg_match('/\$(\d+)/', $annualTitle, $matches);
                        echo isset($matches[1]) ? $matches[1] : 290;
                    ?>)">
                        Subscribe Annually
                    </button>
                </div>
                
                <!-- Teams Plan -->
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Teams — $580 / yr</h3>
                    </div>
                    <div class="pricing-description">
                        <p>Shared annual access for up to 5 HR professionals</p>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li>• Up to <strong>5 named HR users</strong></li>
                            <li>• 12 months access per user</li>
                            <li>• Unlimited questions per user</li>
                            <li>• Centralized annual billing</li>
                        </ul>
                    </div>
                    <div class="pricing-best-for">
                        <strong>Best for:</strong><br>
                        <?php echo htmlspecialchars(getContent('pricing_org_description', 'Small HR teams of 2 to 5 who regularly respond to employee leave questions and want consistent, shared access.')); ?>
                    </div>
                    <button class="btn btn-primary btn-block" onclick="selectPlan('organization', <?php 
                        $teamsTitle = getContent('pricing_org_title', 'Teams — $580 / yr');
                        preg_match('/\$(\d+)/', $teamsTitle, $matches);
                        echo isset($matches[1]) ? $matches[1] : 580;
                    ?>)">
                        Subscribe Annually
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="modal modern-modal" style="display: none;">
        <div class="modal-content payment-modal-content">
            <button class="modal-close-btn" onclick="closePaymentModal()">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="payment-modal-body">
                <div class="payment-header">
                    <div class="payment-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h2>Complete Your Purchase</h2>
                    <p class="payment-subtitle">Choose your preferred payment method</p>
                </div>
                
                <div class="payment-summary">
                    <div class="summary-row">
                        <span class="summary-label">Plan</span>
                        <span class="summary-value" id="selectedPlanName"></span>
                    </div>
                    <div class="summary-row total">
                        <span class="summary-label">Total</span>
                        <span class="summary-value">$<span id="selectedAmount"></span></span>
                    </div>
                </div>
                
                <div class="payment-methods-modern">
                    <button class="payment-method-card paypal-card" onclick="payWithPayPal()">
                        <div class="payment-method-icon">
                            <i class="fab fa-paypal"></i>
                        </div>
                        <div class="payment-method-info">
                            <h4>PayPal</h4>
                            <p>Pay securely with your PayPal account</p>
                        </div>
                        <div class="payment-method-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </button>
                    
                    <button class="payment-method-card stripe-card" onclick="payWithStripe()">
                        <div class="payment-method-icon">
                            <i class="fab fa-stripe"></i>
                        </div>
                        <div class="payment-method-info">
                            <h4>Credit Card</h4>
                            <p>Pay with Stripe (Coming Soon)</p>
                        </div>
                        <div class="payment-method-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </button>
                </div>
                
                <div class="payment-security">
                    <i class="fas fa-shield-alt"></i>
                    <span>Secure 256-bit SSL encrypted payment</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Processing Overlay -->
    <div id="paymentProcessing" class="payment-processing-overlay" style="display: none;">
        <div class="processing-content">
            <div class="processing-spinner">
                <i class="fas fa-circle-notch fa-spin"></i>
            </div>
            <h3>Processing Payment...</h3>
            <p>Please complete the payment in the popup window</p>
            <button class="btn btn-secondary" onclick="cancelPayment()">Cancel</button>
        </div>
    </div>

    <script>
        let selectedPlan = null;
        let selectedAmount = 0;
        let paymentWindow = null;

        function selectPlan(plan, amount) {
            selectedPlan = plan;
            selectedAmount = amount;
            
            const planNames = {
                'trial': 'Free Trial',
                'monthly': 'Monthly Plan',
                'annual': 'Annual Plan',
                'organization': 'Teams Plan'
            };
            
            document.getElementById('selectedPlanName').textContent = planNames[plan];
            document.getElementById('selectedAmount').textContent = amount;
            document.getElementById('paymentModal').style.display = 'flex';
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }

        function payWithPayPal() {
            if (!selectedPlan || selectedAmount === 0) {
                alert('Please select a plan first');
                return;
            }
            
            // Use dynamic base URL instead of hardcoded path
            const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
            const returnUrl = encodeURIComponent(baseUrl + '/payment-callback.php?method=paypal&status=success&amount=' + selectedAmount + '&plan=' + selectedPlan + '&transaction_id=PAYPAL_' + Date.now());
            const cancelUrl = encodeURIComponent(baseUrl + '/payment-callback.php?method=paypal&status=cancelled');
            
            // Use PayPal's payment link with proper encoding
            const paypalUrl = `https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=talk2char@gmail.com&amount=${selectedAmount}&currency_code=USD&item_name=HR%20Leave%20Assistant%20-%20${selectedPlan}%20subscription&return=${returnUrl}&cancel_return=${cancelUrl}&no_shipping=1&no_note=1`;
            
            console.log('PayPal URL:', paypalUrl); // Debug log
            
            // Open payment in new window
            paymentWindow = window.open(paypalUrl, 'PayPal Payment', 'width=800,height=600,scrollbars=yes,resizable=yes');
            
            if (!paymentWindow) {
                alert('Please allow popups for this site to process payment');
                return;
            }
            
            // Show processing overlay
            closePaymentModal();
            document.getElementById('paymentProcessing').style.display = 'flex';
            
            // Listen for payment result
            window.addEventListener('message', handlePaymentResult);
            
            // Check if window is closed
            const checkWindow = setInterval(() => {
                if (paymentWindow && paymentWindow.closed) {
                    clearInterval(checkWindow);
                    document.getElementById('paymentProcessing').style.display = 'none';
                }
            }, 500);
        }

        function payWithStripe() {
            alert('Stripe integration is currently being worked on. Please use PayPal for now or contact support at askhrla@hrleaveassist.com');
        }

        function handlePaymentResult(event) {
            if (event.data.type === 'payment_result') {
                document.getElementById('paymentProcessing').style.display = 'none';
                
                if (event.data.success) {
                    // Show success message
                    showSuccessModal('Payment Successful!', event.data.message);
                    
                    // Redirect to dashboard after 2 seconds
                    setTimeout(() => {
                        window.location.href = '<?php echo appUrl('dashboard.php'); ?>';
                    }, 2000);
                } else {
                    alert('Payment failed: ' + event.data.message);
                }
                
                window.removeEventListener('message', handlePaymentResult);
            }
        }

        function cancelPayment() {
            if (paymentWindow && !paymentWindow.closed) {
                paymentWindow.close();
            }
            document.getElementById('paymentProcessing').style.display = 'none';
        }

        function showSuccessModal(title, message) {
            const modal = document.createElement('div');
            modal.className = 'success-modal-overlay';
            modal.innerHTML = `
                <div class="success-modal-content">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2>${title}</h2>
                    <p>${message}</p>
                    <p class="redirect-message">Redirecting to dashboard...</p>
                </div>
            `;
            document.body.appendChild(modal);
        }

        // Close modal when clicking outside
        document.getElementById('paymentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePaymentModal();
            }
        });
    </script>
</body>
</html>
