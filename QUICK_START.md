# Quick Setup Guide - IAP Note Sharing App

## ⚡ Super Quick Start (5 Minutes)

### 1. Prerequisites Check
```bash
php -v          # Should be 8.0+
mysql --version # Check MySQL is installed
composer --version # Check Composer is installed
```

### 2. Setup Steps
```bash
# 1. Extract project to web server directory
# XAMPP Windows: C:\xampp\htdocs\IAPnotesharingapp-1\
# XAMPP Linux: /opt/lampp/htdocs/IAPnotesharingapp-1\

# 2. Install dependencies
cd IAPnotesharingapp-1
composer install

# 3. Create configuration file
copy conf.sample.php conf.php  # Windows
cp conf.sample.php conf.php    # Linux/Mac
```

### 3. Database Setup
1. Open **phpMyAdmin**: `http://localhost/phpmyadmin`
2. Create database: `iap_notesharing`
3. Import: Select `DATABASE_SETUP.sql` → Click "Go"

### 4. Configure `conf.php`
```php
// Database
$conf['db_name'] = 'iap_notesharing';
$conf['db_user'] = 'root';
$conf['db_pass'] = '';  // Empty for XAMPP default

// Email (Gmail example)
$conf['smtp_username'] = 'your-email@gmail.com';
$conf['smtp_password'] = 'your-16-char-app-password';
$conf['admin_email'] = 'your-email@gmail.com';
```

### 5. Test!
- Visit: `http://localhost/IAPnotesharingapp-1/`
- Register: `http://localhost/IAPnotesharingapp-1/signup.php`
- Sign In: `http://localhost/IAPnotesharingapp-1/signin.php`

---

## 📧 Gmail App Password Setup (2 Minutes)

1. Go to: https://myaccount.google.com/security
2. Enable **2-Step Verification**
3. Search for **"App passwords"**
4. Generate password for **"Mail"**
5. Copy 16-character password
6. Paste in `conf.php` as `smtp_password`

---

## 🗄️ Database Tables

After running `DATABASE_SETUP.sql`, you'll have:

| Table | Purpose |
|-------|---------|
| `users` | User accounts (email, password, name) |
| `two_factor_codes` | OTP codes for 2FA login |
| `remember_tokens` | "Remember Me" persistent login tokens |
| `password_resets` | Password reset tokens |

---

## 🔐 Test User Creation

### Create Test Account via Web:
1. Go to: `http://localhost/IAPnotesharingapp-1/signup.php`
2. Fill form and submit
3. Check your email for welcome message

### Or Insert Directly (phpMyAdmin):
```sql
-- Password is: Test@123
INSERT INTO users (email, password, full_name, email_verified, is_2fa_enabled) 
VALUES (
    'test@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Test User',
    TRUE,
    TRUE
);
```

---

## 🚨 Common Issues & Quick Fixes

### ❌ "Database connection failed"
```bash
# Check MySQL is running
# XAMPP: Open XAMPP Control Panel → Start MySQL
# Verify credentials in conf.php match your MySQL setup
```

### ❌ "Email not sending"
```php
// In conf.php, verify:
$conf['smtp_username'] = 'your-email@gmail.com';  // ← Correct email?
$conf['smtp_password'] = 'xxxx xxxx xxxx xxxx';   // ← App password (not regular password)?
```

### ❌ "Class 'PHPMailer' not found"
```bash
composer install
```

### ❌ Blank white page
```php
// Add to top of index.php temporarily:
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### ❌ "Permission denied" (Linux)
```bash
sudo chmod -R 755 /opt/lampp/htdocs/IAPnotesharingapp-1/
```

---

## 📝 Configuration Checklist

Before testing, verify these in `conf.php`:

- [ ] Database name matches your created database
- [ ] Database username is correct (usually `root`)
- [ ] Database password is correct (empty for XAMPP default)
- [ ] SMTP email is your actual email address
- [ ] SMTP password is **App Password** (16 chars), not regular password
- [ ] Admin email is set
- [ ] Site URL matches your local setup

---

## 🧪 Test Flow

### Complete Authentication Test:
1. **Sign Up** → Should receive welcome email
2. **Sign In** → Should receive 6-digit OTP email
3. **Enter OTP** → Should reach dashboard
4. **Sign Out** → Should return to home
5. **Sign In with "Remember Me"** → Close browser, reopen → Should auto-login

---

## 📂 Important Files

| File | Purpose |
|------|---------|
| `conf.php` | **Your configuration** (create from conf.sample.php) |
| `DATABASE_SETUP.sql` | **Run this in phpMyAdmin** to create tables |
| `README.md` | **Full documentation** with detailed instructions |
| `signin.php` | Sign-in page with 2FA |
| `signup.php` | Registration page |
| `dashboard.php` | Protected user dashboard |

---

## 🔧 Useful Commands

```bash
# Check PHP version
php -v

# Check PHP modules
php -m | grep pdo

# Test PHP syntax of a file
php -l signin.php

# View PHP configuration
php --ini

# Restart Apache (XAMPP)
# Windows: Use XAMPP Control Panel
# Linux: sudo /opt/lampp/lampp restart

# Check MySQL is running
# Windows: Task Manager → Services → MySQL
# Linux: sudo systemctl status mysql
```

---

## 📞 Quick Support

1. **Read full documentation**: `README.md`
2. **Check PHP error logs**:
   - XAMPP: `C:\xampp\apache\logs\error.log`
   - Linux: `/var/log/apache2/error.log`
3. **Enable debug mode** in `conf.php`:
   ```php
   $conf['debug_mode'] = true;
   ```
4. **Test individual components**:
   - Database: Run query in phpMyAdmin
   - Email: Use online SMTP tester
   - PHP: Create `<?php phpinfo(); ?>` file

---

## ✅ Success Indicators

You know it's working when:
- ✅ Registration creates new user in database
- ✅ Email arrives with OTP code (check spam folder)
- ✅ Sign-in requires OTP verification
- ✅ Dashboard shows after successful login
- ✅ "Remember Me" auto-logs you in after browser restart

---

**Setup Time:** ~5-10 minutes  
**Last Updated:** October 23, 2025  

Happy Coding! 🚀
