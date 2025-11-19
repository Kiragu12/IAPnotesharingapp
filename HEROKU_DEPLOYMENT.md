## Heroku Deployment Guide for Note Sharing App

### Prerequisites
1. Install Heroku CLI: https://devcenter.heroku.com/articles/heroku-cli
2. Have a Heroku account: https://signup.heroku.com/
3. Install Git (already have it)

### Step 1: Install Heroku CLI
```powershell
# Download and install from: https://devcenter.heroku.com/articles/heroku-cli
# Or use winget:
winget install Heroku.HerokuCLI
```

### Step 2: Login to Heroku
```powershell
heroku login
```

### Step 3: Create a Heroku App
```powershell
cd C:\Apache24\htdocs\IAPnotesharingapp-1
heroku create your-notesharing-app
# Replace 'your-notesharing-app' with your desired app name
```

### Step 4: Add ClearDB MySQL Database
```powershell
# Add free MySQL database addon
heroku addons:create cleardb:ignite

# Get database credentials
heroku config:get CLEARDB_DATABASE_URL
# This will show: mysql://username:password@hostname/database_name
```

### Step 5: Update Database Configuration
Create a new file `heroku-config.php` that reads from environment variables:

```php
<?php
// Parse ClearDB URL from Heroku
$url = parse_url(getenv("CLEARDB_DATABASE_URL"));

return [
    'db_host' => $url["host"],
    'db_name' => substr($url["path"], 1),
    'db_user' => $url["user"],
    'db_pass' => $url["pass"],
    'site_name' => 'Note Sharing App',
    'site_url' => getenv("SITE_URL") ?: 'https://your-app.herokuapp.com',
    'admin_email' => getenv("SMTP_FROM_EMAIL"),
    'smtp_host' => getenv("SMTP_HOST"),
    'smtp_port' => getenv("SMTP_PORT"),
    'smtp_username' => getenv("SMTP_USERNAME"),
    'smtp_password' => getenv("SMTP_PASSWORD"),
    'valid_email_domain' => ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com'],
    'min_password_length' => 8
];
```

### Step 6: Import Database Schema
```powershell
# First, export your local database
# In MySQL/MariaDB:
# mysqldump -u root -p noteshare_db > database.sql

# Get ClearDB credentials
heroku config:get CLEARDB_DATABASE_URL

# Import to ClearDB (use credentials from above)
mysql -h hostname -u username -p database_name < database.sql
```

### Step 7: Set Environment Variables
```powershell
# Set SMTP configuration
heroku config:set SMTP_HOST=smtp.gmail.com
heroku config:set SMTP_PORT=587
heroku config:set SMTP_USERNAME=your-email@gmail.com
heroku config:set SMTP_PASSWORD=your-app-password
heroku config:set SMTP_FROM_EMAIL=your-email@gmail.com
heroku config:set SMTP_FROM_NAME="Note Sharing App"
heroku config:set SITE_URL=https://your-app-name.herokuapp.com
```

### Step 8: Create .htaccess for Apache
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
```

### Step 9: Update .gitignore
Make sure sensitive files aren't committed:
```
vendor/
.env
uploads/documents/*
!uploads/documents/.gitkeep
debug.log
conf.php
```

### Step 10: Commit and Deploy
```powershell
# Add all files
git add .

# Commit
git commit -m "Prepare for Heroku deployment"

# Deploy to Heroku
git push heroku main
```

### Step 11: Create Upload Directory
```powershell
# SSH into Heroku dyno
heroku run bash

# Create uploads directory
mkdir -p uploads/documents
chmod 755 uploads/documents
exit
```

### Step 12: View Your App
```powershell
heroku open
```

### Step 13: Monitor Logs
```powershell
# View real-time logs
heroku logs --tail

# View specific error logs
heroku logs --tail | grep ERROR
```

## Important Notes:

### File Uploads on Heroku
⚠️ **Heroku has an ephemeral filesystem** - uploaded files will be deleted when the dyno restarts!

**Solutions:**
1. **AWS S3** (Recommended) - Store files in S3 bucket
2. **Cloudinary** - Free tier for file storage
3. **Disable file uploads** - Only allow text notes

### Database Limits
- ClearDB Ignite (Free): 5MB storage, 10 connections
- Upgrade if you need more

### Custom Domain (Optional)
```powershell
heroku domains:add www.yourdomain.com
```

## Troubleshooting:

### Issue: Database connection fails
```powershell
# Check database URL
heroku config:get CLEARDB_DATABASE_URL

# Check logs
heroku logs --tail
```

### Issue: PHP version error
Update `composer.json` to specify PHP version

### Issue: File upload not working
Heroku's filesystem is read-only except for `/tmp`
Consider using cloud storage (S3, Cloudinary)

## Useful Commands:
```powershell
# Restart app
heroku restart

# Check app status
heroku ps

# Access database
heroku run mysql -h hostname -u username -p

# Scale dynos
heroku ps:scale web=1
```

## Next Steps After Deployment:
1. Test all functionality
2. Set up cloud storage for uploads (if needed)
3. Configure custom domain (optional)
4. Set up monitoring/alerts
5. Enable HTTPS (automatic on Heroku)

Good luck! 🚀
