<?php
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Rewards the user is OFFERING
$offering = mysqli_query($conn, "
    SELECT listings.*,
           COUNT(claims.id) as claim_count,
           SUM(CASE WHEN claims.status='approved' THEN 1 ELSE 0 END) as approved_count
    FROM listings
    LEFT JOIN claims ON claims.listing_id = listings.id
    WHERE listings.user_id = $user_id
    AND listings.listing_type = 'lost'
    AND listings.reward IS NOT NULL AND listings.reward != ''
    GROUP BY listings.id
    ORDER BY listings.created_at DESC
");

// Rewards the user may RECEIVE — include reward_paid and reward_method
$receiving = mysqli_query($conn, "
    SELECT listings.title, listings.reward, listings.location, listings.image,
           claims.status, claims.id as claim_id, claims.reward_paid,
           claims.reward_method, listings.id as listing_id
    FROM claims
    JOIN listings ON claims.listing_id = listings.id
    WHERE claims.claimant_id = $user_id
    AND listings.reward IS NOT NULL AND listings.reward != ''
    ORDER BY claims.created_at DESC
");

// Earnings stats
$totalEarned = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(listings.reward), 0) AS total
    FROM claims
    JOIN listings ON claims.listing_id = listings.id
    WHERE claims.claimant_id = $user_id
    AND claims.reward_paid = 1
"))['total'];

$pendingEarnings = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(listings.reward), 0) AS total
    FROM claims
    JOIN listings ON claims.listing_id = listings.id
    WHERE claims.claimant_id = $user_id
    AND claims.status IN ('approved','collected')
    AND claims.reward_paid = 0
"))['total'];

$offeringCount = mysqli_num_rows($offering);
$receivingCount = mysqli_num_rows($receiving);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rewards – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="claims-hero">
    <h1>🏆 Rewards</h1>
    <p>Track rewards you're offering and rewards you may receive</p>
</div>

