<?php
include 'config.php';

// Allow viewing without login; only claim/boost actions require login
$loggedIn = isset($_SESSION['user_id']);
$user_id  = $loggedIn ? (int)$_SESSION['user_id'] : 0;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if(!$id) die("Invalid item.");

$item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM listings WHERE id='$id'"));
if(!$item) die("Item not found.");

$isOwner = ($loggedIn && $item['user_id'] == $user_id);

// Fetch owner info
$ownerData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT username, profile_pic FROM users WHERE id='{$item['user_id']}'"));

// Check if this listing has been resolved (approved or collected claim exists)
$resolvedCheck = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT id FROM claims
    WHERE listing_id={$item['id']}
    AND status IN ('approved','collected')
    LIMIT 1
"));
$isResolved = !empty($resolvedCheck);


/* ========================= HANDLE CLAIM ========================= */
if(isset($_POST['claim_btn'])){

    $listing_id    = (int)$_POST['listing_id'];
    $user_id       = $_SESSION['user_id'];
    $claim_message = mysqli_real_escape_string($conn, trim($_POST['claim_message']));

    if(empty($claim_message)){
        $claimError = "Please enter a claim reason.";
    } else {
        $check = mysqli_query($conn, "
            SELECT * FROM claims
            WHERE listing_id='$listing_id'
            AND claimant_id='$user_id'
            AND status IN ('pending','approved')
        ");

        if(mysqli_num_rows($check) == 0){
            $preferredMethod = in_array($_POST['preferred_reward_method'] ?? '', ['cash','online'])
                               ? $_POST['preferred_reward_method'] : 'cash';

            $insertClaim = $conn->prepare("
                INSERT INTO claims (listing_id, claimant_id, message, status, preferred_reward_method)
                VALUES (?, ?, ?, 'pending', ?)
            ");
            $insertClaim->bind_param('iiss', $listing_id, $user_id, $claim_message, $preferredMethod);
            $insertClaim->execute();
            $insertClaim->close();

            // Notify the listing owner
            $claimant_name = $_SESSION['username'];
            $notif_msg  = "📋 " . htmlspecialchars($claimant_name) . " submitted a claim on your listing: " . htmlspecialchars($item['title']);
            $notif_link = "incoming_claims.php";
            $owner_id   = (int)$item['user_id'];
            $notifStmt  = $conn->prepare("INSERT INTO notifications (user_id, type, message, link) VALUES (?, 'claim', ?, ?)");
            $notifStmt->bind_param('iss', $owner_id, $notif_msg, $notif_link);
            $notifStmt->execute();
            $notifStmt->close();


            // Email the listing owner
            include_once 'mailer_helper.php';
            $ownerEmail = mysqli_fetch_assoc(mysqli_query($conn, "SELECT email FROM users WHERE id=$owner_id"));
            if ($ownerEmail) {
                $claimEmailBody = '
                    <p style="color:#ccc;font-size:15px;line-height:1.7;">
                        Someone has submitted a claim on your listing <strong style="color:white;">' . htmlspecialchars($item['title']) . '</strong>.
                    </p>
                    <p style="color:#ccc;font-size:15px;line-height:1.7;">Log in to review it and approve or reject.</p>
                    <a href="' . SITE_URL . '/incoming_claims.php" style="display:inline-block;margin-top:20px;background:#cc0000;color:white;padding:12px 28px;border-radius:10px;text-decoration:none;font-weight:700;">View Claims</a>
                ';
                sendMail($ownerEmail['email'], $ownerData['username'], 'New claim on your listing', mailTemplate('New claim received 📋', $claimEmailBody));
            }


            $claimSuccess = "Your claim has been submitted successfully!";
        } else {
            $claimError = "You already have an active claim on this item.";
        }
    }
}

/* ========================= HANDLE BOOST ========================= */
if(isset($_POST['boost_request'])){

    $listing_id = (int)$_POST['listing_id'];
    $user_id    = $_SESSION['user_id'];

    $check = mysqli_query($conn, "SELECT * FROM listings WHERE id='$listing_id' AND user_id='$user_id'");

    if(mysqli_num_rows($check) > 0){
        mysqli_query($conn, "UPDATE listings SET boost_requested=1 WHERE id='$listing_id'");
        $boostSuccess = "Boost request sent to admin!";
    } else {
        $boostError = "You are not the owner of this listing.";
    }
}

// Parse description — separate the structured fields from the user's description
function parseDescription($raw){
    $lines     = explode("\n", $raw);
    $fields    = [];
    $descLines = [];
    $inDesc    = false;

    foreach($lines as $line){
        $line = trim($line);
        if(empty($line)){
            $inDesc = true;
            continue;
        }
        if(!$inDesc && strpos($line, ':') !== false && !preg_match('/^\w{10,}/', $line)){
            $parts          = explode(':', $line, 2);
            $fields[trim($parts[0])] = trim($parts[1]);
        } else {
            $descLines[] = $line;
        }
    }
    return ['fields' => $fields, 'description' => implode("\n", $descLines)];
}

$parsed      = parseDescription($item['description']);
$extraFields = $parsed['fields'];
$cleanDesc   = $parsed['description'];
$hasCoords   = !empty($item['lat']) && !empty($item['lng']);


/* ========================= HANDLE FLAG ========================= */
if(isset($_POST['flag_btn'])){
    $listing_id  = (int)$_POST['listing_id'];
    $flag_reason = mysqli_real_escape_string($conn, $_POST['flag_reason']);
    if(!empty($flag_reason)){
        mysqli_query($conn, "UPDATE listings SET is_flagged=1, flag_reason='$flag_reason' WHERE id='$listing_id'");
        $flagSuccess = "This listing has been reported to our admin team.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($item['title']); ?> – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="item-page">

    <!-- LEFT: Image + meta -->
    <div class="item-left">

        <?php
        // Load all images for this listing
        $imgStmt = $conn->prepare("SELECT image FROM listing_images WHERE listing_id=? ORDER BY sort_order ASC");
        $imgStmt->bind_param('i', $item['id']);
        $imgStmt->execute();
        $extraImages = $imgStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $imgStmt->close();

        // Combine: main image first, then extras (deduplicate)
        $allImages = [];
        if(!empty($item['image'])) $allImages[] = $item['image'];
        foreach($extraImages as $ei){
            if($ei['image'] !== $item['image']) $allImages[] = $ei['image'];
        }
        if(empty($allImages)) $allImages[] = 'images/default.jpg';
        ?>

        <div class="item-image-wrap">
            <img src="<?php echo $allImages[0]; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>"
                 id="mainItemImg" style="cursor:<?php echo count($allImages) > 1 ? 'pointer' : 'default'; ?>">

            <?php if(count($allImages) > 1): ?>
            <div class="item-thumb-row">
                <?php foreach($allImages as $ti => $imgPath): ?>
                <img src="<?php echo $imgPath; ?>" class="item-thumb <?php echo $ti === 0 ? 'active' : ''; ?>"
                     onclick="switchImage('<?php echo $imgPath; ?>', this)"
                     alt="Photo <?php echo $ti + 1; ?>">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="type-badge type-<?php echo $item['listing_type']; ?>" style="position:absolute; top:14px; left:14px; font-size:13px; padding:6px 14px;">
                <?php echo strtoupper($item['listing_type']); ?>
            </div>

            <?php if($item['is_boosted']): ?>
                <div class="badge" style="top:14px; right:14px; left:auto;">⭐ SPONSORED</div>
            <?php endif; ?>
        </div>

        <!-- Reporter info -->
        <div class="item-reporter">
            <div class="reporter-avatar">
                <?php if(!empty($ownerData['profile_pic'])): ?>
                    <img src="<?php echo $ownerData['profile_pic']; ?>" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                <?php else: ?>
                    <?php echo strtoupper(substr($ownerData['username'], 0, 1)); ?>
                <?php endif; ?>
            </div>
            <div>
                <p class="reporter-label">Reported by</p>
                <p class="reporter-name"><?php echo htmlspecialchars($ownerData['username']); ?></p>
            </div>
        </div>

        <!-- Meta info -->
        <div class="item-meta-box">
            <div class="item-meta-row">
                <span class="meta-icon">📍</span>
                <div>
                    <p class="meta-label">Location</p>
                    <p class="meta-value"><?php echo htmlspecialchars($item['location']); ?></p>
                </div>
            </div>
            <div class="item-meta-row">
                <span class="meta-icon">🗂️</span>
                <div>
                    <p class="meta-label">Category</p>
                    <p class="meta-value"><?php echo ucfirst($item['category'] ?? 'Item'); ?></p>
                </div>
            </div>
            <?php if(!empty($item['date_lost'])): ?>
            <div class="item-meta-row">
                <span class="meta-icon">📅</span>
                <div>
                    <p class="meta-label"><?php echo $item['listing_type'] == 'lost' ? 'Date Lost' : 'Date Found'; ?></p>
                    <p class="meta-value"><?php echo date('d M Y', strtotime($item['date_lost'])); ?></p>
                </div>
            </div>
            <?php endif; ?>
            <div class="item-meta-row">
                <span class="meta-icon">🕐</span>
                <div>
                    <p class="meta-label">Date Reported</p>
                    <p class="meta-value"><?php echo date('d M Y', strtotime($item['created_at'])); ?></p>
                </div>
            </div>
            <?php if(!empty($item['reward'])): ?>
            <div class="item-meta-row">
                <span class="meta-icon">💰</span>
                <div>
                    <p class="meta-label">Reward Offered</p>
                    <p class="meta-value reward">RM<?php echo htmlspecialchars($item['reward']); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- MAP -->
        <?php if($hasCoords): ?>
        <div class="item-map-wrap">
            <p class="meta-label" style="margin-bottom:10px;">📍 Location on Map</p>
            <div id="itemMap"></div>
        </div>
        <?php endif; ?>

    </div>

    <!-- RIGHT: Details + actions -->
    <div class="item-right">

        <?php if($isResolved): ?>
        <div class="resolved-banner">
            ✅ This item has been claimed and is likely returned to its owner.
            It may no longer be available.
        </div>
        <?php endif; ?>

        <h1 class="item-title"><?php echo htmlspecialchars($item['title']); ?></h1>

        <!-- Extra structured fields (pet/vehicle/person details) -->
        <?php if(!empty($extraFields)): ?>
        <div class="item-description" style="margin-bottom:16px;">
            <h3>Details</h3>
            <div class="item-extra-fields">
                <?php foreach($extraFields as $label => $value): ?>
                    <?php if(!empty(trim($value))): ?>
                    <div class="item-extra-row">
                        <span class="item-extra-label"><?php echo htmlspecialchars($label); ?></span>
                        <span class="item-extra-value"><?php echo htmlspecialchars($value); ?></span>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Description -->
        <?php if(!empty(trim($cleanDesc))): ?>
        <div class="item-description">
            <h3>Description</h3>
            <p><?php echo nl2br(htmlspecialchars($cleanDesc)); ?></p>
        </div>
        <?php endif; ?>

        <div class="item-divider"></div>

        <?php if(!$loggedIn): ?>
            <div class="item-action-box">
                <h3>📋 Claim This Item</h3>
                <p class="action-sub">Sign in to submit a claim on this item.</p>
                <a href="login.php" class="auth-submit-btn" style="display:block; text-align:center; text-decoration:none; margin-top:12px;">Sign In to Claim</a>
            </div>

        <?php elseif(!$isOwner): ?>

            <!-- CLAIM FORM -->
            <div class="item-action-box">

                <h3>📋 Claim This Item</h3>
                <p class="action-sub">Is this yours? Tell the reporter why this item belongs to you.</p>

                <?php if(isset($claimSuccess)): ?>
                    <div class="action-success">✅ <?php echo $claimSuccess; ?></div>
                <?php endif; ?>

                <?php if(isset($claimError)): ?>
                    <div class="action-error">⚠️ <?php echo $claimError; ?></div>
                <?php endif; ?>

                <?php if(!isset($claimSuccess)): ?>
                <form method="POST">
                    <input type="hidden" name="listing_id" value="<?php echo $item['id']; ?>">
                    <textarea
                        name="claim_message"
                        placeholder="Describe identifying details, when/where you lost it, or any proof of ownership..."
                        required
                    ></textarea>

                    <?php if(!empty($item['reward'])): ?>
                    <div style="margin:14px 0;">
                        <p style="font-size:13px; color:#aaa; margin-bottom:10px;">
                            💰 This listing has a reward of <strong>RM<?php echo htmlspecialchars($item['reward']); ?></strong>.
                            How would you like to receive it?
                        </p>
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px;">
                                <input type="radio" name="preferred_reward_method" value="cash" checked>
                                💵 Cash (when we meet up)
                            </label>
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px;">
                                <input type="radio" name="preferred_reward_method" value="online">
                                💳 Online Transfer (ToyyibPay)
                            </label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <button type="submit" name="claim_btn" class="auth-submit-btn">
                        Submit Claim
                    </button>
                </form>
                <?php endif; ?>

            </div>

        <?php else: ?>

            <!-- BOOST FORM (owner only) -->
            <div class="item-action-box">

                <h3>🚀 Boost This Listing</h3>
                <p class="action-sub">Sponsored listings appear at the top of browse and on the homepage.</p>

                <?php if(isset($boostSuccess)): ?>
                    <div class="action-success">✅ <?php echo $boostSuccess; ?></div>
                <?php endif; ?>

                <?php if(isset($boostError)): ?>
                    <div class="action-error">⚠️ <?php echo $boostError; ?></div>
                <?php endif; ?>

                <?php if($item['is_boosted']): ?>
                    <div class="action-success">⭐ This listing is currently sponsored.</div>
                <?php elseif($item['boost_requested']): ?>
                    <div class="action-info">⏳ Boost request pending admin approval.</div>
                <?php else: ?>
                    <a href="boost_pay.php?id=<?php echo $item['id']; ?>" class="auth-submit-btn" style="display:block; text-align:center; text-decoration:none;">
                        🚀 Boost This Listing — As Low As RM5.49
                    </a>
                <?php endif; ?>

            </div>

        <?php endif; ?>

        <!-- FLAG LISTING (non-owner only) -->
        <?php if(!$isOwner): ?>
        <div class="item-action-box" style="margin-top:16px;">
            <h3>🚩 Report This Listing</h3>
            <p class="action-sub">If this listing looks suspicious or inappropriate, report it to our admin team.</p>
            <?php if(isset($flagSuccess)): ?>
                <div class="action-success">✅ <?php echo $flagSuccess; ?></div>
            <?php elseif($item['is_flagged']): ?>
                <div class="action-info">⚠️ This listing has already been reported and is under review.</div>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="listing_id" value="<?php echo $item['id']; ?>">
                    <select name="flag_reason" required style="width:100%; padding:12px 14px; background:#0d0d0d; border:1px solid #2a2a2a; border-radius:10px; color:white; font-size:14px; margin-bottom:12px;">
                        <option value="">Select a reason...</option>
                        <option value="Spam or duplicate listing">Spam or duplicate listing</option>
                        <option value="Fake or misleading information">Fake or misleading information</option>
                        <option value="Inappropriate content">Inappropriate content</option>
                        <option value="Scam attempt">Scam attempt</option>
                        <option value="Other">Other</option>
                    </select>
                    <button type="submit" name="flag_btn" class="claim-action-btn btn-reject" style="width:100%;">
                        🚩 Submit Report
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <a href="browse.php" class="item-back-link">← Back to Browse</a>

    </div>

</div>


<?php
// ---- Smart matching: suggest opposite-type listings in same category ----
$match_type     = ($item['listing_type'] == 'lost') ? 'found' : 'lost';
$match_category = $item['category'] ?? 'item';

$matchStmt = $conn->prepare("
    SELECT id, title, location, image, listing_type, created_at
    FROM listings
    WHERE listing_type = ?
      AND category     = ?
      AND id           != ?
      AND status       = 'active'
    ORDER BY created_at DESC
    LIMIT 4
");
$matchStmt->bind_param('ssi', $match_type, $match_category, $item['id']);
$matchStmt->execute();
$matchResult = $matchStmt->get_result();
$matchStmt->close();

if($matchResult->num_rows > 0):
    $matchLabel = ($item['listing_type'] == 'lost')
        ? '🟢 Found listings that might be yours'
        : '🔴 Lost reports that match this item';
?>
<div class="section" style="margin-top:40px;">
    <h2 class="section-title" style="font-size:18px;"><?php echo $matchLabel; ?></h2>
    <p class="section-sub">Same category · Most recent first</p>
    <div class="card-container" style="margin-top:20px;">
    <?php while($m = $matchResult->fetch_assoc()): ?>
        <a href="item.php?id=<?php echo $m['id']; ?>" style="text-decoration:none; color:white;">
            <div class="card">
                <?php if(!empty($m['image'])): ?>
                    <img src="<?php echo $m['image']; ?>" alt="<?php echo htmlspecialchars($m['title']); ?>">
                <?php else: ?>
                    <img src="images/default.jpg" alt="No image">
                <?php endif; ?>
                <div class="type-badge type-<?php echo $m['listing_type']; ?>">
                    <?php echo strtoupper($m['listing_type']); ?>
                </div>
                <div class="card-content">
                    <h3><?php echo htmlspecialchars($m['title']); ?></h3>
                    <p>📍 <?php echo htmlspecialchars($m['location']); ?></p>
                    <p class="card-date">🕐 <?php echo date('d M Y', strtotime($m['created_at'])); ?></p>
                </div>
            </div>
        </a>
    <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>


<?php include 'footer.php'; ?>

<?php if($hasCoords): ?>
<script>
function initMap() {
    const coords = { lat: <?php echo $item['lat']; ?>, lng: <?php echo $item['lng']; ?> };

    const map = new google.maps.Map(document.getElementById('itemMap'), {
        center: coords,
        zoom: 15,
        styles: [
            { elementType: 'geometry',          stylers: [{ color: '#1a1a1a' }] },
            { elementType: 'labels.text.fill',  stylers: [{ color: '#757575' }] },
            { elementType: 'labels.text.stroke',stylers: [{ color: '#212121' }] },
            { featureType: 'road',              elementType: 'geometry',        stylers: [{ color: '#2c2c2c' }] },
            { featureType: 'road',              elementType: 'labels.text.fill',stylers: [{ color: '#8a8a8a' }] },
            { featureType: 'water',             elementType: 'geometry',        stylers: [{ color: '#000000' }] },
            { featureType: 'poi',               elementType: 'geometry',        stylers: [{ color: '#1a1a1a' }] },
        ]
    });

    new google.maps.Marker({
        position: coords,
        map: map,
        title: '<?php echo addslashes($item['title']); ?>',
        animation: google.maps.Animation.DROP,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 10,
            fillColor: '#ff0000',
            fillOpacity: 1,
            strokeColor: '#ffffff',
            strokeWeight: 2
        }
    });
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyChzn_JIEytG6xbhuf3Wu1UGO-3de_4m0A&callback=initMap" async defer></script>
<?php endif; ?>

<script>
function switchImage(src, thumb) {
    document.getElementById('mainItemImg').src = src;
    document.querySelectorAll('.item-thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
}
</script>

</body>
</html>