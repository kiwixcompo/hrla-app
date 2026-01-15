@echo off
echo ========================================
echo  HRLA - Quick Push to GitHub
echo ========================================
echo.

REM Simple, straightforward push script
echo Adding all files...
git add .

echo.
echo Committing...
git commit -m "Update application"

echo.
echo Pushing to GitHub...
git push origin main

if errorlevel 1 (
    echo.
    echo Trying master branch...
    git push origin master
)

echo.
echo ========================================
if errorlevel 1 (
    echo  FAILED - See error above
    echo.
    echo Try running: diagnose.bat
    echo Or see: DEPLOY_MANUALLY.md
) else (
    echo  SUCCESS!
    echo.
    echo Changes pushed to GitHub
)
echo ========================================
echo.
pause
