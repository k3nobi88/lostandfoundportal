<?php
/*
 * chat_poll.php
 * Called by JavaScript every 3 seconds.
 * Returns only messages NEWER than the last ID the client already has.
 * Returns JSON — no HTML output.
 */
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$user_id  = (int)$_SESSION['user_id'];
$claim_id = isset($_GET['claim_id']) ? (int)$_GET['claim_id'] : 0;
$after_id = isset($_GET['after'])    ? (int)$_GET['after']    : 0;

if (!$claim_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing claim_id']);
    exit();
}

// Access control — only the owner, claimant, or admin may poll this chat
$stmt = $conn->prepare("
    SELECT claims.claimant_id, listings.user_id AS owner_id
    FROM claims
    JOIN listings ON claims.listing_id = listings.id
    WHERE claims.id = ?
");
$stmt->bind_param('i', $claim_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    http_response_code(404);
    echo json_encode(['error' => 'Claim not found']);
    exit();
}

$roleRes  = $conn->prepare("SELECT role FROM users WHERE id = ?");
$roleRes->bind_param('i', $user_id);
$roleRes->execute();
$roleRow  = $roleRes->get_result()->fetch_assoc();
$roleRes->close();
$is_admin = ($roleRow && $roleRow['role'] === 'admin');

if ($row['claimant_id'] != $user_id && $row['owner_id'] != $user_id && !$is_admin) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

// Fetch only new messages (id > after_id)
$stmt = $conn->prepare("
    SELECT messages.id, messages.sender_id, messages.message, messages.created_at,
           users.username
    FROM messages
    JOIN users ON messages.sender_id = users.id
    WHERE messages.claim_id = ?
      AND messages.id > ?
    ORDER BY messages.created_at ASC
");
$stmt->bind_param('ii', $claim_id, $after_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$messages = [];
while ($msg = $result->fetch_assoc()) {
    $messages[] = [
        'id'         => (int)$msg['id'],
        'sender_id'  => (int)$msg['sender_id'],
        'username'   => $msg['username'],
        'message'    => $msg['message'],
        'created_at' => $msg['created_at'],
        'is_me'      => ($msg['sender_id'] == $user_id),
    ];
}

header('Content-Type: application/json');
echo json_encode(['messages' => $messages]);