# 🚀 Heroku Deployment Checklist

## ✅ Files Created:
- [x] Procfile - Tells Heroku how to run your app
- [x] composer.json - Updated with PHP requirements
- [x] heroku-config.php - Environment-aware configuration
- [x] .env.example - Example environment variables
- [x] database_schema.sql - Your database export
- [x] deploy-heroku.ps1 - Automated deployment script
- [x] HEROKU_DEPLOYMENT.md - Full deployment guide

## 📋 Pre-Deployment Checklist:

### 1. Install Heroku CLI
```powershell
# Option 1: Download from website
https://devcenter.heroku.com/articles/heroku-cli

# Option 2: Use winget
winget install Heroku.HerokuCLI
```

### 2. Get Gmail App Password
- Go to: https://myaccount.google.com/apppasswords
- Generate app password for "Mail"
- Save it for deployment

### 3. Update Config Files
You need to modify these files to use `heroku-config.php` on Heroku:

**Files to update:**
- `ClassAutoLoad.php` - Change config loading
- Any file that loads `conf.php`

Add this check at the top:
```php
<?php
// Load appropriate config file
if (getenv('CLEARDB_DATABASE_URL')) {
    $conf = require __DIR__ . '/heroku-config.php';
} else {
    $conf = require __DIR__ . '/conf.php';
}
```

## 🎯 Quick Deployment (Automated):

### Run the PowerShell script:
```powershell
cd C:\Apache24\htdocs\IAPnotesharingapp-1
.\deploy-heroku.ps1
```

This will:
1. Check Heroku CLI installation
2. Login to Heroku
3. Create your app
4. Add MySQL database
5. Set environment variables
6. Deploy your code

## 📝 Manual Deployment Steps:

### Step 1: Login to Heroku
```powershell
heroku login
```

### Step 2: Create App
```powershell
heroku create your-app-name
```

### Step 3: Add MySQL Database
```powershell
heroku addons:create cleardb:ignite
```

### Step 4: Get Database Credentials
```powershell
heroku config:get CLEARDB_DATABASE_URL
```
Save this! Format: `mysql://username:password@hostname/database_name`

### Step 5: Set Environment Variables
```powershell
heroku config:set SMTP_HOST=smtp.gmail.com
heroku config:set SMTP_PORT=587
heroku config:set SMTP_USERNAME=your-email@gmail.com
heroku config:set SMTP_PASSWORD=your-app-password
heroku config:set SMTP_FROM_EMAIL=your-email@gmail.com
heroku config:set SMTP_FROM_NAME="Note Sharing App"
heroku config:set SITE_URL=https://your-app.herokuapp.com
```

### Step 6: Deploy
```powershell
git add .
git commit -m "Deploy to Heroku"
git push heroku main
```

### Step 7: Import Database
Parse the CLEARDB_DATABASE_URL to get credentials, then:
```powershell
mysql -h hostname -u username -ppassword database_name < database_schema.sql
```

### Step 8: Open Your App
```powershell
heroku open
```

## ⚠️ Important Notes:

### File Uploads Warning
Heroku has **ephemeral storage** - uploaded files are deleted when dyno restarts!

**Solutions:**
1. **Integrate AWS S3** for permanent file storage
2. **Use Cloudinary** for uploads
3. **Disable file uploads** temporarily

### Database Limitations
- Free tier: 5MB storage, 10 connections
- Monitor usage: `heroku addons:info cleardb`

### Update Config Loading
Before deploying, update files that load `conf.php`:
- ClassAutoLoad.php
- Any controller that uses config

## 🔍 After Deployment:

### Test Everything:
- [ ] Homepage loads
- [ ] Sign up works
- [ ] Sign in works
- [ ] 2FA emails send
- [ ] Create notes works
- [ ] Edit notes works
- [ ] Delete notes works
- [ ] Favorites work
- [ ] Admin login works
- [ ] Categories work

### Monitor Logs:
```powershell
heroku logs --tail
```

### Check Database:
```powershell
heroku addons:info cleardb
```

## 🆘 Troubleshooting:

### App crashes on startup
```powershell
heroku logs --tail
```
Check for PHP errors or missing dependencies

### Database connection fails
```powershell
heroku config:get CLEARDB_DATABASE_URL
```
Verify credentials are correct

### Emails not sending
Check SMTP config:
```powershell
heroku config
```

### 500 Error
Enable PHP error display (temporarily):
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## 📞 Useful Commands:

```powershell
# Restart app
heroku restart

# View config
heroku config

# Scale dynos
heroku ps:scale web=1

# Access database
heroku addons:info cleardb

# View app info
heroku apps:info

# Open app
heroku open

# SSH into dyno
heroku run bash
```

## 🎉 Success Checklist:
- [ ] App deployed successfully
- [ ] Database imported
- [ ] Environment variables set
- [ ] SMTP configured
- [ ] All features tested
- [ ] Logs show no errors

## 🔗 Resources:
- Heroku PHP Docs: https://devcenter.heroku.com/categories/php-support
- ClearDB Docs: https://devcenter.heroku.com/articles/cleardb
- Heroku CLI Docs: https://devcenter.heroku.com/articles/heroku-cli

Good luck with your deployment! 🚀
