# Server Setup Guide - Namecheap cPanel
## HR Leave Assistant - Complete Setup Instructions

---

## Step 1: Create MySQL Database

1. **Log into cPanel**
2. **Go to MySQL® Databases**
3. **Create a new database:**
   - Database Name: `hrla_database` (or your choice)
   - Click "Create Database"
   - Note the full database name (usually: `username_hrla_database`)

4. **Create a database user:**
   - Username: `hrla_user` (or your choice)
   - Password: Generate a strong password
   - Click "Create User"
   - **SAVE THESE CREDENTIALS!**

5. **Add user to database:**
   - Select the user you just created
   - Select the database you just created
   - Check "ALL PRIVILEGES"
   - Click "Make Changes"

---

## Step 2: Create config/local.php File

1. **Go to File Manager in cPanel**
2. **Navigate to:** `/home/hrledkhw/public_html/config/`
3. **Create a new file:** `local.php`
4. **Add this content** (replace with your actual values):

```php
<?php
/**
 * Local Server Configuration
 * DO NOT commit this file to git!
 */

// Default admin password
define('DEFAULT_ADMIN_PASSWORD', 'Password@123');

// SMTP Email Password
define('SMTP_PASSWORD_LOCAL', 'your-smtp-password-here');

// Database Configuration (if different from defaults)
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_NAME'] = 'hrledkhw_hrla_database';  // Your full database name
$_ENV['DB_USER'] = 'hrledkhw_hrla_user';      // Your database username
$_ENV['DB_PASS'] = 'your-database-password';   // Your database password

// SMTP Configuration (if different from defaults)
// $_ENV['SMTP_HOST'] = 'mail.hrleaveassist.com';
// $_ENV['SMTP_PORT'] = 587;
// $_ENV['SMTP_USERNAME'] = 'askhrla@hrleaveassist.com';
// $_ENV['SMTP_PASSWORD'] = 'your-smtp-password';
?>
```

5. **Set file permissions:**
   - Right-click on `local.php`
   - Click "Change Permissions"
   - Set to `644` (Owner: Read+Write, Group: Read, World: Read)

---

## Step 3: Initialize Database

1. **Visit your website:** `https://www.hrleaveassist.com/init-database.php`
2. **You should see:** "Database initialized successfully!"
3. **If you see errors:**
   - Check that `config/local.php` has correct database credentials
   - Check that database user has all privileges
   - Check PHP error logs in cPanel

---

## Step 4: Verify Installation

1. **Visit homepage:** `https://www.hrleaveassist.com/`
2. **You should see:** The homepage with logo and features
3. **Test login:**
   - Go to: `https://www.hrleaveassist.com/login.php`
   - Email: `talk2char@gmail.com`
   - Password: `Password@123`
4. **You should be able to log in to the admin dashboard**

---

## Step 5: Security - Delete or Rename init-database.php

After successful initialization:

1. **Go to File Manager**
2. **Navigate to:** `/home/hrledkhw/public_html/`
3. **Either:**
   - Delete `init-database.php`, OR
   - Rename it to `init-database.php.bak`

This prevents unauthorized database reinitialization.

---

## Step 6: Set Up Automatic Deployments (Optional)

### Option A: Manual Deployment (Recommended)
1. Go to cPanel → Git Version Control
2. Click "Manage" on hrla-app repository
3. Click "Update from Remote" to pull latest changes
4. Click "Deploy HEAD Commit" to deploy to public_html

### Option B: Webhook Auto-Deployment (Advanced)
See `DEPLOYMENT_GUIDE.md` for webhook setup instructions.

---

## Troubleshooting

### HTTP 500 Error
**Cause:** PHP error, usually database connection issue

**Solutions:**
1. Check `config/local.php` exists and has correct credentials
2. Check database name includes your cPanel username prefix
3. Check PHP error logs in cPanel (Errors section)
4. Enable error display temporarily by adding to `config/local.php`:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

### Database Connection Failed
**Cause:** Wrong credentials or database doesn't exist

**Solutions:**
1. Verify database name in cPanel MySQL Databases
2. Verify user has privileges on the database
3. Check `config/local.php` has correct values
4. Try connecting via phpMyAdmin to test credentials

