# HRLA Deployment Guide

## Problem
Changes made locally don't appear on the live site even after pushing to GitHub and using cPanel's "Deploy HEAD Commit" button.

## Solution

### Step 1: Push Local Changes to GitHub
Run the `update-repo.bat` file on your local machine:
```
update-repo.bat
```
- Type "yes" to confirm
- Enter a commit message
- Wait for the push to complete

### Step 2: Deploy to Live Server

#### Option A: Use Deployment Script (RECOMMENDED)
1. Upload `deploy-to-live.php` to your live server (if not already there)
2. Visit in your browser:
   ```
   https://www.hrleaveassist.com/deploy-to-live.php?key=hrla_deploy_2026_secure
   ```
3. Watch the deployment process
4. Clear your browser cache (Ctrl+F5)

#### Option B: Use cPanel Git Version Control
1. Log into cPanel
2. Go to "Git Version Control"
3. Click "Manage" on your repository
4. Click "Pull or Deploy" tab
5. Click "Update from Remote" button
6. Click "Deploy HEAD Commit" button
7. Clear your browser cache (Ctrl+F5)

#### Option C: Manual SSH Deployment
```bash
ssh username@yourserver.com
cd /home/hrledkhw/repositories/hrla-app
git fetch --all
git reset --hard origin/main
git clean -fd
```

### Step 3: Verify Changes
1. Clear browser cache (Ctrl+F5 or Cmd+Shift+R)
2. Check the page title - should be "HRLA - HR Leave Assist | HR Leave Response Generator"
3. Verify other changes are visible

## Common Issues

### Issue: Changes still not showing
**Solution:** 
- Clear browser cache completely
- Try incognito/private browsing mode
- Check if cPanel has file caching enabled
- Verify the correct repository path in cPanel

### Issue: "Deploy HEAD Commit" doesn't work
**Solution:**
- Use the deployment script instead (Option A)
- Or use manual SSH deployment (Option C)

### Issue: Deployment script shows "Access Denied"
**Solution:**
- Make sure you're using the correct secret key
- Default key: `hrla_deploy_2026_secure`
- Change it in `deploy-to-live.php` if needed

## Security Notes

1. **Change the deployment key** in `deploy-to-live.php`:
   ```php
   define('DEPLOY_SECRET_KEY', 'your-unique-secret-key-here');
   ```

2. **Delete or protect** `deploy-to-live.php` after deployment:
   - Move it outside web root, OR
   - Delete it after use, OR
   - Add password protection via .htaccess

3. **Never commit** sensitive files like:
   - `config/local.php`
   - `.env` files
   - Database backups

## File Structure

```
/home/hrledkhw/
├── public_html/              # Live website files
└── repositories/
    └── hrla-app/             # Git repository
        ├── index.php
        ├── deploy-to-live.php
        ├── update-repo.bat
        └── ...
```

## Troubleshooting

### Check Git Status on Server
```bash
cd /home/hrledkhw/repositories/hrla-app
git status
git log -1
```

### Check Current Commit
```bash
git rev-parse HEAD
```

### Force Update Everything
```bash
git fetch --all
git reset --hard origin/main
git clean -fd
```

### Clear PHP Cache
If using PHP opcache:
```bash
# Add to deploy-to-live.php or run manually
php -r "opcache_reset();"
```

## Quick Reference

| Action | Command/URL |
|--------|-------------|
| Push local changes | `update-repo.bat` |
| Deploy to live | `https://yoursite.com/deploy-to-live.php?key=SECRET` |
| Clear browser cache | Ctrl+F5 (Windows) or Cmd+Shift+R (Mac) |
| Check deployment log | View `deployment.log` on server |

## Support

If issues persist:
1. Check `deployment.log` file on the server
2. Verify repository path in cPanel matches actual path
3. Ensure Git is enabled on your hosting account
4. Contact hosting support if Git commands fail
