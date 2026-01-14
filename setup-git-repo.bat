@echo off
echo ========================================
echo  HRLA - Git Repository Setup
echo ========================================
echo.

REM Check if already a git repository
if exist ".git" (
    echo Git repository already exists!
    echo.
    echo Current remote:
    git remote -v
    echo.
    echo If you need to change the remote URL, run:
    echo   git remote set-url origin YOUR_NEW_GITHUB_URL
    echo.
    pause
    exit /b 0
)

echo This will initialize a new git repository.
echo.
set /p github_url="Enter your GitHub repository URL: "

if "%github_url%"=="" (
    echo ERROR: GitHub URL is required!
    pause
    exit /b 1
)

echo.
echo [1/6] Initializing git repository...
git init

echo.
echo [2/6] Adding remote repository...
git remote add origin %github_url%

echo.
echo [3/6] Creating .gitignore if needed...
if not exist ".gitignore" (
    echo Creating .gitignore file...
    echo node_modules/ > .gitignore
    echo .env >> .gitignore
    echo *.log >> .gitignore
)

echo.
echo [4/6] Adding all files...
git add .

echo.
echo [5/6] Creating initial commit...
git commit -m "Initial commit - HRLA application"

echo.
echo [6/6] Pushing to GitHub...
echo.
echo NOTE: You may be prompted for GitHub credentials.
echo.
git push -u origin main

if errorlevel 1 (
    echo.
    echo Push to 'main' failed, trying 'master'...
    git branch -M main
    git push -u origin main
    
    if errorlevel 1 (
        echo.
        echo ========================================
        echo  SETUP INCOMPLETE
        echo ========================================
        echo.
        echo The repository was initialized but push failed.
        echo.
        echo This could be because:
        echo 1. The GitHub repository doesn't exist yet
        echo 2. Authentication failed
        echo 3. You don't have push permissions
        echo.
        echo To complete setup:
        echo 1. Create the repository on GitHub if it doesn't exist
        echo 2. Make sure you're logged in to GitHub
        echo 3. Run: git push -u origin main
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
echo Git repository has been set up and pushed to GitHub.
echo.
echo You can now use:
echo - update-repo.bat for regular updates
echo - fix-git-secrets.bat if you need to remove secrets
echo.
pause
