<?php
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: myclaims.php");
    exit();
}

$user_id  = (int)$_SESSION['user_id'];
$claim_id = (int)$_POST['claim_id'];
$method   = in_array($_POST['method'] ?? '', ['cash', 'online']) ? $_POST['method'] : 'cash';

// Verify claimant owns this claim, it's collected, has a reward, and not yet paid
$claim = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT claims.*, listings.reward, listings.id AS listing_id,
           listings.user_id AS owner_id, listings.title
    FROM claims
    JOIN listings ON claims.listing_id = listings.id
    WHERE claims.id = $claim_id
    AND claims.claimant_id = $user_id
    AND claims.status = 'collected'
    AND claims.reward_paid = 0
"));

if(!$claim || empty($claim['reward'])){
    header("Location: myclaims.php");
    exit();
}

$amount     = (float)$claim['reward'];
$listing_id = (int)$claim['listing_id'];
$owner_id   = (int)$claim['owner_id'];
$reference  = 'RWD-' . strtoupper(uniqid());

if($method === 'cash'){
    // Record cash arrangement — no payment window needed
    mysqli_query($conn, "
        INSERT INTO payments (user_id, listing_id, type, amount, method, status, reference)
        VALUES ($user_id, $listing_id, 'reward', $amount, 'cash', 'pending', '$reference')
    ");

    mysqli_query($conn, "
        UPDATE claims SET reward_paid=1, reward_method='cash' WHERE id=$claim_id
    ");

    // Notify the owner
    $notifMsg  = "💰 " . htmlspecialchars($_SESSION['username']) . " confirmed a cash reward arrangement for \"" . htmlspecialchars($claim['title']) . "\".";
    $notifLink = "incoming_claims.php";
    mysqli_query($conn, "
        INSERT INTO notifications (user_id, type, message, link)
        VALUES ($owner_id, 'reward', '$notifMsg', '$notifLink')
    ");

    header("Location: reward_claim_success.php?claim_id=$claim_id&ref=$reference&amount=$amount&method=cash");
    exit();

} else {
    // Online — claimant is requesting the owner pays them via ToyyibPay.
    // We record this as a 'requested' payment and notify the owner to action it.
    mysqli_query($conn, "
        INSERT INTO payments (user_id, listing_id, type, amount, method, status, reference, claim_id)
        VALUES ($user_id, $listing_id, 'reward', $amount, 'online', 'requested', '$reference', $claim_id)
    ");

    // Notify the owner to go and pay
    $ownerMsg  = "💳 " . htmlspecialchars($_SESSION['username']) . " has requested an online reward payment of RM" . number_format($amount, 2) . " for \"" . htmlspecialchars($claim['title']) . "\". Go to Incoming Claims to pay.";
    $ownerLink = "incoming_claims.php";
    mysqli_query($conn, "
        INSERT INTO notifications (user_id, type, message, link)
        VALUES ($owner_id, 'reward', '$ownerMsg', '$ownerLink')
    ");

    header("Location: reward_claim_success.php?claim_id=$claim_id&ref=$reference&amount=$amount&method=online");
    exit();
}
?>