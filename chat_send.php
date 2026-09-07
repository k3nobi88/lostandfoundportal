<?php
/*
 * chat_send.php
 * Accepts a POST with { claim_id, message }.
 * Returns JSON { success, message_id }.
 * Replaces the old form POST in claim_detail.php.
 */
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST required']);
    exit();
}

$user_id  = (int)$_SESSION['user_id'];
$claim_id = isset($_POST['claim_id']) ? (int)$_POST['claim_id'] : 0;
$message  = trim($_POST['message'] ?? '');

if (!$claim_id || $message === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing fields']);
    exit();
}

// Verify the user is part of this claim
$stmt = $conn->prepare("
    SELECT claims.claimant_id, listings.user_id AS owner_id, claims.status
    FROM claims
    JOIN listings ON claims.listing_id = listings.id
    WHERE claims.id = ?
");
$stmt->bind_param('i', $claim_id);
$stmt->execute();
$claim = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$claim) {
    http_response_code(404);
    echo json_encode(['error' => 'Claim not found']);
    exit();
}

// Only owner or claimant can send
if ($claim['claimant_id'] != $user_id && $claim['owner_id'] != $user_id) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

// Chat must be open
if (!in_array($claim['status'], ['pending', 'approved'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Chat is closed']);
    exit();
}

// Insert the message using a prepared statement
$stmt = $conn->prepare("
    INSERT INTO messages (claim_id, sender_id, message)
    VALUES (?, ?, ?)
");
$stmt->bind_param('iis', $claim_id, $user_id, $message);
$stmt->execute();
$new_id = $conn->insert_id;
$stmt->close();

echo json_encode(['success' => true, 'message_id' => $new_id]);