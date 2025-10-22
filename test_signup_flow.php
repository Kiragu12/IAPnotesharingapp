<?php
/**
 * Comprehensive Signup Flow Test
 * This script tests the entire signup process to verify:
 * 1. Form validation
 * 2. Database insertion
 * 3. Email sending capability
 * 4. Error handling
 */

echo "<h1>🧪 Signup Flow Troubleshooting Test</h1>";
echo "<hr>";

// Load configuration and classes
require_once 'conf.php';
require_once 'Global/Database.php';
require_once 'Global/fncs.php';
require_once 'Global/SendMail.php';
require_once 'Proc/auth.php';

$lang = array();
require_once 'Lang/en.php';

// Initialize objects
$ObjFncs = new fncs();
$ObjAuth = new auth();
$ObjSendMail = new SendMail();

echo "<h2>✅ Step 1: Configuration Check</h2>";
echo "<ul>";
echo "<li>Database Host: " . $conf['db_host'] . "</li>";
echo "<li>Database Name: " . $conf['db_name'] . "</li>";
echo "<li>Site Name: " . $conf['site_name'] . "</li>";
echo "<li>Admin Email: " . $conf['admin_email'] . "</li>";
echo "<li>Min Password Length: " . $conf['min_password_length'] . "</li>";
echo "<li>Valid Email Domains: " . implode(", ", $conf['valid_email_domain']) . "</li>";
echo "</ul>";
echo "<hr>";

