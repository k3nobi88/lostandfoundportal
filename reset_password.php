<?php
include 'config.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

// Validate token (expires after 1 hour)
$reset = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM password_resets WHERE token='".mysqli_real_escape_string($conn, $token)."'
     AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)"
));

if (!$reset) {
    $error = "This reset link is invalid or has expired.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reset) {
    $newPass = $_POST['password'];
    $confirm = $_POST['confirm'];

    if (strlen($newPass) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($newPass !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $email = mysqli_real_escape_string($conn, $reset['email']);
        mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE email='$email'");
        mysqli_query($conn, "DELETE FROM password_resets WHERE email='$email'");
        $success = "Password updated! You can now <a href='login.php'>sign in</a>.";
    }
}
?>
<!DOCTYPE html>
<html><head>
    <title>Reset Password – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head><body class="auth-page">
<?php include 'navbar.php'; ?>
<div class="auth-wrapper">
    <div class="auth-right" style="width:100%;max-width:480px;margin:80px auto;">
        <div class="auth-form-box">
            <h1>Set New Password</h1>
            <?php if ($error): ?>
                <div class="auth-error">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="auth-error" style="border-color:#22c55e;color:#22c55e;">✅ <?php echo $success; ?></div>
            <?php elseif ($reset): ?>
            <form method="POST">
                <div class="auth-field">
                    <label>New Password</label>
                    <input type="password" name="password" placeholder="At least 6 characters" required>
                </div>
                <div class="auth-field">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm" placeholder="Repeat password" required>
                </div>
                <button type="submit" class="auth-submit-btn">Update Password</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
</body></html>