<?php
/**
 * Subscription/Upgrade Page
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once 'config/app.php';
require_once 'includes/auth.php';

$auth = getAuth();
$auth->requireAuth();

$user = $auth->getCurrentUser();

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
</head>
<body>
    <div id="subscription" class="page">
        <nav class="app-nav">
            <div class="nav-container">
                <div class="nav-brand">
                    <img src="hrla_logo.png" alt="HRLA" class="nav-logo">
                    <span class="nav-title">Upgrade Your Plan</span>
                </div>
                <div class="nav-menu">
                    <a href="<?php echo appUrl('dashboard.php'); ?>" class="btn btn-ghost">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back</span>
                    </a>
                    <a href="<?php echo appUrl('logout.php'); ?>" class="btn btn-ghost">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </nav>
        
        <div class="subscription-container">
            <div class="subscription-header">
                <h1>Choose Your Plan</h1>
                <p>Select the perfect plan for your HR compliance needs</p>
            </div>
            
            <!-- Pricing Cards -->
            <div class="pricing-grid">
                <!-- Monthly Plan -->
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Monthly</h3>
                        <div class="pricing-amount">
                            <span class="currency">$</span>
                            <span class="price">29</span>
                            <span class="period">/month</span>
                        </div>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> Federal FMLA Assistant</li>
                        <li><i class="fas fa-check"></i> California Leave Assistant</li>
                        <li><i class="fas fa-check"></i> Unlimited Requests</li>
                        <li><i class="fas fa-check"></i> AI-Powered Responses</li>
                        <li><i class="fas fa-check"></i> Email Support</li>
                    </ul>
                    <button class="btn btn-primary btn-block" onclick="selectPlan('monthly', 29)">
                        Subscribe Now
                    </button>
                </div>
                
                <!-- Annual Plan (Most Popular) -->
                <div class="pricing-card featured">
                    <div class="pricing-badge">Most Popular</div>
                    <div class="pricing-header">
                        <h3>Annual</h3>
                        <div class="pricing-amount">
                            <span class="currency">$</span>
                            <span class="price">290</span>
                            <span class="period">/year</span>
                        </div>
                        <div class="pricing-save">Save $58 (2 months free)</div>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> Federal FMLA Assistant</li>
                        <li><i class="fas fa-check"></i> California Leave Assistant</li>
                        <li><i class="fas fa-check"></i> Unlimited Requests</li>
                        <li><i class="fas fa-check"></i> AI-Powered Responses</li>
                        <li><i class="fas fa-check"></i> Priority Email Support</li>
                        <li><i class="fas fa-check"></i> 2 Months Free</li>
                    </ul>
                    <button class="btn btn-primary btn-block" onclick="selectPlan('annual', 290)">
                        Subscribe Now
                    </button>
                </div>
                
                <!-- Organization Plan -->
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Organization</h3>
                        <div class="pricing-amount">
                            <span class="currency">$</span>
                            <span class="price">99</span>
                            <span class="period">/month</span>
                        </div>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> Up to 10 Users</li>
                        <li><i class="fas fa-check"></i> Federal FMLA Assistant</li>
                        <li><i class="fas fa-check"></i> California Leave Assistant</li>
                        <li><i class="fas fa-check"></i> Unlimited Requests</li>
                        <li><i class="fas fa-check"></i> AI-Powered Responses</li>
                        <li><i class="fas fa-check"></i> Priority Support</li>
                        <li><i class="fas fa-check"></i> Team Management</li>
                    </ul>
                    <button class="btn btn-primary btn-block" onclick="selectPlan('organization', 99)">
                        Subscribe Now
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
                'monthly': 'Monthly Plan',
                'annual': 'Annual Plan',
                'organization': 'Organization Plan'
            };
            
            document.getElementById('selectedPlanName').textContent = planNames[plan];
            document.getElementById('selectedAmount').textContent = amount;
            document.getElementById('paymentModal').style.display = 'flex';
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }

        function payWithPayPal() {
            const returnUrl = encodeURIComponent(window.location.origin + '/leave_assistant/payment-callback.php?method=paypal&status=success&amount=' + selectedAmount + '&plan=' + selectedPlan + '&transaction_id=PAYPAL_' + Date.now());
            const cancelUrl = encodeURIComponent(window.location.origin + '/leave_assistant/payment-callback.php?method=paypal&status=cancelled');
            
            const paypalUrl = `https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=talk2char@gmail.com&amount=${selectedAmount}&currency_code=USD&item_name=HR Leave Assistant - ${selectedPlan} subscription&return=${returnUrl}&cancel_return=${cancelUrl}`;
            
            // Open payment in new window
            paymentWindow = window.open(paypalUrl, 'PayPal Payment', 'width=800,height=600,scrollbars=yes');
            
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
