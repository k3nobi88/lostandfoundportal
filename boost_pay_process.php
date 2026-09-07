<?php
include 'config.php';
include 'toyyibpay_helper.php';

if (!isset($_SESSION['user_id']))        { header("Location: login.php");  exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST'){ header("Location: browse.php"); exit(); }

$user_id    = (int)$_SESSION['user_id'];
$listing_id = (int)$_POST['listing_id'];
$amount     = (float)$_POST['amount'];
$days       = (int)$_POST['days'];
$plan_label = $_POST['plan_label'] ?? '1 Month';

// Verify ownership
$listing = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM listings WHERE id=$listing_id AND user_id=$user_id"
));
if (!$listing) { header("Location: browse.php"); exit(); }

// Get payer info
$payer = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT username, email FROM users WHERE id=$user_id"
));

$reference = 'BOOST-' . strtoupper(uniqid());

// Insert as PENDING — callback will mark it paid and activate boost
mysqli_query($conn, "
    INSERT INTO payments (user_id, listing_id, type, amount, method, status, reference, boost_days)
    VALUES ($user_id, $listing_id, 'boost', $amount, 'online', 'pending', '$reference', $days)
");

$returnUrl   = SITE_URL . '/boost_success.php?ref=' . $reference
             . '&id=' . $listing_id
             . '&days=' . $days
             . '&amount=' . $amount
             . '&plan=' . urlencode($plan_label);
$callbackUrl = SITE_URL . '/toyyibpay_callback.php';

$result = toyyibpayCreateBill(
    'Listing Boost',
    'Boost ' . $listing['title'],
    $amount,
    $reference,
    $returnUrl,
    $callbackUrl,
    $payer['username'] ?? 'Customer',
    !empty($payer['email']) ? $payer['email'] : 'customer@lostandfound.com',
    '0123456789'
);

if ($result['success']) {
    // Redirect to ToyyibPay payment page
    header("Location: " . $result['payment_url']);
    exit();
} else {
    // Failed to create bill — clean up and go back
    mysqli_query($conn, "DELETE FROM payments WHERE reference='$reference'");
    header("Location: boost_pay.php?id=$listing_id&error=1");
    exit();
}