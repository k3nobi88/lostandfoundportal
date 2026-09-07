<?php
include 'config.php';

/*
 * ToyyibPay POSTs these fields here after payment:
 * refno            - ToyyibPay payment reference
 * status           - 1=success, 2=pending, 3=fail
 * reason           - status description
 * billcode         - the bill code
 * order_id         - your billExternalReferenceNo (our reference like BOOST-xxx)
 * amount           - amount paid in sen
 * transaction_time - datetime
 * hash             - MD5 for verification (MUST validate this)
 */

$status    = $_POST['status']   ?? '';
$reference = $_POST['order_id'] ?? '';
$refno     = $_POST['refno']    ?? '';
$hash      = $_POST['hash']     ?? '';

// 1. Validate hash before doing anything
$expectedHash = md5(TOYYIBPAY_SECRET_KEY . $status . $reference . $refno . 'ok');
if ($hash !== $expectedHash) {
    http_response_code(403);
    exit('Invalid hash');
}

// 2. Only process successful payments
if ($status != '1' || empty($reference)) {
    http_response_code(200);
    exit('OK');
}

// 3. Route by reference prefix
if (strpos($reference, 'BOOST-') === 0) {

    $stmt = $conn->prepare("SELECT * FROM payments WHERE reference=? AND status='pending' LIMIT 1");
    $stmt->bind_param('s', $reference);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($payment) {
        // Mark payment as paid
        $upd = $conn->prepare("UPDATE payments SET status='paid', tp_reference=? WHERE reference=?");
        $upd->bind_param('ss', $refno, $reference);
        $upd->execute();
        $upd->close();

        // Activate listing boost
        $days    = (int)($payment['boost_days'] ?? 30);
        $expires = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        $boost   = $conn->prepare("UPDATE listings SET is_boosted=1, boost_requested=0, boost_expires_at=? WHERE id=?");
        $boost->bind_param('si', $expires, $payment['listing_id']);
        $boost->execute();
        $boost->close();

        // Notify the user
        $msg  = "⭐ Your listing boost is now active for {$days} days!";
        $link = "item.php?id=" . $payment['listing_id'];
        $notif = $conn->prepare("INSERT INTO notifications (user_id, type, message, link) VALUES (?,?,?,?)");
        $notif->bind_param('isss', $payment['user_id'], 'boost', $msg, $link);
        $notif->execute();
        $notif->close();
    }

} elseif (strpos($reference, 'RWD-') === 0) {

    $stmt = $conn->prepare("SELECT * FROM payments WHERE reference=? AND status='pending' LIMIT 1");
    $stmt->bind_param('s', $reference);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($payment) {
        // Mark payment as paid
        $upd = $conn->prepare("UPDATE payments SET status='paid', tp_reference=? WHERE reference=?");
        $upd->bind_param('ss', $refno, $reference);
        $upd->execute();
        $upd->close();

        // Mark claim reward as paid
        $claimUpd = $conn->prepare("UPDATE claims SET reward_paid=1, reward_method='online' WHERE id=?");
        $claimUpd->bind_param('i', $payment['claim_id']);
        $claimUpd->execute();
        $claimUpd->close();

        // Notify the claimant
        $claimInfo = $conn->prepare("
            SELECT claims.claimant_id, listings.title
            FROM claims
            JOIN listings ON claims.listing_id = listings.id
            WHERE claims.id = ?
        ");
        $claimInfo->bind_param('i', $payment['claim_id']);
        $claimInfo->execute();
        $ci = $claimInfo->get_result()->fetch_assoc();
        $claimInfo->close();

        if ($ci) {
            $msg   = "💰 Your reward for \"" . $ci['title'] . "\" has been paid online! Check your rewards page.";
            $link  = "rewards.php";
            $notif = $conn->prepare("INSERT INTO notifications (user_id, type, message, link) VALUES (?,?,?,?)");
            $notif->bind_param('isss', $ci['claimant_id'], 'reward', $msg, $link);
            $notif->execute();
            $notif->close();
        }
    }
}

http_response_code(200);
echo 'OK';
exit();