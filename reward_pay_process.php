<?php
include 'config.php';
include 'toyyibpay_helper.php';

if (!isset($_SESSION['user_id']))        { header("Location: login.php");          exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST'){ header("Location: incoming_claims.php"); exit(); }

$user_id    = (int)$_SESSION['user_id'];
$claim_id   = (int)$_POST['claim_id'];
$amount     = (float)$_POST['amount'];
$listing_id = (int)$_POST['listing_id'];

// Verify the payer IS the listing owner
$claim = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT claims.id, claims.claimant_id, listings.title
    FROM claims
    JOIN listings ON claims.listing_id = listings.id
    WHERE claims.id = $claim_id
    AND listings.user_id = $user_id
    AND listings.id = $listing_id
"));
if (!$claim) { header("Location: incoming_claims.php"); exit(); }

// Get payer info
$payer = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT username, email FROM users WHERE id=$user_id"
));

$reference    = 'RWD-' . strtoupper(uniqid());
$serviceFee   = round($amount * 0.05, 2);
$claimantGets = round($amount - $serviceFee, 2);

// Insert as PENDING — amount = full amount owner pays, claimant_amount = what claimant receives
mysqli_query($conn, "
    INSERT INTO payments (user_id, listing_id, type, amount, method, status, reference, claim_id, claimant_amount)
    VALUES ($user_id, $listing_id, 'reward', $amount, 'online', 'pending', '$reference', $claim_id, $claimantGets)
");

$returnUrl   = SITE_URL . '/reward_success.php?claim_id=' . $claim_id
             . '&ref=' . $reference
             . '&amount=' . $amount;
$callbackUrl = SITE_URL . '/toyyibpay_callback.php';

$result = toyyibpayCreateBill(
    'Reward Payment',
    'Reward for ' . $claim['title'],
    $amount,
    $reference,
    $returnUrl,
    $callbackUrl,
    $payer['username'] ?? 'Customer',
    !empty($payer['email']) ? $payer['email'] : 'customer@lostandfound.com',
    '0123456789'
);

if ($result['success']) {
    header("Location: " . $result['payment_url']);
    exit();
} else {
    mysqli_query($conn, "DELETE FROM payments WHERE reference='$reference'");
    header("Location: reward_pay.php?claim_id=$claim_id&error=1");
    exit();
}