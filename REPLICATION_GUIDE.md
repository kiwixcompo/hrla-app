# Implementing Email Verification & Password Reset in a PHP MVC Application

This guide walks through implementing two authentication features from scratch:
- Email verification via a 6-digit code sent on registration
- Password reset via a secure tokenised link

The implementation assumes a custom PHP MVC framework with a router, base controller, PDO-based database layer, and environment variable configuration. Adapt the patterns to your framework's conventions.

---

## Overview of the Flow

**Email Verification:**
1. User registers → system generates a 6-digit code, stores it with an expiry, sends it via email
2. User submits the code → system validates it, marks the account as verified, starts the session

**Password Reset:**
1. User submits their email → system generates a 64-character hex token, stores it with a 1-hour expiry, emails a reset link
2. User clicks the link → system validates the token hasn't expired, presents the reset form
3. User submits a new password → system hashes it, updates the record, clears the token

---

## Step 1: Database Schema

Your `users` table needs the following columns. Add them via migration if the table already exists.

```sql
-- For a new table
CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(255) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,

    -- Email verification
    verification_code     VARCHAR(10)  DEFAULT NULL,
    verification_expires  DATETIME     DEFAULT NULL,
    email_verified        TINYINT(1)   DEFAULT 0,

    -- Password reset
    reset_token           VARCHAR(100) DEFAULT NULL,
    reset_token_expires   DATETIME     DEFAULT NULL,  -- name this carefully, see note below

    -- Account state
    account_status  ENUM('pending','active','suspended') DEFAULT 'pending',
    role            VARCHAR(50) DEFAULT 'user',

    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login  TIMESTAMP NULL,

    KEY idx_verification_code (verification_code),
    KEY idx_reset_token (reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Or, if altering an existing table
ALTER TABLE users
    ADD COLUMN verification_code    VARCHAR(10)  DEFAULT NULL,
    ADD COLUMN verification_expires DATETIME     DEFAULT NULL,
    ADD COLUMN email_verified       TINYINT(1)   DEFAULT 0,
    ADD COLUMN reset_token          VARCHAR(100) DEFAULT NULL,
    ADD COLUMN reset_token_expires  DATETIME     DEFAULT NULL,
    ADD COLUMN account_status       ENUM('pending','active','suspended') DEFAULT 'pending';
```

> Be deliberate with your column names and stay consistent throughout the codebase. A mismatch between what your model writes and what the database column is actually called will produce a fatal SQL error at runtime.

---

## Step 2: Mail Configuration

Create a `config/mail.php` that reads from environment variables. Never hardcode credentials.

```php
<?php
return [
    'mailers' => [
        'smtp' => [
            'host'       => $_ENV['MAIL_HOST']       ?? 'localhost',
            'port'       => $_ENV['MAIL_PORT']       ?? 587,
            'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
            'username'   => $_ENV['MAIL_USERNAME']   ?? null,
            'password'   => $_ENV['MAIL_PASSWORD']   ?? null,
            'timeout'    => 60,
        ],
    ],
    'from' => [
        'address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@yourdomain.com',
        'name'    => $_ENV['MAIL_FROM_NAME']    ?? 'Your App',
    ],
];
```

Add the corresponding values to your `.env`:

```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your-sending-address@yourdomain.com
MAIL_PASSWORD=your-smtp-app-password
MAIL_FROM_ADDRESS=your-sending-address@yourdomain.com
MAIL_FROM_NAME="Your App Name"

APP_URL=https://yourdomain.com
```

If you're using Google Workspace or Gmail, generate an App Password rather than using your account password directly.

---

## Step 3: Install PHPMailer

```bash
composer require phpmailer/phpmailer
```

PHPMailer gives you reliable SMTP sending with proper TLS handling. The native `mail()` function is acceptable as a fallback for development but should not be your primary sending mechanism in production.

---

## Step 4: Build the Email Helper

Create a dedicated class responsible for all outbound email. Keep transport logic, template rendering, and debug utilities in one place.

Key responsibilities:
- Configure PHPMailer from your mail config on instantiation
- Expose `sendVerificationEmail(string $email, string $code): bool`
- Expose `sendPasswordResetEmail(string $email, string $token): bool`
- Fall back to `mail()` if PHPMailer is unavailable or misconfigured
- In non-production environments, write outbound emails to disk for inspection

