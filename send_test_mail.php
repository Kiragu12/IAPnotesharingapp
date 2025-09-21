<?php
require_once __DIR__ . '/conf.php';
require_once __DIR__ . '/Global/SendMail.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = trim($_POST['email'] ?? '');
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $mailCnt = [
                'name_from' => $conf['site_name'] ?? 'IAP App',
                'mail_from' => $conf['smtp_user'],
                'name_to'   => $to,
                'mail_to'   => $to,
                'subject'   => 'Test email from IAP app',
                'body'      => 'This is a test email sent via PHPMailer. If you received this, SMTP is working.'
            ];
            $mailer = new SendMail();
            $mailer->Send_Mail($conf, $mailCnt);
            $message = 'Email sent successfully to ' . htmlspecialchars($to);
        } catch (Exception $e) {
            $error = 'Failed to send: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Send Test Mail</title>
  <style>
    body { font-family: system-ui, Arial, sans-serif; margin: 2rem; }
    .box { max-width: 520px; padding: 1rem 1.25rem; border: 1px solid #ddd; border-radius: 8px; }
    .row { margin-bottom: 0.75rem; }
    label { display:block; font-weight:600; margin-bottom:0.25rem; }
    input[type=email] { width:100%; padding:0.5rem; border:1px solid #ccc; border-radius:6px; }
    button { padding:0.5rem 0.9rem; border:0; background:#0d6efd; color:#fff; border-radius:6px; cursor:pointer; }
    .msg { margin-top:0.75rem; padding:0.5rem 0.75rem; border-radius:6px; }
    .ok { background:#e9f7ef; color:#2e7d32; border:1px solid #c8e6c9; }
    .err { background:#fdecea; color:#c62828; border:1px solid #f5c6cb; }
  </style>
  <!-- This page uses conf.php; ensure it exists locally (copy from conf.sample.php) -->
  <!-- Do NOT commit conf.php with real secrets -->
  </head>
<body>
  <div class="box">
    <h3>Send Test Mail</h3>
    <form method="post">
      <div class="row">
        <label for="email">Recipient email</label>
        <input type="email" id="email" name="email" placeholder="name@example.com" required>
      </div>
      <button type="submit">Send</button>
    </form>
    <?php if ($message): ?><div class="msg ok"><?php echo $message; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="msg err"><?php echo $error; ?></div><?php endif; ?>
  </div>
</body>
</html>
