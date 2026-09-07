<?php
include 'config.php';
include 'mailer_helper.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE email='".mysqli_real_escape_string($conn, $email)."'"));

    // Always show the same message whether email exists or not (prevents user enumeration)
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        mysqli_query($conn, "DELETE FROM password_resets WHERE email='".mysqli_real_escape_string($conn, $email)."'");
        mysqli_query($conn, "INSERT INTO password_resets (email, token, created_at) VALUES ('".mysqli_real_escape_string($conn, $email)."', '$token', NOW())");

        $resetLink = SITE_URL . "/reset_password.php?token=$token";
        $html = mailTemplate('Reset Your Password',
            '<p style="color:#ccc;">You requested a password reset. Click the button below to set a new password. This link expires in 1 hour.</p>
             <a href="'.$resetLink.'" style="display:inline-block;background:#cc0000;color:white;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:700;margin:16px 0;">Reset Password</a>
             <p style="color:#555;font-size:12px;">If you did not request this, ignore this email.</p>'
        );
        sendMail($email, $user['username'], 'Reset Your Password – Lost & Found', $html);
    }

    $message = "If that email exists in our system, a reset link has been sent.";
}
?>
<!DOCTYPE html>
<html><head>
    <title>Forgot Password – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head><body class="auth-page">
<?php include 'navbar.php'; ?>
<div class="auth-wrapper">
    <div class="auth-right" style="width:100%;max-width:480px;margin:80px auto;">
        <div class="auth-form-box">
            <h1>Forgot Password</h1>
            <p class="auth-subtitle">Enter your email and we'll send you a reset link.</p>
            <?php if ($message): ?>
                <div class="auth-error" style="border-color:#22c55e;color:#22c55e;">✅ <?php echo $message; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="auth-field">
                    <label>Email address</label>
                    <input type="email" name="email" placeholder="you@example.com" required>
                </div>
                <button type="submit" class="auth-submit-btn">Send Reset Link</button>
            </form>
            <p class="auth-switch" style="margin-top:16px;"><a href="login.php">← Back to login</a></p>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
</body></html>