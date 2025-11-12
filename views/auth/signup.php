<?php
/**
 * Guided Sign-Up Page (Test)
 * Allows new users to register, confirms account creation, and links to sign-in.
 */

session_start();
require_once '../../config/conf.php';
require_once '../../config/Lang/en.php';
require_once '../../app/Services/Global/Database.php';
require_once '../../app/Services/Global/fncs.php';
require_once '../../app/Services/Global/SendMail.php';
require_once '../../app/Controllers/Proc/auth.php';

$ObjFncs = new fncs();
$ObjSendMail = new SendMail();
$ObjAuth = new auth();
$db = new Database($conf);

$alerts = [];
$stage = $_SESSION['signup_stage'] ?? 'form';

function addAlert(array &$alerts, string $type, string $message): void {
    $alerts[] = [
        'type' => $type,
        'message' => $message
    ];
}

if (isset($_POST['reset_flow'])) {
    unset($_SESSION['signup_stage']);
    header('Location: signup.php');
    exit;
}

if ($stage === 'form' && isset($_POST['signup_submit'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (!preg_match('/^[a-zA-Z ]+$/', $fullname)) {
        addAlert($alerts, 'danger', 'Full name must contain only letters and spaces.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        addAlert($alerts, 'danger', 'Please enter a valid email address.');
    } elseif (strlen($password) < ($conf['min_password_length'] ?? 8)) {
        addAlert($alerts, 'danger', 'Password must be at least ' . ($conf['min_password_length'] ?? 8) . ' characters long.');
    } else {
        $email_domain = substr(strrchr($email, "@"), 1);
        if (!in_array($email_domain, $conf['valid_email_domain'])) {
            addAlert($alerts, 'danger', 'Email domain must be one of: ' . implode(', ', $conf['valid_email_domain']));
        } else {
            try {
                $sql_check = "SELECT id FROM users WHERE email = :email LIMIT 1";
                $existing_user = $db->fetchOne($sql_check, [':email' => $email]);
                if ($existing_user) {
                    addAlert($alerts, 'danger', 'An account with this email already exists. Please sign in instead.');
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    // Generate username from full name (remove spaces and make lowercase)
                    $username = strtolower(str_replace(' ', '', $fullname));
                    
                    // Ensure username is unique by adding number if needed
                    $base_username = $username;
                    $counter = 1;
                    while (true) {
                        $check_username = $db->fetchOne("SELECT id FROM users WHERE username = :username", [':username' => $username]);
                        if (!$check_username) break;
                        $username = $base_username . $counter;
                        $counter++;
                    }
                    
                    $sql_insert = "INSERT INTO users (username, email, password, full_name, is_verified, is_2fa_enabled, created_at) VALUES (:username, :email, :password, :full_name, 1, 1, NOW())";
                    $db->execute($sql_insert, [
                        ':username' => $username,
                        ':email' => $email,
                        ':password' => $hashedPassword,
                        ':full_name' => $fullname
                    ]);
                    $_SESSION['signup_stage'] = 'success';
                    $stage = 'success';
                    addAlert($alerts, 'success', 'Account created! You can now sign in below.');
                }
            } catch (Exception $e) {
                error_log("Signup Error: " . $e->getMessage());
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    addAlert($alerts, 'danger', 'An account with this email already exists. Please sign in instead.');
                } else {
                    addAlert($alerts, 'danger', 'Database operation failed. Please try again later.');
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - <?php echo htmlspecialchars($conf['site_name'] ?? 'NotesShare'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root{--brand-1:#667eea;--brand-2:#764ba2;--muted:#6c757d}
        body{background:linear-gradient(135deg,var(--brand-2),var(--brand-1));min-height:100vh;display:flex;align-items:center;justify-content:center;padding:28px 12px;font-family:'Poppins',system-ui,Roboto,Arial;color:#111}
        .flow-card{max-width:640px;width:100%;background:#fff;border-radius:14px;box-shadow:0 14px 36px rgba(20,25,60,0.12);overflow:hidden;border:1px solid rgba(110,115,240,0.06)}
        .flow-header{padding:2rem 1.5rem 1rem;text-align:center;background:linear-gradient(180deg, rgba(255,255,255,0.6), rgba(255,255,255,0.35))}
        .flow-step{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:12px;flex-wrap:wrap}
        .flow-step span{background:rgba(102,126,234,0.08);color:var(--brand-1);padding:7px 12px;border-radius:999px;font-weight:600}
        .form-control:focus{box-shadow:0 8px 20px rgba(102,126,234,0.12);border-color:var(--brand-1)}
        .btn-primary{background:linear-gradient(90deg,var(--brand-1),var(--brand-2));border:none}
        .small-muted{color:var(--muted)}
        .debug-box{background:#fbfdff;border-radius:10px;padding:14px;border:1px solid rgba(102,126,234,0.08);margin-top:14px;font-size:0.95rem}
        .otp-code{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;background:linear-gradient(90deg,var(--brand-1),var(--brand-2));color:#fff;padding:8px 12px;border-radius:8px;letter-spacing:4px;font-weight:700}
        .btn-copy{background:linear-gradient(90deg,var(--brand-1),var(--brand-2));color:#fff;border:none;padding:.45rem .65rem;border-radius:8px;cursor:pointer}
        @media (max-width:540px){.flow-card{margin:16px}.flow-header h2{font-size:1.2rem}}
    </style>
</head>
<body>
    <!-- Back to Home Link -->
    <a href="../index.php" style="position: fixed; top: 20px; left: 20px; z-index: 1000; text-decoration: none; color: #667eea; background: rgba(255,255,255,0.9); padding: 8px 16px; border-radius: 25px; backdrop-filter: blur(10px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,1)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.background='rgba(255,255,255,0.9)'; this.style.transform='translateY(0)'">
        <i class="bi bi-arrow-left me-2"></i>Back to Home
    </a>
    
    <div class="flow-card">
        <div class="flow-header">
            <div class="flow-step">
                <span><i class="bi bi-person-plus me-2"></i>Step 1: Sign Up</span>
                <span><i class="bi bi-person-check me-2"></i>Step 2: Sign In</span>
                <span><i class="bi bi-key me-2"></i>Step 3: 2FA</span>
            </div>
            <h2 class="fw-bold mb-2">Create Your Account</h2>
            <p class="text-muted mb-0">Register to get started with secure authentication.</p>
        </div>
        <div class="p-4 p-md-5">
            <?php foreach ($alerts as $alert): ?>
                <div class="alert alert-<?php echo htmlspecialchars($alert['type']); ?> alert-dismissible fade show" role="alert">
                    <?php echo $alert['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
            <?php if ($stage === 'form'): ?>
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="fullname" class="form-label fw-semibold">Full Name</label>
                        <input type="text" class="form-control" id="fullname" name="fullname" required placeholder="Your full name">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="you@example.com">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="At least 8 characters">
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="signup_submit" class="btn btn-primary btn-lg">
                            Create Account <i class="bi bi-person-plus ms-2"></i>
                        </button>
                    </div>
                </form>
            <?php elseif ($stage === 'success'): ?>
                <div class="text-center py-4">
                    <div class="mb-3">
                        <i class="bi bi-stars" style="font-size: 2.5rem; color: #4c63d9;"></i>
                    </div>
                    <h3 class="fw-bold">Account Created!</h3>
                    <p class="text-muted mb-4">Your account was saved successfully. You can now sign in and complete 2FA.</p>
                    <div class="d-grid gap-2">
                        <a href="signin.php" class="btn btn-success btn-lg">Go to Sign-In</a>
                        <form method="POST">
                            <button type="submit" name="reset_flow" class="btn btn-link text-danger">Register another account</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // copy OTP if present
        (function(){
            const btn = document.getElementById('copyOtpBtn2');
            if(!btn) return;
            btn.addEventListener('click', function(){
                const code = this.dataset.clipboardText || '';
                if(navigator.clipboard) navigator.clipboard.writeText(code).then(()=>{this.textContent='Copied'; setTimeout(()=>this.textContent='Copy',1200)});
            });
        })();
    </script>
</body>
</html>
