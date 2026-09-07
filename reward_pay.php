<?php
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id      = (int)$_SESSION['user_id'];
$claim_id     = isset($_GET['claim_id']) ? (int)$_GET['claim_id'] : 0;
$paymentError = isset($_GET['error']) ? 'Payment gateway error. Please try again.' : '';

if(!$claim_id){ header("Location: incoming_claims.php"); exit(); }

$claim = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT claims.*, listings.title, listings.image, listings.location,
           listings.reward, listings.id as listing_id,
           listings.user_id as owner_id,
           users.username as claimant_name
    FROM claims
    JOIN listings ON claims.listing_id = listings.id
    JOIN users    ON claims.claimant_id = users.id
    WHERE claims.id = $claim_id
"));

if(!$claim || $claim['owner_id'] != $user_id){
    header("Location: incoming_claims.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pay Reward – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container" style="max-width:580px; padding:50px 20px;">

    <div class="report-header">
        <h1>💰 Pay Reward</h1>
        <p>Send the reward to <?php echo htmlspecialchars($claim['claimant_name']); ?> via ToyyibPay</p>
    </div>

    <!-- Error message (shown when redirected back after a failed bill creation) -->
    <?php if($paymentError): ?>
    <div class="action-error" style="margin-bottom:20px; padding:14px 16px;">
        ⚠️ <?php echo $paymentError; ?>
    </div>
    <?php endif; ?>

    <!-- Item preview -->
    <div class="boost-preview-card">
        <div class="boost-preview-img">
            <?php if(!empty($claim['image'])): ?>
                <img src="<?php echo $claim['image']; ?>">
            <?php else: ?>
                <img src="images/default.jpg">
            <?php endif; ?>
        </div>
        <div class="boost-preview-body">
            <h3><?php echo htmlspecialchars($claim['title']); ?></h3>
            <p>📍 <?php echo htmlspecialchars($claim['location']); ?></p>
            <p style="margin-top:6px;">Claimant: <strong><?php echo htmlspecialchars($claim['claimant_name']); ?></strong></p>
        </div>
    </div>

    <!-- Payment summary -->
    <div class="report-section" style="margin-top:20px;">
        <p class="report-section-title">Payment Summary</p>
        <div class="boost-summary">
            <div class="boost-summary-row">
                <span>Recipient</span>
                <span><?php echo htmlspecialchars($claim['claimant_name']); ?></span>
            </div>
            <div class="boost-summary-row">
                <span>Item</span>
                <span><?php echo htmlspecialchars($claim['title']); ?></span>
            </div>
            <?php
            $grossReward   = (float)$claim['reward'];
            $serviceFee    = round($grossReward * 0.05, 2);
            $claimantGets  = round($grossReward - $serviceFee, 2);
            ?>
            <div class="boost-summary-divider"></div>
            <div class="boost-summary-row">
                <span>Reward Offered</span>
                <span>RM<?php echo number_format($grossReward, 2); ?></span>
            </div>
            <div class="boost-summary-row">
                <span style="color:#f59e0b;">Platform Fee (5%)</span>
                <span style="color:#f59e0b;">- RM<?php echo number_format($serviceFee, 2); ?></span>
            </div>
            <div class="boost-summary-row boost-total">
                <span>Claimant Receives</span>
                <span style="color:#22c55e;">RM<?php echo number_format($claimantGets, 2); ?></span>
            </div>
        </div>
    </div>

    <!-- Pay button -->
    <div class="boost-pay-btns">
        <form method="POST" action="reward_pay_process.php">
            <input type="hidden" name="claim_id"   value="<?php echo $claim_id; ?>">
            <input type="hidden" name="amount"     value="<?php echo $claim['reward']; ?>">
            <input type="hidden" name="listing_id" value="<?php echo $claim['listing_id']; ?>">
            <button type="submit" class="auth-submit-btn boost-pay-btn">
                💳 Pay RM<?php echo number_format($claim['reward'], 2); ?> via ToyyibPay
            </button>
        </form>
        <p class="boost-pay-note">🔒 Secure payment powered by ToyyibPay</p>
    </div>

    <a href="incoming_claims.php" class="item-back-link" style="display:block; margin-top:20px;">← Back to Claims</a>

</div>

<?php include 'footer.php'; ?>

</body>
</html>