```php
<?php

namespace App\Helpers;

class EmailHelper
{
    private array $config;
    private ?\PHPMailer\PHPMailer\PHPMailer $mailer;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/mail.php';
        $this->setupMailer();
    }

    private function setupMailer(): void
    {
        if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            $this->mailer = null;
            return;
        }

        $smtp = $this->config['mailers']['smtp'];

        if (empty($smtp['host']) || empty($smtp['username']) || empty($smtp['password'])) {
            $this->mailer = null;
            return;
        }

        try {
            $this->mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
            $this->mailer->isSMTP();
            $this->mailer->Host       = $smtp['host'];
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = $smtp['username'];
            $this->mailer->Password   = $smtp['password'];
            $this->mailer->SMTPSecure = $smtp['port'] == 587
                ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS
                : $smtp['encryption'];
            $this->mailer->Port       = $smtp['port'];
            $this->mailer->CharSet    = 'UTF-8';
            $this->mailer->Timeout    = $smtp['timeout'];
            $this->mailer->setFrom(
                $this->config['from']['address'],
                $this->config['from']['name']
            );
            $this->mailer->isHTML(true);
        } catch (\Exception $e) {
            error_log('PHPMailer setup failed: ' . $e->getMessage());
            $this->mailer = null;
        }
    }

    public function sendVerificationEmail(string $email, string $code): bool
    {
        $subject = 'Verify Your Account';
        $body    = $this->buildVerificationBody($code);

        // Persist the code to a debug file so you can retrieve it without
        // needing a working mail server during development
        $this->writeDebugFile('verification_codes.txt', "$email | $code");

        return $this->dispatch($email, $subject, $body);
    }

    public function sendPasswordResetEmail(string $email, string $token): bool
    {
        $resetUrl = rtrim($_ENV['APP_URL'] ?? 'https://yourdomain.com', '/')
                  . '/reset-password?token=' . $token;

        $subject = 'Reset Your Password';
        $body    = $this->buildPasswordResetBody($resetUrl);

        $this->writeDebugFile('reset_links.txt', "$email | $resetUrl");

        return $this->dispatch($email, $subject, $body);
    }

    private function dispatch(string $to, string $subject, string $body): bool
    {
        // Persist the full HTML to disk for development inspection
        $this->saveEmailToDisk($to, $subject, $body);

        if ($this->mailer !== null) {
            try {
                $this->mailer->clearAddresses();
                $this->mailer->addAddress($to);
                $this->mailer->Subject = $subject;
                $this->mailer->Body    = $body;
                $this->mailer->AltBody = strip_tags($body);
                $result = $this->mailer->send();
                error_log("Email sent via SMTP to: $to");
                return $result;
            } catch (\Exception $e) {
                error_log('PHPMailer send failed: ' . $e->getMessage());
                // Fall through to mail() fallback
            }
        }

        return $this->sendViaNativeMailFunction($to, $subject, $body);
    }

    private function sendViaNativeMailFunction(string $to, string $subject, string $body): bool
    {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->config['from']['name']} <{$this->config['from']['address']}>\r\n";
        return @mail($to, $subject, $body, $headers);
    }

    private function writeDebugFile(string $filename, string $line): void
    {
        try {
            $path = __DIR__ . '/../../public/' . $filename;
            file_put_contents($path, date('Y-m-d H:i:s') . ' | ' . $line . "\n", FILE_APPEND | LOCK_EX);
        } catch (\Exception $e) {
            error_log('Could not write debug file: ' . $e->getMessage());
        }
    }

    private function saveEmailToDisk(string $to, string $subject, string $body): void
    {
        try {
            $dir = __DIR__ . '/../../storage/emails';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $filename = $dir . '/' . date('Y-m-d_H-i-s') . '_' . md5($to . $subject) . '.html';
            file_put_contents($filename, $body);
        } catch (\Exception $e) {
            error_log('Could not save email to disk: ' . $e->getMessage());
        }
    }

    private function buildVerificationBody(string $code): string
    {
        // Return your HTML email template here.
        // At minimum, prominently display $code and state the expiry window.
        return "
        <html><body>
            <h2>Verify Your Account</h2>
            <p>Your verification code is:</p>
            <h1 style='letter-spacing:8px;'>{$code}</h1>
            <p>This code expires in 24 hours.</p>
        </body></html>";
    }

    private function buildPasswordResetBody(string $resetUrl): string
    {
        // Return your HTML email template here.
        // The reset URL must be a clickable link and also shown as plain text.
        return "
        <html><body>
            <h2>Reset Your Password</h2>
            <p><a href='{$resetUrl}'>Click here to reset your password</a></p>
            <p>This link expires in 1 hour.</p>
            <p>If you did not request this, ignore this email.</p>
        </body></html>";
    }
}
```

