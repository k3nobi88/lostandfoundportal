<?php
include 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false]);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->close();

echo json_encode(['ok' => true]);