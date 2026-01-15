# Manual Deployment Guide

If the `update-repo.bat` script isn't working, follow these manual steps:

## Step 1: Check Git Status

Open Command Prompt in your project folder and run:

```bash
git status
```

**Expected output**: Should show modified files

**If you see "not a git repository"**:
```bash
git init
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO.git
```

## Step 2: Add Files

```bash
git add .
```

This stages all your changes.

## Step 3: Commit Changes

```bash
git commit -m "Update application with local config system"
```

## Step 4: Push to GitHub

Try pushing to main branch:
```bash
git push origin main
```

If that fails, try master branch:
```bash
git push origin master
```

## Common Issues

### "fatal: not a git repository"
**Solution**: Initialize git first
```bash
git init
git remote add origin YOUR_GITHUB_URL
```

### "fatal: 'origin' does not appear to be a git repository"
**Solution**: Add remote repository
```bash
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO.git
```

### "error: failed to push some refs"
**Solution**: Pull first, then push
```bash
git pull origin main --rebase
git push origin main
```

### "GitHub detected secrets"
**Solution**: This should be fixed now with config/local.php
- The password is in local.php (gitignored)
- Only local.example.php is committed (no real password)
- Try pushing again

### Authentication Failed
**Solution**: Set up GitHub authentication
- Use GitHub Desktop, or
- Configure Git credentials:
  ```bash
  git config --global user.name "Your Name"
  git config --global user.email "your@email.com"
  ```

## Verify What Will Be Pushed

Before pushing, check what's staged:
```bash
git status
git diff --cached
```

Make sure `config/local.php` is NOT listed (it should be ignored).

## Check Remote Repository

```bash
git remote -v
```

Should show your GitHub repository URL.

## Check Current Branch

```bash
git branch
```

Should show `main` or `master` with an asterisk (*).

## Force Push (Use with Caution)

If you need to overwrite remote history:
```bash
git push --force origin main
```

**Warning**: This will overwrite the remote repository!

## Alternative: Use GitHub Desktop

1. Download GitHub Desktop: https://desktop.github.com/
2. Open your project folder
3. Review changes
4. Commit with message
5. Click "Push origin"

## Files That Should Be Committed

✅ These files WILL be pushed:
- All .php files (except config/local.php)
- All .css files
- All .js files
- config/local.example.php (template)
- .gitignore

❌ These files will NOT be pushed (gitignored):
- config/local.php (contains passwords)
- node_modules/
- .env files
- *.log files

## After Successful Push

1. **Verify on GitHub**: Check your repository to see the changes
2. **Upload config/local.php to server**: Use FTP or cPanel File Manager
3. **Test your website**: Make sure everything works

## Need Help?

Run these diagnostic commands:
```bash
git status
git remote -v
git branch
git log --oneline -5
```

Copy the output and review for errors.
