<?php
/**
 * Email Templates System
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once __DIR__ . '/../config/app.php';

class EmailTemplates {
    private $fromName;
    private $fromEmail;
    private $replyTo;
    
    public function __construct() {
        $this->fromName = config('email.from_name');
        $this->fromEmail = config('email.from_email');
        $this->replyTo = config('app.support_email');
    }
    
    /**
     * Send verification email
     */
    public function sendVerificationEmail($email, $firstName, $verificationLink, $accessCodeData = null) {
        $subject = '✅ Verify your HR Leave Assistant account - Start your access';
        
        $htmlContent = $this->getVerificationEmailHTML($firstName, $verificationLink, $accessCodeData);
        $textContent = $this->getVerificationEmailText($firstName, $verificationLink, $accessCodeData);
        
        return $this->sendEmail($email, $subject, $htmlContent, $textContent);
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($email, $firstName, $resetLink) {
        $subject = '🔒 Reset your HR Leave Assistant password';
        
        $htmlContent = $this->getPasswordResetEmailHTML($firstName, $resetLink);
        $textContent = $this->getPasswordResetEmailText($firstName, $resetLink);
        
        return $this->sendEmail($email, $subject, $htmlContent, $textContent);
    }
    
    /**
     * Send welcome email after verification
     */
    public function sendWelcomeEmail($email, $firstName) {
        $subject = '🎉 Welcome to HR Leave Assistant!';
        
        $htmlContent = $this->getWelcomeEmailHTML($firstName);
        $textContent = $this->getWelcomeEmailText($firstName);
        
        return $this->sendEmail($email, $subject, $htmlContent, $textContent);
    }
    
    /**
     * Send trial expiry warning
     */
    public function sendTrialExpiryWarning($email, $firstName, $hoursLeft) {
        $subject = '⏰ Your HR Leave Assistant trial expires soon';
        
        $htmlContent = $this->getTrialExpiryWarningHTML($firstName, $hoursLeft);
        $textContent = $this->getTrialExpiryWarningText($firstName, $hoursLeft);
        
        return $this->sendEmail($email, $subject, $htmlContent, $textContent);
    }
    
    /**
     * Send subscription confirmation
     */
    public function sendSubscriptionConfirmation($email, $firstName, $plan, $amount) {
        $subject = '✅ Subscription confirmed - HR Leave Assistant';
        
        $htmlContent = $this->getSubscriptionConfirmationHTML($firstName, $plan, $amount);
        $textContent = $this->getSubscriptionConfirmationText($firstName, $plan, $amount);
        
        return $this->sendEmail($email, $subject, $htmlContent, $textContent);
    }
    
    // HTML Email Templates
    
    private function getVerificationEmailHTML($firstName, $verificationLink, $accessCodeData) {
        $currentYear = date('Y');
        $accessCodeSection = '';
        
        if ($accessCodeData) {
            $accessCodeSection = '<p style="background: #e8f5e8; padding: 15px; border-radius: 5px; border-left: 4px solid #4CAF50;"><strong>🎫 Access Code Applied:</strong> Your account will have ' . $accessCodeData['duration'] . ' ' . $accessCodeData['duration_type'] . ' of extended access once verified.</p>';
        } else {
            $accessCodeSection = '<p style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;"><strong>🆓 Free Trial:</strong> Your account includes a 24-hour free trial to explore all features.</p>';
        }
        
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Verify Your HR Leave Assistant Account</title>
            <style>
                body { 
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333333; 
                    margin: 0; 
                    padding: 0; 
                    background-color: #f8f9fa;
                }
                .container { 
                    max-width: 600px; 
                    margin: 20px auto; 
                    padding: 0; 
                    background-color: #ffffff;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .header { 
                    background: linear-gradient(135deg, #0023F5 0%, #0322D8 100%); 
                    color: white; 
                    padding: 30px 20px; 
                    text-align: center; 
                    border-radius: 8px 8px 0 0; 
                }
                .content { 
                    background: #ffffff; 
                    padding: 30px 20px; 
                    border-radius: 0 0 8px 8px; 
                }
                .button { 
                    display: inline-block; 
                    background: #0023F5; 
                    color: white !important; 
                    padding: 15px 30px; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    margin: 20px 0; 
                    font-weight: bold;
                    font-size: 16px;
                    text-align: center;
                }
                .footer { 
                    text-align: center; 
                    margin-top: 30px; 
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                    font-size: 14px; 
                    color: #666666; 
                }
                .logo { 
                    font-size: 24px; 
                    font-weight: bold; 
                    margin-bottom: 10px;
                }
                .support { 
                    background: #f8f9fa; 
                    padding: 20px; 
                    border-radius: 5px; 
                    margin-top: 20px;
                    border-left: 4px solid #0023F5;
                }
                .verification-code {
                    background: #e3f2fd;
                    padding: 15px;
                    border-radius: 5px;
                    font-family: monospace;
                    font-size: 14px;
                    word-break: break-all;
                    margin: 15px 0;
                }
                .company-info {
                    margin-top: 20px;
                    padding: 15px;
                    background: #f8f9fa;
                    border-radius: 5px;
                    font-size: 12px;
                    color: #666;
                }
                a { color: #0023F5; }
                .preheader { display: none !important; visibility: hidden; opacity: 0; color: transparent; height: 0; width: 0; }
            </style>
        </head>
        <body>
            <div class='preheader'>Complete your HR Leave Assistant registration by verifying your email address. This helps us ensure account security.</div>
            <div class='container'>
                <div class='header'>
                    <div class='logo'>HR Leave Assistant</div>
                    <p style='margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;'>hrleaveassist.com</p>
                </div>
                <div class='content'>
                    <h2 style='color: #333; margin-top: 0;'>Account Verification Required</h2>
                    <p>Hello $firstName,</p>
                    <p>Thank you for creating your HR Leave Assistant account. To ensure the security of your account and complete your registration, please verify your email address.</p>
                    
                    $accessCodeSection
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$verificationLink' class='button' style='color: white;'>Verify Email Address</a>
                    </div>
                    
                    <p><strong>Alternative verification method:</strong></p>
                    <p>If the button above doesn't work, copy and paste this link into your web browser:</p>
                    <div class='verification-code'>$verificationLink</div>
                    
                    <div class='support'>
                        <p style='margin: 0 0 10px 0;'><strong>Need assistance?</strong></p>
                        <p style='margin: 0;'>Our support team is here to help. Contact us at <a href='mailto:{$this->replyTo}'>{$this->replyTo}</a> for any questions about your account or our services.</p>
                    </div>
                </div>
                <div class='footer'>
                    <p style='margin: 0;'>This email was sent by HR Leave Assistant</p>
                    <p style='margin: 5px 0;'><a href='" . appUrl() . "'>" . config('app.url') . "</a> | <a href='mailto:{$this->replyTo}'>{$this->replyTo}</a></p>
                    <p style='margin: 10px 0 0 0; font-size: 12px;'>© $currentYear HR Leave Assistant. All rights reserved.</p>
                    <p style='margin: 5px 0 0 0; font-size: 11px;'>
                        If you did not create this account, please ignore this email or contact us at {$this->replyTo}
                    </p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    private function getVerificationEmailText($firstName, $verificationLink, $accessCodeData) {
        $currentYear = date('Y');
        $accessCodeText = '';
        
        if ($accessCodeData) {
            $accessCodeText = "Access Code Applied: Your account will have {$accessCodeData['duration']} {$accessCodeData['duration_type']} of extended access once verified.";
        } else {
            $accessCodeText = "Free Trial: Your account includes a 24-hour free trial to explore all features.";
        }
        
        return "
HR Leave Assistant - Account Verification Required

Hello $firstName,

Thank you for creating your HR Leave Assistant account. To ensure the security of your account and complete your registration, please verify your email address.

$accessCodeText

VERIFICATION LINK:
$verificationLink

ALTERNATIVE METHOD:
Copy and paste the link above into your web browser to verify your account.

NEED ASSISTANCE?
Our support team is here to help. Contact us at {$this->replyTo} for any questions about your account or our services.

---
This email was sent by HR Leave Assistant
Website: " . config('app.url') . "
Support: {$this->replyTo}

© $currentYear HR Leave Assistant. All rights reserved.

If you did not create this account, please ignore this email or contact us at {$this->replyTo}
        ";
    }
    
    private function getPasswordResetEmailHTML($firstName, $resetLink) {
        $currentYear = date('Y');
        
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Reset Your HR Leave Assistant Password</title>
            <style>
                body { 
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333333; 
                    margin: 0; 
                    padding: 0; 
                    background-color: #f8f9fa;
                }
                .container { 
                    max-width: 600px; 
                    margin: 20px auto; 
                    padding: 0; 
                    background-color: #ffffff;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .header { 
                    background: linear-gradient(135deg, #0023F5 0%, #0322D8 100%); 
                    color: white; 
                    padding: 30px 20px; 
                    text-align: center; 
                    border-radius: 8px 8px 0 0; 
                }
                .content { 
                    background: #ffffff; 
                    padding: 30px 20px; 
                    border-radius: 0 0 8px 8px; 
                }
                .button { 
                    display: inline-block; 
                    background: #0023F5; 
                    color: white !important; 
                    padding: 15px 30px; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    margin: 20px 0; 
                    font-weight: bold;
                    font-size: 16px;
                    text-align: center;
                }
                .footer { 
                    text-align: center; 
                    margin-top: 30px; 
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                    font-size: 14px; 
                    color: #666666; 
                }
                .logo { 
                    font-size: 24px; 
                    font-weight: bold; 
                    margin-bottom: 10px;
                }
                .support { 
                    background: #f8f9fa; 
                    padding: 20px; 
                    border-radius: 5px; 
                    margin-top: 20px;
                    border-left: 4px solid #0023F5;
                }
                .reset-code {
                    background: #e3f2fd;
                    padding: 15px;
                    border-radius: 5px;
                    font-family: monospace;
                    font-size: 14px;
                    word-break: break-all;
                    margin: 15px 0;
                }
                .warning {
                    background: #fff3cd;
                    padding: 15px;
                    border-radius: 5px;
                    border-left: 4px solid #ffc107;
                    margin: 20px 0;
                }
                a { color: #0023F5; }
                .preheader { display: none !important; visibility: hidden; opacity: 0; color: transparent; height: 0; width: 0; }
            </style>
        </head>
        <body>
            <div class='preheader'>Reset your HR Leave Assistant password using the secure link provided in this email.</div>
            <div class='container'>
                <div class='header'>
                    <div class='logo'>HR Leave Assistant</div>
                    <p style='margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;'>hrleaveassist.com</p>
                </div>
                <div class='content'>
                    <h2 style='color: #333; margin-top: 0;'>🔒 Password Reset Request</h2>
                    <p>Hello $firstName,</p>
                    <p>We received a request to reset the password for your HR Leave Assistant account. If you made this request, click the button below to create a new password.</p>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$resetLink' class='button' style='color: white;'>Reset My Password</a>
                    </div>
                    
                    <div class='warning'>
                        <p style='margin: 0 0 10px 0;'><strong>⏰ Important Security Information:</strong></p>
                        <ul style='margin: 0; padding-left: 20px;'>
                            <li>This password reset link will expire in 1 hour for security</li>
                            <li>You can only use this link once</li>
                            <li>If you didn't request this reset, please ignore this email</li>
                        </ul>
                    </div>
                    
                    <p><strong>Alternative reset method:</strong></p>
                    <p>If the button above doesn't work, copy and paste this link into your web browser:</p>
                    <div class='reset-code'>$resetLink</div>
                    
                    <div class='support'>
                        <p style='margin: 0 0 10px 0;'><strong>Need assistance?</strong></p>
                        <p style='margin: 0;'>If you're having trouble resetting your password or didn't request this change, contact our support team at <a href='mailto:{$this->replyTo}'>{$this->replyTo}</a></p>
                    </div>
                </div>
                <div class='footer'>
                    <p style='margin: 0;'>This email was sent by HR Leave Assistant</p>
                    <p style='margin: 5px 0;'><a href='" . appUrl() . "'>" . config('app.url') . "</a> | <a href='mailto:{$this->replyTo}'>{$this->replyTo}</a></p>
                    <p style='margin: 10px 0 0 0; font-size: 12px;'>© $currentYear HR Leave Assistant. All rights reserved.</p>
                    <p style='margin: 5px 0 0 0; font-size: 11px;'>
                        If you did not request this password reset, please ignore this email or contact us at {$this->replyTo}
                    </p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    private function getPasswordResetEmailText($firstName, $resetLink) {
        $currentYear = date('Y');
        
        return "
HR Leave Assistant - Password Reset Request

Hello $firstName,

We received a request to reset the password for your HR Leave Assistant account. If you made this request, use the link below to create a new password.

PASSWORD RESET LINK:
$resetLink

IMPORTANT SECURITY INFORMATION:
- This password reset link will expire in 1 hour for security
- You can only use this link once
- If you didn't request this reset, please ignore this email

ALTERNATIVE METHOD:
Copy and paste the link above into your web browser to reset your password.

NEED ASSISTANCE?
If you're having trouble resetting your password or didn't request this change, contact our support team at {$this->replyTo}

---
This email was sent by HR Leave Assistant
Website: " . config('app.url') . "
Support: {$this->replyTo}

© $currentYear HR Leave Assistant. All rights reserved.

If you did not request this password reset, please ignore this email or contact us at {$this->replyTo}
        ";
    }
    
    /**
     * Send email using PHP mail or SMTP
     */
    private function sendEmail($to, $subject, $htmlContent, $textContent) {
        try {
            // Use PHPMailer if available, otherwise fall back to mail()
            if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                return $this->sendEmailSMTP($to, $subject, $htmlContent, $textContent);
            } else {
                return $this->sendEmailNative($to, $subject, $htmlContent, $textContent);
            }
        } catch (Exception $e) {
            logMessage("Email sending failed: " . $e->getMessage(), 'error', [
                'to' => $to,
                'subject' => $subject
            ]);
            
            // Log email to console for development
            $this->logEmailToConsole($to, $subject, $textContent);
            
            return false;
        }
    }
    
    /**
     * Send email using native PHP mail()
     */
    private function sendEmailNative($to, $subject, $htmlContent, $textContent) {
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            "From: {$this->fromName} <{$this->fromEmail}>",
            "Reply-To: {$this->replyTo}",
            "Return-Path: {$this->fromEmail}",
            'X-Mailer: HR Leave Assistant v' . APP_VERSION,
            'X-Priority: 3',
            'X-MSMail-Priority: Normal',
            'Importance: Normal',
            "List-Unsubscribe: <mailto:{$this->replyTo}?subject=Unsubscribe>",
            'X-Auto-Response-Suppress: OOF, DR, RN, NRN, AutoReply',
            'Message-ID: <' . time() . '.' . uniqid() . '@hrleaveassist.com>',
            'Date: ' . date('r'),
            'Content-Language: en-US',
            'Organization: HR Leave Assistant',
            "X-Sender: {$this->fromEmail}",
            'X-Originating-IP: [' . ($_SERVER['SERVER_ADDR'] ?? '127.0.0.1') . ']',
            'X-Source-Dir: hrleaveassist.com'
        ];
        
        $headerString = implode("\r\n", $headers);
        
        $sent = mail($to, $subject, $htmlContent, $headerString);
        
        if ($sent) {
            logMessage("Email sent successfully", 'info', [
                'to' => $to,
                'subject' => $subject,
                'method' => 'native_mail'
            ]);
        } else {
            logMessage("Email sending failed", 'error', [
                'to' => $to,
                'subject' => $subject,
                'method' => 'native_mail'
            ]);
            
            // Log to console for debugging
            $this->logEmailToConsole($to, $subject, $textContent);
        }
        
        return $sent;
    }
    
    /**
     * Log email to console for development
     */
    private function logEmailToConsole($to, $subject, $textContent) {
        $logEntry = "\n" . str_repeat('🟢', 40) . "\n";
        $logEntry .= "📧 EMAIL VERIFICATION REQUIRED - DEVELOPMENT MODE\n";
        $logEntry .= str_repeat('🟢', 40) . "\n";
        $logEntry .= "📧 To: $to\n";
        $logEntry .= "📧 Subject: $subject\n";
        $logEntry .= "\n📋 EMAIL CONTENT:\n";
        $logEntry .= $textContent . "\n";
        
        // Extract verification link
        if (preg_match('/https?:\/\/[^\s]+/', $textContent, $matches)) {
            $logEntry .= "\n" . str_repeat('🔗', 40) . "\n";
            $logEntry .= "🔗 VERIFICATION LINK (COPY THIS):\n";
            $logEntry .= str_repeat('🔗', 40) . "\n";
            $logEntry .= $matches[0] . "\n";
            $logEntry .= str_repeat('🔗', 40) . "\n";
            $logEntry .= "\n✅ TESTING INSTRUCTIONS:\n";
            $logEntry .= "1. Copy the verification link above\n";
            $logEntry .= "2. Open it in your browser\n";
            $logEntry .= "3. Your account will be verified automatically\n";
            $logEntry .= "\n💡 In production, this would be sent as a real email\n";
        }
        
        $logEntry .= str_repeat('🟢', 40) . "\n\n";
        
        error_log($logEntry);
    }
}
?>