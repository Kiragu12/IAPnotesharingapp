public function verifyCode($email, $code) {
    $db = new Database();
    $pdo = $db->connect();

    // Get user info
    $stmt = $pdo->prepare("SELECT verification_code, code_expiry FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        return "User not found.";
    }

    // Check if code matches
    if ($user["verification_code"] != $code) {
        return "Invalid code.";
    }

    // Check if code expired
    if (strtotime($user["code_expiry"]) < time()) {
        return "Code expired. Please request a new one.";
    }

    // Mark user as verified
    $update = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE email = ?");
    $update->execute([$email]);

    return true;
}