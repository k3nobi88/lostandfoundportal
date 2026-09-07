<?php
include 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0, 'items' => []]);
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Fetch unread notifications
$stmt = $conn->prepare("
    SELECT id, type, message, link, created_at
    FROM notifications
    WHERE user_id = ? AND is_read = 0
    ORDER BY created_at DESC
    LIMIT 10
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode([
    'count' => count($rows),
    'items' => $rows
]);