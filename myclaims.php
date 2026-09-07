<?php
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT claims.*, listings.title, listings.image, listings.location,
           listings.reward
    FROM claims
    JOIN listings ON claims.listing_id = listings.id
    WHERE claims.claimant_id = '$user_id'
    ORDER BY claims.created_at DESC
";

$result = mysqli_query($conn, $sql);
$claims = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Claims – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="claims-hero">
    <h1>My Claims</h1>
    <p>Track the status of items you have claimed</p>
</div>

<div class="container">

    <?php if(count($claims) == 0): ?>

        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <h3>No claims yet</h3>
            <p>You haven't submitted any claims. Browse listings to find your lost item.</p>
            <a href="browse.php" class="btn">Browse Listings</a>
        </div>

    <?php else: ?>

        <div class="claims-grid">

        <?php foreach($claims as $row): ?>

            <div class="claim-card">

                <div class="claim-card-img">
                    <?php if(!empty($row['image'])): ?>
                        <img src="<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                    <?php else: ?>
                        <img src="images/default.jpg" alt="No image">
                    <?php endif; ?>
                </div>

                <div class="claim-card-body">

                    <div class="claim-card-top">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <?php
                            $status = $row['status'];
                            if($status == 'pending')   echo '<span class="claim-status status-pending">🟡 Pending</span>';
                            if($status == 'approved')  echo '<span class="claim-status status-approved">🟢 Approved</span>';
                            if($status == 'rejected')  echo '<span class="claim-status status-rejected">🔴 Rejected</span>';
                            if($status == 'collected') echo '<span class="claim-status status-collected">✅ Collected</span>';
                        ?>
                    </div>

                    <p class="claim-location">📍 <?php echo htmlspecialchars($row['location']); ?></p>
                    <p class="claim-date">🕐 Submitted <?php echo date('d M Y', strtotime($row['created_at'])); ?></p>

                    <?php if($status == 'approved'): ?>
                        <p class="claim-hint">Your claim was approved. Open chat to arrange collection.</p>
                    <?php elseif($status == 'rejected'): ?>
                        <p class="claim-hint">Your claim was not approved by the owner.</p>
                    <?php elseif($status == 'collected'): ?>
                        <?php if(!empty($row['reward']) && !$row['reward_paid']): ?>
                            <p class="claim-hint" style="color:gold;">💰 You have an unclaimed reward of RM<?php echo htmlspecialchars($row['reward']); ?>!</p>
                        <?php elseif(!empty($row['reward']) && $row['reward_paid']): ?>
                            <p class="claim-hint">✅ Reward claimed via <?php echo ucfirst($row['reward_method'] ?? 'cash'); ?>.</p>
                        <?php else: ?>
                            <p class="claim-hint">Item successfully collected. Case closed.</p>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if($status == 'collected' && !empty($row['reward']) && !$row['reward_paid']): ?>
                        <a href="reward_claim.php?claim_id=<?php echo $row['id']; ?>" class="claim-btn" style="background:gold; color:#111; font-weight:700;">
                            💰 Claim Reward
                        </a>
                    <?php else: ?>
                        <a href="claim_detail.php?id=<?php echo $row['id']; ?>" class="claim-btn">
                            <?php echo ($status == 'approved') ? '💬 Open Chat' : 'View Details'; ?>
                        </a>
                    <?php endif; ?>

                </div>

            </div>

        <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

<?php include 'footer.php'; ?>

</body>
</html>