<?php
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Handle delete listing
if(isset($_GET['delete']) && is_numeric($_GET['delete'])){
    $del_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM claims WHERE listing_id=$del_id");
    mysqli_query($conn, "DELETE FROM listings WHERE id=$del_id AND user_id=$user_id");
    header("Location: profile.php");
    exit();
}

// Get user info
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));

// Stats — only count active (non-resolved) listings
$totalListings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM listings WHERE user_id=$user_id AND (resolved_status IS NULL OR resolved_status='active')"))['c'];
$totalLost     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM listings WHERE user_id=$user_id AND listing_type='lost' AND (resolved_status IS NULL OR resolved_status='active')"))['c'];
$totalFound    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM listings WHERE user_id=$user_id AND listing_type='found' AND (resolved_status IS NULL OR resolved_status='active')"))['c'];
$totalClaims   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM claims WHERE claimant_id=$user_id"))['c'];
$totalApproved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM claims WHERE claimant_id=$user_id AND status='approved'"))['c'];

// Active listings
$listings = mysqli_query($conn, "
    SELECT * FROM listings
    WHERE user_id=$user_id
    AND (resolved_status IS NULL OR resolved_status='active')
    ORDER BY created_at DESC
");

// Archived/resolved listings
$archivedListings = mysqli_query($conn, "
    SELECT * FROM listings
    WHERE user_id=$user_id
    AND resolved_status='resolved'
    ORDER BY created_at DESC
");
$archivedCount = mysqli_num_rows($archivedListings);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($user['username']); ?> – Profile</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="profile-page">

    <!-- LEFT: User card -->
    <div class="profile-sidebar">

        <div class="profile-avatar-large">
            <?php if(!empty($user['profile_pic'])): ?>
                <img src="<?php echo $user['profile_pic']; ?>" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
            <?php else: ?>
                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
            <?php endif; ?>
        </div>

        <h2 class="profile-username"><?php echo htmlspecialchars($user['username']); ?></h2>
        <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
        <p class="profile-joined">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>

        <div class="profile-stats">
            <div class="profile-stat">
                <span class="profile-stat-num"><?php echo $totalListings; ?></span>
                <span class="profile-stat-label">Listings</span>
            </div>
            <div class="profile-stat">
                <span class="profile-stat-num"><?php echo $totalClaims; ?></span>
                <span class="profile-stat-label">Claims</span>
            </div>
            <div class="profile-stat">
                <span class="profile-stat-num"><?php echo $totalApproved; ?></span>
                <span class="profile-stat-label">Approved</span>
            </div>
        </div>

        <div class="profile-sidebar-links">
            <a href="edit_profile.php" class="profile-sidebar-btn">✏️ Edit Profile</a>
            <a href="myclaims.php"     class="profile-sidebar-btn">📋 My Claims</a>
            <a href="rewards.php"      class="profile-sidebar-btn">🏆 Rewards</a>
            <a href="logout.php"       class="profile-sidebar-btn profile-logout-btn">🚪 Logout</a>
        </div>

    </div>

    <!-- RIGHT: Listings -->
    <div class="profile-main">

        <!-- ===== ACTIVE LISTINGS ===== -->
        <div class="profile-main-header">
            <h2>My Listings</h2>
            <div class="profile-listing-tabs">
                <button class="profile-tab active" onclick="filterListings('all',   this)">All (<?php echo $totalListings; ?>)</button>
                <button class="profile-tab"        onclick="filterListings('lost',  this)">Lost (<?php echo $totalLost; ?>)</button>
                <button class="profile-tab"        onclick="filterListings('found', this)">Found (<?php echo $totalFound; ?>)</button>
            </div>
        </div>

        <?php if($totalListings == 0): ?>
            <div class="empty-state">
                <div class="empty-icon">📦</div>
                <h3>No active listings</h3>
                <p>You haven't reported any active lost or found items yet.</p>
                <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
                    <a href="lostitem.php"  class="btn">Report Lost Item</a>
                    <a href="founditem.php" class="btn btn-outline">Report Found Item</a>
                </div>
            </div>
        <?php else: ?>
            <div class="profile-listings-grid" id="listingsGrid">
            <?php while($row = mysqli_fetch_assoc($listings)): ?>

                <div class="profile-listing-card-wrap" data-type="<?php echo $row['listing_type']; ?>">

                    <a href="item.php?id=<?php echo $row['id']; ?>" class="profile-listing-card">
                        <div class="profile-listing-img">
                            <?php if(!empty($row['image'])): ?>
                                <img src="<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                            <?php else: ?>
                                <img src="images/default.jpg" alt="No image">
                            <?php endif; ?>
                            <div class="type-badge type-<?php echo $row['listing_type']; ?>" style="position:absolute; top:8px; left:8px; font-size:11px; padding:4px 10px;">
                                <?php echo strtoupper($row['listing_type']); ?>
                            </div>
                        </div>
                        <div class="profile-listing-body">
                            <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                            <p>📍 <?php echo htmlspecialchars($row['location']); ?></p>
                            <p class="card-date">🕐 <?php echo date('d M Y', strtotime($row['created_at'])); ?></p>
                            <?php if(!empty($row['reward'])): ?>
                                <p class="reward">💰 RM<?php echo htmlspecialchars($row['reward']); ?> reward</p>
                            <?php endif; ?>
                        </div>
                    </a>

                    <a href="profile.php?delete=<?php echo $row['id']; ?>"
                       class="profile-listing-delete"
                       onclick="return confirm('Delete this listing? This cannot be undone.')">
                        🗑️ Delete
                    </a>

                </div>

            <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <!-- ===== ARCHIVED / RESOLVED LISTINGS ===== -->
        <?php if($archivedCount > 0): ?>
        <div class="profile-archived-section">

            <div class="profile-archived-header" onclick="toggleArchived()">
                <h3>📦 Settled & Archived (<?php echo $archivedCount; ?>)</h3>
                <span class="archived-toggle-icon" id="archivedToggleIcon">▼</span>
            </div>

            <div id="archivedGrid" class="profile-listings-grid" style="margin-top:16px; display:none;">
            <?php while($row = mysqli_fetch_assoc($archivedListings)): ?>

                <div class="profile-listing-card-wrap">
                    <a href="item.php?id=<?php echo $row['id']; ?>" class="profile-listing-card archived">
                        <div class="profile-listing-img">
                            <?php if(!empty($row['image'])): ?>
                                <img src="<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                            <?php else: ?>
                                <img src="images/default.jpg" alt="No image">
                            <?php endif; ?>
                            <div class="type-badge type-<?php echo $row['listing_type']; ?>" style="position:absolute; top:8px; left:8px; font-size:11px; padding:4px 10px;">
                                <?php echo strtoupper($row['listing_type']); ?>
                            </div>
                            <div class="archived-badge">✅ RESOLVED</div>
                        </div>
                        <div class="profile-listing-body">
                            <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                            <p>📍 <?php echo htmlspecialchars($row['location']); ?></p>
                            <p class="card-date">🕐 <?php echo date('d M Y', strtotime($row['created_at'])); ?></p>
                        </div>
                    </a>
                </div>

            <?php endwhile; ?>
            </div>

        </div>
        <?php endif; ?>

    </div>
</div>

<?php include 'footer.php'; ?>

<script>
function filterListings(type, btn) {
    document.querySelectorAll('.profile-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.profile-listing-card-wrap:not(#archivedGrid .profile-listing-card-wrap)').forEach(card => {
        if(type === 'all' || card.getAttribute('data-type') === type){
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

function toggleArchived() {
    const grid = document.getElementById('archivedGrid');
    const icon = document.getElementById('archivedToggleIcon');
    const open = grid.style.display !== 'none';
    grid.style.display = open ? 'none' : 'grid';
    icon.textContent   = open ? '▼' : '▲';
}
</script>

</body>
</html>