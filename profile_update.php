<?php
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: profile.php");
    exit();
}

$user_id  = (int)$_SESSION['user_id'];
$username = mysqli_real_escape_string($conn, trim($_POST['username']));
$email    = mysqli_real_escape_string($conn, trim($_POST['email']));

$new_password     = $_POST['new_password']     ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Check if email is taken by another user
$emailCheck = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE email='$email' AND id != $user_id"));
if($emailCheck){
    header("Location: edit_profile.php?error=email_taken");
    exit();
}

// Handle profile picture upload
$pic_sql = "";
if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0){
    $allowed  = ['jpg','jpeg','png','gif','webp'];
    $ext      = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
    if(in_array($ext, $allowed)){
        $filename    = 'profile_' . $user_id . '_' . time() . '.' . $ext;
        $destination = 'uploads/' . $filename;
        if(move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination)){
            $pic_sql = ", profile_pic='$destination'";
        }
    }
}

// Password handling
if(!empty($new_password)){
    if($new_password !== $confirm_password){
        header("Location: edit_profile.php?error=password_mismatch");
        exit();
    }
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    mysqli_query($conn, "UPDATE users SET username='$username', email='$email', password='$hashed' $pic_sql WHERE id=$user_id");
} else {
    mysqli_query($conn, "UPDATE users SET username='$username', email='$email' $pic_sql WHERE id=$user_id");
}

$_SESSION['username'] = $username;

header("Location: edit_profile.php?success=1");
exit();
?>