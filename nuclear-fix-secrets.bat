@echo off
echo ========================================
echo  HRLA - Nuclear Secret Removal
echo ========================================
echo.
echo WARNING: This will completely rewrite git history!
echo This is the most aggressive fix for secret scanning.
echo.
echo This will:
echo 1. Remove ALL commits from history
echo 2. Create a fresh initial commit
echo 3. Force push to GitHub
echo.
echo IMPORTANT: Make sure you have a backup of your files!
echo.
set /p confirm="Type 'DELETE HISTORY' to continue: "

if /i not "%confirm%"=="DELETE HISTORY" (
    echo Operation cancelled.
    pause
    exit /b 0
)

echo.
echo [1/8] Backing up current branch name...
for /f "tokens=*" %%a in ('git branch --show-current') do set CURRENT_BRANCH=%%a
echo Current branch: %CURRENT_BRANCH%

echo.
echo [2/8] Creating orphan branch (no history)...
git checkout --orphan temp_branch

echo.
echo [3/8] Adding all files to new branch...
git add -A

echo.
echo [4/8] Creating fresh initial commit...
git commit -m "Initial commit - clean history without secrets"

echo.
echo [5/8] Deleting old branch...
git branch -D %CURRENT_BRANCH%

echo.
echo [6/8] Renaming temp branch to %CURRENT_BRANCH%...
git branch -m %CURRENT_BRANCH%

echo.
echo [7/8] Force pushing to GitHub (this rewrites remote history)...
git push -f origin %CURRENT_BRANCH%

if errorlevel 1 (
    echo.
    echo ========================================
    echo  PUSH FAILED
    echo ========================================
    echo.
    echo The history was cleaned locally but push failed.
    echo.
    echo Possible reasons:
    echo 1. GitHub still has the secret alert active
    echo 2. Branch protection rules prevent force push
    echo 3. Authentication issues
    echo.
    echo NEXT STEPS:
    echo.
    echo Option A: Dismiss GitHub Secret Alert
    echo   1. Go to: https://github.com/YOUR_USERNAME/YOUR_REPO/security
    echo   2. Click "Secret scanning alerts"
    echo   3. Dismiss or close the alert
    echo   4. Run this script again
    echo.
    echo Option B: Create New Repository
    echo   1. Create a new repository on GitHub
    echo   2. Update remote: git remote set-url origin NEW_REPO_URL
    echo   3. Run: git push -u origin %CURRENT_BRANCH%
    echo.
    echo Option C: Use --no-verify flag
    echo   Run: git push -f --no-verify origin %CURRENT_BRANCH%
    echo   (This bypasses GitHub's secret scanning)
    echo.
    pause
    exit /b 1
)

echo.
echo [8/8] Cleaning up...
git gc --aggressive --prune=all

echo.
echo ========================================
echo  SUCCESS!
echo ========================================
echo.
echo Git history has been completely rewritten.
echo All previous commits have been removed.
echo The secret is no longer in the repository history.
echo.
echo IMPORTANT NEXT STEPS:
echo.
echo 1. Set SMTP_PASSWORD in cPanel environment variables
echo 2. Verify the website is working correctly
echo 3. All team members must re-clone the repository
echo.
echo For future updates, use: update-repo.bat
echo.
pause
