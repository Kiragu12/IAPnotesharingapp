# Quick Start: Testing Your Project

## 🚀 5-Minute Setup

### Step 1: Check Current Status
```powershell
# Check PHP syntax on all core files
php -l app/Controllers/Proc/auth.php
php -l app/Controllers/NotesController.php
php -l app/Services/Global/Database.php

# Check database tables
php scripts/check_tables.php

# View notes table schema
php scripts/show_notes_schema.php
```

**Expected:** All show "No syntax errors" ✅

---

### Step 2: Test Database Connection
```powershell
# Run simple DB connection test
php -r "require 'conf.php'; require 'app/Services/Global/Database.php'; \$db = new Database(\$conf); echo 'DB Connected: ' . (\$db->getPDO() ? 'YES' : 'NO');"
```

**Expected:** "DB Connected: YES" ✅

---

### Step 3: Manual Feature Test

#### Test 3A: Create Test User Account
```powershell
# Navigate to signup page in browser
start http://localhost/IAPnotesharingapp-1/views/auth/signup.php
```
1. Fill in form:
   - Name: Test User
   - Email: test@gmail.com
   - Password: Test1234!
2. Click "Create Account"
3. ✅ **Check:** Redirected to home with success message
4. ✅ **Check:** Email sent (look in debug.log or Mailtrap)

#### Test 3B: Login with 2FA
```powershell
start http://localhost/IAPnotesharingapp-1/views/auth/signin.php
```
1. Enter credentials from above
2. Click "Sign In"
3. ✅ **Check:** Redirected to 2FA page
4. Check session debug OTP:
   ```powershell
   # Look in debug.log for:
   Get-Content debug.log -Tail 20 | Select-String "debug_otp"
   ```
5. Enter OTP code
6. ✅ **Check:** Logged in to dashboard

#### Test 3C: Upload a Note
```powershell
start http://localhost/IAPnotesharingapp-1/views/notes/create.php
```
1. Click "File Upload" tab
2. Choose a PDF/DOCX file
3. Enter title: "Test Note"
4. Select category
5. Click "Upload Note"
6. ✅ **Check:** Redirected to dashboard
7. ✅ **Check:** File appears in uploads/documents/
8. ✅ **Check:** Note visible in Recent Notes

---

## 🔍 Current Issues Found

### 🔴 BLOCKER: Merge Conflicts
**Files:** `views/dashboard.php`, `views/notes/create.php`

**Quick fix options:**
```powershell
# Option A: Keep local changes (your version)
git checkout --ours views/dashboard.php views/notes/create.php
git add views/dashboard.php views/notes/create.php

# Option B: Keep remote changes (their version)
git checkout --theirs views/dashboard.php views/notes/create.php
git add views/dashboard.php views/notes/create.php

# Option C: Manual merge (recommended)
# Open files in VS Code and resolve conflicts manually
code views/dashboard.php
code views/notes/create.php
```

**After resolving:**
```powershell
git add views/dashboard.php views/notes/create.php
git commit -m "Resolved merge conflicts in dashboard and create views"
```

---

### 🟡 WARNING: Remember-Me Token Bug
**File:** `app/Controllers/Proc/auth.php`

**Issue:** Line 740 hashes token, but line 665 expects raw token

**Quick test:**
```powershell
# Test if remember-me works
# 1. Sign in with "Remember Me" checked
# 2. Close browser
# 3. Reopen and check if auto-logged in

# Check remember_tokens table
php -r "require 'conf.php'; require 'app/Services/Global/Database.php'; \$db = new Database(\$conf); print_r(\$db->fetchAll('SELECT * FROM remember_tokens'));"
```

**If broken:** See PROJECT_STATUS_REPORT.md Section "Known Issues #2" for fix

---

## 📊 Health Check Script

Create `health_check.php`:

