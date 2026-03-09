@echo off
echo ========================================
echo Deploying Email Fix to Production
echo ========================================
echo.

echo Step 1: Adding files to git...
git add vendor/
git add includes/email_templates.php
git add includes/auth.php
git add register.php
git add api/auth.php
git add composer.json
git add install-phpmailer.php
git add test-email.php
git add test-resend-verification.php
git add deploy.php
git add *.txt
git add *.md

echo.
echo Step 2: Committing changes...
git commit -m "Fix: Add PHPMailer for SMTP email support and resend verification functionality"

echo.
echo Step 3: Pushing to GitHub...
git push origin main

echo.
echo ========================================
echo Deployment Complete!
echo ========================================
echo.
echo Next steps:
echo 1. Wait 30 seconds for GitHub to process
echo 2. Visit: https://www.hrleaveassist.com/deploy.php?manual=true
echo 3. After deployment, visit: https://www.hrleaveassist.com/install-phpmailer.php
echo 4. Then test: https://www.hrleaveassist.com/test-email.php
echo.
pause
