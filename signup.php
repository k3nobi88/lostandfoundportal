<?php
include 'config.php';

$error = '';

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $name      = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email_raw = trim($_POST['email']);
    $email     = mysqli_real_escape_string($conn, $email_raw);
	$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if(mysqli_num_rows($check) > 0){
        $error = "An account with this email already exists.";
    } else {

        $sql = "INSERT INTO users (username, email, password) VALUES ('$name', '$email', '$password')";

        if(mysqli_query($conn, $sql)){
            // Send welcome email (non-blocking — failure won't stop signup)
            include_once 'mailer_helper.php';
            $welcomeBody = '
                <p style="color:#ccc;font-size:15px;line-height:1.7;">Hi <strong style="color:white;">' . htmlspecialchars($name) . '</strong>,</p>
                <p style="color:#ccc;font-size:15px;line-height:1.7;">Welcome to the Lost &amp; Found Portal! Your account has been created successfully.</p>
                <p style="color:#ccc;font-size:15px;line-height:1.7;">You can now report lost or found items, submit claims, and chat with other users.</p>
                <a href="' . SITE_URL . '/browse.php" style="display:inline-block;margin-top:20px;background:#cc0000;color:white;padding:12px 28px;border-radius:10px;text-decoration:none;font-weight:700;">Browse Listings</a>
            ';
            sendMail($email_raw, $name, 'Welcome to Lost & Found Portal!', mailTemplate('Welcome aboard 🎉', $welcomeBody));

            header("Location: login.php");
            exit();
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up – Lost & Found</title>
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
            <h2>Help your community.<br>Find what's lost.</h2>
            <p>Whether you've lost something or found something that belongs to someone else — this is where it comes together.</p>

            <div class="auth-features">
                <div class="auth-feature-item">
                    <span class="auth-feature-icon">📍</span>
                    <div>
                        <strong>Location-based reports</strong>
                        <p>Tag exactly where items were lost or found</p>
                    </div>
                </div>
                <div class="auth-feature-item">
                    <span class="auth-feature-icon">💬</span>
                    <div>
                        <strong>Built-in chat</strong>
                        <p>Communicate directly with reporters</p>
                    </div>
                </div>
                <div class="auth-feature-item">
                    <span class="auth-feature-icon">🏆</span>
                    <div>
                        <strong>Rewards system</strong>
                        <p>Offer or receive rewards for reuniting items</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL: Form -->
    <div class="auth-right">
        <div class="auth-form-box">

            <h1>Create account</h1>
            <p class="auth-subtitle">Join the community — it's free</p>

            <?php if($error): ?>
                <div class="auth-error">
                    <span>⚠️</span> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="auth-field">
                    <label>Full name</label>
                    <input type="text" name="name" placeholder="Your full name" required>
                </div>

                <div class="auth-field">
                    <label>Email address</label>
                    <input type="email" name="email" placeholder="you@example.com" required>
                </div>

                <div class="auth-field">
                    <label>Password</label>
                    <div class="password-wrap">
                        <input type="password" name="password" id="passwordInput" placeholder="Create a strong password" required>
                        <button type="button" class="eye-btn" onclick="togglePassword('passwordInput', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <p class="auth-terms">
                    By creating an account, you agree to our
                    <a href="terms.php">Terms of Service</a> and <a href="privacy.php">Privacy Policy</a>.
                </p>

                <button type="submit" class="auth-submit-btn">Create Account</button>

            </form>

            <div class="auth-divider"><span>or</span></div>

            <p class="auth-switch">
                Already have an account? <a href="login.php">Sign in</a>
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

</body>
</html>