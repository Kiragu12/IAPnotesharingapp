<?php
/**
 * Test 2FA Flow
 * This page tests the complete 2FA authentication flow
 */

require_once 'conf.php';
require_once 'Global/Database.php';

// Initialize database
$db = new Database($conf);

echo "<h1>2FA Flow Test</h1>";
echo "<hr>";

// Test 1: Check if two_factor_codes table exists
echo "<h3>Test 1: Check two_factor_codes table</h3>";
if ($db->tableExists('two_factor_codes')) {
    echo "<p style='color: green;'>✓ Table 'two_factor_codes' exists</p>";
    
    // Get table structure
    $sql = "DESCRIBE two_factor_codes";
    $columns = $db->fetchAll($sql);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>✗ Table 'two_factor_codes' does not exist</p>";
}

echo "<hr>";

// Test 2: Check if users table exists and has required fields
echo "<h3>Test 2: Check users table</h3>";
if ($db->tableExists('users')) {
    echo "<p style='color: green;'>✓ Table 'users' exists</p>";
    
    // Get table structure
    $sql = "DESCRIBE users";
    $columns = $db->fetchAll($sql);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>✗ Table 'users' does not exist</p>";
}

echo "<hr>";

// Test 3: Test generating and inserting OTP code
echo "<h3>Test 3: Generate and Insert OTP Code</h3>";
try {
    // Check if we have any users
    $sql = "SELECT id, email, full_name FROM users LIMIT 1";
    $user = $db->fetchOne($sql);
    
    if ($user) {
        echo "<p style='color: green;'>✓ Found test user: " . htmlspecialchars($user['email']) . "</p>";
        
        // Generate OTP
        $otp_code = sprintf("%06d", mt_rand(100000, 999999));
        $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        echo "<p>Generated OTP: <strong>" . $otp_code . "</strong></p>";
        echo "<p>Expires at: <strong>" . $expires_at . "</strong></p>";
        
        // Insert OTP
        $sql_insert = "INSERT INTO two_factor_codes (user_id, code, expires_at, attempts_used, code_type) 
                       VALUES (:user_id, :code, :expires_at, 0, 'login')";
        $db->execute($sql_insert, [
            ':user_id' => $user['id'],
            ':code' => $otp_code,
            ':expires_at' => $expires_at
        ]);
        
        echo "<p style='color: green;'>✓ OTP code inserted successfully</p>";
        
        // Verify insertion
        $sql_verify = "SELECT * FROM two_factor_codes WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1";
        $inserted = $db->fetchOne($sql_verify, [':user_id' => $user['id']]);
        
        if ($inserted) {
            echo "<p style='color: green;'>✓ Verified OTP in database</p>";
            echo "<pre>";
            print_r($inserted);
            echo "</pre>";
            
            // Clean up test data
            $sql_delete = "DELETE FROM two_factor_codes WHERE id = :id";
            $db->execute($sql_delete, [':id' => $inserted['id']]);
            echo "<p style='color: blue;'>ℹ Test OTP deleted from database</p>";
        } else {
            echo "<p style='color: red;'>✗ Could not verify inserted OTP</p>";
        }
        
    } else {
        echo "<p style='color: orange;'>⚠ No users found in database. Please create a user first.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";

// Test 4: Test Database execute method
echo "<h3>Test 4: Test Database Execute Method</h3>";
try {
    $sql = "SELECT 1 as test";
    $result = $db->fetchOne($sql);
    if ($result) {
        echo "<p style='color: green;'>✓ Database connection working</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<h3>Summary</h3>";
echo "<p>All tests completed. Check above for any errors.</p>";
echo "<p><a href='signin.php'>Go to Sign In Page</a> | <a href='test_db_connection.php'>Test DB Connection</a></p>";
?>
