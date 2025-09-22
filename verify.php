<?php
session_start();
require_once "Proc/auth.php";  


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $code = $_POST["code"];
    $email = $_SESSION["email"]; 

    $auth = new Auth();
    $result = $auth->verifyCode($email, $code);

    if ($result === true) {
        echo "<p style='color:green;'>Verification successful! You can now <a href='signin.php'>login</a>.</p>";
    } else {
        echo "<p style='color:red;'>$result</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Account</title>
</head>
<body>
    <h2>Enter the 6-digit code sent to your email</h2>
    <form method="POST">
        <input type="text" name="code" placeholder="Enter code" required>
        <button type="submit">Verify</button>
    </form>
</body>
</html>