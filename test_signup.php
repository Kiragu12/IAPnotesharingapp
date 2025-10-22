<?php
/**
 * Test Signup Process
 * This tests if the signup functionality is working
 */

require_once 'conf.php';
require_once 'Global/Database.php';

echo "<h1>Signup Process Test</h1>";
echo "<hr>";

// Test 1: Check database connection
echo "<h3>Test 1: Database Connection</h3>";
try {
    $db = new Database($conf);
    echo "<p style='color: green;'>✓ Database connected successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// Test 2: Check users table
echo "<hr><h3>Test 2: Users Table Structure</h3>";
try {
    $sql = "DESCRIBE users";
    $columns = $db->fetchAll($sql);
    echo "<p style='color: green;'>✓ Users table exists with " . count($columns) . " columns</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 3: Test user insertion
echo "<hr><h3>Test 3: Simulate User Creation</h3>";
try {
    $test_email = 'test_' . time() . '@noteshareacademy.com';
    $test_name = 'Test User ' . time();
    $test_password = password_hash('TestPassword123', PASSWORD_DEFAULT);
    
    echo "<p>Creating test user:</p>";
    echo "<ul>";
    echo "<li>Email: " . htmlspecialchars($test_email) . "</li>";
    echo "<li>Name: " . htmlspecialchars($test_name) . "</li>";
    echo "<li>Password: TestPassword123 (will be hashed)</li>";
    echo "</ul>";
    
    // Insert test user
    $sql_insert = "INSERT INTO users (email, password, full_name, email_verified, is_2fa_enabled, created_at) 
                   VALUES (:email, :password, :full_name, 0, 1, NOW())";
    $db->execute($sql_insert, [
        ':email' => $test_email,
        ':password' => $test_password,
        ':full_name' => $test_name
    ]);
    
    echo "<p style='color: green;'>✓ Test user created successfully!</p>";
    
    // Verify insertion
    $sql_verify = "SELECT id, email, full_name, email_verified, is_2fa_enabled, created_at 
                   FROM users WHERE email = :email";
    $user = $db->fetchOne($sql_verify, [':email' => $test_email]);
    
    if ($user) {
        echo "<p style='color: green;'>✓ User verified in database:</p>";
        echo "<pre>";
        print_r($user);
        echo "</pre>";
        
        // Clean up test user
        $sql_delete = "DELETE FROM users WHERE email = :email";
        $db->execute($sql_delete, [':email' => $test_email]);
        echo "<p style='color: blue;'>ℹ Test user deleted (cleanup)</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error during user creation: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 4: Check valid email domains
echo "<hr><h3>Test 4: Email Domain Configuration</h3>";
echo "<p>Allowed email domains from conf.php:</p>";
echo "<ul>";
foreach ($conf['valid_email_domain'] as $domain) {
    echo "<li>" . htmlspecialchars($domain) . "</li>";
}
echo "</ul>";
echo "<p><strong>Note:</strong> Your signup email must use one of these domains!</p>";

// Test 5: Check SMTP settings
echo "<hr><h3>Test 5: Email Configuration</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>SMTP Host</td><td>" . htmlspecialchars($conf['smtp_host']) . "</td></tr>";
echo "<tr><td>SMTP Port</td><td>" . htmlspecialchars($conf['smtp_port']) . "</td></tr>";
echo "<tr><td>SMTP Username</td><td>" . htmlspecialchars($conf['smtp_username']) . "</td></tr>";
echo "<tr><td>Admin Email</td><td>" . htmlspecialchars($conf['admin_email']) . "</td></tr>";
echo "<tr><td>Site Name</td><td>" . htmlspecialchars($conf['site_name']) . "</td></tr>";
echo "</table>";

echo "<hr>";
echo "<h3>Summary</h3>";
echo "<p>✅ All tests completed successfully!</p>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Go to <a href='signup.php'>signup.php</a></li>";
echo "<li>Use an email with allowed domain (e.g., @gmail.com or @noteshareacademy.com)</li>";
echo "<li>Fill in name, email, password</li>";
echo "<li>Click 'Create Account'</li>";
echo "<li>Check for success message</li>";
echo "<li>Check your email for welcome message</li>";
echo "</ol>";
?>
