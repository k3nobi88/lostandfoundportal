<?php
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id  = (int)$_SESSION['user_id'];
$claim_id = isset($_GET['claim_id']) ? (int)$_GET['claim_id'] : 0;

if(!$claim_id){ header("Location: myclaims.php"); exit(); }

// Only the claimant can access this
$claim = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT claims.*, listings.title, listings.image, listings.location,
           listings.reward, listings.id AS listing_id,
           listings.user_id AS owner_id,
           u_owner.username AS owner_name
    FROM claims
    JOIN listings ON claims.listing_id = listings.id
    JOIN users u_owner ON listings.user_id = u_owner.id
    WHERE claims.id = $claim_id
    AND claims.claimant_id = $user_id
"));

if(!$claim){
    header("Location: myclaims.php");
    exit();
}

// Must be collected status and have a reward
if($claim['status'] !== 'collected' || empty($claim['reward'])){
    header("Location: myclaims.php");
    exit();
}

// Already paid out
if($claim['reward_paid']){
    header("Location: myclaims.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Claim Reward – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container" style="max-width:580px; padding:50px 20px;">

    <div class="report-header">
        <h1>💰 Claim Your Reward</h1>
        <p>You're owed a reward for returning this item</p>
    </div>

    <!-- Item preview -->
    <div class="boost-preview-card">
        <div class="boost-preview-img">
            <?php if(!empty($claim['image'])): ?>
                <img src="<?php echo $claim['image']; ?>" alt="<?php echo htmlspecialchars($claim['title']); ?>">
            <?php else: ?>
                <img src="images/default.jpg" alt="No image">
            <?php endif; ?>
        </div>
        <div class="boost-preview-body">
            <h3><?php echo htmlspecialchars($claim['title']); ?></h3>
            <p>📍 <?php echo htmlspecialchars($claim['location']); ?></p>
            <p style="margin-top:6px;">Reward from: <strong><?php echo htmlspecialchars($claim['owner_name']); ?></strong></p>
        </div>
    </div>

    <!-- Amount -->
    <div class="report-section" style="margin-top:20px;">
        <p class="report-section-title">Reward Summary</p>
        <div class="boost-summary">
            <div class="boost-summary-row">
                <span>Item</span>
                <span><?php echo htmlspecialchars($claim['title']); ?></span>
            </div>
            <div class="boost-summary-row">
                <span>Offered by</span>
                <span><?php echo htmlspecialchars($claim['owner_name']); ?></span>
            </div>
            <div class="boost-summary-divider"></div>
            <div class="boost-summary-row boost-total">
                <span>Reward Amount</span>
                <span>RM<?php echo number_format($claim['reward'], 2); ?></span>
            </div>
        </div>
    </div>

    <!-- Choose method -->
    <div class="report-section" style="margin-top:20px;">
        <p class="report-section-title">How would you like to receive it?</p>

        <div class="reward-method-grid">

            <!-- Cash option -->
            <div class="reward-method-card" id="cashCard" onclick="selectMethod('cash')">
                <div class="reward-method-icon">💵</div>
                <div class="reward-method-label">Cash</div>
                <div class="reward-method-desc">Settle in person when you meet up. No payment window needed.</div>
                <div class="reward-method-check" id="cashCheck">✓</div>
            </div>

            <!-- Online option -->
            <div class="reward-method-card" id="onlineCard" onclick="selectMethod('online')">
                <div class="reward-method-icon">💳</div>
                <div class="reward-method-label">Online Transfer</div>
                <div class="reward-method-desc">Owner pays you via ToyyibPay. Payment is processed online.</div>
                <div class="reward-method-check" id="onlineCheck" style="display:none;">✓</div>
            </div>

        </div>

        <div id="cashConfirmBox" style="margin-top:20px;">
            <p style="font-size:13px; color:#888; margin-bottom:14px;">
                ℹ️ Selecting cash means you and the owner will settle the reward amount directly when you meet.
                This will be recorded as a cash agreement.
            </p>
            <form method="POST" action="reward_claim_process.php">
                <input type="hidden" name="claim_id"   value="<?php echo $claim_id; ?>">
                <input type="hidden" name="method"     value="cash">
                <button type="submit" class="auth-submit-btn"
                    onclick="return confirm('Confirm cash reward arrangement with <?php echo addslashes($claim["owner_name"]); ?>?')">
                    ✅ Confirm Cash Arrangement
                </button>
            </form>
        </div>

        <div id="onlinePayBox" style="display:none; margin-top:20px;">
            <p style="font-size:13px; color:#888; margin-bottom:14px;">
                ℹ️ This will send a reward payment request. The owner will be notified and can pay via ToyyibPay.
            </p>
            <form method="POST" action="reward_claim_process.php">
                <input type="hidden" name="claim_id"   value="<?php echo $claim_id; ?>">
                <input type="hidden" name="method"     value="online">
                <button type="submit" class="auth-submit-btn boost-pay-btn">
                    💳 Request Online Transfer (RM<?php echo number_format($claim['reward'], 2); ?>)
                </button>
            </form>
            <p class="boost-pay-note">🔒 Payment processed by ToyyibPay</p>
        </div>

    </div>

    <a href="myclaims.php" class="item-back-link" style="display:block; margin-top:20px;">← Back to My Claims</a>

</div>

<?php include 'footer.php'; ?>

<script>
function selectMethod(method) {
    // Toggle card selected state
    document.getElementById('cashCard').classList.toggle('selected',   method === 'cash');
    document.getElementById('onlineCard').classList.toggle('selected', method === 'online');
    document.getElementById('cashCheck').style.display   = method === 'cash'   ? 'flex' : 'none';
    document.getElementById('onlineCheck').style.display = method === 'online' ? 'flex' : 'none';

    // Toggle action boxes
    document.getElementById('cashConfirmBox').style.display = method === 'cash'   ? 'block' : 'none';
    document.getElementById('onlinePayBox').style.display   = method === 'online' ? 'block' : 'none';
}
</script>

</body>
</html>