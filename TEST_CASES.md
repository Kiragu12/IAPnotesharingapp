# NotesShare Academy - Test Cases & Testing Guide

**Project:** IAPnotesharingapp  
**Date:** November 18, 2025  
**Testing Framework:** PHPUnit 9.x

---

## 📋 Table of Contents
1. [Setup Instructions](#setup-instructions)
2. [Unit Tests](#unit-tests)
3. [Integration Tests](#integration-tests)
4. [Manual Test Cases](#manual-test-cases)
5. [Test Data](#test-data)
6. [Running Tests](#running-tests)

---

## 🔧 Setup Instructions

### 1. Install PHPUnit

```bash
# Using Composer (recommended)
composer require --dev phpunit/phpunit

# Verify installation
vendor/bin/phpunit --version
```

### 2. Create Test Database

```sql
-- Connect to MySQL
mysql -u root -p

-- Create test database
CREATE DATABASE noteshare_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant permissions
GRANT ALL PRIVILEGES ON noteshare_test.* TO 'your_user'@'localhost';
FLUSH PRIVILEGES;

-- Import schema from main DB
mysqldump -u root -p noteshare_db --no-data > schema.sql
mysql -u root -p noteshare_test < schema.sql
```

### 3. Create Test Configuration

Create `config/conf.test.php`:

```php
<?php
// Test configuration - DO NOT USE IN PRODUCTION
$conf = [
    // Database
    'db_host' => 'localhost',
    'db_name' => 'noteshare_test',  // ← Test database
    'db_user' => 'your_user',
    'db_pass' => 'your_password',
    
    // Email (use Mailtrap or mock)
    'smtp_host' => 'smtp.mailtrap.io',
    'smtp_port' => 2525,
    'smtp_user' => 'your_mailtrap_user',
    'smtp_pass' => 'your_mailtrap_pass',
    'admin_email' => 'test@noteshare.test',
    
    // Site
    'site_name' => 'NotesShare TEST',
    'site_url' => 'http://localhost/IAPnotesharingapp-1',
    
    // Security
    'min_password_length' => 8,
    'valid_email_domain' => ['gmail.com', 'outlook.com', 'test.com'],
    
    // Testing flags
    'debug_mode' => true,
    'disable_email' => false  // Set true to skip actual email sending
];
```

### 4. Create PHPUnit Configuration

Create `phpunit.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap.php"
         colors="true"
         verbose="true"
         stopOnFailure="false">
    <testsuites>
        <testsuite name="Unit Tests">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration Tests">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_NAME" value="noteshare_test"/>
    </php>
</phpunit>
```

### 5. Create Test Bootstrap

Create `tests/bootstrap.php`:

```php
<?php
// Test bootstrap file
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('UTC');

// Load test configuration
require_once __DIR__ . '/../config/conf.test.php';

// Load classes
require_once __DIR__ . '/../app/Services/Global/Database.php';
require_once __DIR__ . '/../app/Services/Global/fncs.php';
require_once __DIR__ . '/../app/Services/Global/SendMail.php';
require_once __DIR__ . '/../app/Controllers/Proc/auth.php';
require_once __DIR__ . '/../app/Controllers/NotesController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper: Clean test database
function cleanTestDatabase($conf) {
    $db = new Database($conf);
    $tables = ['notes', 'categories', 'two_factor_codes', 'remember_tokens', 'password_resets', 'users'];
    foreach ($tables as $table) {
        try {
            $db->execute("TRUNCATE TABLE $table");
        } catch (Exception $e) {
            // Table might not exist in test DB
        }
    }
}

// Helper: Create test user
function createTestUser($conf, $email = 'test@test.com', $password = 'Test1234!') {
    $db = new Database($conf);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (username, email, password, full_name, is_verified, is_2fa_enabled, created_at) 
            VALUES (:username, :email, :password, :full_name, 1, 1, NOW())";
    
    $db->query($sql, [
        ':username' => 'testuser',
        ':email' => $email,
        ':password' => $hashedPassword,
        ':full_name' => 'Test User'
    ]);
    
    return $db->getPDO()->lastInsertId();
}

// Helper: Create test category
function createTestCategory($conf, $name = 'Test Category') {
    $db = new Database($conf);
    $sql = "INSERT INTO categories (name, description, created_at) VALUES (:name, :desc, NOW())";
    $db->query($sql, [':name' => $name, ':desc' => 'Test category description']);
    return $db->getPDO()->lastInsertId();
}
```

---

## 🧪 Unit Tests

### AuthTest.php

Create `tests/Unit/AuthTest.php`:

```php
<?php
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase {
    private $conf;
    private $auth;
    private $ObjFncs;
    private $ObjSendMail;
    
    protected function setUp(): void {
        global $conf;
        $this->conf = $conf;
        $this->auth = new auth();
        $this->ObjFncs = new fncs();
        $this->ObjSendMail = new SendMail();
        
        // Clean database before each test
        cleanTestDatabase($this->conf);
    }
    
    /**
     * Test: Signup with valid data should create user
     */
    public function testSignupWithValidDataCreatesUser() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['signup'] = '1';
        $_POST['fullname'] = 'John Doe';
        $_POST['email'] = 'john@gmail.com';
        $_POST['password'] = 'SecurePass123!';
        
        // Capture session
        session_start();
        $_SESSION['fullname'] = $_POST['fullname'];
        $_SESSION['email'] = $_POST['email'];
        $_SESSION['password'] = $_POST['password'];
        
        // Run signup (will fail at redirect, that's OK for test)
        ob_start();
        $this->auth->signup($this->conf, $this->ObjFncs, $this->conf['lang'] ?? [], $this->ObjSendMail);
        ob_end_clean();
        
        // Verify user was created
        $db = new Database($this->conf);
        $user = $db->fetchOne("SELECT * FROM users WHERE email = :email", [':email' => 'john@gmail.com']);
        
        $this->assertNotNull($user, 'User should be created in database');
        $this->assertEquals('John Doe', $user['full_name']);
        $this->assertTrue(password_verify('SecurePass123!', $user['password']));
    }
    
    /**
     * Test: Signup with invalid email domain should fail
     */
    public function testSignupWithInvalidEmailDomainFails() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['signup'] = '1';
        $_POST['fullname'] = 'Jane Doe';
        $_POST['email'] = 'jane@invalid-domain.xyz';
        $_POST['password'] = 'SecurePass123!';
        
        session_start();
        $_SESSION['fullname'] = $_POST['fullname'];
        $_SESSION['email'] = $_POST['email'];
        $_SESSION['password'] = $_POST['password'];
        
        ob_start();
        $this->auth->signup($this->conf, $this->ObjFncs, [], $this->ObjSendMail);
        ob_end_clean();
        
        // Check for error message
        $errors = $this->ObjFncs->getMsg('errors');
        $this->assertNotEmpty($errors, 'Should have validation errors');
        $this->assertArrayHasKey('mailDomain_error', $errors);
    }
    
    /**
     * Test: Login with correct credentials generates OTP
     */
    public function testLoginWithCorrectCredentialsGeneratesOTP() {
        // Create test user
        $userId = createTestUser($this->conf, 'login@test.com', 'MyPassword123!');
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['signin'] = '1';
        $_POST['email'] = 'login@test.com';
        $_POST['password'] = 'MyPassword123!';
        $_POST['remember_me'] = '0';
        
        session_start();
        
        // Run login (will redirect to 2FA page)
        ob_start();
        $this->auth->login($this->conf, $this->ObjFncs, $this->ObjSendMail);
        ob_end_clean();
        
        // Verify OTP was created
        $db = new Database($this->conf);
        $otp = $db->fetchOne(
            "SELECT * FROM two_factor_codes WHERE user_id = :uid AND code_type = 'login' ORDER BY created_at DESC LIMIT 1",
            [':uid' => $userId]
        );
        
        $this->assertNotNull($otp, 'OTP should be generated');
        $this->assertEquals(6, strlen($otp['code']), 'OTP should be 6 digits');
        $this->assertNotNull($_SESSION['temp_user_id'], 'Temp user ID should be in session');
    }
    
    /**
     * Test: Login with wrong password fails
     */
    public function testLoginWithWrongPasswordFails() {
        createTestUser($this->conf, 'user@test.com', 'CorrectPassword123!');
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['signin'] = '1';
        $_POST['email'] = 'user@test.com';
        $_POST['password'] = 'WrongPassword123!';
        
        session_start();
        
        ob_start();
        $result = $this->auth->login($this->conf, $this->ObjFncs, $this->ObjSendMail);
        ob_end_clean();
        
        $this->assertFalse($result, 'Login should fail');
        $msg = $this->ObjFncs->getMsg('msg');
        $this->assertStringContainsString('Invalid', $msg);
    }
    
    /**
     * Test: 2FA verification with correct code succeeds
     */
    public function testVerify2FAWithCorrectCodeSucceeds() {
        // Create user and OTP
        $userId = createTestUser($this->conf, '2fa@test.com', 'Pass123!');
        $db = new Database($this->conf);
        
        $otpCode = '123456';
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        $db->query(
            "INSERT INTO two_factor_codes (user_id, code, code_type, expires_at, attempts_used, ip_address) 
             VALUES (:uid, :code, 'login', :exp, 0, '127.0.0.1')",
            [':uid' => $userId, ':code' => $otpCode, ':exp' => $expiresAt]
        );
        
        // Set session temp data
        session_start();
        $_SESSION['temp_user_id'] = $userId;
        $_SESSION['temp_user_email'] = '2fa@test.com';
        $_SESSION['temp_user_name'] = 'Test User';
        $_SESSION['temp_remember_me'] = false;
        
        // Verify 2FA
        $result = $this->auth->verify2FA($this->conf, $this->ObjFncs, $otpCode);
        
        $this->assertTrue($result, '2FA verification should succeed');
        $this->assertEquals($userId, $_SESSION['user_id'], 'User should be logged in');
        $this->assertTrue($_SESSION['logged_in']);
    }
    
    /**
     * Test: 2FA verification with expired code fails
     */
    public function testVerify2FAWithExpiredCodeFails() {
        $userId = createTestUser($this->conf);
        $db = new Database($this->conf);
        
        $otpCode = '654321';
        $expiresAt = date('Y-m-d H:i:s', strtotime('-5 minutes')); // Expired
        
        $db->query(
            "INSERT INTO two_factor_codes (user_id, code, code_type, expires_at, attempts_used) 
             VALUES (:uid, :code, 'login', :exp, 0)",
            [':uid' => $userId, ':code' => $otpCode, ':exp' => $expiresAt]
        );
        
        session_start();
        $_SESSION['temp_user_id'] = $userId;
        
        $result = $this->auth->verify2FA($this->conf, $this->ObjFncs, $otpCode);
        
        $this->assertFalse($result, '2FA should fail with expired code');
        $msg = $this->ObjFncs->getMsg('msg');
        $this->assertStringContainsString('expired', $msg);
    }
    
    /**
     * Test: Remember token creation stores token correctly
     */
    public function testRememberTokenCreationStoresToken() {
        $userId = createTestUser($this->conf);
        
        $result = $this->auth->createRememberToken($userId, $this->conf);
        
        $this->assertTrue($result, 'Remember token should be created');
        
        // Verify token in database
        $db = new Database($this->conf);
        $token = $db->fetchOne(
            "SELECT * FROM remember_tokens WHERE user_id = :uid ORDER BY created_at DESC LIMIT 1",
            [':uid' => $userId]
        );
        
        $this->assertNotNull($token, 'Token should exist in database');
        $this->assertNotEmpty($token['token']);
        $this->assertEquals($userId, $token['user_id']);
        
        // Verify cookie was set (check $_COOKIE in real browser test)
    }
    
    protected function tearDown(): void {
        // Clean up $_POST, $_SERVER, $_SESSION
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
```

---

### NotesControllerTest.php

Create `tests/Unit/NotesControllerTest.php`:

```php
<?php
use PHPUnit\Framework\TestCase;

class NotesControllerTest extends TestCase {
    private $conf;
    private $notesController;
    private $userId;
    private $categoryId;
    
    protected function setUp(): void {
        global $conf;
        $this->conf = $conf;
        
        cleanTestDatabase($this->conf);
        
        // Create test user and category
        $this->userId = createTestUser($this->conf);
        $this->categoryId = createTestCategory($this->conf, 'Math');
        
        // Start session and set user
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $this->userId;
        
        $this->notesController = new NotesController();
    }
    
    /**
     * Test: Create text note successfully
     */
    public function testCreateTextNoteSuccessfully() {
        $data = [
            'user_id' => $this->userId,
            'title' => 'My First Note',
            'content' => 'This is the content of my first note.',
            'category_id' => $this->categoryId,
            'note_type' => 'text',
            'is_public' => 1,
            'status' => 'published'
        ];
        
        $noteId = $this->notesController->createNote($data);
        
        $this->assertIsInt($noteId, 'Should return note ID');
        $this->assertGreaterThan(0, $noteId);
        
        // Verify in database
        $db = new Database($this->conf);
        $note = $db->fetchOne("SELECT * FROM notes WHERE id = :id", [':id' => $noteId]);
        
        $this->assertEquals('My First Note', $note['title']);
        $this->assertEquals('text', $note['note_type']);
        $this->assertEquals(1, $note['is_public']);
    }
    
    /**
     * Test: Create note with free-text category (find or create)
     */
    public function testCreateNoteWithFreeTextCategoryCreatesNew() {
        $data = [
            'user_id' => $this->userId,
            'title' => 'Physics Note',
            'content' => 'Newton\'s laws of motion',
            'category_name' => 'Physics',  // Free text, not in DB yet
            'note_type' => 'text',
            'is_public' => 0,
            'status' => 'draft'
        ];
        
        $noteId = $this->notesController->createNote($data);
        $this->assertGreaterThan(0, $noteId);
        
        // Verify category was created
        $db = new Database($this->conf);
        $category = $db->fetchOne("SELECT * FROM categories WHERE name = :name", [':name' => 'Physics']);
        
        $this->assertNotNull($category, 'Physics category should be created');
        
        // Verify note is linked to new category
        $note = $db->fetchOne("SELECT * FROM notes WHERE id = :id", [':id' => $noteId]);
        $this->assertEquals($category['id'], $note['category_id']);
    }
    
    /**
     * Test: Get user's notes returns only their notes
     */
    public function testGetUserNotesReturnsOnlyUserNotes() {
        // Create notes for test user
        $this->notesController->createNote([
            'user_id' => $this->userId,
            'title' => 'My Note 1',
            'content' => 'Content 1',
            'note_type' => 'text',
            'status' => 'published'
        ]);
        
        $this->notesController->createNote([
            'user_id' => $this->userId,
            'title' => 'My Note 2',
            'content' => 'Content 2',
            'note_type' => 'text',
            'status' => 'draft'
        ]);
        
        // Create another user's note
        $otherUserId = createTestUser($this->conf, 'other@test.com');
        $db = new Database($this->conf);
        $db->query(
            "INSERT INTO notes (user_id, title, content, note_type, status) VALUES (:u, 'Other Note', 'Content', 'text', 'published')",
            [':u' => $otherUserId]
        );
        
        // Get notes for test user
        $userNotes = $this->notesController->getUserNotes($this->userId);
        
        $this->assertCount(2, $userNotes, 'Should return exactly 2 notes');
        foreach ($userNotes as $note) {
            $this->assertEquals($this->userId, $note['user_id']);
        }
    }
    
    /**
     * Test: Delete note as owner succeeds
     */
    public function testDeleteNoteAsOwnerSucceeds() {
        $noteId = $this->notesController->createNote([
            'user_id' => $this->userId,
            'title' => 'To Delete',
            'content' => 'This will be deleted',
            'note_type' => 'text',
            'status' => 'draft'
        ]);
        
        $result = $this->notesController->deleteNote($noteId, $this->userId);
        
        $this->assertTrue($result, 'Delete should succeed');
        
        // Verify note is gone
        $db = new Database($this->conf);
        $note = $db->fetchOne("SELECT * FROM notes WHERE id = :id", [':id' => $noteId]);
        $this->assertFalse($note, 'Note should be deleted from database');
    }
    
    /**
     * Test: Delete someone else's note fails
     */
    public function testDeleteOthersNoteFails() {
        // Create note owned by another user
        $otherUserId = createTestUser($this->conf, 'another@test.com');
        $db = new Database($this->conf);
        $db->query(
            "INSERT INTO notes (user_id, title, content, note_type, status) VALUES (:u, 'Protected Note', 'Content', 'text', 'published')",
            [':u' => $otherUserId]
        );
        $noteId = $db->getPDO()->lastInsertId();
        
        // Try to delete as different user
        $result = $this->notesController->deleteNote($noteId, $this->userId);
        
        $this->assertFalse($result, 'Delete should fail for non-owner');
        
        // Verify note still exists
        $note = $db->fetchOne("SELECT * FROM notes WHERE id = :id", [':id' => $noteId]);
        $this->assertNotFalse($note, 'Note should still exist');
    }
    
    /**
     * Test: Search notes finds matching content
     */
    public function testSearchNotesFindsMatchingContent() {
        // Create searchable notes
        $this->notesController->createNote([
            'user_id' => $this->userId,
            'title' => 'JavaScript Tutorial',
            'content' => 'Learn JavaScript basics',
            'note_type' => 'text',
            'is_public' => 1,
            'status' => 'published'
        ]);
        
        $this->notesController->createNote([
            'user_id' => $this->userId,
            'title' => 'Python Guide',
            'content' => 'Python programming fundamentals',
            'note_type' => 'text',
            'is_public' => 1,
            'status' => 'published'
        ]);
        
        // Search for "JavaScript"
        $results = $this->notesController->searchNotes($this->userId, 'JavaScript', 10);
        
        $this->assertCount(1, $results, 'Should find 1 matching note');
        $this->assertStringContainsString('JavaScript', $results[0]['title']);
    }
    
    protected function tearDown(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
```

---

## 🔗 Integration Tests

### FileUploadTest.php

Create `tests/Integration/FileUploadTest.php`:

```php
<?php
use PHPUnit\Framework\TestCase;

class FileUploadTest extends TestCase {
    private $conf;
    private $userId;
    private $uploadDir;
    
    protected function setUp(): void {
        global $conf;
        $this->conf = $conf;
        
        cleanTestDatabase($this->conf);
        $this->userId = createTestUser($this->conf);
        
        // Create test upload directory
        $this->uploadDir = __DIR__ . '/../../uploads/test_files/';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
        
        session_start();
        $_SESSION['user_id'] = $this->userId;
    }
    
    /**
     * Test: Upload PDF file successfully
     */
    public function testUploadPDFFileSuccessfully() {
        // Create a fake PDF file
        $testFile = $this->uploadDir . 'test_document.pdf';
        file_put_contents($testFile, '%PDF-1.4 Fake PDF content for testing');
        
        // Simulate $_FILES
        $_FILES['file'] = [
            'name' => 'test_document.pdf',
            'type' => 'application/pdf',
            'tmp_name' => $testFile,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($testFile)
        ];
        
        $notesController = new NotesController();
        
        $data = [
            'user_id' => $this->userId,
            'title' => 'Test PDF Upload',
            'content' => 'Uploaded via test',
            'note_type' => 'file',
            'file_name' => $_FILES['file']['name'],
            'file_type' => $_FILES['file']['type'],
            'file_size' => $_FILES['file']['size'],
            'is_public' => 1,
            'status' => 'published'
        ];
        
        $noteId = $notesController->createNote($data);
        
        $this->assertGreaterThan(0, $noteId, 'Note ID should be returned');
        
        // Verify file metadata in DB
        $db = new Database($this->conf);
        $note = $db->fetchOne("SELECT * FROM notes WHERE id = :id", [':id' => $noteId]);
        
        $this->assertEquals('test_document.pdf', $note['file_name']);
        $this->assertEquals('application/pdf', $note['file_type']);
        $this->assertEquals('file', $note['note_type']);
    }
    
    /**
     * Test: Download file with valid note ID
     */
    public function testDownloadFileWithValidNoteId() {
        // This would require actual HTTP request simulation
        // Or use Guzzle/HTTP client to hit download.php endpoint
        
        $this->markTestIncomplete('Requires HTTP client testing');
    }
    
    protected function tearDown(): void {
        // Clean up test files
        if (is_dir($this->uploadDir)) {
            array_map('unlink', glob("$this->uploadDir/*.*"));
            rmdir($this->uploadDir);
        }
        
        session_destroy();
    }
}
```

---

## ✅ Manual Test Cases

### Test Case 1: User Registration
```
ID: TC-AUTH-001
Feature: User Signup
Priority: Critical

Steps:
1. Navigate to http://localhost/IAPnotesharingapp-1/views/auth/signup.php
2. Enter:
   - Full Name: "Test User"
   - Email: "test@gmail.com"
   - Password: "SecurePass123!"
3. Click "Create Account"

Expected Result:
- Redirect to index.php with success message
- User record created in `users` table
- Welcome email sent (check Mailtrap/debug.log)
- Password is hashed (bcrypt)

Pass/Fail: _____
Notes: _____
```

### Test Case 2: Login with 2FA
```
ID: TC-AUTH-002
Feature: Login + Two-Factor Authentication
Priority: Critical

Steps:
1. Navigate to signin.php
2. Enter email: "test@gmail.com", password: "SecurePass123!"
3. Click "Sign In"
4. Check email for 6-digit OTP code
5. Enter OTP on two_factor_auth_new.php
6. Submit verification

Expected Result:
- After step 3: Redirect to 2FA page
- OTP inserted in `two_factor_codes` table
- Email sent with code
- After step 6: Redirect to dashboard.php
- Session variables set: user_id, user_email, user_name, logged_in

Pass/Fail: _____
OTP Code: _____
```

### Test Case 3: Upload File Note
```
ID: TC-NOTES-001
Feature: File Upload
Priority: High

Steps:
1. Sign in as test user
2. Navigate to dashboard.php
3. Click "Create New Note"
4. Switch to "File Upload" tab
5. Enter title: "Test PDF Upload"
6. Select a PDF file < 5MB
7. Choose category: "Computer Science"
8. Click "Upload Note"

Expected Result:
- Redirect to dashboard.php with success message
- Note appears in "Recent Notes"
- File saved in uploads/documents/ with unique name
- Database record in `notes` table with file_path, file_name, file_type, file_size
- note_type = 'file'

Pass/Fail: _____
File Path: _____
```

### Test Case 4: Download Uploaded File
```
ID: TC-NOTES-002
Feature: Secure File Download
Priority: High

Steps:
1. Sign in as test user
2. Go to dashboard.php
3. Find uploaded file note
4. Click "Download" from dropdown menu
5. Verify file download starts

Expected Result:
- File downloaded with correct filename
- Content-Type header matches file type
- Content-Disposition: attachment
- File contents intact
- Access log updated (if implemented)

Pass/Fail: _____
Downloaded File: _____
```

### Test Case 5: Remember Me Cookie
```
ID: TC-AUTH-003
Feature: Remember Me Functionality
Priority: Medium

Steps:
1. Sign in with "Remember Me" checkbox checked
2. Complete 2FA verification
3. Close browser
4. Reopen browser and navigate to signin.php

Expected Result:
- User auto-logged in without credentials
- `remember_token` cookie present (30-day expiration)
- Token in `remember_tokens` table
- Session restored with user_id

Pass/Fail: _____
Cookie Present: _____
Auto-Login Worked: _____
```

---

## 🔄 Running Tests

### Run All Tests
```bash
vendor/bin/phpunit
```

### Run Specific Test Suite
```bash
# Unit tests only
vendor/bin/phpunit --testsuite "Unit Tests"

# Integration tests only
vendor/bin/phpunit --testsuite "Integration Tests"
```

### Run Single Test Class
```bash
vendor/bin/phpunit tests/Unit/AuthTest.php
```

### Run with Coverage
```bash
vendor/bin/phpunit --coverage-html coverage/
```

### Continuous Testing (Watch Mode)
```bash
# Install phpunit-watcher
composer require --dev spatie/phpunit-watcher

# Run watcher
vendor/bin/phpunit-watcher watch
```

---

## 📊 Test Data

### Sample Test Users

```sql
-- Test user 1 (verified, 2FA enabled)
INSERT INTO users (username, email, password, full_name, is_verified, is_2fa_enabled) 
VALUES ('testuser1', 'test1@gmail.com', '$2y$10$hashed_password_here', 'Test User One', 1, 1);

-- Test user 2 (not verified)
INSERT INTO users (username, email, password, full_name, is_verified, is_2fa_enabled) 
VALUES ('testuser2', 'test2@outlook.com', '$2y$10$hashed_password_here', 'Test User Two', 0, 1);
```

### Sample Categories
```sql
INSERT INTO categories (name, description) VALUES
('Test Math', 'Mathematics test category'),
('Test Science', 'Science test category'),
('Test Programming', 'CS test category');
```

### Sample Notes
```sql
INSERT INTO notes (user_id, category_id, title, content, note_type, is_public, status) VALUES
(1, 1, 'Test Algebra Note', 'Quadratic equations...', 'text', 1, 'published'),
(1, 2, 'Test Physics Note', 'Newton laws...', 'text', 0, 'draft'),
(2, 3, 'Test Code Note', 'Python tutorial...', 'text', 1, 'published');
```

---

## 🎯 Test Coverage Goals

### Minimum Acceptable Coverage
- **Auth module:** 80%
- **Notes module:** 75%
- **Database layer:** 90%
- **Email system:** 60%

### Priority Areas
1. ✅ User authentication (signup, login, 2FA)
2. ✅ Notes CRUD operations
3. ✅ File upload/download
4. ⚠️ Permission checks (owner vs. non-owner)
5. ⚠️ Session management
6. ⚠️ Remember-me tokens
7. ⚠️ Email delivery
8. ⏳ Search functionality
9. ⏳ Category management
10. ⏳ Error handling

---

## 📝 Notes

- Always use test database (`noteshare_test`) - NEVER test on production data
- Clean database between tests using `cleanTestDatabase()`
- Mock email sending in CI/CD (use Mailtrap for manual testing)
- Use `@dataProvider` for parameterized tests
- Mark incomplete tests with `$this->markTestIncomplete()`
- Use `setUp()` for test initialization, `tearDown()` for cleanup

---

**Last Updated:** November 18, 2025  
**Next Review:** After merge conflicts resolved
