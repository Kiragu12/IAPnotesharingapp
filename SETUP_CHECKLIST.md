# Team Setup Checklist

## IAP Note Sharing App - New Developer Onboarding

**Developer Name:** _______________________  
**Setup Date:** _______________________  
**Completed By:** _______________________

---

## ☐ PART 1: Environment Setup (15 min)

### Software Installation
- [ ] PHP 8.0+ installed (`php -v` to verify)
- [ ] Apache/XAMPP/WAMP running
- [ ] MySQL/MariaDB running
- [ ] Composer installed (`composer --version` to verify)
- [ ] Git installed (optional, `git --version` to verify)

### PHP Extensions Enabled
Check in `php.ini` (usually at `C:\xampp\php\php.ini`):
- [ ] `extension=pdo_mysql` (uncommented)
- [ ] `extension=openssl` (uncommented)
- [ ] `extension=mbstring` (uncommented)
- [ ] `extension=curl` (uncommented)

**Note:** After editing php.ini, restart Apache!

---

## ☐ PART 2: Project Setup (10 min)

### Get the Code
- [ ] Project downloaded/cloned to: `________________________`
- [ ] Located in web server directory:
  - [ ] XAMPP: `C:\xampp\htdocs\IAPnotesharingapp-1\`
  - [ ] WAMP: `C:\wamp64\www\IAPnotesharingapp-1\`
  - [ ] Other: `_______________________`

### Install Dependencies
```bash
cd IAPnotesharingapp-1
composer install
```
- [ ] `vendor/` directory created
- [ ] No error messages
- [ ] PHPMailer installed (check `vendor/phpmailer/`)

### Create Configuration File
- [ ] Copied `conf.sample.php` to `conf.php`
- [ ] `conf.php` exists in project root
- [ ] `conf.php` is readable (not permission errors)

---

## ☐ PART 3: Database Setup (5 min)

### Create Database
- [ ] Opened phpMyAdmin at `http://localhost/phpmyadmin`
- [ ] Created database named: `iap_notesharing`
- [ ] Collation set to: `utf8mb4_unicode_ci`

### Import Schema
- [ ] Imported `DATABASE_SETUP.sql` via phpMyAdmin
- [ ] No import errors
- [ ] Verified 4 tables created:
  - [ ] `users`
  - [ ] `two_factor_codes`
  - [ ] `remember_tokens`
  - [ ] `password_resets`

### Verify Import
Run this in phpMyAdmin SQL tab:
```sql
SHOW TABLES;
```
Expected result: **4 tables listed**
- [ ] Query ran successfully

---

## ☐ PART 4: Configuration (10 min)

### Database Settings in `conf.php`
```php
$conf['db_host'] = 'localhost';              // ← Verified
$conf['db_name'] = 'iap_notesharing';        // ← Matches database name
$conf['db_user'] = 'root';                    // ← Verified (or your MySQL user)
$conf['db_pass'] = '';                        // ← Verified (empty for XAMPP default)
```
- [ ] Database host is correct
- [ ] Database name matches created database
- [ ] Database user is correct
- [ ] Database password is correct

### Email Settings in `conf.php`

**Email Provider:** _____________________ (Gmail, Outlook, Yahoo, etc.)

#### Gmail Setup (if using Gmail):
1. [ ] Enabled 2-Step Verification at: https://myaccount.google.com/security
2. [ ] Generated App Password at: https://myaccount.google.com/apppasswords
3. [ ] Copied 16-character app password: `________________`

#### Configuration:
```php
$conf['smtp_host'] = '________________';      // e.g., smtp.gmail.com
$conf['smtp_port'] = ______;                   // e.g., 587
$conf['smtp_secure'] = '______';               // tls or ssl
$conf['smtp_username'] = '________________';   // Your email
$conf['smtp_password'] = '________________';   // App password (not regular password!)
$conf['admin_email'] = '________________';     // Your email
```
- [ ] SMTP host is correct for your email provider
- [ ] SMTP port is correct (587 for TLS, 465 for SSL)
- [ ] SMTP username is your full email address
- [ ] SMTP password is App Password (for Gmail) or email password
- [ ] Admin email is set

### Site Settings in `conf.php`
```php
$conf['site_name'] = 'IAP Note Sharing';
$conf['site_url'] = 'http://localhost/IAPnotesharingapp-1';
$conf['debug_mode'] = true;  // Set to false for production
```
- [ ] Site name is set
- [ ] Site URL matches your local setup
- [ ] Debug mode set to `true` for development