### "Table doesn't exist" Error
**Cause:** Database not initialized

**Solution:**
1. Visit: `https://www.hrleaveassist.com/init-database.php`
2. This will create all tables automatically

### Can't Login to Admin
**Cause:** Database not initialized or wrong password

**Solutions:**
1. Make sure you ran `init-database.php`
2. Default admin email: `talk2char@gmail.com`
3. Default admin password: `Password@123` (or what you set in `config/local.php`)
4. Check `users` table in phpMyAdmin to verify admin exists

### Email Not Sending
**Cause:** SMTP credentials not configured

**Solution:**
1. Add SMTP password to `config/local.php`:
   ```php
   define('SMTP_PASSWORD_LOCAL', 'your-actual-smtp-password');
   ```
2. Verify SMTP settings in cPanel Email Accounts

### Files Not Updating After Git Deploy
**Cause:** Browser cache or deployment didn't run

**Solutions:**
1. Clear browser cache (Ctrl+Shift+R or Cmd+Shift+R)
2. Check deployment ran successfully in cPanel Git Version Control
3. Verify files updated in File Manager
4. Check `.cpanel.yml` file exists in repository

---

## Important File Locations

### On Server:
- **Repository:** `/home/hrledkhw/repositories/hrla-app/`
- **Live Site:** `/home/hrledkhw/public_html/`
- **Config:** `/home/hrledkhw/public_html/config/local.php`
- **Logs:** `/home/hrledkhw/public_html/logs/`

### Configuration Files:
- `config/local.php` - Server-specific settings (NOT in git)
- `config/app.php` - Application settings
- `config/database.php` - Database class and table definitions
- `.cpanel.yml` - Deployment configuration

---

## Default Admin Credentials

**Email:** `talk2char@gmail.com`  
**Password:** `Password@123`

**⚠️ IMPORTANT:** Change the admin password after first login!

1. Log in to admin dashboard
2. Go to Settings (or create a password change feature)
3. Update to a secure password

---

## Database Tables Created

The `init-database.php` script creates these tables:
- `users` - User accounts and authentication
- `access_codes` - Extended access codes
- `api_config` - OpenAI API configuration
- `ai_instructions` - Custom AI instructions per tool
- `user_sessions` - Active user sessions
- `conversations` - AI conversation history
- `transactions` - Payment transactions
- `pending_verifications` - Email verification queue
- `system_logs` - Application logs

---

## Quick Reference

### Update Site from GitHub:
```
1. Make changes locally
2. Run update-repo.bat
3. Go to cPanel → Git Version Control
4. Click "Update from Remote"
5. Click "Deploy HEAD Commit"
```

### Check PHP Errors:
```
cPanel → Errors → View last 300 errors
```

### Access Database:
```
cPanel → phpMyAdmin → Select your database
```

### View Application Logs:
```
File Manager → public_html/logs/ → View today's log file
```

---

## Support

If you encounter issues:
1. Check PHP error logs in cPanel
2. Check application logs in `/logs/` folder
3. Verify `config/local.php` settings
4. Test database connection via phpMyAdmin
5. Clear browser cache and try again

---

## Next Steps After Setup

1. ✅ Change admin password
2. ✅ Add OpenAI API key in admin dashboard
3. ✅ Configure SMTP email settings
4. ✅ Test registration and email verification
5. ✅ Test AI assistant tools (Federal & California)
6. ✅ Set up Stripe payment integration
7. ✅ Create access codes for extended trials
8. ✅ Test subscription flow

---

## Production Checklist

- [ ] Database created and configured
- [ ] `config/local.php` created with correct credentials
- [ ] Database initialized via `init-database.php`
- [ ] `init-database.php` deleted or renamed
- [ ] Admin login working
- [ ] Homepage loading correctly
- [ ] SSL certificate active (HTTPS)
- [ ] Email sending working
- [ ] OpenAI API key configured
- [ ] Stripe keys configured (if using payments)
- [ ] Git deployment working
- [ ] Error logs being written
- [ ] Admin password changed from default

---

**Your site should now be live and fully functional!**
