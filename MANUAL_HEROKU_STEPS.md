# 🚀 Manual Heroku Deployment Steps

## ⚠️ Account Verification Required

Heroku requires you to verify your account by adding a credit/debit card (even for free tier).
**Don't worry - you won't be charged unless you upgrade from free tier.**

### Step 1: Verify Your Heroku Account
1. Go to: https://heroku.com/verify
2. Add your credit/debit card
3. Complete verification

---

## 📝 After Verification, Run These Commands:

### 1. Create Heroku App
```powershell
heroku create notesharing-app-kiragu
```

### 2. Add MySQL Database (Free Tier)
```powershell
heroku addons:create cleardb:ignite --app notesharing-app-kiragu
```

### 3. Get Database Credentials
```powershell
heroku config:get CLEARDB_DATABASE_URL --app notesharing-app-kiragu
```
**Save this!** It looks like: `mysql://username:password@hostname/database_name`

### 4. Set Environment Variables
Replace with your actual Gmail details:
```powershell
heroku config:set SMTP_HOST=smtp.gmail.com --app notesharing-app-kiragu
heroku config:set SMTP_PORT=587 --app notesharing-app-kiragu
heroku config:set SMTP_USERNAME=your-email@gmail.com --app notesharing-app-kiragu
heroku config:set SMTP_PASSWORD=your-gmail-app-password --app notesharing-app-kiragu
heroku config:set SMTP_FROM_EMAIL=your-email@gmail.com --app notesharing-app-kiragu
heroku config:set "SMTP_FROM_NAME=Note Sharing App" --app notesharing-app-kiragu
heroku config:set "SITE_URL=https://notesharing-app-kiragu.herokuapp.com" --app notesharing-app-kiragu
```

### 5. Deploy to Heroku
```powershell
git push heroku main
```

### 6. Import Database
Parse your CLEARDB_DATABASE_URL to get the credentials, then:
```powershell
# Example: mysql://b123abc:xyz789@us-cdbr-east.cleardb.com/heroku_abc123
# Extract: hostname, username, password, database_name

mysql -h hostname -u username -ppassword database_name < database_schema.sql
```

### 7. Open Your App
```powershell
heroku open --app notesharing-app-kiragu
```

### 8. Check Logs
```powershell
heroku logs --tail --app notesharing-app-kiragu
```

---

## 📧 Get Gmail App Password

1. Go to: https://myaccount.google.com/apppasswords
2. Select "Mail" as the app
3. Select "Windows Computer" as device
4. Click "Generate"
5. Copy the 16-character password
6. Use it in SMTP_PASSWORD above

---

## ✅ All Files Are Ready!

Your project already has:
- ✅ Procfile
- ✅ composer.json
- ✅ heroku-config.php
- ✅ database_schema.sql
- ✅ Updated ClassAutoLoad.php

Everything is prepared for deployment!

---

## 🆘 Troubleshooting

### App won't create
- Verify your account at https://heroku.com/verify

### Database import fails
- Check your CLEARDB credentials
- Make sure you parsed the URL correctly

### App crashes
- Check logs: `heroku logs --tail --app notesharing-app-kiragu`

### Emails not sending
- Use Gmail App Password, not regular password
- Enable "Less secure app access" if using old Gmail

---

## 🎉 After Successful Deployment

Your app will be live at:
**https://notesharing-app-kiragu.herokuapp.com**

Test everything:
- Sign up
- Sign in
- 2FA codes
- Create notes
- Categories
- Favorites

Good luck! 🚀