### Other Settings
- [ ] `valid_email_domain` array includes your test email domains
- [ ] `min_password_length` is set (default: 8)

---

## ☐ PART 5: Testing (15 min)

### Basic Access Test
- [ ] Visited `http://localhost/IAPnotesharingapp-1/`
- [ ] Home page loads without errors
- [ ] No PHP errors displayed

### Registration Test
- [ ] Visited `http://localhost/IAPnotesharingapp-1/signup.php`
- [ ] Filled registration form:
  - **Email:** ____________________
  - **Name:** ____________________
  - **Password:** ____________________
- [ ] Registration successful
- [ ] User appears in database (check `users` table in phpMyAdmin)

### Email Test
- [ ] Received welcome email at registered address
- [ ] Email not in spam folder
- [ ] Email contains correct sender name

### Sign-In Test
- [ ] Visited `http://localhost/IAPnotesharingapp-1/signin.php`
- [ ] Entered email and password
- [ ] Clicked "Continue to Verification"
- [ ] Received OTP email
- [ ] OTP code: `______` (6 digits)
- [ ] Entered OTP on verification page
- [ ] Successfully logged in
- [ ] Redirected to dashboard

### Dashboard Access Test
- [ ] Dashboard page loads
- [ ] User name displayed correctly
- [ ] Navigation works
- [ ] No PHP errors

### Remember Me Test
- [ ] Signed in with "Remember this device" checked
- [ ] Closed browser completely
- [ ] Reopened browser
- [ ] Visited application
- [ ] Automatically logged in (didn't need to enter password again)

### Logout Test
- [ ] Clicked logout/sign out
- [ ] Redirected to home page
- [ ] Session cleared (can't access dashboard without logging in)

---

## ☐ PART 6: Troubleshooting (If Needed)

### If Database Connection Fails:
- [ ] MySQL/MariaDB service is running
- [ ] Database credentials in `conf.php` are correct
- [ ] Tested connection with: `mysql -u root -p`

### If Email Not Sending:
- [ ] SMTP credentials are correct
- [ ] Using App Password (for Gmail), not regular password
- [ ] Port 587 or 465 not blocked by firewall
- [ ] Checked PHP error logs for detailed error

### If Blank White Page:
- [ ] Enabled error display:
  ```php
  // Add to top of problematic page
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ```
- [ ] Checked Apache error logs:
  - Windows: `C:\xampp\apache\logs\error.log`
  - Linux: `/var/log/apache2/error.log`

### If Composer Fails:
- [ ] Ran as administrator/sudo
- [ ] Internet connection is working
- [ ] Tried: `composer install --no-scripts`

---

## ☐ PART 7: Code Familiarization (30 min)

### Key Files to Review:
- [ ] `signin.php` - Sign-in flow with 2FA
- [ ] `signup.php` - Registration flow
- [ ] `Proc/auth.php` - Authentication methods
- [ ] `Global/Database.php` - Database wrapper
- [ ] `Global/SendMail.php` - Email sending
- [ ] `ClassAutoLoad.php` - Class loader and auto-login

### Understanding the Flow:
- [ ] Read through signin.php to understand multi-stage flow
- [ ] Reviewed how OTP is generated in `Proc/auth.php`
- [ ] Understood session management in signin/signup
- [ ] Familiar with database tables and relationships

---

## ☐ FINAL CHECKLIST

- [ ] All tests passed
- [ ] No PHP errors in browser
- [ ] No errors in Apache logs
- [ ] Can register new accounts
- [ ] Can sign in with 2FA
- [ ] Emails are being sent and received
- [ ] Remember Me feature works
- [ ] Dashboard is accessible after login
- [ ] Code structure is understood
- [ ] Ready to start development work

---

## 📝 Notes & Issues Encountered

_Use this space to note any problems or deviations from the standard setup:_

```
_________________________________________________________________________

_________________________________________________________________________

_________________________________________________________________________

_________________________________________________________________________

_________________________________________________________________________
```

---

## ✅ Sign-Off

**Setup Completed By:** _______________________  
**Date:** _______________________  
**Time Taken:** _______ minutes  

**Verified By (Team Lead/Senior Dev):** _______________________  
**Date:** _______________________

---

**For Help:**
- Read `README.md` for detailed documentation
- Read `QUICK_START.md` for quick reference
- Check `DATABASE_SETUP.sql` comments for table info
- Review `conf.sample.php` for all available configuration options

**Happy Coding! 🚀**