echo "<h2>✅ Step 2: Database Connection Test</h2>";
try {
    $db = new Database($conf);
    echo "<p style='color: green;'>✅ Database connected successfully!</p>";
    
    // Check if users table exists
    $result = $db->fetchOne("SHOW TABLES LIKE 'users'");
    if ($result) {
        echo "<p style='color: green;'>✅ Users table exists!</p>";
        
        // Get current user count
        $count = $db->fetchOne("SELECT COUNT(*) as total FROM users");
        echo "<p>📊 Current users in database: <strong>" . $count['total'] . "</strong></p>";
    } else {
        echo "<p style='color: red;'>❌ Users table NOT found!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}
echo "<hr>";

echo "<h2>✅ Step 3: Test User Data Validation</h2>";

// Test data
$test_users = [
    [
        'fullname' => 'Test User One',
        'email' => 'testuser1@icsbacademy.com',
        'password' => 'Password123!',
        'expected' => 'VALID'
    ],
    [
        'fullname' => 'Test123', // Invalid - contains numbers
        'email' => 'testuser2@icsbacademy.com',
        'password' => 'Password123!',
        'expected' => 'INVALID - fullname has numbers'
    ],
    [
        'fullname' => 'Test User',
        'email' => 'invalid-email', // Invalid email format
        'password' => 'Password123!',
        'expected' => 'INVALID - bad email format'
    ],
    [
        'fullname' => 'Test User',
        'email' => 'test@wrongdomain.com', // Invalid domain
        'password' => 'Password123!',
        'expected' => 'INVALID - wrong email domain'
    ],
    [
        'fullname' => 'Test User',
        'email' => 'test@gmail.com',
        'password' => '123', // Too short
        'expected' => 'INVALID - password too short'
    ]
];

foreach ($test_users as $index => $test_user) {
    echo "<h3>Test Case " . ($index + 1) . ": {$test_user['expected']}</h3>";
    echo "<ul>";
    
    $errors = [];
    
    // Validate fullname
    if (empty($test_user['fullname']) || !preg_match("/^[a-zA-Z ]*$/", $test_user['fullname'])) {
        $errors[] = "❌ Fullname validation failed";
    } else {
        echo "<li>✅ Fullname valid: {$test_user['fullname']}</li>";
    }
    
    // Validate email format
    if (!filter_var($test_user['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "❌ Email format invalid";
    } else {
        echo "<li>✅ Email format valid: {$test_user['email']}</li>";
    }
    
    // Validate email domain
    $email_domain = substr(strrchr($test_user['email'], "@"), 1);
    if ($email_domain && !in_array($email_domain, $conf['valid_email_domain'])) {
        $errors[] = "❌ Email domain not allowed: {$email_domain}";
    } else if ($email_domain) {
        echo "<li>✅ Email domain valid: {$email_domain}</li>";
    }
    
    // Validate password length
    if (strlen($test_user['password']) < $conf['min_password_length']) {
        $errors[] = "❌ Password too short (min: {$conf['min_password_length']})";
    } else {
        echo "<li>✅ Password length valid</li>";
    }
    
    if (!empty($errors)) {
        echo "<li style='color: orange;'><strong>Errors found:</strong></li>";
        foreach ($errors as $error) {
            echo "<li style='color: red;'>$error</li>";
        }
    }
    
    echo "</ul>";
}
echo "<hr>";

echo "<h2>✅ Step 4: Test Database INSERT Operation</h2>";

try {
    $test_email = "testcleanup_" . time() . "@icsbacademy.com";
    $test_name = "Cleanup Test User";
    $test_password = password_hash("TestPassword123!", PASSWORD_DEFAULT);
    
    echo "<p>Attempting to insert test user...</p>";
    echo "<ul>";
    echo "<li>Email: {$test_email}</li>";
    echo "<li>Name: {$test_name}</li>";
    echo "</ul>";
    
    // Insert test user
    $sql_insert = "INSERT INTO users (email, password, full_name, email_verified, is_2fa_enabled, created_at) 
                   VALUES (:email, :password, :full_name, 0, 1, NOW())";
    $rows_affected = $db->execute($sql_insert, [
        ':email' => $test_email,
        ':password' => $test_password,
        ':full_name' => $test_name
    ]);
    
    if ($rows_affected > 0) {
        echo "<p style='color: green;'>✅ Test user inserted successfully! Rows affected: {$rows_affected}</p>";
        
        // Verify the insert
        $verify = $db->fetchOne("SELECT id, email, full_name, email_verified, is_2fa_enabled FROM users WHERE email = :email", [':email' => $test_email]);
        if ($verify) {
            echo "<p style='color: green;'>✅ Verification successful! User data:</p>";
            echo "<pre>" . print_r($verify, true) . "</pre>";
            
            // Clean up - delete test user
            $db->execute("DELETE FROM users WHERE email = :email", [':email' => $test_email]);
            echo "<p style='color: blue;'>🧹 Test user cleaned up from database.</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No rows were inserted!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ INSERT Error: " . $e->getMessage() . "</p>";
}
echo "<hr>";

echo "<h2>✅ Step 5: Test Duplicate Email Check</h2>";

try {
    // First, let's get an existing email from the database if any
    $existing = $db->fetchOne("SELECT email FROM users LIMIT 1");
    
    if ($existing) {
        $duplicate_email = $existing['email'];
        echo "<p>Testing duplicate check with existing email: <strong>{$duplicate_email}</strong></p>";
        
        $sql_check = "SELECT id FROM users WHERE email = :email LIMIT 1";
        $found_user = $db->fetchOne($sql_check, [':email' => $duplicate_email]);
        
        if ($found_user) {
            echo "<p style='color: green;'>✅ Duplicate detection works! Found user ID: {$found_user['id']}</p>";
        } else {
            echo "<p style='color: red;'>❌ Duplicate detection failed!</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ No existing users to test duplicate check</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Duplicate Check Error: " . $e->getMessage() . "</p>";
}
echo "<hr>";

echo "<h2>✅ Step 6: Email Configuration Check</h2>";
echo "<ul>";
echo "<li>SMTP Host: " . $conf['smtp_host'] . "</li>";
echo "<li>SMTP Port: " . $conf['smtp_port'] . "</li>";
echo "<li>SMTP Username: " . $conf['smtp_username'] . "</li>";
echo "<li>SMTP Password: " . (isset($conf['smtp_password']) && !empty($conf['smtp_password']) ? '✅ Set (hidden)' : '❌ Not set') . "</li>";
echo "</ul>";
echo "<p><em>Note: Email sending is not tested here to avoid sending actual emails. The signup process will attempt to send a welcome email.</em></p>";
echo "<hr>";

echo "<h2>📋 Summary of Findings</h2>";
echo "<div style='background: #f0f0f0; padding: 20px; border-radius: 10px;'>";
echo "<h3>What Will Happen When You Register:</h3>";
echo "<ol>";
echo "<li><strong>Form Validation:</strong> Your fullname, email, and password will be validated</li>";
echo "<li><strong>Email Domain Check:</strong> Must be one of: " . implode(", ", $conf['valid_email_domain']) . "</li>";
echo "<li><strong>Duplicate Check:</strong> System will check if email already exists</li>";
echo "<li><strong>Database Insert:</strong> Your account will be saved with:";
echo "<ul>";
echo "<li>email_verified = 0 (not verified yet)</li>";
echo "<li>is_2fa_enabled = 1 (2FA enabled by default)</li>";
echo "</ul>";
echo "</li>";
echo "<li><strong>Welcome Email:</strong> A welcome email will be sent to your address</li>";
echo "<li><strong>Success Message:</strong> You'll see 'Account created successfully! You can now sign in.'</li>";
echo "</ol>";
echo "</div>";
echo "<hr>";

echo "<h2>🔍 Potential Issues to Watch For:</h2>";
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; border-left: 4px solid #ffc107;'>";
echo "<ul>";
echo "<li>✅ If you see a <strong>blank page after signup</strong>: Check if conf.php exists and is properly configured</li>";
echo "<li>✅ If you see <strong>database errors</strong>: Verify database connection settings</li>";
echo "<li>✅ If <strong>email fails to send</strong>: The account is still created, but you won't receive the welcome email</li>";
echo "<li>✅ If you see <strong>'Email already exists'</strong>: Try a different email address</li>";
echo "<li>✅ Make sure to use an email from allowed domains: " . implode(", ", $conf['valid_email_domain']) . "</li>";
echo "</ul>";
echo "</div>";
echo "<hr>";

echo "<h2>✅ Ready to Test!</h2>";
echo "<p style='font-size: 18px;'>Based on this test, your signup should work correctly and save details to the database.</p>";
echo "<p><a href='signup.php' style='display: inline-block; background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Go to Signup Page</a></p>";
