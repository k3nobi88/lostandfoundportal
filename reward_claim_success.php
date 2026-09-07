<?php
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$claim_id  = isset($_GET['claim_id']) ? (int)$_GET['claim_id'] : 0;
$reference = $_GET['ref']    ?? 'N/A';
$amount    = (float)($_GET['amount'] ?? 0);
$method    = $_GET['method'] === 'online' ? 'online' : 'cash';

$claim = $claim_id ? mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT claims.*, listings.title, listings.user_id AS owner_id,
           u_owner.username AS owner_name
    FROM claims
    JOIN listings ON claims.listing_id = listings.id
    JOIN users u_owner ON listings.user_id = u_owner.id
    WHERE claims.id = $claim_id
")) : null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reward Claimed – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="report-success" style="padding:80px 20px;">

    <div class="report-success-icon"><?php echo $method === 'online' ? '💳' : '💵'; ?></div>

    <?php if($method === 'cash'): ?>
        <h2>Cash Arrangement Confirmed!</h2>
        <p>Your cash reward arrangement with <strong><?php echo $claim ? htmlspecialchars($claim['owner_name']) : 'the owner'; ?></strong> has been recorded. Collect your reward when you meet up.</p>
    <?php else: ?>
        <h2>Online Transfer Requested!</h2>
        <p>Your reward request has been sent to <strong><?php echo $claim ? htmlspecialchars($claim['owner_name']) : 'the owner'; ?></strong>. They will be notified to complete the payment via ToyyibPay.</p>
    <?php endif; ?>

    <div class="boost-receipt">
        <div class="boost-receipt-row">
            <span>Reference</span>
            <span><?php echo htmlspecialchars($reference); ?></span>
        </div>
        <?php if($claim): ?>
        <div class="boost-receipt-row">
            <span>Item</span>
            <span><?php echo htmlspecialchars($claim['title']); ?></span>
        </div>
        <div class="boost-receipt-row">
            <span>From</span>
            <span><?php echo htmlspecialchars($claim['owner_name']); ?></span>
        </div>
        <?php endif; ?>
        <div class="boost-receipt-row">
            <span>Reward Amount</span>
            <span>RM<?php echo number_format($amount, 2); ?></span>
        </div>
        <div class="boost-receipt-row">
            <span>Method</span>
            <span><?php echo $method === 'online' ? '💳 Online Transfer' : '💵 Cash'; ?></span>
        </div>
        <div class="boost-receipt-row">
            <span>Status</span>
            <span style="color:#22c55e; font-weight:700;">
                <?php echo $method === 'online' ? '✅ Payment Requested' : '✅ Arrangement Recorded'; ?>
            </span>
        </div>
    </div>

    <div class="report-success-btns">
        <a href="myclaims.php" class="btn">Back to My Claims</a>
        <a href="browse.php" class="btn btn-outline">Browse Listings</a>
    </div>

</div>

<?php include 'footer.php'; ?>
</body>
</html>