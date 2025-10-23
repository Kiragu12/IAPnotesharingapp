<?php
/**
 * Sign-In Flow
 * ------------
 * Complete email+password -> 2FA -> dashboard authentication flow.
 */

session_start();

require_once 'conf.php';
require_once 'Lang/en.php';
require_once 'Global/Database.php';
require_once 'Global/fncs.php';
require_once 'Global/SendMail.php';
require_once 'Proc/auth.php';

$ObjFncs = new fncs();
$ObjSendMail = new SendMail();
$ObjAuth = new auth();
$db = new Database($conf);

$flow = $_SESSION['signin_flow'] ?? ['stage' => 'login'];
$alerts = [];
$stage = $flow['stage'] ?? 'login';

// Reset the flow
if (isset($_POST['reset_flow'])) {
    unset($_SESSION['signin_flow']);
    unset($_SESSION['temp_user_id'], $_SESSION['temp_user_email'], $_SESSION['temp_user_name'], $_SESSION['temp_remember_me']);
    header('Location: signin.php');
    exit;
}

function addAlert(array &$alerts, string $type, string $message): void {
    $alerts[] = [
        'type' => $type,
        'message' => $message
    ];
}

// Handle login stage submission
if (isset($_POST['login_submit'])) {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $remember = !empty($_POST['remember_me']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        addAlert($alerts, 'danger', 'Please enter a valid email address.');
        $stage = 'login';
        $flow = ['stage' => 'login'];
        unset($_SESSION['signin_flow']);
    } elseif (strlen($password) === 0) {
        addAlert($alerts, 'danger', 'Password is required.');
        $stage = 'login';
        $flow = ['stage' => 'login'];
        unset($_SESSION['signin_flow']);
    } else {
        try {
            $user = $db->fetchOne("SELECT id, email, password, full_name FROM users WHERE email = :email LIMIT 1", [':email' => $email]);

            if (!$user || !password_verify($password, $user['password'])) {
                addAlert($alerts, 'danger', 'Invalid email or password.');
                $stage = 'login';
                $flow = ['stage' => 'login'];
                unset($_SESSION['signin_flow']);
            } else {
                $otpResult = $ObjAuth->regenerateOtpForUser((int)$user['id'], $conf, $ObjSendMail);

                if (!$otpResult['success']) {
                    addAlert($alerts, 'danger', $otpResult['message'] ?? 'Could not generate verification code.');
                    $stage = 'login';
                } else {
                    $_SESSION['temp_user_id'] = $user['id'];
                    $_SESSION['temp_user_email'] = $user['email'];
                    $_SESSION['temp_user_name'] = $user['full_name'] ?? 'User';
                    $_SESSION['temp_remember_me'] = $remember;

                    $flow = [
                        'stage' => 'otp',
                        'user_id' => $user['id'],
                        'email' => $user['email'],
                        'name' => $user['full_name'] ?? 'User',
                        'remember' => $remember,
                        'otp' => $otpResult['code'],
                        'expires_at' => $otpResult['expires_at']
                    ];
                    $_SESSION['signin_flow'] = $flow;

                    $stage = 'otp';

                    addAlert(
                        $alerts,
                        $otpResult['email_sent'] ? 'success' : 'warning',
                        $otpResult['email_sent']
                            ? 'We emailed a 6-digit verification code to ' . htmlspecialchars($user['email']) . '.'
                            : 'Verification code generated but email could not be sent. Use the code shown below or resend.'
                    );
                }
            }
        } catch (Exception $e) {
            addAlert($alerts, 'danger', 'Error during login: ' . $e->getMessage());
            $stage = 'login';
            $flow = ['stage' => 'login'];
            unset($_SESSION['signin_flow']);
        }
    }
}