---

## Step 5: User Model Methods

Add the following methods to your User model. These are the only database operations the auth flow needs.

```php
// Store a reset token against a user record
public function setPasswordResetToken(int $userId, string $token, string $expires): bool
{
    return $this->db->update(
        'users',
        ['reset_token' => $token, 'reset_token_expires' => $expires],
        'id = ?',
        [$userId]
    ) > 0;
}

// Retrieve a user by token only if the token has not expired
public function findByResetToken(string $token): ?array
{
    return $this->db->fetch(
        "SELECT * FROM users WHERE reset_token = ? AND reset_token_expires > NOW()",
        [$token]
    );
}

// Nullify the token columns after a successful reset
public function clearPasswordResetToken(int $userId): bool
{
    return $this->db->update(
        'users',
        ['reset_token' => null, 'reset_token_expires' => null],
        'id = ?',
        [$userId]
    ) > 0;
}

// Write the new bcrypt hash
public function updatePassword(int $userId, string $hashedPassword): bool
{
    return $this->db->update(
        'users',
        ['password_hash' => $hashedPassword],
        'id = ?',
        [$userId]
    ) > 0;
}

// Rotate the verification code (used by resend)
public function updateVerificationCode(int $userId, string $code, string $expires): bool
{
    return $this->db->update(
        'users',
        ['verification_code' => $code, 'verification_expires' => $expires],
        'id = ?',
        [$userId]
    ) > 0;
}

// Mark the account as verified and clear the code
public function verifyEmail(int $userId): bool
{
    return $this->db->update(
        'users',
        [
            'email_verified'       => 1,
            'verification_code'    => null,
            'verification_expires' => null,
        ],
        'id = ?',
        [$userId]
    ) > 0;
}

// Look up an unverified user by their code (used as a fallback lookup)
public function findByVerificationCode(string $code): ?array
{
    return $this->db->fetch(
        "SELECT * FROM users WHERE verification_code = ? AND email_verified = 0",
        [$code]
    );
}
```

---

## Step 6: Controller Methods

Instantiate `EmailHelper` in your `AuthController` constructor. Then implement the following actions.

### Registration

On a valid registration submission:
1. Generate a 6-digit code: `sprintf('%06d', mt_rand(100000, 999999))`
2. Set expiry to 24 hours from now: `date('Y-m-d H:i:s', strtotime('+24 hours'))`
3. Persist the user record with the code and expiry
4. Call `$this->emailHelper->sendVerificationEmail($email, $code)` **before** returning any response
5. Store the new user's ID in `$_SESSION['pending_verification']`
6. Return a JSON success response with a redirect to the verification page

### Email Verification (POST handler)

1. Read the submitted 6-digit code
2. Resolve the user — first from `$_SESSION['pending_verification']`, then from a posted email field, then by looking up the code directly
3. Check `$user['email_verified']` — if already true, return an appropriate error
4. Compare `$user['verification_code'] === $code` — mismatch returns an error
5. Check `strtotime($user['verification_expires']) < time()` — expired returns an error
6. Call `$this->userModel->verifyEmail($userId)`
7. Set `$_SESSION['user_id']`, unset `$_SESSION['pending_verification']`
8. Return success with a redirect

### Resend Verification

1. Verify CSRF
2. Read `$_SESSION['pending_verification']`
3. Generate a fresh code and expiry
4. Call `updateVerificationCode()` then `sendVerificationEmail()`
5. Return success

### Forgot Password (POST handler)

1. Validate the submitted email
2. Look up the user — do this silently; always return the same success message regardless of whether the email exists (prevents enumeration)
3. If the user exists and is verified:
   - Generate token: `bin2hex(random_bytes(32))` — produces a 64-character hex string
   - Set expiry to 1 hour: `date('Y-m-d H:i:s', strtotime('+1 hour'))`
   - Call `setPasswordResetToken()`
   - Call `sendPasswordResetEmail()` — **do this before returning the response**
4. Return the generic success message

### Show Reset Password (GET handler)

1. Read `token` from the query string
2. Call `findByResetToken($token)` — this query checks expiry via `reset_token_expires > NOW()`
3. If no result, render an "expired link" view
4. Otherwise render the reset form, passing the token into a hidden field

