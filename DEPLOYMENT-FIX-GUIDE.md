# Server Deployment - Permission Issues Fix

## Problem Identified

Your server error log shows:
```
Failed to open [/home/hrledkhw/public_html/.htaccess]: Permission denied
```

This means:
1. The `.htaccess` file has incorrect permissions
2. The web server cannot read it
3. Directory listing is shown instead of your application

## Quick Fix - Option 1: Run PHP Script

1. Upload all files to your server
2. Visit: `https://www.hrleaveassist.com/fix-permissions.php`
3. The script will automatically fix permissions
4. Check the results and follow any manual steps if needed

## Manual Fix - Option 2: cPanel File Manager

### Step 1: Fix .htaccess Permissions

1. Log into cPanel
2. Open **File Manager**
3. Navigate to `public_html`
4. Find `.htaccess` file
5. Right-click → **Change Permissions**
6. Set to: **644** (rw-r--r--)
   - Owner: Read + Write
   - Group: Read
   - Public: Read
7. Click **Change Permissions**

### Step 2: Fix Directory Permissions

Set these directories to **755** (rwxr-xr-x):
- `config/`
- `includes/`
- `assets/`
- `api/`
- `admin/`

Set these directories to **777** (rwxrwxrwx):
- `logs/` (needs to be writable)
- `data/` (needs to be writable)

### Step 3: Fix Subdirectory .htaccess Files

Set these files to **644**:
- `config/.htaccess`
- `data/.htaccess`
- `logs/.htaccess`
- `includes/.htaccess`

## Manual Fix - Option 3: FTP/SSH

### Via FTP (FileZilla, etc.):

```
Right-click file/folder → File Permissions → Set numeric value
```

### Via SSH:

```bash
cd /home/hrledkhw/public_html

# Fix .htaccess
chmod 644 .htaccess

# Fix directories
chmod 755 config includes assets api admin
chmod 777 logs data

# Fix subdirectory .htaccess files
chmod 644 config/.htaccess
chmod 644 data/.htaccess
chmod 644 logs/.htaccess
chmod 644 includes/.htaccess

# Fix all PHP files
find . -name "*.php" -type f -exec chmod 644 {} \;
```

## Verify the Fix

After fixing permissions:

1. Visit: `https://www.hrleaveassist.com/`
2. You should see your homepage, NOT a directory listing
3. If you still see directory listing, check:
   - Is `index.php` in the root directory?
   - Does `index.php` have 644 permissions?
   - Is `.htaccess` readable by the web server?

## Common Permission Values

| Permission | Numeric | Description |
|------------|---------|-------------|
| rw-r--r-- | 644 | Files (readable by all, writable by owner) |
| rwxr-xr-x | 755 | Directories (executable/listable) |
| rwxrwxrwx | 777 | Writable directories (logs, data) |

## Troubleshooting

### Still seeing directory listing?

**Check 1: Is index.php present?**
```bash
ls -la /home/hrledkhw/public_html/index.php
```

**Check 2: Is DirectoryIndex set?**
The `.htaccess` should have:
```apache
DirectoryIndex index.php index.html
```

**Check 3: Are Options correct?**
The `.htaccess` should have:
```apache
Options -Indexes
```

### Still getting permission errors?

**Check file ownership:**
```bash
ls -la /home/hrledkhw/public_html/.htaccess
```

The owner should be your cPanel username (hrledkhw).

**If ownership is wrong, fix it:**
```bash
chown hrledkhw:hrledkhw .htaccess
```

Or via cPanel File Manager:
- Right-click file → Change Permissions → Check "Reset all child permissions"

## After Fixing Permissions

1. ✓ Test your homepage: `https://www.hrleaveassist.com/`
2. ✓ Test login: `https://www.hrleaveassist.com/login.php`
3. ✓ Check error log: `https://www.hrleaveassist.com/view-error-log.php`
4. ✓ Delete test files:
   - `fix-permissions.php`
   - `test-basic.php`
   - `test-error-logging.php`

## Security Note

After everything works:
1. Change the password in `view-error-log.php`
2. Consider deleting or protecting diagnostic files
3. Keep error logging active to catch future issues

## Need Help?

If you still have issues after trying these fixes:
1. Check your hosting provider's error logs
2. Contact your hosting support with this error:
   ```
   Failed to open .htaccess: Permission denied
   ```
3. They can fix permissions from their end

## Quick Reference

**Run this first:**
```
https://www.hrleaveassist.com/fix-permissions.php
```

**If that doesn't work:**
- cPanel → File Manager → Right-click .htaccess → Permissions → 644

**Still not working?**
- Contact hosting support
