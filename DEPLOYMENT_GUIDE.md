# Automatic Deployment Guide
## HRLA App - GitHub to Namecheap cPanel & Netlify

---

## Part 1: Namecheap cPanel Deployment (PHP Backend)

### Initial Setup

#### Step 1: Enable Git Version Control in cPanel
1. Log into your Namecheap cPanel
2. Search for "Git Version Control" in the search bar
3. Click "Create" to add a new repository

#### Step 2: Clone Your Repository
1. In the Git Version Control page, click "Create"
2. Fill in the details:
   - **Clone a Repository**: Yes
   - **Clone URL**: `https://github.com/kiwixcompo/hrla-app.git`
   - **Repository Path**: `/home/yourusername/repositories/hrla-app`
   - **Repository Name**: `hrla-app`
3. Click "Create"

#### Step 3: Set Up Deployment Path
1. After creating, click "Manage" next to your repository
2. Set the deployment path to your public directory:
   - Example: `/home/yourusername/public_html` (for main domain)
   - Or: `/home/yourusername/public_html/hrla` (for subdirectory)
3. Click "Update"

#### Step 4: Initial Deployment
1. In the repository management page, click "Pull or Deploy"
2. Click "Deploy HEAD Commit"
3. Your files will be copied to the deployment path

#### Step 5: Configure Database and Settings
1. Go to File Manager in cPanel
2. Navigate to your deployment directory
3. Create `config/local.php` file with your server settings:

```php
<?php
// Local configuration - DO NOT commit to git
define('DEFAULT_ADMIN_PASSWORD', 'Password@123');
define('SMTP_PASSWORD', 'your-smtp-password');

// Database settings (if different from config/database.php)
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'your_database_name');
// define('DB_USER', 'your_database_user');
// define('DB_PASS', 'your_database_password');
```

4. Set proper permissions:
   - `config/local.php`: 644
   - `data/` folder: 755
   - `logs/` folder: 755

#### Step 6: Initialize Database
1. Visit: `https://yourdomain.com/init-database.php`
2. This will create all necessary tables
3. Delete or rename `init-database.php` after running

### Automatic Updates (cPanel)

#### Option A: Manual Pull (Recommended for cPanel)
1. Go to cPanel → Git Version Control
2. Click "Manage" on your repository
3. Click "Pull or Deploy"
4. Click "Update from Remote" to pull latest changes
5. Click "Deploy HEAD Commit" to deploy

#### Option B: Webhook Automation (Advanced)
1. In cPanel, create a webhook script at `/home/yourusername/public_html/deploy-webhook.php`:

```php
<?php
// Webhook to auto-deploy from GitHub
$secret = 'your-webhook-secret-key'; // Change this!

// Verify GitHub signature
$hub_signature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';
$payload = file_get_contents('php://input');
$signature = 'sha1=' . hash_hmac('sha1', $payload, $secret);

if (!hash_equals($hub_signature, $signature)) {
    http_response_code(403);
    die('Invalid signature');
}

// Execute git pull and deploy
$repo_path = '/home/yourusername/repositories/hrla-app';
$deploy_path = '/home/yourusername/public_html';

chdir($repo_path);
exec('git pull origin main 2>&1', $output, $return_var);

// Copy files to deployment path
exec("rsync -av --exclude='.git' {$repo_path}/ {$deploy_path}/", $output2);

// Log the deployment
file_put_contents('/home/yourusername/deploy.log', date('Y-m-d H:i:s') . " - Deployed\n", FILE_APPEND);

echo json_encode(['status' => 'success', 'output' => $output]);
```

2. In GitHub repository settings:
   - Go to Settings → Webhooks → Add webhook
   - Payload URL: `https://yourdomain.com/deploy-webhook.php`
   - Content type: `application/json`
   - Secret: (same as in the PHP file)
   - Events: Just the push event
   - Active: ✓

3. Now every push to GitHub will auto-deploy to cPanel!

---

## Part 2: Netlify Deployment (Frontend/Static)

### Initial Setup