```php
<?php
require_once 'conf.php';
require_once 'app/Services/Global/Database.php';

echo "=== NotesShare Health Check ===\n\n";

// 1. Database
try {
    $db = new Database($conf);
    echo "✅ Database: Connected\n";
    
    $tables = ['users', 'categories', 'notes', 'two_factor_codes', 'remember_tokens'];
    foreach ($tables as $table) {
        $count = $db->fetchOne("SELECT COUNT(*) as c FROM $table")['c'];
        echo "   - $table: $count rows\n";
    }
} catch (Exception $e) {
    echo "❌ Database: FAILED - " . $e->getMessage() . "\n";
}

// 2. Upload directory
$uploadDir = __DIR__ . '/uploads/documents/';
if (is_dir($uploadDir) && is_writable($uploadDir)) {
    echo "✅ Upload Directory: Writable\n";
    $files = glob($uploadDir . '*');
    echo "   - Files: " . count($files) . "\n";
} else {
    echo "❌ Upload Directory: Not writable or missing\n";
}

// 3. Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "✅ Sessions: Working\n";
} else {
    echo "⚠️  Sessions: Already active\n";
}

// 4. Email (basic check)
if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "✅ PHPMailer: Loaded\n";
} else {
    echo "❌ PHPMailer: Not found\n";
}

echo "\n=== End Health Check ===\n";
```

**Run it:**
```powershell
php health_check.php
```

---

## 🧪 Install PHPUnit (5 Minutes)

```powershell
# Install Composer if not installed
# Download from: https://getcomposer.org/download/

# Initialize composer.json if doesn't exist
composer init --no-interaction

# Install PHPUnit
composer require --dev phpunit/phpunit

# Verify
.\vendor\bin\phpunit --version
```

**Expected output:** PHPUnit 9.x or 10.x

---

## ✅ Run First Test

Create `tests/SimpleTest.php`:

```php
<?php
use PHPUnit\Framework\TestCase;

class SimpleTest extends TestCase {
    public function testDatabaseConnection() {
        require_once __DIR__ . '/../conf.php';
        require_once __DIR__ . '/../app/Services/Global/Database.php';
        
        $db = new Database($GLOBALS['conf'] ?? $conf);
        $this->assertInstanceOf(PDO::class, $db->getPDO());
    }
    
    public function testPasswordHashing() {
        $password = 'Test1234!';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->assertTrue(password_verify($password, $hash));
    }
}
```

**Run it:**
```powershell
.\vendor\bin\phpunit tests/SimpleTest.php
```

**Expected:** 2 tests, 2 assertions, ✅ OK

---

## 📋 Today's Checklist

- [x] Read PROJECT_STATUS_REPORT.md
- [x] Read TEST_CASES.md
- [ ] Resolve merge conflicts (Option C recommended)
- [ ] Run health_check.php
- [ ] Test signup → login → 2FA → upload flow
- [ ] Install PHPUnit
- [ ] Run SimpleTest.php
- [ ] Review remember-me token code

**Time estimate:** 30-45 minutes total

---

## 🆘 Troubleshooting

### Issue: "Class not found"
```powershell
# Check conf.php exists
Test-Path conf.php

# If not, copy sample
Copy-Item conf.sample.php conf.php
# Then edit database credentials
```

### Issue: Database connection failed
```powershell
# Test MySQL connection
mysql -u root -p
# Inside MySQL:
SHOW DATABASES;
USE noteshare_db;
SHOW TABLES;
```

### Issue: Upload fails
```powershell
# Check permissions
icacls uploads\documents
# Should show write permissions for Apache user

# Create directory if missing
New-Item -ItemType Directory -Path uploads\documents -Force
```

### Issue: Email not sending
```powershell
# Check debug.log
Get-Content debug.log -Tail 50

# Verify SMTP settings in conf.php
# For testing, use Mailtrap.io or set smtp_host to 'localhost'
```

---

## 📚 Next Steps

1. **Resolve conflicts** → Commit → Push
2. **Fix remember-me bug** (15 min fix)
3. **Write 5 unit tests** (1 hour)
4. **Set up CI/CD** with GitHub Actions
5. **Security audit** (PROJECT_STATUS_REPORT.md has list)

---

**Questions?** Check:
- PROJECT_STATUS_REPORT.md - Full architecture & issues
- TEST_CASES.md - Detailed test examples
- debug.log - Runtime errors
- scripts/ directory - Helpful DB tools

**Good luck! 🚀**
