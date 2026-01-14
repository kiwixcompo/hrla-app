@echo off
echo ========================================
echo  HRLA - Git Status Checker
echo ========================================
echo.

echo Current Directory:
echo %CD%
echo.

echo Checking for .git folder...
if exist ".git" (
    echo [OK] .git folder exists
    echo.
    
    echo Git Version:
    git --version
    echo.
    
    echo Current Branch:
    git branch
    echo.
    
    echo Remote Repository:
    git remote -v
    echo.
    
    echo Recent Commits:
    git log --oneline -5
    echo.
    
    echo Git Status:
    git status
    echo.
    
    echo ========================================
    echo  DIAGNOSIS: Git is properly set up
    echo ========================================
    echo.
    echo You can now run:
    echo - fix-git-secrets.bat (to remove secrets)
    echo - update-repo.bat (for regular updates)
    echo.
) else (
    echo [ERROR] .git folder NOT found
    echo.
    echo ========================================
    echo  DIAGNOSIS: Git is NOT initialized
    echo ========================================
    echo.
    echo This folder is not a git repository.
    echo.
    echo To fix this, run:
    echo   setup-git-repo.bat
    echo.
    echo Or manually initialize:
    echo   git init
    echo   git remote add origin YOUR_GITHUB_URL
    echo.
)

pause