// Handle OTP verification stage
if ($stage === 'otp' && isset($_POST['verify_submit'])) {
    $inputCode = preg_replace('/[^0-9]/', '', $_POST['otp_code'] ?? '');
    $userId = $flow['user_id'] ?? null;

    if (!$userId || empty($_SESSION['temp_user_id'])) {
        addAlert($alerts, 'danger', 'Session expired. Please start again.');
        $stage = 'login';
        $flow = ['stage' => 'login'];
        unset($_SESSION['signin_flow']);
    } elseif (strlen($inputCode) !== 6) {
        addAlert($alerts, 'danger', 'Please enter the 6-digit verification code.');
    } else {
        try {
            $sql = "SELECT tfc.id as code_id, tfc.attempts_used, tfc.max_attempts, tfc.expires_at, u.id, u.email, u.full_name
                    FROM two_factor_codes tfc
                    JOIN users u ON tfc.user_id = u.id
                    WHERE tfc.user_id = :user_id
                      AND tfc.code = :code
                      AND tfc.expires_at > NOW()
                      AND tfc.used_at IS NULL
                    ORDER BY tfc.created_at DESC
                    LIMIT 1";

            $result = $db->fetchOne($sql, [
                ':user_id' => $userId,
                ':code' => $inputCode
            ]);

            if (!$result) {
                $db->execute(
                    "UPDATE two_factor_codes SET attempts_used = attempts_used + 1 WHERE user_id = :user_id AND expires_at > NOW() AND used_at IS NULL",
                    [':user_id' => $userId]
                );
                addAlert($alerts, 'danger', 'Invalid or expired verification code.');
            } else {
                $maxAttempts = (int)($result['max_attempts'] ?? 3);
                if ((int)$result['attempts_used'] >= $maxAttempts) {
                    addAlert($alerts, 'danger', 'Too many failed attempts. Request a new code.');
                } else {
                    $db->execute("UPDATE two_factor_codes SET used_at = NOW() WHERE id = :code_id", [':code_id' => $result['code_id']]);

                    $_SESSION['user_id'] = $result['id'];
                    $_SESSION['user_email'] = $result['email'];
                    $_SESSION['user_name'] = $result['full_name'];
                    $_SESSION['login_time'] = time();

                    if (!empty($flow['remember'])) {
                        $ObjAuth->createRememberToken($result['id'], $conf);
                    }

                    unset($_SESSION['temp_user_id'], $_SESSION['temp_user_email'], $_SESSION['temp_user_name'], $_SESSION['temp_remember_me']);

                    $flow['stage'] = 'complete';
                    $_SESSION['signin_flow'] = $flow;
                    $stage = 'complete';

                    addAlert($alerts, 'success', '🎉 Verification successful! You are now signed in.');
                }
            }
        } catch (Exception $e) {
            addAlert($alerts, 'danger', 'Error verifying code: ' . $e->getMessage());
        }
    }
}