### Reset Password (POST handler)

1. Verify CSRF
2. Read `token`, `password`, `password_confirmation` from POST
3. Validate password length and confirmation match
4. Call `findByResetToken($token)` again — the token must still be valid at submission time
5. Hash the password: `password_hash($password, PASSWORD_DEFAULT)`
6. Call `updatePassword()` then `clearPasswordResetToken()`
7. Return success with a redirect to login

---

## Step 7: Routes

Register these routes in your router. GET routes render views; POST routes handle submissions.

```php
// Email verification
$router->get('/verify-email',          'AuthController@showVerifyEmail');
$router->post('/verify-email',         'AuthController@verifyEmail');
$router->post('/resend-verification',  'AuthController@resendVerification');

// Password reset
$router->get('/forgot-password',   'AuthController@showForgotPassword');
$router->post('/forgot-password',  'AuthController@forgotPassword');
$router->get('/reset-password',    'AuthController@showResetPassword');
$router->post('/reset-password',   'AuthController@resetPassword');
```

---

## Step 8: Views

You need four views:

- `verify-email` — six individual single-character inputs that combine into a hidden field; handle paste events; auto-advance focus; disable submit until all six digits are filled
- `forgot-password` — single email input, submits via fetch, shows a generic success message
- `reset-password` — two password inputs with a strength indicator, submits via fetch, redirects to login on success
- `reset-password-expired` — static page informing the user the link has expired with a link back to forgot-password

All forms must include a CSRF token in a hidden field. Submit via `fetch` and handle the JSON response to show inline feedback without a full page reload.

---

## Step 9: CSRF Handling

Your base controller needs two methods:

```php
protected function generateCSRFToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

protected function verifyCSRFToken(): bool
{
    // Check POST field
    $token = $_POST['csrf_token'] ?? null;

    // Check JSON body
    if (!$token) {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $body  = json_decode(file_get_contents('php://input'), true);
            $token = $body['csrf_token'] ?? null;
        }
    }

    // Check request header (for fetch() calls that set X-CSRF-Token)
    if (!$token) {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    }

    return isset($_SESSION['csrf_token'])
        && !empty($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}
```

Checking all three locations means the same token works for traditional form posts, fetch with `FormData`, and fetch with a JSON body.

---

## Step 10: Required Directory Structure

```
public/
    verification_codes.txt   ← auto-created by EmailHelper (debug)
    reset_links.txt          ← auto-created by EmailHelper (debug)
storage/
    emails/                  ← auto-created by EmailHelper (debug HTML copies)
```

The `EmailHelper` creates these automatically, but the parent directories must be writable by the web server process. In production you can remove or restrict access to these files once you've confirmed SMTP is working reliably.

---

## Common Pitfalls

**Token column name mismatch**
If your model writes to `reset_token_expires` but your database column is named something else (e.g. `reset_expires`), you'll get a fatal `Unknown column` SQL error. Define the column name once and reference it consistently everywhere.

**Sending email after returning a response**
`json()` or any method that calls `exit` will terminate execution immediately. Always send the email before returning the response, not after.

**CSRF token not available**
`session_start()` must be called before any controller runs. If your bootstrap or front controller doesn't start the session early, `$_SESSION['csrf_token']` will never be set and every CSRF check will fail.

**Token expiry not enforced at submission**
Validate the token in both the GET handler (to decide whether to show the form) and the POST handler (to decide whether to process the submission). A user could load the form with a valid token and then wait until it expires before submitting.

**Not clearing the token after use**
Call `clearPasswordResetToken()` immediately after a successful password update. A token that remains in the database after use is a security vulnerability.

---

## Verification Checklist

- [ ] `users` table has all six new columns
- [ ] PHPMailer installed (`vendor/autoload.php` includes it)
- [ ] `.env` has all `MAIL_*` values set
- [ ] `config/mail.php` reads from `$_ENV`
- [ ] `EmailHelper` instantiated in `AuthController` constructor
- [ ] Verification email sent before `json()` is called in `register()`
- [ ] Reset email sent before `json()` is called in `forgotPassword()`
- [ ] `findByResetToken()` query includes `AND reset_token_expires > NOW()`
- [ ] `clearPasswordResetToken()` called after successful password update
- [ ] All four auth views exist and include CSRF hidden fields
- [ ] All eight routes registered
- [ ] `session_start()` fires before any controller runs
- [ ] `storage/emails/` and `public/` are writable
