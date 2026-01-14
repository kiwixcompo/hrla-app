@echo off
echo ========================================
echo  HRLA - Fix Git Secrets Issue
echo ========================================
echo.

REM Check if we're in a git repository
if not exist ".git" (
    echo ERROR: Not a git repository!
    echo.
    echo This folder does not contain a .git directory.
    echo.
    echo Please do ONE of the following:
    echo.
    echo Option 1: Initialize Git Repository
    echo   1. Run: git init
    echo   2. Run: git remote add origin YOUR_GITHUB_URL
    echo   3. Run this script again
    echo.
    echo Option 2: Clone from GitHub
    echo   1. Go to parent folder
    echo   2. Run: git clone YOUR_GITHUB_URL
    echo   3. Copy your files to the cloned folder
    echo   4. Run this script from there
    echo.
    pause
    exit /b 1
)

echo Current directory: %CD%
echo Git repository detected: YES
echo.
echo This script will remove the commit containing secrets
echo and create a new clean commit.
echo.
echo WARNING: This will rewrite git history!
echo.
set /p confirm="Are you sure you want to continue? (yes/no): "

if /i not "%confirm%"=="yes" (
    echo Operation cancelled.
    pause
    exit /b 0
)

echo.
echo [1/5] Checking current branch...
git branch

echo.
echo [2/5] Resetting last commit (keeping changes)...
git reset --soft HEAD~1

if errorlevel 1 (
    echo.
    echo ERROR: Could not reset commit.
    echo This might be the first commit or there's another issue.
    echo.
    echo Try running: git log
    echo to see your commit history.
    echo.
    pause
    exit /b 1
)

echo.
echo [3/5] Adding all changes to staging...
git add .

echo.
echo [4/5] Creating new commit without secrets...
git commit -m "Update application - removed hardcoded secrets"

echo.
echo [5/5] Force pushing to GitHub (this will rewrite history)...
git push --force origin main

if errorlevel 1 (
    echo.
    echo ========================================
    echo  PUSH FAILED - Trying 'master' branch
    echo ========================================
    echo.
    git push --force origin master
    
    if errorlevel 1 (
        echo.
        echo ========================================
        echo  PUSH STILL FAILED
        echo ========================================
        echo.
        echo Possible reasons:
        echo 1. GitHub still detecting secrets
        echo 2. Branch name is not 'main' or 'master'
        echo 3. No remote repository configured
        echo 4. Authentication issues
        echo.
        echo Check your branch name with: git branch
        echo Check your remote with: git remote -v
        echo.
        echo You may need to:
        echo 1. Contact GitHub support to remove the secret alert
        echo 2. Or create a new repository
        echo.
        pause
        exit /b 1
    )
)

echo.
echo ========================================
echo  SUCCESS!
echo ========================================
echo.
echo The git history has been cleaned and pushed.
echo Hardcoded secrets have been removed.
echo.
echo IMPORTANT: Set SMTP_PASSWORD as an environment variable
echo in your hosting control panel (cPanel).
echo.
pause
