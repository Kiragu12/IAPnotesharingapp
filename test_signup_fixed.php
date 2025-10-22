<?php
/**
 * Test Signup Flow After Fix
 * Tests if the signup form now shows confirmation messages properly
 */

echo "<h1>🧪 Testing Signup Flow After Fix</h1>";
echo "<hr>";

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'signup' => '1',
    'fullname' => 'Test Signup User',
    'email' => 'signup_test_' . time() . '@gmail.com',
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
    // Capture any output
    ob_start();
    
    // Start session
    session_start();
    
    // Load application
    require_once 'ClassAutoLoad.php';
    
    echo "<h2>✅ Step 1: Application loaded successfully</h2>";
    
    // Process signup
    $ObjAuth->signup($conf, $ObjFncs, $lang, $ObjSendMail);
    
    // Get any output from email sending
    $output = ob_get_clean();
    
    // Get messages
    $success_msg = $ObjFncs->getMsg('msg');
    $errors = $ObjFncs->getMsg('errors');
    
    echo "<h2>📋 Step 2: Results</h2>";
    
    if (!empty($output)) {
        echo "<div style='color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>⚠️ Unwanted Output Detected:</strong><br>";
        echo htmlspecialchars($output);
        echo "</div>";
    } else {
        echo "<p style='color: green;'>✅ No unwanted output - this is good!</p>";
    }
    
    if ($success_msg) {
        echo "<div style='color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>Success Message:</strong><br>" . $success_msg;
        echo "</div>";
    } else {
        echo "<div style='color: orange; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>⚠️ No success message found!</strong>";
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
    
    // Check if user was created
    $db = new Database($conf);
    $new_user = $db->fetchOne("SELECT id, email, full_name FROM users WHERE email = :email", [
        ':email' => $_POST['email']
    ]);
    
    if ($new_user) {
        echo "<p style='color: green;'>✅ <strong>User successfully created in database!</strong></p>";
        echo "<p>User ID: " . $new_user['id'] . " | Email: " . $new_user['email'] . "</p>";
        
        // Clean up test user
        $db->execute("DELETE FROM users WHERE id = :id", [':id' => $new_user['id']]);
        echo "<p style='color: blue;'>🧹 Test user cleaned up.</p>";
    } else {
        echo "<p style='color: red;'>❌ User was NOT created in database!</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>❌ Error during signup test:</strong><br>";
    echo $e->getMessage();
    echo "</div>";
}

echo "<hr>";
echo "<h2>📊 Summary</h2>";
echo "<p><strong>The Fix:</strong> Removed echo statements from SendMail.php that were interfering with form processing.</p>";
echo "<p><strong>Expected Result:</strong> Signup form should now show confirmation messages without losing data.</p>";
echo "<p><strong>Next:</strong> Try the actual signup form at <a href='signup.php'>signup.php</a></p>";
?>