<div class="container">

    <!-- EARNINGS STATS BAR -->
    <div class="rewards-stats-bar">
        <div class="rewards-stat">
            <div class="rewards-stat-icon">💰</div>
            <div>
                <div class="rewards-stat-num">RM<?php echo number_format((float)$totalEarned, 2); ?></div>
                <div class="rewards-stat-label">Total Earned</div>
            </div>
        </div>
        <div class="rewards-stat-divider"></div>
        <div class="rewards-stat">
            <div class="rewards-stat-icon">⏳</div>
            <div>
                <div class="rewards-stat-num" style="color:gold;">RM<?php echo number_format((float)$pendingEarnings, 2); ?></div>
                <div class="rewards-stat-label">Pending / Unclaimed</div>
            </div>
        </div>
        <div class="rewards-stat-divider"></div>
        <div class="rewards-stat">
            <div class="rewards-stat-icon">📋</div>
            <div>
                <div class="rewards-stat-num"><?php echo $receivingCount; ?></div>
                <div class="rewards-stat-label">Reward Claims</div>
            </div>
        </div>
    </div>

    <!-- REWARDS YOU ARE OFFERING -->
    <div class="rewards-section">
        <div class="rewards-section-header">
            <h2>Rewards You're Offering</h2>
            <p>Your lost item reports with a reward for the finder</p>
        </div>

        <?php if($offeringCount == 0): ?>
            <div class="empty-state" style="padding:40px 20px;">
                <div class="empty-icon">💰</div>
                <h3>No rewards offered yet</h3>
                <p>When you report a lost item with a reward amount, it will appear here.</p>
                <a href="lostitem.php" class="btn">Report Lost Item</a>
            </div>
        <?php else: ?>
            <div class="claims-grid">
            <?php while($row = mysqli_fetch_assoc($offering)): ?>
                <div class="claim-card">
                    <div class="claim-card-img">
                        <img src="<?php echo !empty($row['image']) ? $row['image'] : 'images/default.jpg'; ?>">
                    </div>
                    <div class="claim-card-body">
                        <div class="claim-card-top">
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <span class="reward-amount-badge">💰 RM<?php echo htmlspecialchars($row['reward']); ?></span>
                        </div>
                        <p class="claim-location">📍 <?php echo htmlspecialchars($row['location']); ?></p>
                        <p class="claim-date">🕐 <?php echo date('d M Y', strtotime($row['created_at'])); ?></p>
                        <p class="claim-date">📋 <?php echo $row['claim_count']; ?> claim(s) — <?php echo $row['approved_count']; ?> approved</p>
                        <?php if($row['approved_count'] > 0): ?>
                            <div class="action-success" style="margin-top:8px; padding:8px 12px; font-size:13px;">
                                ✅ Claim approved — arrange reward via chat or incoming claims
                            </div>
                        <?php endif; ?>
                        <div class="claim-actions" style="margin-top:12px;">
                            <a href="item.php?id=<?php echo $row['id']; ?>" class="claim-btn">View Listing</a>
                            <a href="incoming_claims.php" class="claim-btn">View Claims</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="rewards-divider"></div>

    <!-- REWARDS YOU MAY RECEIVE -->
    <div class="rewards-section">
        <div class="rewards-section-header">
            <h2>Rewards You May Receive</h2>
            <p>Listings you've claimed that have a reward attached</p>
        </div>

        <?php if($receivingCount == 0): ?>
            <div class="empty-state" style="padding:40px 20px;">
                <div class="empty-icon">🎁</div>
                <h3>No pending rewards</h3>
                <p>When you claim a listing that has a reward, it will appear here once approved.</p>
                <a href="browse.php" class="btn">Browse Listings</a>
            </div>
        <?php else: ?>
            <div class="claims-grid">
            <?php while($row = mysqli_fetch_assoc($receiving)): ?>

                <?php
                $s           = $row['status'];
                $reward_paid = $row['reward_paid'];
                $method      = $row['reward_method'];
                ?>

                <div class="claim-card">
                    <div class="claim-card-img">
                        <img src="<?php echo !empty($row['image']) ? $row['image'] : 'images/default.jpg'; ?>">
                    </div>
                    <div class="claim-card-body">
                        <div class="claim-card-top">
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <span class="reward-amount-badge">💰 RM<?php echo htmlspecialchars($row['reward']); ?></span>
                        </div>
                        <p class="claim-location">📍 <?php echo htmlspecialchars($row['location']); ?></p>

                        <?php
                        if($s == 'pending')   echo '<span class="claim-status status-pending"  style="margin-top:8px;display:inline-block;">🟡 Claim Pending</span>';
                        if($s == 'approved')  echo '<span class="claim-status status-approved" style="margin-top:8px;display:inline-block;">🟢 Approved — Reward Due</span>';
                        if($s == 'rejected')  echo '<span class="claim-status status-rejected" style="margin-top:8px;display:inline-block;">🔴 Claim Rejected</span>';
                        if($s == 'collected') echo '<span class="claim-status status-collected" style="margin-top:8px;display:inline-block;">✅ Collected</span>';
                        ?>

                        <?php if($reward_paid): ?>
                            <!-- Already paid out -->
                            <div class="action-success" style="margin-top:8px; padding:8px 12px; font-size:13px;">
                                ✅ Reward received via <?php echo ucfirst($method ?? 'cash'); ?>
                                (RM<?php echo number_format((float)$row['reward'], 2); ?>)
                            </div>

                        <?php elseif($s === 'collected'): ?>
                            <!-- Collected but reward not yet claimed -->
                            <div style="background:rgba(255,200,0,0.07); border:1px solid rgba(255,200,0,0.25); border-radius:10px; padding:10px 12px; margin-top:10px; font-size:13px; color:gold;">
                                💰 You have an unclaimed reward of <strong>RM<?php echo htmlspecialchars($row['reward']); ?></strong>
                            </div>

                        <?php elseif($s === 'approved'): ?>
                            <div class="action-success" style="margin-top:8px; padding:8px 12px; font-size:13px;">
                                🎉 Claim approved! Arrange collection via chat, then claim your reward below.
                            </div>
                        <?php endif; ?>

                        <div class="claim-actions" style="margin-top:12px;">

                            <?php if(($s === 'collected' || $s === 'approved') && !$reward_paid): ?>
                                <a href="reward_claim.php?claim_id=<?php echo $row['claim_id']; ?>"
                                   class="claim-btn"
                                   style="background:gold; color:#111; font-weight:700; text-align:center;">
                                    💰 Claim Reward
                                </a>
                            <?php endif; ?>

                            <a href="claim_detail.php?id=<?php echo $row['claim_id']; ?>" class="claim-btn">💬 Chat</a>
                            <a href="item.php?id=<?php echo $row['listing_id']; ?>"       class="claim-btn">View Listing</a>

                        </div>
                    </div>
                </div>

            <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include 'footer.php'; ?>
</body>
</html>