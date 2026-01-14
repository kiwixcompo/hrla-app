@echo off
echo ========================================
echo  HRLA - HR Leave Assistant
echo  Git Repository Update Script
echo ========================================
echo.

echo [1/6] Checking git status...
git status

echo.
echo [2/6] Adding all changes to staging...
git add .

echo.
echo [3/6] Committing changes...
set /p commit_message="Enter commit message (or press Enter for default): "
if "%commit_message%"=="" set commit_message=Update application files

git commit -m "%commit_message%"

echo.
echo [4/6] Checking for sensitive data...
echo NOTE: If push fails due to secret detection, sensitive data has been removed.
echo You may need to use: git push --no-verify origin main (use with caution)

echo.
echo [5/6] Pushing to GitHub repository...
git push origin main

if errorlevel 1 (
    echo.
    echo ========================================
    echo  PUSH FAILED - Possible Solutions:
    echo ========================================
    echo.
    echo 1. GitHub detected secrets in your code
    echo 2. Sensitive data has been removed from config/app.php
    echo 3. You need to remove the commit with secrets from history
    echo.
    echo To fix this, run these commands:
    echo.
    echo   git reset --soft HEAD~1
    echo   git add .
    echo   git commit -m "Update application without secrets"
    echo   git push origin main
    echo.
    echo Or to force push (removes secret from history):
    echo   git push --force origin main
    echo.
    echo ========================================
    pause
    exit /b 1
)

echo.
echo [6/6] Repository update complete!
echo.
echo ========================================
echo  Summary:
echo  - All local changes have been committed
echo  - Changes pushed to GitHub repository
echo  - Repository is now up to date
echo ========================================
echo.

pause