// Handle resend in test flow
if ($stage === 'otp' && isset($_POST['resend_submit'])) {
    $userId = $flow['user_id'] ?? null;
    if ($userId) {
        $otpResult = $ObjAuth->regenerateOtpForUser((int)$userId, $conf, $ObjSendMail);
        if ($otpResult['success']) {
            $flow['otp'] = $otpResult['code'];
            $flow['expires_at'] = $otpResult['expires_at'];
            $_SESSION['signin_flow'] = $flow;
            addAlert(
                $alerts,
                $otpResult['email_sent'] ? 'success' : 'warning',
                $otpResult['email_sent']
                    ? 'A new verification code was emailed to ' . htmlspecialchars($flow['email']) . '.'
                    : 'New code generated but email could not be sent. Use the code shown below.'
            );
        } else {
            addAlert($alerts, 'danger', $otpResult['message'] ?? 'Could not resend verification code.');
        }
    } else {
        addAlert($alerts, 'danger', 'Session expired. Please start again.');
        $stage = 'login';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - IAP Note Sharing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root{--brand-1:#667eea;--brand-2:#764ba2;--muted:#6c757d}
        body {
            background: linear-gradient(135deg,var(--brand-2) 0%,var(--brand-1) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 12px;
            font-family: 'Poppins', system-ui, -apple-system, "Segoe UI", Roboto, 'Helvetica Neue', Arial;
            color: #222;
        }
        .flow-card{
            max-width:760px;
            width:100%;
            background:#ffffffee;
            border-radius:16px;
            box-shadow:0 12px 36px rgba(24,28,63,0.18);
            overflow:hidden;
            border:1px solid rgba(110,115,240,0.06);
        }
        .flow-header{padding:2.25rem 1.75rem 1.25rem;text-align:center;background:linear-gradient(180deg, rgba(255,255,255,0.6), rgba(255,255,255,0.35));}
        .flow-step{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:12px;flex-wrap:wrap}
        .flow-step span{background:rgba(102,126,234,0.08);color:var(--brand-1);padding:7px 12px;border-radius:999px;font-weight:600;font-size:0.95rem}
        .btn-ghost{background:transparent;border:1px solid rgba(0,0,0,0.06);padding:.5rem .75rem;border-radius:8px}
        .form-control:focus{box-shadow:0 6px 20px rgba(102,126,234,0.12);border-color:var(--brand-1)}
        @media (max-width:540px){.flow-card{margin:16px}.flow-header h2{font-size:1.2rem}}
    </style>
</head>
<body>
    <div class="flow-card">
        <div class="flow-header">
            <div class="flow-step">
                <span><i class="bi bi-person-check me-2"></i>Step 1: Sign In</span>
                <span><i class="bi bi-key me-2"></i>Step 2: Enter Code</span>
                <span><i class="bi bi-rocket-takeoff me-2"></i>Step 3: Dashboard</span>
            </div>
            <h2 class="fw-bold mb-2">Sign In to Your Account</h2>
            <p class="text-muted mb-0">Enter your credentials to access your dashboard.</p>
        </div>

        <div class="p-4 p-md-5">
            <?php foreach ($alerts as $alert): ?>
                <div class="alert alert-<?php echo htmlspecialchars($alert['type']); ?> alert-dismissible fade show" role="alert">
                    <?php echo $alert['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>

            <?php if ($stage === 'login'): ?>
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="you@example.com">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Your password">
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember_me">
                        <label class="form-check-label" for="remember">
                            Remember this device (uses remember token)
                        </label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="login_submit" class="btn btn-primary btn-lg">
                            Continue to Verification <i class="bi bi-arrow-right-circle ms-2"></i>
                        </button>
                    </div>
                </form>
            <?php elseif ($stage === 'otp'): ?>
                <div class="mb-4">
                    <h4 class="fw-semibold mb-1">Account Details</h4>
                    <p class="text-muted mb-0"><strong>Email:</strong> <?php echo htmlspecialchars($flow['email']); ?></p>
                    <p class="text-muted"><strong>Name:</strong> <?php echo htmlspecialchars($flow['name']); ?></p>
                </div>

                <form method="POST" class="mb-3">
                    <div class="mb-3">
                        <label for="otp_code" class="form-label fw-semibold">Enter the 6-digit verification code</label>
                        <input type="text" class="form-control form-control-lg" id="otp_code" name="otp_code" maxlength="6" pattern="\d{6}" required placeholder="123456">
                        <div class="form-text">This matches the code sent to your inbox.</div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" name="verify_submit" class="btn btn-success btn-lg">
                            Verify &amp; Finish Login <i class="bi bi-shield-check ms-2"></i>
                        </button>
                        <button type="submit" name="resend_submit" class="btn btn-outline-primary">
                            Resend Code <i class="bi bi-arrow-repeat ms-2"></i>
                        </button>
                    </div>
                </form>

            <?php elseif ($stage === 'complete'): ?>
                <div class="text-center py-4">
                    <div class="mb-3">
                        <i class="bi bi-stars" style="font-size: 2.5rem; color: #4c63d9;"></i>
                    </div>
                    <h3 class="fw-bold">You're all set!</h3>
                    <p class="text-muted mb-4">The full authentication flow completed successfully.</p>
                    <div class="d-grid gap-2">
                        <a href="dashboard.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
                        <form method="POST">
                            <button type="submit" name="reset_flow" class="btn btn-outline-secondary">Start Over</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($stage !== 'complete'): ?>
                <form method="POST" class="mt-4">
                    <button type="submit" name="reset_flow" class="btn btn-link text-muted">Start Over</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // copy OTP to clipboard
        const copyBtn = document.getElementById('copyOtpBtn');
        if(copyBtn){
            copyBtn.addEventListener('click', function(){
                const code = this.getAttribute('data-clipboard-text') || document.getElementById('latestOtp').textContent || '';
                if(!navigator.clipboard) {
                    alert('Copy not supported');
                    return;
                }
                navigator.clipboard.writeText(code).then(()=>{
                    const orig = this.textContent;
                    this.textContent = 'Copied';
                    setTimeout(()=> this.textContent = orig, 1500);
                });
            });
        }
        // autofocus OTP input when on OTP stage
        (function(){
            const otpInput = document.getElementById('otp_code');
            if(otpInput){otpInput.focus();}
        })();
    </script>
</body>
</html>
