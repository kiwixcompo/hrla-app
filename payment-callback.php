<?php
/**
 * Payment Callback Handler
 * Processes payment confirmations from PayPal/Stripe
 */

require_once 'config/app.php';
require_once 'includes/auth.php';

$auth = getAuth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
$db = getDB();

// Get payment details from URL parameters
$paymentMethod = $_GET['method'] ?? '';
$status = $_GET['status'] ?? '';
$amount = $_GET['amount'] ?? 0;
$plan = $_GET['plan'] ?? '';
$transactionId = $_GET['transaction_id'] ?? '';

$success = false;
$message = '';

if ($status === 'success' && $paymentMethod && $amount > 0) {
    try {
        // Calculate subscription period based on plan
        $subscriptionDays = 30; // Default monthly
        $accessLevel = 'subscribed';
        
        switch ($plan) {
            case 'monthly':
                $subscriptionDays = 30;
                break;
            case 'annual':
                $subscriptionDays = 365;
                break;
            case 'organization':
                $subscriptionDays = 30;
                $accessLevel = 'organization';
                break;
        }
        
        // Update user subscription
        $expiryDate = date('Y-m-d H:i:s', strtotime("+{$subscriptionDays} days"));
        
        $sql = "UPDATE users 
                SET access_level = ?, 
                    subscription_expiry = ?,
                    updated_at = NOW()
                WHERE id = ?";
        
        $db->query($sql, [$accessLevel, $expiryDate, $user['id']]);
        
        // Record transaction
        $sql = "INSERT INTO transactions (user_id, amount, currency, payment_method, paypal_order_id, status, subscription_period, created_at) 
                VALUES (?, ?, 'USD', ?, ?, 'completed', ?, NOW())";
        
        $db->query($sql, [$user['id'], $amount, $paymentMethod, $transactionId, $subscriptionDays]);
        
        $success = true;
        $message = 'Payment successful! Your subscription has been activated.';
        
    } catch (Exception $e) {
        $message = 'Error processing payment: ' . $e->getMessage();
        logMessage("Payment processing error: " . $e->getMessage(), 'error', [
            'user_id' => $user['id'],
            'payment_method' => $paymentMethod,
            'amount' => $amount
        ]);
    }
} else {
    $message = 'Payment was cancelled or failed.';
}

$pageTitle = $success ? 'Payment Successful' : 'Payment Failed';
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
    <link rel="stylesheet" href="styles.css?v=1.3">
    <link rel="icon" type="image/png" href="hrla_logo.png">
</head>
<body>
    <div class="payment-result-page">
        <div class="payment-result-container">
            <?php if ($success): ?>
                <div class="payment-result success">
                    <div class="result-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h1>Payment Successful!</h1>
                    <p><?php echo htmlspecialchars($message); ?></p>
                    <div class="result-details">
                        <p><strong>Plan:</strong> <?php echo ucfirst($plan); ?></p>
                        <p><strong>Amount:</strong> $<?php echo number_format($amount, 2); ?></p>
                        <p><strong>Valid Until:</strong> <?php echo date('F j, Y', strtotime($expiryDate)); ?></p>
                    </div>
                    <p class="closing-message">This window will close automatically...</p>
                </div>
            <?php else: ?>
                <div class="payment-result error">
                    <div class="result-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h1>Payment Failed</h1>
                    <p><?php echo htmlspecialchars($message); ?></p>
                    <p class="closing-message">This window will close automatically...</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Notify parent window of payment result
        if (window.opener) {
            window.opener.postMessage({
                type: 'payment_result',
                success: <?php echo $success ? 'true' : 'false'; ?>,
                message: <?php echo json_encode($message); ?>
            }, '*');
        }

        // Close window after 3 seconds
        setTimeout(() => {
            window.close();
        }, 3000);
    </script>
</body>
</html>
