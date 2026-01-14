# Git Deployment Fix - Secret Scanning Issue

## Problem
GitHub detected hardcoded secrets (passwords) in your code and blocked the push.

## What Was Fixed
1. **Removed hardcoded password** from `config/app.php`
   - Changed: `'Password@123'` → `''` (empty, to be set via environment variable)

2. **Removed password display** from `test-connection.php`
   - No longer shows default password in test output

3. **Updated `.gitignore`** to exclude sensitive files
   - Added `config/database.php` and `config/secrets.php`

## How to Fix and Deploy

### Option 1: Use the Fix Script (Recommended)
1. Run `fix-git-secrets.bat`
2. Type `yes` when prompted
3. This will:
   - Remove the commit with secrets
   - Create a new clean commit
   - Force push to GitHub

### Option 2: Manual Fix
Run these commands in order:

```bash
# Reset the last commit (keeps your changes)
git reset --soft HEAD~1

# Add all changes
git add .

# Create new commit without secrets
git commit -m "Update application - removed hardcoded secrets"

# Force push to rewrite history
git push --force origin main
```

## After Successful Push

### Set Environment Variables in cPanel
1. Log into your cPanel
2. Go to "Select PHP Version" or "MultiPHP Manager"
3. Click "Switch To PHP Options" or "Options"
4. Add environment variable:
   - **Name**: `SMTP_PASSWORD`
   - **Value**: Your actual email password

### Alternative: Create a Local Config File
Create `config/secrets.php` (this file is gitignored):

```php
<?php
// Local configuration - NOT committed to git
define('SMTP_PASSWORD_LOCAL', 'your-actual-password-here');
```

Then update `config/app.php` to use it:
```php
define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? SMTP_PASSWORD_LOCAL ?? '');
```

## Future Deployments

Use `update-repo.bat` for normal updates. It will:
- Check git status
- Add and commit changes
- Push to GitHub
- Show helpful error messages if secrets are detected

## Important Notes

1. **Never commit passwords or API keys** to git
2. **Use environment variables** for sensitive data
3. **Keep `.gitignore` updated** to exclude sensitive files
4. **Force push rewrites history** - only use when necessary

## If Push Still Fails

If GitHub continues to block pushes:

1. **Remove the secret from GitHub's alert**:
   - Go to your repository on GitHub
   - Click "Security" tab
   - Click "Secret scanning alerts"
   - Dismiss or resolve the alert

2. **Contact GitHub Support** if the alert persists

3. **Last Resort**: Create a new repository and push clean code

## Files Modified

- `config/app.php` - Removed hardcoded password
- `test-connection.php` - Removed password display
- `.gitignore` - Added sensitive file exclusions
- `update-repo.bat` - Improved with error handling
- `fix-git-secrets.bat` - New script to fix git history

## Security Best Practices

✅ **DO**:
- Use environment variables for secrets
- Keep sensitive files in `.gitignore`
- Use strong, unique passwords
- Rotate passwords regularly

❌ **DON'T**:
- Commit passwords to git
- Share API keys in code
- Use default passwords in production
- Commit `.env` files
