# Configuration Setup

## Local Configuration File

The application uses `config/local.php` for server-specific settings that should NOT be committed to git.

### File: `config/local.php`

This file contains:
- Default admin password
- SMTP password
- Other local/server-specific settings

**Important**: This file is in `.gitignore` and will NOT be pushed to GitHub.

### Setup on Your Server

1. **Upload `config/local.php` to your server** (it's already created locally)
2. **Edit the file on your server** to set production values:
   ```php
   define('DEFAULT_ADMIN_PASSWORD', 'YourSecurePassword');
   define('SMTP_PASSWORD_LOCAL', 'YourEmailPassword');
   ```

### Default Admin Credentials

- **Email**: talk2char@gmail.com
- **Password**: Set in `config/local.php` (default: Password@123)

### Why This Approach?

✅ **Secure**: Passwords not in git repository
✅ **Flexible**: Different settings per environment
✅ **Simple**: One file to manage local config

### Files Modified

- `config/app.php` - Loads local.php if it exists
- `config/database.php` - Uses DEFAULT_ADMIN_PASSWORD constant
- `init-database.php` - Uses DEFAULT_ADMIN_PASSWORD constant
- `.gitignore` - Excludes config/local.php

### Deployment

When you push to GitHub:
- `config/local.php` stays on your local machine
- GitHub won't see any passwords
- Upload `config/local.php` manually to your server via FTP/cPanel

### For Team Members

Each team member should:
1. Create their own `config/local.php`
2. Set their own local passwords
3. Never commit this file to git
