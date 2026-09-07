<?php

include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle approve / reject / collected actions
if(isset($_POST['action']) && isset($_POST['claim_id'])){

    $claim_id = (int)$_POST['claim_id'];
    $action   = $_POST['action'];

    $ownerCheck = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT claims.id FROM claims
        JOIN listings ON claims.listing_id = listings.id
        WHERE claims.id = $claim_id AND listings.user_id = '$user_id'
    "));

    if($ownerCheck){
            if(in_array($action, ['approved', 'rejected', 'collected'])){
                $updStmt = $conn->prepare("UPDATE claims SET status=? WHERE id=?");
                $updStmt->bind_param('si', $action, $claim_id);
                $updStmt->execute();
                $updStmt->close();

                // When collected, mark the listing as resolved so it moves to archived in profile
                if($action === 'collected'){
                    $resStmt = $conn->prepare("
                        UPDATE listings
                        JOIN claims ON claims.listing_id = listings.id
                        SET listings.resolved_status = 'resolved'
                        WHERE claims.id = ?
                    ");
                    $resStmt->bind_param('i', $claim_id);
                    $resStmt->execute();
                    $resStmt->close();
                }

                // When collected, mark the listing as resolved
                if($action === 'collected'){
                    $resStmt = $conn->prepare("
                        UPDATE listings l
                        JOIN claims c ON c.listing_id = l.id
                        SET l.resolved_status = 'resolved'
                        WHERE c.id = ?
                    ");
                    $resStmt->bind_param('i', $claim_id);
                    $resStmt->execute();
                    $resStmt->close();
                }

                // Notify the claimant of status change
                $claimInfo = $conn->prepare("
                    SELECT claims.claimant_id, listings.title
                    FROM claims JOIN listings ON claims.listing_id=listings.id
                    WHERE claims.id=?
                ");
                $claimInfo->bind_param('i', $claim_id);
                $claimInfo->execute();
                $ci = $claimInfo->get_result()->fetch_assoc();
                $claimInfo->close();

                if($ci){
                    $statusWord = ucfirst($action);
                    $notifMsg   = "Your claim on \"{$ci['title']}\" has been $statusWord.";
                    $notifLink  = "myclaims.php";
                    $notifStmt  = $conn->prepare("INSERT INTO notifications (user_id, type, message, link) VALUES (?,?,?,?)");
                    $notifStmt->bind_param('isss', $ci['claimant_id'], $action, $notifMsg, $notifLink);
                    $notifStmt->execute();
                    $notifStmt->close();
                }


                // Email the claimant about the status change
                include_once 'mailer_helper.php';
                $claimantInfo = mysqli_fetch_assoc(mysqli_query($conn, "
                    SELECT users.email, users.username, listings.title
                    FROM claims
                    JOIN users ON claims.claimant_id = users.id
                    JOIN listings ON claims.listing_id = listings.id
                    WHERE claims.id = $claim_id
                "));
                if ($claimantInfo) {
                    if ($action === 'approved') {
                        $statusBody = '
                            <p style="color:#ccc;font-size:15px;line-height:1.7;">
                                Great news! Your claim on <strong style="color:white;">' . htmlspecialchars($claimantInfo['title']) . '</strong> has been <span style="color:#22c55e;font-weight:700;">approved</span>.
                            </p>
                            <p style="color:#ccc;font-size:15px;line-height:1.7;">You can now chat with the owner to arrange collection.</p>
                            <a href="' . SITE_URL . '/myclaims.php" style="display:inline-block;margin-top:20px;background:#22c55e;color:white;padding:12px 28px;border-radius:10px;text-decoration:none;font-weight:700;">View My Claims</a>
                        ';
                        sendMail($claimantInfo['email'], $claimantInfo['username'], 'Your claim was approved!', mailTemplate('Claim approved ✅', $statusBody));
                    } elseif ($action === 'rejected') {
                        $statusBody = '
                            <p style="color:#ccc;font-size:15px;line-height:1.7;">
                                Unfortunately, your claim on <strong style="color:white;">' . htmlspecialchars($claimantInfo['title']) . '</strong> has been <span style="color:#ff4444;font-weight:700;">rejected</span> by the owner.
                            </p>
                            <p style="color:#ccc;font-size:15px;line-height:1.7;">You can still browse other listings that might match what you\'re looking for.</p>
                            <a href="' . SITE_URL . '/browse.php" style="display:inline-block;margin-top:20px;background:#cc0000;color:white;padding:12px 28px;border-radius:10px;text-decoration:none;font-weight:700;">Browse Listings</a>
                        ';
                        sendMail($claimantInfo['email'], $claimantInfo['username'], 'Update on your claim', mailTemplate('Claim update 📋', $statusBody));
                    }
                }
            }

	if($action == 'cash_paid'){
		mysqli_query($conn, "UPDATE claims SET reward_paid=1, reward_method='cash' WHERE id='$claim_id'");

		// Record in payments table
		$claimData = mysqli_fetch_assoc(mysqli_query($conn, "
			SELECT claims.*, listings.reward, listings.id as listing_id
			FROM claims JOIN listings ON claims.listing_id=listings.id
			WHERE claims.id=$claim_id
		"));
		if($claimData && !empty($claimData['reward'])){
			$reward_amount = (float)$claimData['reward'];
			$listing_id    = (int)$claimData['listing_id'];
			mysqli_query($conn, "
				INSERT INTO payments (user_id, listing_id, type, amount, method, status, reference)
				VALUES ({$_SESSION['user_id']}, $listing_id, 'reward', $reward_amount, 'cash', 'paid', 'CASH-".strtoupper(uniqid())."')
			");
		}
	}

    }

    header("Location: incoming_claims.php");
    exit();
}

$sql = "
    SELECT
        claims.*,
        listings.title,
        listings.image,
        listings.location,
        listings.reward,
        users.username AS claimant_name
    FROM claims
    JOIN listings ON claims.listing_id = listings.id
    JOIN users    ON claims.claimant_id = users.id
    WHERE listings.user_id = '$user_id'
    ORDER BY claims.created_at DESC
";

$result = mysqli_query($conn, $sql);
$claims = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Incoming Claims – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="claims-hero">
    <h1>Incoming Claims</h1>
    <p>Review and manage claims submitted on your listings</p>
</div>

<div class="container">

    <?php if(count($claims) == 0): ?>

        <div class="empty-state">
            <div class="empty-icon">📥</div>
            <h3>No incoming claims</h3>
            <p>Nobody has submitted a claim on your listings yet.</p>
            <a href="browse.php" class="btn">View Your Listings</a>
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
                    <p class="claim-claimant">👤 Claimed by <strong><?php echo htmlspecialchars($row['claimant_name']); ?></strong></p>
                    <p class="claim-date">🕐 <?php echo date('d M Y', strtotime($row['created_at'])); ?></p>

                    <?php if(!empty($row['message'])): ?>
                        <p class="claim-message">"<?php echo htmlspecialchars($row['message']); ?>"</p>
                    <?php endif; ?>

                    <div class="claim-actions">

                        <a href="claim_detail.php?id=<?php echo $row['id']; ?>" class="claim-btn">
                            💬 Open Chat
                        </a>

                        <?php if($status == 'pending'): ?>

                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="claim_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action" value="approved">
                                <button type="submit" class="claim-action-btn btn-approve"
                                    onclick="return confirm('Approve this claim?')">
                                    ✅ Approve
                                </button>
                            </form>

                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="claim_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action" value="rejected">
                                <button type="submit" class="claim-action-btn btn-reject"
                                    onclick="return confirm('Reject this claim?')">
                                    ❌ Reject
                                </button>
                            </form>

                        <?php elseif($status == 'approved'): ?>

							<?php if(!empty($row['reward']) && !$row['reward_paid']): ?>
                        <div class="reward-pay-box">
                            <p class="reward-pay-label">
                                💰 RM<?php echo htmlspecialchars($row['reward']); ?> reward due
                                <?php
                                $pref = $row['preferred_reward_method'] ?? 'cash';
                                if($pref === 'online') echo '— claimant prefers <strong>online transfer</strong>';
                                else echo '— claimant prefers <strong>cash on meetup</strong>';
                                ?>
                            </p>
                            <div class="reward-pay-btns">
                                <a href="reward_pay.php?claim_id=<?php echo $row['id']; ?>"
                                   class="claim-action-btn btn-approve <?php echo $pref==='online'?'':''; ?>">
                                    💳 Pay Online
                                </a>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="claim_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="action"   value="cash_paid">
                                    <button type="submit" class="claim-action-btn btn-collected"
                                        onclick="return confirm('Confirm you have paid the reward in cash?')">
                                        💵 Paid Cash
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($row['reward']) && $row['reward_paid']): ?>
                        <div class="action-success" style="margin-top:8px; padding:8px 12px; font-size:13px;">
                            ✅ Reward paid via <?php echo ucfirst($row['reward_method'] ?? 'cash'); ?>
                        </div>
                        <?php endif; ?>

							<form method="POST" style="display:inline;">
								<input type="hidden" name="claim_id" value="<?php echo $row['id']; ?>">
								<input type="hidden" name="action" value="collected">
								<button type="submit" class="claim-action-btn btn-collected"
									onclick="return confirm('Mark this item as collected?')">
									📦 Mark Collected
								</button>
							</form>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

<?php include 'footer.php'; ?>

</body>
</html>