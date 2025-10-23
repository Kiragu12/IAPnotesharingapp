# IAP Note Sharing App - Setup Guide

## 📋 Table of Contents
1. [Prerequisites](#prerequisites)
2. [Installation Steps](#installation-steps)
3. [Database Setup](#database-setup)
4. [Configuration](#configuration)
5. [Testing the Installation](#testing-the-installation)
6. [Troubleshooting](#troubleshooting)
7. [Project Structure](#project-structure)
8. [Features](#features)

---

## 🔧 Prerequisites

Before you begin, make sure you have the following installed on your system:

### Required Software
- **PHP 8.0 or higher**
  - Check version: `php -v`
  - Download from: https://www.php.net/downloads
  
- **Apache Web Server (or any PHP-compatible web server)**
  - XAMPP (recommended for Windows): https://www.apachefriends.org/
  - WAMP: https://www.wampserver.com/
  - MAMP (for macOS): https://www.mamp.info/
  
- **MySQL/MariaDB Database Server**
  - Usually included with XAMPP/WAMP/MAMP
  - Check version: `mysql --version`
  
- **Composer** (PHP dependency manager)
  - Check version: `composer --version`
  - Download from: https://getcomposer.org/download/

### PHP Extensions Required
Make sure these extensions are enabled in your `php.ini`:
- `php_pdo`
- `php_pdo_mysql`
- `php_openssl`
- `php_mbstring`
- `php_curl`

---

## 📥 Installation Steps

### Step 1: Clone or Download the Project

```bash
# If using Git
git clone https://github.com/Kiragu12/IAPnotesharingapp.git

# Or download the ZIP file and extract it
```

### Step 2: Move to Web Server Directory

Move the project folder to your web server's document root:

**For XAMPP (Windows):**
```
C:\xampp\htdocs\IAPnotesharingapp-1\
```

**For XAMPP (Linux/macOS):**
```
/opt/lampp/htdocs/IAPnotesharingapp-1/
```

**For WAMP:**
```
C:\wamp64\www\IAPnotesharingapp-1\
```

### Step 3: Install Dependencies

Open a terminal/command prompt in the project directory and run:

```bash
composer install
```

This will install:
- PHPMailer (for sending emails)
- Other required dependencies

---

## 🗄️ Database Setup

### Step 1: Create the Database

1. Open **phpMyAdmin** (usually at `http://localhost/phpmyadmin`)
2. Click on "New" to create a new database
3. Database name: `iap_notesharing` (or your preferred name)
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

### Step 2: Import the Database Schema

**Option A: Using phpMyAdmin (Recommended)**
1. Select your database from the left sidebar
2. Click the "Import" tab
3. Click "Choose File" and select `DATABASE_SETUP.sql` from the project root
4. Scroll down and click "Go"
5. You should see: "Import has been successfully finished"

**Option B: Using MySQL Command Line**
```bash
mysql -u root -p iap_notesharing < DATABASE_SETUP.sql
```

### Step 3: Verify Tables Were Created

Run this query in phpMyAdmin SQL tab:
```sql
SHOW TABLES;
```

You should see 4 tables:
- ✅ `users`
- ✅ `two_factor_codes`
- ✅ `remember_tokens`
- ✅ `password_resets`

---

## ⚙️ Configuration

### Step 1: Copy Configuration Template

1. Locate the file `conf.sample.php` in the project root
2. Copy it and rename to `conf.php`:

```bash
# Windows Command Prompt
copy conf.sample.php conf.php

# Linux/macOS/Git Bash
cp conf.sample.php conf.php
```

### Step 2: Edit Configuration File

Open `conf.php` in a text editor and update the following settings:

#### Database Configuration
```php
// Database credentials
$conf['db_host'] = 'localhost';        // Usually 'localhost'
$conf['db_name'] = 'iap_notesharing'; // Your database name
$conf['db_user'] = 'root';             // Your MySQL username
$conf['db_pass'] = '';                 // Your MySQL password (empty for XAMPP default)
```

#### Email Configuration (SMTP Settings)

**For Gmail:**
```php
$conf['smtp_host'] = 'smtp.gmail.com';
$conf['smtp_port'] = 587;
$conf['smtp_secure'] = 'tls';
$conf['smtp_username'] = 'your-email@gmail.com';
$conf['smtp_password'] = 'your-app-password'; // See below for App Password setup
$conf['admin_email'] = 'your-email@gmail.com';
```

**Important: Gmail App Password Setup**
1. Go to: https://myaccount.google.com/security
2. Enable 2-Step Verification
3. Search for "App passwords" and create one for "Mail"
4. Copy the 16-character password and use it in `smtp_password`

**For Other Email Providers:**
- **Outlook/Hotmail:** `smtp.office365.com`, Port: `587`, TLS
- **Yahoo:** `smtp.mail.yahoo.com`, Port: `587`, TLS
- **Custom SMTP:** Contact your email provider for settings

#### Site Settings
```php
$conf['site_name'] = 'IAP Note Sharing';
$conf['site_url'] = 'http://localhost/IAPnotesharingapp-1';
```

#### Security Settings
```php
$conf['min_password_length'] = 8;
$conf['valid_email_domain'] = ['gmail.com', 'yahoo.com', 'outlook.com']; // Allowed email domains
$conf['debug_mode'] = false; // Set to true only during development
```

### Step 3: Set File Permissions (Linux/macOS only)

```bash
chmod -R 755 .
chmod -R 777 uploads/  # If you have an uploads directory
```

---

## 🧪 Testing the Installation

### 1. Access the Application

Open your web browser and go to:
```
http://localhost/IAPnotesharingapp-1/
```

You should see the landing/home page.

### 2. Test User Registration

1. Go to: `http://localhost/IAPnotesharingapp-1/signup.php`
2. Fill in the registration form:
   - Full Name: `Test User`
   - Email: `test@gmail.com` (use a domain in your `valid_email_domain` list)
   - Password: `Test@123` (at least 8 characters)
3. Click "Create Account"
4. You should see a success message

### 3. Test Sign-In with 2FA

1. Go to: `http://localhost/IAPnotesharingapp-1/signin.php`
2. Enter your email and password
3. Check your email for the 6-digit verification code
4. Enter the code on the verification page
5. You should be redirected to the dashboard

### 4. Test "Remember Me" Feature

1. Sign in again with "Remember this device" checked
2. Close your browser completely
3. Reopen and visit the application
4. You should be automatically logged in!

---

## 🐛 Troubleshooting

### Issue: "Database connection failed"

**Solution:**
1. Check that MySQL/MariaDB is running
2. Verify database credentials in `conf.php`
3. Make sure the database `iap_notesharing` exists
4. Test connection with:
   ```bash
   mysql -u root -p
   ```

### Issue: "Email not sending" / "Could not send OTP"

**Solutions:**
1. **Check SMTP credentials** in `conf.php`
2. **For Gmail:** Make sure you're using an App Password, not your regular password
3. **Check PHP error logs** for detailed error messages
4. **Test email manually:**
   ```bash
   php -r "echo ini_get('SMTP');"  # Check PHP mail config
   ```
5. **Firewall:** Ensure port 587 (TLS) or 465 (SSL) is not blocked

### Issue: "Call to undefined function password_hash()"

**Solution:**
- Your PHP version is too old. Upgrade to PHP 7.4 or higher.

### Issue: "Class 'PHPMailer' not found"

**Solution:**
```bash
composer install
```

### Issue: "Permission denied" (Linux/macOS)

**Solution:**
```bash
sudo chmod -R 755 /opt/lampp/htdocs/IAPnotesharingapp-1/
```

### Issue: Blank white page / No errors shown

**Solutions:**
1. Enable error reporting in `php.ini`:
   ```ini
   display_errors = On
   error_reporting = E_ALL
   ```
2. Check Apache error logs:
   - **XAMPP:** `C:\xampp\apache\logs\error.log`
   - **Linux:** `/var/log/apache2/error.log`

---

## 📁 Project Structure

```
IAPnotesharingapp-1/
│
├── index.php                  # Landing/home page
├── signup.php                 # User registration
├── signin.php                 # User sign-in with 2FA
├── dashboard.php              # User dashboard (protected)
├── logout.php                 # Sign-out handler
├── forgot_password.php        # Password reset request
├── reset_password.php         # Password reset with token
│
├── conf.php                   # Configuration file (YOU CREATE THIS)
├── conf.sample.php            # Configuration template
├── ClassAutoLoad.php          # Auto-loads classes, checks remember tokens
│
├── Proc/                      # Processing classes
│   └── auth.php               # Authentication logic (signup, login, OTP)
│
├── Global/                    # Global utilities
│   ├── Database.php           # Database wrapper
│   ├── fncs.php               # Helper functions
│   ├── SendMail.php           # Email sending class
│   └── providers.php          # Service providers
│
├── Forms/                     # Form rendering classes
│   └── forms.php
│
├── Layouts/                   # Layout/template classes
│   └── layouts.php
│
├── Lang/                      # Language files
│   └── en.php                 # English translations
│
├── css/                       # Stylesheets
│   └── bootstrap.min.css
│
├── js/                        # JavaScript files
│   └── bootstrap.bundle.min.js
│
├── vendor/                    # Composer dependencies (auto-generated)
│
├── composer.json              # PHP dependencies
├── composer.lock              # Locked dependency versions
│
├── DATABASE_SETUP.sql         # Complete database schema
├── README.md                  # This file
└── CLEANUP_SUMMARY.md         # Project cleanup log
```

---

## ✨ Features

### Authentication & Security
- ✅ **User Registration** with email validation
- ✅ **Secure Password Hashing** (bcrypt)
- ✅ **Two-Factor Authentication (2FA)** via email OTP
- ✅ **Remember Me** functionality (30-day secure tokens)
- ✅ **Password Reset** via email link
- ✅ **Session Management** with auto-login
- ✅ **CSRF Protection** (form tokens)

### User Experience
- ✅ **Responsive Design** (Bootstrap 5.3.0)
- ✅ **Single-Page Flows** for signup and signin
- ✅ **Real-time Validation** and error messages
- ✅ **Email Notifications** for OTP codes and account activity
- ✅ **Clean, Modern UI** with purple gradient theme

### Code Quality
- ✅ **Object-Oriented PHP** with proper class structure
- ✅ **PDO Database Layer** (prepared statements, SQL injection protection)
- ✅ **Modular Architecture** (separation of concerns)
- ✅ **Error Handling** and logging
- ✅ **Clean, Production-Ready Code** (no debug files)

---

## 📧 Support & Contact

If you encounter any issues during setup:

1. **Check the Troubleshooting section** above
2. **Review PHP error logs** in your web server's log directory
3. **Verify all configuration settings** in `conf.php`
4. **Contact the development team** with:
   - PHP version (`php -v`)
   - Error message (from logs)
   - Steps to reproduce the issue

---

## 🔐 Security Notes

### For Production Deployment:

1. **Change default credentials:**
   - Database password should not be empty
   - Create a dedicated database user with limited privileges

2. **Enable HTTPS:**
   - Use SSL/TLS certificates
   - Update `conf.php`: `$conf['site_url'] = 'https://yourdomain.com';`

3. **Set secure cookie flags:**
   - In `Proc/auth.php`, change `createRememberToken()`:
     ```php
     setcookie(
         'remember_token',
         $token,
         time() + (30 * 24 * 60 * 60),
         '/',
         '',
         true,  // Secure flag - only send over HTTPS
         true   // HttpOnly flag
     );
     ```

4. **Disable debug mode:**
   ```php
   $conf['debug_mode'] = false;
   ```

5. **Restrict file permissions** (Linux):
   ```bash
   chmod 644 conf.php
   chmod 755 -R .
   ```

6. **Keep dependencies updated:**
   ```bash
   composer update
   ```

---

## 📄 License

This project is for educational purposes as part of the IAP course.

---

**Last Updated:** October 23, 2025  
**Version:** 1.0.0  
**Maintainer:** IAP Development Team
