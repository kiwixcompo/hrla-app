@echo off
echo ========================================
echo  HRLA - Force Repository Sync Script
echo ========================================
echo.
echo WARNING: This will make the repository
echo          match your local files exactly!
echo.
echo Any files on GitHub that don't exist
echo locally will be DELETED from the repo.
echo.
set /p confirm="Are you sure? (yes/no): "
if /i not "%confirm%"=="yes" (
    echo.
    echo Operation cancelled.
    pause
    exit /b 0
)

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

echo.
echo [1/6] Checking git status...
git status
if errorlevel 1 (
    echo ERROR: Git status failed
    pause
    exit /b 1
)

echo.
echo [2/6] Adding all changes (including deletions)...
git add -A
if errorlevel 1 (
    echo ERROR: Git add failed
    pause
    exit /b 1
)

echo.
echo [3/6] Showing what will be synced...
git status --short
echo.

echo.
echo [4/6] Committing changes...
set /p commit_message="Enter commit message (or press Enter for default): "
if "%commit_message%"=="" set commit_message=Sync repository with local files

git commit -m "%commit_message%"
if errorlevel 1 (
    echo.
    echo NOTE: Nothing to commit
    echo Repository is already in sync
    echo.
    pause
    exit /b 0
)

echo.
echo [5/6] Force pushing to GitHub repository...
echo This will overwrite the remote repository!
echo.

REM Try main branch first
git push --force origin main
if errorlevel 1 (
    echo.
    echo Push to 'main' failed, trying 'master'...
    git push --force origin master
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
echo [6/6] Repository sync complete!
echo.
echo ========================================
echo  SUCCESS!
echo ========================================
echo.
echo Your local files have been synced to GitHub.
echo The repository now matches your local folder.
echo.
echo IMPORTANT: Pull this on your server with:
echo   git fetch --all
echo   git reset --hard origin/main
echo   (or origin/master depending on branch)
echo.
pause
