<?php
/**
 * Email Verification Page
 * Supports both token link (?token=...) and 6-digit code entry
 */

require_once 'config/app.php';
require_once 'includes/auth.php';

$auth = getAuth();

$tokenVerified = false;
$tokenError    = '';

// Handle token-link flow (fallback / email link click)
$token = $_GET['token'] ?? '';
if ($token) {
    $result = $auth->verifyEmail($token);
    if ($result['success']) {
        $tokenVerified = true;
        header("refresh:3;url=" . appUrl('login.php?verified=true'));
    } else {
        $tokenError = $result['error'];
    }
}

// Pre-fill email from query string (set by register.php redirect)
$prefillEmail = htmlspecialchars($_GET['email'] ?? '');

$pageTitle = 'Verify Your Email - HR Leave Assistant';
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
    <link rel="stylesheet" href="assets/css/main.css?v=<?php echo time(); ?>">
    <link rel="icon" type="image/png" href="hrla_logo.png">
    <style>
        .verify-card {
            max-width: 460px;
            margin: 60px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            padding: 40px 36px;
            text-align: center;
        }
        .verify-icon { font-size: 48px; margin-bottom: 12px; }
        .verify-card h2 { margin: 0 0 8px; font-size: 1.5rem; color: #1a1a2e; }
        .verify-card .subtitle { color: #666; margin-bottom: 28px; font-size: 0.95rem; }
        .code-inputs {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 24px 0 8px;
        }
        .code-inputs input {
            width: 48px;
            height: 58px;
            text-align: center;
            font-size: 1.6rem;
            font-weight: 700;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            outline: none;
            transition: border-color 0.2s;
            font-family: monospace;
            color: #0322D8;
        }
        .code-inputs input:focus { border-color: #0322D8; box-shadow: 0 0 0 3px rgba(3,34,216,0.12); }
        .code-inputs input.filled { border-color: #0322D8; background: #f0f4ff; }
        .email-field {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95rem;
            margin-bottom: 16px;
            box-sizing: border-box;
        }
        .btn-verify {
            width: 100%;
            padding: 13px;
            background: #0322D8;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 8px;
        }
        .btn-verify:hover { background: #0023F5; }
        .btn-verify:disabled { background: #9ca3af; cursor: not-allowed; }
        .msg { padding: 12px 16px; border-radius: 8px; margin: 16px 0; font-size: 0.9rem; text-align: left; }
        .msg-error { background: #fee2e2; color: #991b1b; border-left: 4px solid #dc2626; }
        .msg-success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
        .resend-row { margin-top: 20px; font-size: 0.88rem; color: #666; }
        .resend-row a, .resend-row button { color: #0322D8; background: none; border: none; cursor: pointer; font-size: 0.88rem; text-decoration: underline; padding: 0; }
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 24px 0; }
        .success-big { font-size: 56px; margin-bottom: 12px; }
        .countdown { color: #666; font-size: 0.88rem; margin-top: 12px; }
    </style>
</head>
<body style="background:#f8f9fa; font-family:'Inter',sans-serif;">

<?php if ($tokenVerified): ?>
    <!-- Token link verified successfully -->
    <div class="verify-card">
        <div class="success-big">✅</div>
        <h2>Email Verified!</h2>
        <p class="subtitle">Your account is now active. Redirecting you to sign in...</p>
        <p class="countdown">Redirecting in <span id="countdown">3</span> seconds...</p>
        <a href="<?php echo appUrl('login.php?verified=true'); ?>" class="btn-verify" style="display:inline-block;text-decoration:none;margin-top:16px;">
            Continue to Sign In
        </a>
    </div>
    <script>
        let s = 3;
        const el = document.getElementById('countdown');
        setInterval(() => { s--; el.textContent = s; if (s <= 0) location.href = '<?php echo appUrl('login.php?verified=true'); ?>'; }, 1000);
    </script>

<?php elseif ($tokenError): ?>
    <!-- Token link failed -->
    <div class="verify-card">
        <div class="verify-icon">❌</div>
        <h2>Link Invalid</h2>
        <div class="msg msg-error"><?php echo htmlspecialchars($tokenError); ?></div>
        <p style="color:#666;font-size:0.9rem;">Enter your 6-digit code below instead, or request a new one.</p>
        <hr class="divider">
        <?php include __DIR__ . '/includes/verify_code_form.php'; ?>
    </div>

<?php else: ?>
    <!-- Normal code entry page -->
    <div class="verify-card">
        <div class="verify-icon">📧</div>
        <h2>Check your email</h2>
        <p class="subtitle">We sent a 6-digit verification code to your email address. Enter it below to activate your account.</p>
        <?php include __DIR__ . '/includes/verify_code_form.php'; ?>
    </div>
<?php endif; ?>

</body>
</html>
