# Heroku Deployment Script for Note Sharing App
# Run this script in PowerShell

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "Heroku Deployment Setup" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Check if Heroku CLI is installed
Write-Host "Checking Heroku CLI installation..." -ForegroundColor Yellow
try {
    $herokuVersion = heroku --version
    Write-Host "✓ Heroku CLI is installed: $herokuVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ Heroku CLI is not installed!" -ForegroundColor Red
    Write-Host "Please install from: https://devcenter.heroku.com/articles/heroku-cli" -ForegroundColor Yellow
    Write-Host "Or run: winget install Heroku.HerokuCLI" -ForegroundColor Yellow
    exit
}

Write-Host ""

# Step 2: Login to Heroku
Write-Host "Logging into Heroku..." -ForegroundColor Yellow
Write-Host "A browser window will open for login." -ForegroundColor Cyan
heroku login

Write-Host ""

# Step 3: Create Heroku App
Write-Host "Creating Heroku app..." -ForegroundColor Yellow
$appName = Read-Host "Enter your desired app name (e.g., my-notesharing-app)"
heroku create $appName

Write-Host ""

# Step 4: Add ClearDB MySQL
Write-Host "Adding ClearDB MySQL database..." -ForegroundColor Yellow
heroku addons:create cleardb:ignite --app $appName

Write-Host ""
Start-Sleep -Seconds 5

# Step 5: Get database credentials
Write-Host "Getting database credentials..." -ForegroundColor Yellow
$dbUrl = heroku config:get CLEARDB_DATABASE_URL --app $appName
Write-Host "Database URL: $dbUrl" -ForegroundColor Green
Write-Host ""
Write-Host "IMPORTANT: Save this database URL for importing your schema!" -ForegroundColor Red
Write-Host ""

# Step 6: Set environment variables
Write-Host "Setting up environment variables..." -ForegroundColor Yellow
Write-Host "Please provide your SMTP details:" -ForegroundColor Cyan

$smtpEmail = Read-Host "Your Gmail address"
$smtpPassword = Read-Host "Your Gmail App Password (not regular password)" -AsSecureString
$smtpPasswordPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($smtpPassword))

heroku config:set SMTP_HOST=smtp.gmail.com --app $appName
heroku config:set SMTP_PORT=587 --app $appName
heroku config:set SMTP_USERNAME=$smtpEmail --app $appName
heroku config:set SMTP_PASSWORD=$smtpPasswordPlain --app $appName
heroku config:set SMTP_FROM_EMAIL=$smtpEmail --app $appName
heroku config:set "SMTP_FROM_NAME=Note Sharing App" --app $appName
heroku config:set "SITE_URL=https://$appName.herokuapp.com" --app $appName

Write-Host ""
Write-Host "✓ Environment variables set!" -ForegroundColor Green
Write-Host ""

# Step 7: Deploy
Write-Host "Deploying to Heroku..." -ForegroundColor Yellow
Write-Host "Adding files to git..." -ForegroundColor Cyan

git add .
git commit -m "Deploy to Heroku"

Write-Host "Pushing to Heroku..." -ForegroundColor Cyan
git push heroku main

Write-Host ""
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "Deployment Complete!" -ForegroundColor Green
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host "1. Import your database schema to ClearDB" -ForegroundColor White
Write-Host "2. Test your app: heroku open --app $appName" -ForegroundColor White
Write-Host "3. View logs: heroku logs --tail --app $appName" -ForegroundColor White
Write-Host ""
Write-Host "Your app URL: https://$appName.herokuapp.com" -ForegroundColor Green
