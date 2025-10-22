<?php
/**
 * Debug Signup Redirect Issue
 */

echo "<h1>🔍 Debugging Signup Redirect Issue</h1>";
echo "<hr>";

// Test if redirect works properly
echo "<h2>Testing Signup Process...</h2>";

// Capture any output that might prevent redirects
ob_start();

// Simulate the exact same process as signup form
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'signup' => '1',
    'fullname' => 'Debug Test User',
    'email' => 'debug_test_' . time() . '@gmail.com',
    'password' => 'TestPassword123!'
];

echo "<p><strong>Test Data:</strong></p>";
echo "<ul>";
echo "<li>Email: " . $_POST['email'] . "</li>";
echo "<li>Password: " . $_POST['password'] . "</li>";
echo "</ul>";

try {
    // Start session like signup.php does
    session_start();
    
    // Load ClassAutoLoad like signup.php does
    require_once 'ClassAutoLoad.php';
    
    echo "<h3>✅ ClassAutoLoad loaded</h3>";
    
    // Check if any output was generated so far
    $output_so_far = ob_get_contents();
    if (!empty($output_so_far)) {
        echo "<div style='color: red; background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>⚠️ Output detected before redirect attempt!</strong><br>";
        echo "This will prevent the redirect from working.<br>";
        echo "Output length: " . strlen($output_so_far) . " characters";
        echo "</div>";
    }
    
    // Clean the buffer for the redirect test
    ob_clean();
    
    // Now test the signup method
    echo "<h3>🔄 Testing signup method...</h3>";
    
    // Capture the signup process
    ob_start();
    
    $ObjAuth->signup($conf, $ObjFncs, $lang, $ObjSendMail);
    
    $signup_output = ob_get_contents();
    ob_end_clean();
    
    if (!empty($signup_output)) {
        echo "<div style='color: red; background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Signup method produced output!</strong><br>";
        echo "Output: <pre>" . htmlspecialchars($signup_output) . "</pre>";
        echo "<strong>This prevents the redirect from working!</strong>";
        echo "</div>";
    } else {
        echo "<div style='color: green; background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ No output from signup method - redirect should work!</strong>";
        echo "</div>";
    }
    
    // Check if redirect actually happened (it won't in this test, but let's see what happens)
    echo "<h3>📋 What should happen:</h3>";
    echo "<ol>";
    echo "<li>User submits form</li>";
    echo "<li>signup() method processes data</li>";
    echo "<li>User is saved to database</li>";
    echo "<li>Welcome email is sent</li>";
    echo "<li>Success message is stored in session</li>";
    echo "<li>header('Location: signin.php?signup=success') is called</li>";
    echo "<li>exit() stops execution</li>";
    echo "<li>Browser redirects to signin.php</li>";
    echo "</ol>";
    
    // Check if user was actually created
    $db = new Database($conf);
    $new_user = $db->fetchOne("SELECT id FROM users WHERE email = :email", [':email' => $_POST['email']]);
    
    if ($new_user) {
        echo "<p style='color: green;'>✅ User was created in database (ID: " . $new_user['id'] . ")</p>";
        // Clean up
        $db->execute("DELETE FROM users WHERE id = :id", [':id' => $new_user['id']]);
        echo "<p style='color: blue;'>🧹 Test user cleaned up</p>";
    } else {
        echo "<p style='color: red;'>❌ User was NOT created in database</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>❌ Error:</strong> " . $e->getMessage();
    echo "</div>";
}

$final_output = ob_get_clean();
echo $final_output;

echo "<hr>";
echo "<h2>🔧 Potential Fixes:</h2>";
echo "<ol>";
echo "<li><strong>If output is detected:</strong> Remove echo statements from included files</li>";
echo "<li><strong>If redirect fails:</strong> Use JavaScript redirect as backup</li>";
echo "<li><strong>If form stays on same page:</strong> Check for form processing errors</li>";
echo "</ol>";
?>