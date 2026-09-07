<?php
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$user    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));
$success = isset($_GET['success']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container" style="max-width:600px; padding:50px 20px;">

    <div class="report-header">
        <h1>Edit Profile</h1>
        <p>Update your account information</p>
    </div>

    <?php if($success): ?>
        <div class="action-success" style="margin-bottom:24px;">✅ Profile updated successfully.</div>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <?php if($_GET['error'] == 'password_mismatch'): ?>
            <div class="action-error" style="margin-bottom:24px;">⚠️ Passwords don't match. Please try again.</div>
        <?php elseif($_GET['error'] == 'email_taken'): ?>
            <div class="action-error" style="margin-bottom:24px;">⚠️ That email is already used by another account.</div>
        <?php endif; ?>
    <?php endif; ?>

    <form method="POST" action="profile_update.php" enctype="multipart/form-data">

        <!-- PROFILE PICTURE -->
        <div class="report-section">
            <p class="report-section-title">Profile Picture</p>

            <div class="profile-pic-edit-wrap">

                <div class="profile-pic-preview" id="picPreview">
                    <?php if(!empty($user['profile_pic'])): ?>
                        <img src="<?php echo $user['profile_pic']; ?>" id="picImg">
                    <?php else: ?>
                        <div class="profile-pic-placeholder" id="picPlaceholder">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                        <img src="" id="picImg" style="display:none;">
                    <?php endif; ?>
                </div>

                <div>
                    <label class="file-upload-btn" for="profilePicInput">📷 Choose Photo</label>
                    <input type="file" name="profile_pic" id="profilePicInput" accept="image/*" onchange="previewPic(this)">
                    <p style="font-size:12px; color:#555; margin-top:8px;">JPG, PNG or WEBP. Recommended: square image.</p>
                </div>

            </div>
        </div>

        <!-- ACCOUNT INFO -->
        <div class="report-section">
            <p class="report-section-title">Account Information</p>

            <div class="report-field">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>

            <div class="report-field">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

        </div>

        <!-- CHANGE PASSWORD -->
        <div class="report-section">
            <p class="report-section-title">Change Password <span style="color:#555; font-weight:400; text-transform:none; letter-spacing:0;">(leave blank to keep current)</span></p>

            <div class="report-field">
                <label>New Password</label>
                <div class="password-wrap">
                    <input type="password" name="new_password" id="newPass" placeholder="Enter new password">
                    <button type="button" class="eye-btn" onclick="togglePass('newPass', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <div class="report-field">
                <label>Confirm New Password</label>
                <div class="password-wrap">
                    <input type="password" name="confirm_password" id="confirmPass" placeholder="Confirm new password">
                    <button type="button" class="eye-btn" onclick="togglePass('confirmPass', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

        </div>

        <div style="display:flex; gap:12px;">
            <button type="submit" class="auth-submit-btn">Save Changes</button>
            <a href="profile.php" class="btn btn-outline" style="display:flex; align-items:center; padding:14px 24px;">Cancel</a>
        </div>

    </form>

</div>

<?php include 'footer.php'; ?>

<script>
function togglePass(id, btn) {
    const input   = document.getElementById(id);
    input.type    = input.type === 'password' ? 'text' : 'password';
    btn.style.opacity = input.type === 'text' ? '1' : '0.4';
}

function previewPic(input) {
    const file = input.files[0];
    if(file){
        const reader  = new FileReader();
        reader.onload = function(e) {
            const img         = document.getElementById('picImg');
            const placeholder = document.getElementById('picPlaceholder');
            img.src           = e.target.result;
            img.style.display = 'block';
            if(placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
}
</script>

</body>
</html>