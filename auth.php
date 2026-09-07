<?php
require_once 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

// get current user role

$user_id = (int)$_SESSION['user_id'];

$result = mysqli_query(
    $conn,
    "SELECT role FROM users WHERE id=$user_id"
);
$user = mysqli_fetch_assoc($result);

$isAdmin = ($user['role'] === 'admin');
?>