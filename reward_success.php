<?php
include 'config.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

// ToyyibPay return parameters
$tpStatus  = isset($_GET['status_id']) ? (int)$_GET['status_id'] : 1;
$claim_id  = isset($_GET['claim_id']) ? (int)$_GET['claim_id'] : 0;
$reference = $_GET['ref'] ?? 'N/A';
$amount    = $_GET['amount'] ?? '0';

// 1. If payment failed (status_id = 3), redirect back with an error
// (Adjust 'pay_reward.php' to match your actual reward payment page filename if different)
if ($tpStatus == 3) {
    header("Location: incoming_claims.php?claim_id=" . $claim_id . "&error=1");
    exit();
}

// 2. Backup Logic: If status=1 and reward is still marked as unpaid in DB 
// (Fallback in case the primary webhook/callback hasn't arrived yet)
if ($tpStatus == 1 && $claim_id > 0) {
    $claim_check = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT reward_paid FROM claims WHERE id = $claim_id LIMIT 1"
    ));
    
    // If the claim exists and reward_paid is still 0, update it to 1
    if ($claim_check && (int)$claim_check['reward_paid'] === 0) {
        mysqli_query($conn, "UPDATE claims SET reward_paid = 1 WHERE id = $claim_id");
    }
}

// Fetch claim details for receipt display
$claim = $claim_id ? mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT claims.*, listings.title, users.username as claimant_name
    FROM claims
    JOIN listings ON claims.listing_id = listings.id
    JOIN users    ON claims.claimant_id = users.id
    WHERE claims.id = $claim_id
")) : null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reward Sent – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="report-success" style="padding:80px 20px;">

    <div class="report-success-icon">💰</div>
    <h2>Reward Sent!</h2>
    <p>Your payment was successful. The reward has been sent to <strong><?php echo $claim ? htmlspecialchars($claim['claimant_name']) : 'the claimant'; ?></strong>. Thank you for being honest!</p>

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
            <span>Recipient</span>
            <span><?php echo htmlspecialchars($claim['claimant_name']); ?></span>
        </div>
        <?php endif; ?>
        <?php
        $grossAmt     = (float)$amount;
        $feeAmt       = round($grossAmt * 0.05, 2);
        $claimantAmt  = round($grossAmt - $feeAmt, 2);
        ?>
        <div class="boost-receipt-row">
            <span>Amount Paid</span>
            <span>RM<?php echo number_format($grossAmt, 2); ?></span>
        </div>
        <div class="boost-receipt-row">
            <span style="color:#f59e0b;">Platform Fee (5%)</span>
            <span style="color:#f59e0b;">RM<?php echo number_format($feeAmt, 2); ?></span>
        </div>
        <div class="boost-receipt-row">
            <span>Claimant Receives</span>
            <span style="color:#22c55e;">RM<?php echo number_format($claimantAmt, 2); ?></span>
        </div>
        <div class="boost-receipt-row">
            <span>Method</span>
            <span>ToyyibPay (Online)</span>
        </div>
        <div class="boost-receipt-row">
            <span>Status</span>
            <span style="color:#22c55e; font-weight:700;">✅ Paid</span>
        </div>
    </div>

    <div class="report-success-btns">
        <a href="incoming_claims.php" class="btn">Back to Claims</a>
        <a href="browse.php" class="btn btn-outline">Browse Listings</a>
        <button onclick="window.print()" class="btn btn-outline">🖨️ Save as PDF</button>
    </div>

</div>

<?php include 'footer.php'; ?>

</body>
</html>