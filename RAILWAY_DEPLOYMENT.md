# 🚀 Railway.app Deployment Guide - EASY & FREE!

## ✅ Why Railway?
- ✅ **No credit card required!**
- ✅ $5 free credit per month
- ✅ Supports PHP + MySQL
- ✅ Super easy deployment (just 5 minutes!)
- ✅ Modern dashboard
- ✅ Automatic deployments from GitHub

---

## 📋 Step-by-Step Deployment

### Step 1: Push to GitHub (Already Done! ✅)
Your code is already on GitHub at: https://github.com/Kiragu12/IAPnotesharingapp

### Step 2: Create Railway Account
1. Go to: **https://railway.app/**
2. Click **"Start a New Project"**
3. Click **"Login with GitHub"**
4. Authorize Railway to access your GitHub

### Step 3: Deploy from GitHub
1. Click **"Deploy from GitHub repo"**
2. Select your repository: **Kiragu12/IAPnotesharingapp** (or webnoteapp)
3. Click **"Deploy Now"**

Railway will automatically:
- Detect it's a PHP app
- Install dependencies
- Start the app

### Step 4: Add MySQL Database
1. In your Railway project dashboard
2. Click **"+ New"**
3. Select **"Database"**
4. Choose **"Add MySQL"**
5. Wait for database to provision (~30 seconds)

### Step 5: Connect Database to App
Railway automatically creates these environment variables:
- `MYSQLHOST`
- `MYSQLPORT`
- `MYSQLDATABASE`
- `MYSQLUSER`
- `MYSQLPASSWORD`

**These are already configured in `railway-config.php`!** ✅

### Step 6: Add Environment Variables
In your Railway app settings, add these variables:

Click on your app → **Variables** tab → Add:

```
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-gmail-app-password
SMTP_FROM_EMAIL=your-email@gmail.com
SMTP_FROM_NAME=Note Sharing App
```

### Step 7: Get Your Database Credentials
1. Click on the **MySQL database** in Railway
2. Go to **"Connect"** tab
3. You'll see connection details
4. Or click **"Data"** tab to use web interface

### Step 8: Import Database Schema
**Option A: Using Railway Web Interface**
1. Click on MySQL database
2. Go to **"Data"** tab
3. Click **"Query"**
4. Copy contents of `database_schema.sql`
5. Paste and execute

**Option B: Using MySQL Client**
1. Get connection string from Railway
2. Run:
```powershell
mysql -h mysql-host.railway.app -u root -pYOUR_PASSWORD -P PORT database_name < database_schema.sql
```

### Step 9: Deploy!
1. Railway automatically deploys when you push to GitHub
2. Or click **"Deploy"** in Railway dashboard

### Step 10: Get Your URL
1. Click on your app in Railway
2. Go to **"Settings"** tab
3. Under **"Domains"**, click **"Generate Domain"**
4. You'll get a URL like: `notesharing-app-production.up.railway.app`

---

## 🎉 You're Live!

Your app will be accessible at:
**https://your-app-name.up.railway.app**

---

## 📧 Get Gmail App Password

Before adding SMTP variables:
1. Go to: https://myaccount.google.com/apppasswords
2. Select "Mail" as the app
3. Select "Windows Computer" as device
4. Click "Generate"
5. Copy the 16-character password
6. Use it in `SMTP_PASSWORD` variable

---

## 🔄 Automatic Deployments

Railway automatically deploys when you push to GitHub:
```powershell
git add .
git commit -m "Update app"
git push origin main
```

Railway will detect changes and redeploy! 🚀

---

## 💰 Free Tier Limits

Railway gives you:
- **$5 free credit per month**
- This is enough for:
  - Small PHP app
  - MySQL database
  - Low-medium traffic

If you run out:
- Add a credit card (only charged if you exceed free tier)
- Or wait for next month's credit

---

## 📊 Monitor Your App

In Railway dashboard:
- **Deployments**: See deployment history
- **Metrics**: CPU, Memory, Network usage
- **Logs**: View application logs in real-time
- **Variables**: Manage environment variables

---

## 🆘 Troubleshooting

### App won't start
- Check **Logs** in Railway dashboard
- Look for PHP errors
- Verify `composer.json` is correct

### Database connection fails
- Check if MySQL service is running
- Verify environment variables are set
- Check `railway-config.php`

### Emails not sending
- Use Gmail App Password (not regular password)
- Check SMTP variables are set correctly

### 500 Error
- Check logs in Railway dashboard
- Enable PHP error display temporarily

---

## ✅ Post-Deployment Checklist

Test everything:
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

---

## 🎯 Quick Reference

**Railway Dashboard**: https://railway.app/dashboard
**Your GitHub Repo**: https://github.com/Kiragu12/IAPnotesharingapp
**Gmail App Passwords**: https://myaccount.google.com/apppasswords

---

## 🚀 Ready to Deploy?

1. Go to **https://railway.app/**
2. Login with GitHub
3. Deploy from your repo
4. Add MySQL database
5. Set environment variables
6. Import database schema
7. Generate domain
8. **You're live!** 🎉

**Total time: ~5-10 minutes!**

---

Good luck! Railway is super easy - you'll love it! 💜
