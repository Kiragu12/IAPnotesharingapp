<?php
/**
 * Test Signup Redirect After Fix
 */

echo "<h1>🧪 Testing Signup Redirect After Fix</h1>";
echo "<hr>";

// Simulate a signup form submission
$test_email = 'redirect_test_' . time() . '@gmail.com';
$test_name = 'Redirect Test User';
$test_password = 'TestPassword123!';

echo "<h2>Test Account Details:</h2>";
echo "<ul>";
echo "<li><strong>Email:</strong> $test_email</li>";
echo "<li><strong>Name:</strong> $test_name</li>";
echo "<li><strong>Password:</strong> $test_password</li>";
echo "</ul>";

echo "<h2>Simulating Signup Process...</h2>";

// Create a test form file that mimics the signup behavior
$test_form_content = '<?php
// Start session
session_start();

// Load required files
require_once "ClassAutoLoad.php";

// Process signup if form submitted BEFORE any output
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["signup"])) {
    // This will redirect and exit if successful, so no further code will run
    $result = $ObjAuth->signup($conf, $ObjFncs, $lang, $ObjSendMail);
    echo "If you see this message, the redirect did not work.";
}

// If we reach here, either no form was submitted or there was an error
echo "Form page loaded - no redirect occurred";
?>';

file_put_contents('test_signup_redirect.php', $test_form_content);

echo "<p>✅ Created test signup file</p>";

// Test the signup with curl to see if redirect works
echo "<h3>🔄 Testing signup with curl...</h3>";

$curl_command = 'curl -X POST -d "signup=1&fullname=' . urlencode($test_name) . '&email=' . urlencode($test_email) . '&password=' . urlencode($test_password) . '" -i http://localhost/IAPnotesharingapp-1/test_signup_redirect.php';

echo "<p><strong>Command:</strong> <code>$curl_command</code></p>";

$output = shell_exec($curl_command);

if ($output) {
    echo "<h4>Response:</h4>";
    echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto;'>";
    echo htmlspecialchars($output);
    echo "</pre>";
    
    // Check if redirect header is present
    if (strpos($output, 'Location: signin.php?signup=success') !== false) {
        echo "<div style='color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>✅ SUCCESS: Redirect header found!</strong><br>";
        echo "The signup redirect is now working correctly.";
        echo "</div>";
    } else {
        echo "<div style='color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>❌ ISSUE: No redirect header found</strong><br>";
        echo "The redirect may still not be working properly.";
        echo "</div>";
    }
} else {
    echo "<p style='color: red;'>❌ Could not test with curl</p>";
}

// Clean up test file
unlink('test_signup_redirect.php');
echo "<p>🧹 Cleaned up test file</p>";

echo "<hr>";
echo "<h2>📋 What was fixed:</h2>";
echo "<ol>";
echo "<li><strong>Moved signup processing BEFORE HTML output</strong> - This ensures no headers are sent before the redirect</li>";
echo "<li><strong>Removed message retrieval after redirect</strong> - Since redirect exits, those lines won't run anyway</li>";
echo "<li><strong>Simplified the flow</strong> - Process form → redirect OR show form</li>";
echo "</ol>";

echo "<h2>🧪 Next Steps:</h2>";
echo "<ol>";
echo "<li>Test the actual signup form in your browser</li>";
echo "<li>Create an account and verify it redirects to signin.php</li>";
echo "<li>Check that the success message appears on the signin page</li>";
echo "</ol>";
?>