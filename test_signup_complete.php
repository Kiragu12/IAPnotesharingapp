<?php
/**
 * Test Signup Flow - Comprehensive Test
 * Tests the complete signup process including validation, database insertion, and email sending
 */

echo "<h1>🧪 Testing Complete Signup Flow</h1>";
echo "<hr>";

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'signup' => '1',
    'fullname' => 'John Test User',
    'email' => 'testuser' . time() . '@icsbacademy.com',
    'password' => 'TestPassword123!'
];

echo "<h2>📝 Test Data</h2>";
echo "<ul>";
echo "<li><strong>Full Name:</strong> " . $_POST['fullname'] . "</li>";
echo "<li><strong>Email:</strong> " . $_POST['email'] . "</li>";
echo "<li><strong>Password:</strong> " . $_POST['password'] . "</li>";
echo "</ul>";
echo "<hr>";

try {
    // Start session
    session_start();
    
    // Load application
    require_once 'ClassAutoLoad.php';
    
    echo "<h2>✅ Step 1: Application loaded successfully</h2>";
    
    // Get current user count before signup
    $db = new Database($conf);
    $before_count = $db->fetchOne("SELECT COUNT(*) as total FROM users");
    echo "<p>Users in database before signup: <strong>" . $before_count['total'] . "</strong></p>";
    
    echo "<h2>🔄 Step 2: Processing signup...</h2>";
    
    // Process signup
    $result = $ObjAuth->signup($conf, $ObjFncs, $lang, $ObjSendMail);
    
    // Get messages
    $success_msg = $ObjFncs->getMsg('msg');
    $errors = $ObjFncs->getMsg('errors');
    
    echo "<h2>📋 Step 3: Results</h2>";
    
    if ($success_msg) {
        echo "<div style='color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>Success Message:</strong><br>" . $success_msg;
        echo "</div>";
    }
    
    if ($errors) {
        echo "<div style='color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>Errors Found:</strong><br>";
        if (is_array($errors)) {
            foreach ($errors as $field => $error) {
                echo "- <strong>$field:</strong> $error<br>";
            }
        } else {
            echo $errors;
        }
        echo "</div>";
    }
    
    // Check if user was actually created
    $after_count = $db->fetchOne("SELECT COUNT(*) as total FROM users");
    echo "<p>Users in database after signup: <strong>" . $after_count['total'] . "</strong></p>";
    
    if ($after_count['total'] > $before_count['total']) {
        echo "<p style='color: green;'>✅ <strong>User successfully created in database!</strong></p>";
        
        // Get the new user details
        $new_user = $db->fetchOne("SELECT id, email, full_name, email_verified, is_2fa_enabled, created_at FROM users WHERE email = :email", [
            ':email' => $_POST['email']
        ]);
        
        if ($new_user) {
            echo "<h3>👤 New User Details:</h3>";
            echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
            echo "<tr><td><strong>ID</strong></td><td>" . $new_user['id'] . "</td></tr>";
            echo "<tr><td><strong>Email</strong></td><td>" . $new_user['email'] . "</td></tr>";
            echo "<tr><td><strong>Full Name</strong></td><td>" . $new_user['full_name'] . "</td></tr>";
            echo "<tr><td><strong>Email Verified</strong></td><td>" . ($new_user['email_verified'] ? 'Yes' : 'No') . "</td></tr>";
            echo "<tr><td><strong>2FA Enabled</strong></td><td>" . ($new_user['is_2fa_enabled'] ? 'Yes' : 'No') . "</td></tr>";
            echo "<tr><td><strong>Created At</strong></td><td>" . $new_user['created_at'] . "</td></tr>";
            echo "</table>";
            
            // Clean up test user
            echo "<p style='color: blue;'>🧹 Cleaning up test user...</p>";
            $db->execute("DELETE FROM users WHERE id = :id", [':id' => $new_user['id']]);
            echo "<p style='color: blue;'>✅ Test user deleted.</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ <strong>User was NOT created in database!</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>❌ Error during signup test:</strong><br>";
    echo $e->getMessage();
    echo "</div>";
}

echo "<hr>";
echo "<h2>📊 Summary</h2>";
echo "<p>If you see:</p>";
echo "<ul>";
echo "<li>✅ <strong>Green success message:</strong> Signup form validation and processing works</li>";
echo "<li>✅ <strong>User count increased:</strong> Database insertion works</li>";
echo "<li>✅ <strong>User details displayed:</strong> Data was saved correctly</li>";
echo "<li>✅ <strong>Test user cleaned up:</strong> Database operations are working</li>";
echo "</ul>";
echo "<p><strong>Next:</strong> Try the actual signup form in your browser at <a href='signup.php'>signup.php</a></p>";
?>