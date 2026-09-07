<?php
include 'config.php';

// ToyyibPay return URL includes status_id: 1=success, 2=pending, 3=fail
$tpStatus = isset($_GET['status_id']) ? (int)$_GET['status_id'] : 1;
$reference = $_GET['ref'] ?? '';

// If payment pending/failed, show appropriate message
if ($tpStatus == 3) {
    header("Location: boost_pay.php?id=" . (int)$_GET['id'] . "&error=1");
    exit();
}

// If status=1 and payment still pending in DB (callback not yet received),
// activate it here as backup (callback is the primary method)
if ($tpStatus == 1 && $reference) {
    $pmt = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT * FROM payments WHERE reference='" . mysqli_real_escape_string($conn, $reference) . "' AND status='pending' LIMIT 1"
    ));
    if ($pmt) {
        $days    = (int)($pmt['boost_days'] ?? 30);
        $expires = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        mysqli_query($conn, "UPDATE payments SET status='paid' WHERE reference='" . mysqli_real_escape_string($conn, $reference) . "'");
        mysqli_query($conn, "UPDATE listings SET is_boosted=1, boost_requested=0, boost_expires_at='$expires' WHERE id=" . (int)$pmt['listing_id']);
    }
}

if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit(); }

$listing_id = isset($_GET['id'])     ? (int)$_GET['id']        : 0;
$reference  = isset($_GET['ref'])    ? $_GET['ref']             : 'N/A';
$days       = isset($_GET['days'])   ? (int)$_GET['days']       : 30;
$amount     = isset($_GET['amount']) ? (float)$_GET['amount']   : 0;
$plan       = isset($_GET['plan'])   ? $_GET['plan']            : '1 Month';

$listing = $listing_id ? mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM listings WHERE id=$listing_id")) : null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Successful – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="report-success" style="padding:80px 20px;">
    <div class="report-success-icon">🎉</div>
    <h2>Boost Activated!</h2>
    <p>Your listing is now sponsored and will appear at the top of browse results and on the homepage for <strong><?php echo $days; ?> days</strong>.</p>

    <div class="boost-receipt">
        <div class="boost-receipt-row"><span>Reference</span><span><?php echo htmlspecialchars($reference); ?></span></div>
        <div class="boost-receipt-row"><span>Listing</span><span><?php echo $listing ? htmlspecialchars($listing['title']) : '—'; ?></span></div>
        <div class="boost-receipt-row"><span>Plan</span><span><?php echo htmlspecialchars($plan); ?></span></div>
        <div class="boost-receipt-row"><span>Amount Paid</span><span>RM<?php echo number_format($amount,2); ?></span></div>
        <div class="boost-receipt-row"><span>Duration</span><span><?php echo $days; ?> Days</span></div>
        <div class="boost-receipt-row"><span>Expires</span><span><?php echo date('d M Y', strtotime("+{$days} days")); ?></span></div>
        <div class="boost-receipt-row"><span>Status</span><span style="color:#22c55e;font-weight:700;">✅ Paid</span></div>
    </div>

    <div class="report-success-btns">
        <?php if($listing): ?>
            <a href="item.php?id=<?php echo $listing_id; ?>" class="btn">View Listing</a>
        <?php endif; ?>
        <a href="browse.php" class="btn btn-outline">Browse Listings</a>
        <button onclick="window.print()" class="btn btn-outline">🖨️ Save as PDF</button>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>