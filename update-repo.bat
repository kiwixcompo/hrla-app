@echo off
echo ========================================
echo  HRLA - Git Repository Update Script
echo ========================================
echo.

REM Check if we're in a git repository
if not exist ".git" (
    echo ERROR: Not a git repository!
    echo.
    echo Please initialize git first:
    echo   git init
    echo   git remote add origin YOUR_GITHUB_URL
    echo.
    pause
    exit /b 1
)

echo [1/5] Checking git status...
git status
if errorlevel 1 (
    echo ERROR: Git status failed
    pause
    exit /b 1
)

echo.
echo [2/5] Adding all changes to staging...
git add .
if errorlevel 1 (
    echo ERROR: Git add failed
    pause
    exit /b 1
)

echo.
echo [3/5] Committing changes...
set /p commit_message="Enter commit message (or press Enter for default): "
if "%commit_message%"=="" set commit_message=Update application files

git commit -m "%commit_message%"
if errorlevel 1 (
    echo.
    echo NOTE: Nothing to commit or commit failed
    echo This is normal if there are no changes
    echo.
)

echo.
echo [4/5] Pushing to GitHub repository...
git push origin main
if errorlevel 1 (
    echo.
    echo Push to 'main' failed, trying 'master'...
    git push origin master
    if errorlevel 1 (
        echo.
        echo ========================================
        echo  PUSH FAILED
        echo ========================================
        echo.
        echo Possible reasons:
        echo 1. No remote repository configured
        echo 2. Authentication failed
        echo 3. Branch name is different
        echo 4. Network issues
        echo.
        echo Check your remote with: git remote -v
        echo Check your branch with: git branch
        echo.
        pause
        exit /b 1
    )
)

echo.
echo [5/5] Repository update complete!
echo.
echo ========================================
echo  SUCCESS!
echo ========================================
echo.
echo Your changes have been pushed to GitHub.
echo.
pause
