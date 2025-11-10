<?php
// Settings page — updated to use the project's Database class and config
session_start();

// Load configuration and required services
require_once __DIR__ . '/../../config/conf.php';
require_once __DIR__ . '/../../app/Services/Global/Database.php';
require_once __DIR__ . '/../../app/Services/Global/fncs.php';

$ObjFncs = new fncs();
$db = new Database($conf);

// Check user is logged in
if (!isset($_SESSION['user_id'])) {
    // not logged in — redirect to signin
    header('Location: signin.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// Fetch current user data to prefill form
try {
    $current_user = $db->fetchOne('SELECT id, full_name, email FROM users WHERE id = :id LIMIT 1', [':id' => $user_id]);
} catch (Exception $e) {
    error_log('Settings: failed to fetch user: ' . $e->getMessage());
    $current_user = null;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));

    // Basic validation
    if ($name === '' || !preg_match('/^[\p{L} \-\.]+$/u', $name)) {
        $error_message = 'Please enter a valid name.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        try {
            $db->query('UPDATE users SET full_name = :name, email = :email WHERE id = :id', [
                ':name' => $name,
                ':email' => $email,
                ':id' => $user_id
            ]);

            $success_message = 'Profile updated successfully!';
            // Refresh current user values
            $current_user['full_name'] = $name;
            $current_user['email'] = $email;
        } catch (Exception $e) {
            error_log('Settings (update profile) error: ' . $e->getMessage());
            $error_message = 'Failed to update profile. Please try again later.';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';

    if ($old_pass === '' || $new_pass === '') {
        $error_message = 'Please provide both old and new passwords.';
    } elseif (strlen($new_pass) < ($conf['min_password_length'] ?? 6)) {
        $error_message = 'New password must be at least ' . ($conf['min_password_length'] ?? 6) . ' characters long.';
    } else {
        try {
            $row = $db->fetchOne('SELECT password FROM users WHERE id = :id LIMIT 1', [':id' => $user_id]);
            $hash = $row['password'] ?? '';

            if ($hash && password_verify($old_pass, $hash)) {
                $hashed_new_pass = password_hash($new_pass, PASSWORD_DEFAULT);
                $db->query('UPDATE users SET password = :pass WHERE id = :id', [':pass' => $hashed_new_pass, ':id' => $user_id]);
                $success_message = 'Password changed successfully!';
            } else {
                $error_message = 'Old password is incorrect.';
            }
        } catch (Exception $e) {
            error_log('Settings (change password) error: ' . $e->getMessage());
            $error_message = 'Failed to change password. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
   <head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <style>
        body {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;

        }
        
        .settings-container {
            width: 80%;
            margin: 30px auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
        }
        h2 { margin-bottom: 15px; }
        form { margin-bottom: 25px; }
        label { display: block; margin-bottom: 8px; }
        input[type=text], input[type=email], input[type=password] {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
            border: none;
            border-radius: 5px;
        }
        .message { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
<div class="settings-container">
    <h2>Account Settings</h2>

    <?php if (isset($success_message)) echo "<p class='message'>$success_message</p>"; ?>
    <?php if (isset($error_message)) echo "<p class='error'>$error_message</p>"; ?>

    <form method="POST">
        <h3>Update Profile</h3>
        <label>Name</label>
        <input type="text" name="name" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <button type="submit" name="update_profile">Save Changes</button>
    </form>

    <form method="POST">
        <h3>Change Password</h3>
        <label>Old Password</label>
        <input type="password" name="old_password" required>
        <label>New Password</label>
        <input type="password" name="new_password" required>
        <button type="submit" name="change_password">Change Password</button>
    </form>
</div>
</body>

</html>