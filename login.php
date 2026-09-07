<?php
include 'config.php';

$error = '';

$totalItems  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM listings"))['c'];
$totalUsers  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
$totalClaims = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM claims WHERE status='approved'"))['c'];

// ── Rate limiting via session ──────────────────────────────────────────────
// Allow 5 attempts per 15 minutes per browser session
$maxAttempts  = 5;
$lockoutSecs  = 15 * 60; // 15 minutes

if (!isset($_SESSION['login_attempts']))  $_SESSION['login_attempts']  = 0;
if (!isset($_SESSION['login_locked_until'])) $_SESSION['login_locked_until'] = 0;

$isLocked    = (time() < $_SESSION['login_locked_until']);
$remaining   = max(0, $_SESSION['login_locked_until'] - time());
$attemptsLeft = max(0, $maxAttempts - $_SESSION['login_attempts']);
// ─────────────────────────────────────────────────────────────────────────

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$isLocked) {

    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            // ✅ Success — clear rate limit
            $_SESSION['login_attempts']    = 0;
            $_SESSION['login_locked_until'] = 0;

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            header("Location: index.php");
            exit();

        } else {
            // ❌ Wrong password — THIS is where rate limiting triggers
            $_SESSION['login_attempts']++;
            if ($_SESSION['login_attempts'] >= $maxAttempts) {
                $_SESSION['login_locked_until'] = time() + $lockoutSecs;
                $error = "Too many failed attempts. Please wait 15 minutes before trying again.";
            } else {
                $attemptsLeft = $maxAttempts - $_SESSION['login_attempts'];
                $error = "Wrong password. " . $attemptsLeft . " attempt" . ($attemptsLeft == 1 ? "" : "s") . " remaining before lockout.";
            }
        }

    } else {
        // ❌ Email not found — NO rate limit, just a simple message
        $error = "No account found with that email. <a href='signup.php' style='color:#cc0000;'>Create one?</a>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign In – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body class="auth-page">

<?php include 'navbar.php'; ?>

<div class="auth-wrapper">

    <!-- LEFT PANEL -->
    <div class="auth-left">
        <div class="auth-left-content">
            <div class="auth-left-logo">LOST & <span>FOUND</span></div>
            <h2>Reuniting people<br>with what matters.</h2>
            <p>Join thousands of Malaysians who have already recovered their lost belongings through our community platform.</p>

            <div class="auth-left-stats">
				<div class="auth-stat">
					<span class="auth-stat-num"><?php echo $totalItems; ?>+</span>
					<span class="auth-stat-label">Items Reported</span>
				</div>
				<div class="auth-stat">
					<span class="auth-stat-num"><?php echo $totalClaims; ?>+</span>
					<span class="auth-stat-label">Items Reunited</span>
				</div>
				<div class="auth-stat">
					<span class="auth-stat-num"><?php echo $totalUsers; ?>+</span>
					<span class="auth-stat-label">Active Users</span>
				</div>
</div>
        </div>
    </div>

    <!-- RIGHT PANEL: Form -->
    <div class="auth-right">
        <div class="auth-form-box">

            <h1>Welcome back</h1>
            <p class="auth-subtitle">Sign in to your account to continue</p>

            <?php if($isLocked): ?>
                <div class="auth-error">
                    <span>🔒</span> Account temporarily locked due to too many failed attempts.
                    Please wait <strong id="lockCountdown"><?php echo gmdate('i:s', $remaining); ?></strong> before trying again.
                </div>
            <?php elseif($error): ?>
                <div class="auth-error">
                    <span>⚠️</span> <?php echo $error; ?>
                </div>
                <?php if($_SESSION['login_attempts'] >= 3 && !$isLocked): ?>
                    <div style="text-align:center; margin-top:10px;">
                        <a href="forgot_password.php" style="color:#cc0000; font-size:13px; font-weight:600;">
                            🔑 Forgot your password?
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <form method="POST">

                <div class="auth-field">
                    <label>Email address</label>
                    <input type="email" name="email" placeholder="you@example.com" required>
                </div>

                <div class="auth-field">
                    <label style="display:flex; justify-content:space-between; align-items:center;">
                        Password
                        <a href="forgot_password.php" style="font-size:12px; color:#cc0000; font-weight:500;">Forgot password?</a>
                    </label>
                    <div class="password-wrap">
                        <input type="password" name="password" id="passwordInput" placeholder="Enter your password" required>
                        <button type="button" class="eye-btn" onclick="togglePassword('passwordInput', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="auth-submit-btn"
                    <?php echo $isLocked ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''; ?>>
                    <?php echo $isLocked ? '🔒 Account Locked' : 'Sign In'; ?>
                </button>

            </form>

            <div class="auth-divider"><span>or</span></div>

            <p class="auth-switch">
                Don't have an account? <a href="signup.php">Create one free</a>
            </p>

        </div>
    </div>

</div>

<?php include 'footer.php'; ?>

<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    input.type = input.type === "password" ? "text" : "password";
    btn.style.opacity = input.type === "text" ? "1" : "0.4";
}
</script>


<?php if($isLocked): ?>
<script>
// Live countdown timer
let secs = <?php echo $remaining; ?>;
const el  = document.getElementById('lockCountdown');
if (el) {
    const t = setInterval(() => {
        secs--;
        if (secs <= 0) { clearInterval(t); location.reload(); return; }
        const m = String(Math.floor(secs / 60)).padStart(2, '0');
        const s = String(secs % 60).padStart(2, '0');
        el.textContent = m + ':' + s;
    }, 1000);
}
</script>
<?php endif; ?>


</body>
</html>