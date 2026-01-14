# Git Setup and Deployment Guide

## Current Issue: "fatal: not a git repository"

This error means your folder is not initialized as a git repository or the `.git` folder is missing.

## Solution: Choose One Option

### Option 1: Initialize Git in Current Folder (Recommended if you have no GitHub repo yet)

1. **Run the setup script**:
   ```
   Double-click: setup-git-repo.bat
   ```

2. **Enter your GitHub repository URL** when prompted:
   ```
   https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
   ```

3. **The script will**:
   - Initialize git
   - Add your GitHub remote
   - Create initial commit
   - Push to GitHub

### Option 2: Clone Existing GitHub Repository

If you already have a GitHub repository with code:

1. **Open Command Prompt in parent folder** (one level up from current folder)

2. **Clone the repository**:
   ```bash
   git clone https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
   ```

3. **Copy your updated files** to the cloned folder

4. **Navigate to the cloned folder**:
   ```bash
   cd YOUR_REPO_NAME
   ```

5. **Run the fix script**:
   ```
   fix-git-secrets.bat
   ```

### Option 3: Manual Git Setup

If you prefer manual setup:

```bash
# 1. Initialize git
git init

# 2. Add remote repository
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git

# 3. Add all files
git add .

# 4. Create initial commit
git commit -m "Initial commit"

# 5. Push to GitHub
git push -u origin main
```

## After Git is Set Up

### To Remove Secrets from History

Once git is properly initialized, run:
```
fix-git-secrets.bat
```

This will:
- Remove the commit with hardcoded passwords
- Create a clean commit
- Force push to GitHub

### For Regular Updates

Use this for normal deployments:
```
update-repo.bat
```

## Troubleshooting

### "fatal: not a git repository"
- You're not in a git-initialized folder
- Run `setup-git-repo.bat` or manually initialize git

### "remote origin already exists"
- Git is initialized but remote is wrong
- Run: `git remote set-url origin YOUR_CORRECT_URL`

### "failed to push some refs"
- Your local branch is behind remote
- Run: `git pull origin main` then try again
- Or use: `git push --force origin main` (careful!)

### "GitHub detected secrets"
- Hardcoded passwords found in code
- We've removed them from `config/app.php`
- Run `fix-git-secrets.bat` to clean history

### "Authentication failed"
- GitHub credentials not configured
- Set up GitHub authentication:
  - Use GitHub Desktop, or
  - Configure Git credentials, or
  - Use SSH keys

## Files in This Project

### Deployment Scripts
- `setup-git-repo.bat` - Initialize git repository
- `fix-git-secrets.bat` - Remove secrets from git history
- `update-repo.bat` - Regular deployment updates

### Documentation
- `GIT_SETUP_GUIDE.md` - This file
- `GIT_DEPLOYMENT_FIX.md` - Secret scanning fix details

## Quick Start Checklist

- [ ] Verify you're in the correct folder
- [ ] Check if `.git` folder exists
- [ ] If no `.git`, run `setup-git-repo.bat`
- [ ] If `.git` exists, run `fix-git-secrets.bat`
- [ ] Set SMTP_PASSWORD in cPanel environment variables
- [ ] Test deployment with `update-repo.bat`

## Important Notes

1. **Never commit passwords** - Use environment variables
2. **Keep `.gitignore` updated** - Exclude sensitive files
3. **Test locally first** - Before pushing to production
4. **Backup your database** - Before major updates

## Need Help?

If you're still having issues:

1. Check if git is installed: `git --version`
2. Check current directory: `cd` (shows current path)
3. Check for `.git` folder: `dir /a` (shows hidden folders)
4. Verify GitHub URL: `git remote -v`

## Next Steps After Successful Push

1. **Set environment variables in cPanel**:
   - Variable: `SMTP_PASSWORD`
   - Value: Your actual email password

2. **Test the application**:
   - Visit your website
   - Test login functionality
   - Test email sending

3. **Monitor for issues**:
   - Check error logs
   - Test all features
   - Verify database connection
