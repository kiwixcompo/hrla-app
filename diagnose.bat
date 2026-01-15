@echo off
echo ========================================
echo  HRLA - Git Diagnostic Tool
echo ========================================
echo.

echo Current Directory:
cd
echo.

echo Checking for .git folder...
if exist ".git" (
    echo [OK] Git repository detected
) else (
    echo [ERROR] Not a git repository
    echo.
    echo You need to initialize git:
    echo   git init
    echo   git remote add origin YOUR_GITHUB_URL
    echo.
    pause
    exit /b 1
)

echo.
echo ========================================
echo Git Configuration
echo ========================================
echo.

echo Git Version:
git --version
echo.

echo Current Branch:
git branch
echo.

echo Remote Repositories:
git remote -v
echo.

echo ========================================
echo Repository Status
echo ========================================
echo.

echo Modified Files:
git status --short
echo.

echo Last 5 Commits:
git log --oneline -5
echo.

echo ========================================
echo Files to be Committed
echo ========================================
echo.
git diff --name-only --cached
echo.

echo ========================================
echo Checking .gitignore
echo ========================================
echo.

if exist ".gitignore" (
    echo [OK] .gitignore exists
    echo.
    echo Checking if config/local.php is ignored:
    git check-ignore config/local.php
    if errorlevel 1 (
        echo [WARNING] config/local.php is NOT ignored!
        echo This file contains passwords and should be in .gitignore
    ) else (
        echo [OK] config/local.php is properly ignored
    )
) else (
    echo [WARNING] .gitignore not found
)

echo.
echo ========================================
echo Diagnosis Complete
echo ========================================
echo.
echo If you see any errors above, refer to DEPLOY_MANUALLY.md
echo for step-by-step instructions.
echo.
pause