#### Step 1: Connect GitHub to Netlify
1. Go to [Netlify](https://app.netlify.com)
2. Click "Add new site" → "Import an existing project"
3. Choose "Deploy with GitHub"
4. Authorize Netlify to access your GitHub account
5. Select repository: `kiwixcompo/hrla-app`

#### Step 2: Configure Build Settings
Since this is a PHP app, you have two options:

**Option A: Deploy Static Files Only (Homepage)**
- Base directory: (leave empty)
- Build command: (leave empty)
- Publish directory: `/`
- This will serve your `index.php` as static HTML (won't execute PHP)

**Option B: Use Netlify Functions for Backend**
- Base directory: (leave empty)
- Build command: `npm install`
- Publish directory: `/`
- Functions directory: `netlify/functions`

For Option B, you'll need to create serverless functions.

#### Step 3: Environment Variables (if using functions)
1. In Netlify dashboard, go to Site settings → Environment variables
2. Add your variables:
   - `DB_HOST`
   - `DB_NAME`
   - `DB_USER`
   - `DB_PASS`
   - `OPENAI_API_KEY`
   - `STRIPE_SECRET_KEY`
   - etc.

#### Step 4: Deploy
1. Click "Deploy site"
2. Netlify will automatically build and deploy
3. You'll get a URL like: `https://random-name-123.netlify.app`

### Automatic Updates (Netlify)

Netlify automatically deploys when you push to GitHub! No additional setup needed.

**How it works:**
1. You run `update-repo.bat` to push changes to GitHub
2. Netlify detects the push via webhook (automatically configured)
3. Netlify rebuilds and redeploys your site
4. Changes are live in 1-2 minutes

**To monitor deployments:**
1. Go to Netlify dashboard
2. Click on your site
3. Go to "Deploys" tab
4. See real-time build logs and deployment status

---

## Part 3: Recommended Workflow

### For PHP Backend (Namecheap cPanel):
```
1. Make changes locally
2. Run update-repo.bat
3. Go to cPanel → Git Version Control
4. Click "Update from Remote"
5. Click "Deploy HEAD Commit"
```

### For Frontend/Static (Netlify):
```
1. Make changes locally
2. Run update-repo.bat
3. Wait 1-2 minutes
4. Netlify auto-deploys!
```

### Combined Workflow:
```
1. Make changes locally
2. Test locally
3. Run update-repo.bat (pushes to GitHub)
4. Manually deploy to cPanel (or use webhook)
5. Netlify auto-deploys
6. Done!
```

---

## Part 4: Important Notes

### For cPanel:
- PHP files will execute properly
- Database connections work
- Full backend functionality
- Recommended for production

### For Netlify:
- PHP won't execute (unless using serverless functions)
- Best for static frontend or JAMstack
- Great for staging/preview
- Free SSL and CDN included

### Recommended Setup:
- **Production Backend**: Namecheap cPanel (PHP + MySQL)
- **Production Frontend**: Point domain to cPanel
- **Staging/Preview**: Netlify (for testing before deploying to cPanel)

---

## Part 5: Troubleshooting

### cPanel Issues:
- **Files not updating**: Clear deployment path and redeploy
- **Permission errors**: Set folders to 755, files to 644
- **Database errors**: Check `config/local.php` settings

### Netlify Issues:
- **Build failed**: Check build logs in Netlify dashboard
- **PHP not working**: Netlify doesn't support PHP natively
- **Environment variables**: Make sure they're set in Netlify settings

### GitHub Issues:
- **Push rejected**: Make sure you're pushing to correct branch (main/master)
- **Authentication failed**: Use personal access token instead of password

---

## Quick Reference Commands

### Push changes to GitHub:
```bash
update-repo.bat
```

### Check git status:
```bash
git status
```

### Pull latest changes:
```bash
git pull origin main
```

### Force push (use carefully):
```bash
git push origin main --force
```

---

## Support

If you encounter issues:
1. Check deployment logs in cPanel or Netlify
2. Verify `config/local.php` exists and has correct settings
3. Ensure database is initialized
4. Check file permissions
5. Review error logs in `logs/` folder
