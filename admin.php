<?php
include 'auth.php';

if(!$isAdmin){
    header("Location: index.php");
    exit();
}

/* ========================= ACTIONS ========================= */

// Claim actions
if(isset($_GET['action']) && isset($_GET['id'])){
    $claim_id = (int)$_GET['id'];
    $action   = $_GET['action'];
    if($action == 'approve')   mysqli_query($conn, "UPDATE claims SET status='approved'  WHERE id='$claim_id'");
    if($action == 'reject')    mysqli_query($conn, "UPDATE claims SET status='rejected'  WHERE id='$claim_id'");
    if($action == 'collected') mysqli_query($conn, "UPDATE claims SET status='collected' WHERE id='$claim_id'");
    header("Location: admin.php#claims"); exit();
}

// Boost actions
if(isset($_GET['boost_action']) && isset($_GET['id'])){
    $listing_id   = (int)$_GET['id'];
    $boost_action = $_GET['boost_action'];
    if($boost_action == 'approve') mysqli_query($conn, "UPDATE listings SET is_boosted=1, boost_requested=0 WHERE id='$listing_id'");
    if($boost_action == 'reject')  mysqli_query($conn, "UPDATE listings SET boost_requested=0             WHERE id='$listing_id'");
    header("Location: admin.php#boosts"); exit();
}

// Delete listing
if(isset($_GET['delete_listing'])){
    $lid = (int)$_GET['delete_listing'];
    mysqli_query($conn, "DELETE FROM claims   WHERE listing_id=$lid");
    mysqli_query($conn, "DELETE FROM listings WHERE id=$lid");
    header("Location: admin.php#listings"); exit();
}

// Delete user
if(isset($_GET['delete_user'])){
    $uid = (int)$_GET['delete_user'];
    mysqli_query($conn, "DELETE FROM users WHERE id=$uid AND role != 'admin'");
    header("Location: admin.php#users"); exit();
}

// Unflag listing
if(isset($_GET['unflag'])){
    $lid = (int)$_GET['unflag'];
    mysqli_query($conn, "UPDATE listings SET is_flagged=0, flag_reason=NULL WHERE id=$lid");
    header("Location: admin.php#flagged"); exit();
}

// Mark listing as collected (admin force-close)
if(isset($_GET['force_collect'])){
    $lid = (int)$_GET['force_collect'];
    mysqli_query($conn, "UPDATE claims SET status='collected' WHERE listing_id=$lid AND status='approved'");
    header("Location: admin.php#listings"); exit();
}

// Announcement actions
if(isset($_POST['add_announcement'])){
    $msg = mysqli_real_escape_string($conn, trim($_POST['announcement_msg']));
    if(!empty($msg)){
        mysqli_query($conn, "INSERT INTO announcements (message) VALUES ('$msg')");
    }
    header("Location: admin.php#announcements"); exit();
}

if(isset($_GET['delete_announcement'])){
    $aid = (int)$_GET['delete_announcement'];
    mysqli_query($conn, "DELETE FROM announcements WHERE id=$aid");
    header("Location: admin.php#announcements"); exit();
}

if(isset($_GET['toggle_announcement'])){
    $aid = (int)$_GET['toggle_announcement'];
    mysqli_query($conn, "UPDATE announcements SET is_active = !is_active WHERE id=$aid");
    header("Location: admin.php#announcements"); exit();
}

// ==========================================
// ARCHIVE OVERRIDES (MANUAL ROUTINES)
// ==========================================
if(isset($_GET['archive_listing'])){
    $lid = (int)$_GET['archive_listing'];
    mysqli_query($conn, "UPDATE listings SET is_archived=1, archived_at=NOW(), archived_reason='manual' WHERE id=$lid");
    header("Location: admin.php#listings"); exit();
}

if(isset($_GET['restore_listing'])){
    $lid = (int)$_GET['restore_listing'];
    mysqli_query($conn, "UPDATE listings SET is_archived=0, archived_at=NULL, archived_reason=NULL WHERE id=$lid");
    header("Location: admin.php#listings"); exit();
}


/* ========================= STATS ========================= */
$totalUsers     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM users"))['c'];
$totalListings  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM listings"))['c'];
$totalLost      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM listings WHERE listing_type='lost'"))['c'];
$totalFound     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM listings WHERE listing_type='found'"))['c'];
$totalClaims    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM claims"))['c'];
$pendingClaims  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM claims WHERE status='pending'"))['c'];
$approvedClaims = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM claims WHERE status='approved'"))['c'];
$rejectedClaims = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM claims WHERE status='rejected'"))['c'];
$boostRequests  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM listings WHERE boost_requested=1 AND is_boosted=0"))['c'];
$flaggedCount   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM listings WHERE is_flagged=1"))['c'];

/* ========================= CHART DATA ========================= */
$chartLabels = []; $chartLost = []; $chartFound = [];
for($i = 6; $i >= 0; $i--){
    $date          = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[] = date('d M', strtotime("-$i days"));
    $chartLost[]   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM listings WHERE listing_type='lost'  AND DATE(created_at)='$date'"))['c'];
    $chartFound[]  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM listings WHERE listing_type='found' AND DATE(created_at)='$date'"))['c'];
}

$catItems   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM listings WHERE category='item'"))['c'];
$catPets    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM listings WHERE category='pet'"))['c'];
$catVehicle = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM listings WHERE category='vehicle'"))['c'];
$catPerson  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM listings WHERE category='person'"))['c'];

/* ========================= TABLE DATA ========================= */
$recentActivity = mysqli_query($conn, "
    (SELECT 'listing' as type, title as detail, created_at FROM listings ORDER BY created_at DESC LIMIT 5)
    UNION ALL
    (SELECT 'claim', CONCAT('Claim on: ', listings.title), claims.created_at FROM claims JOIN listings ON claims.listing_id=listings.id ORDER BY claims.created_at DESC LIMIT 5)
    UNION ALL
    (SELECT 'user', CONCAT('New user: ', username), created_at FROM users ORDER BY created_at DESC LIMIT 5)
    ORDER BY created_at DESC LIMIT 10
");

$allListings = mysqli_query($conn, "
    SELECT listings.*, users.username FROM listings
    JOIN users ON listings.user_id = users.id
    ORDER BY listings.created_at DESC LIMIT 50
");

$allUsers = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");

$allClaims = mysqli_query($conn, "
    SELECT claims.*, listings.title, users.username AS claimant_name
    FROM claims
    JOIN listings ON claims.listing_id = listings.id
    JOIN users    ON claims.claimant_id = users.id
    ORDER BY claims.created_at DESC
");

$boostListings = mysqli_query($conn, "
    SELECT listings.*, users.username FROM listings
    JOIN users ON listings.user_id = users.id
    WHERE boost_requested=1 AND is_boosted=0
    ORDER BY listings.created_at DESC
");

$flaggedListings = mysqli_query($conn, "
    SELECT listings.*, users.username FROM listings
    JOIN users ON listings.user_id = users.id
    WHERE is_flagged=1
    ORDER BY listings.created_at DESC
");

$announcements = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="admin-wrap">

    <div class="admin-sidebar">
        <div class="admin-sidebar-header">
            <p>Admin Panel</p>
        </div>
        <nav class="admin-nav">
            <a href="#" class="admin-nav-link active" onclick="showTab('overview',       this)">📊 Overview</a>
            <a href="#" class="admin-nav-link"        onclick="showTab('claims',         this)">📋 Claims
                <?php if($pendingClaims > 0): ?><span class="admin-badge"><?php echo $pendingClaims; ?></span><?php endif; ?>
            </a>
            
            <a href="#" class="admin-nav-link"        onclick="showTab('flagged',        this)">🚩 Flagged
                <?php if($flaggedCount > 0): ?><span class="admin-badge" style="background:orange;"><?php echo $flaggedCount; ?></span><?php endif; ?>
            </a>
            <a href="#" class="admin-nav-link"        onclick="showTab('listings',       this)">📦 Listings</a>
            <a href="#" class="admin-nav-link"        onclick="showTab('users',          this)">👥 Users</a>
            <a href="#" class="admin-nav-link"        onclick="showTab('announcements',  this)">📢 Announcements</a>
            <a href="#" class="admin-nav-link" onclick="showTab('payments', this)">💳 Payments</a>
            <a href="#" class="admin-nav-link"        onclick="showTab('activity',       this)">🕐 Activity</a>
        </nav>
        <a href="index.php" class="admin-back-link">← Back to Site</a>
    </div>

    <div class="admin-main">

        <div id="tab-overview" class="admin-tab active">

            <div class="admin-page-header">
                <h1>Overview</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            </div>

            <div class="admin-stats-grid">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon">👥</div>
                    <div><p class="admin-stat-num"><?php echo $totalUsers; ?></p><p class="admin-stat-label">Total Users</p></div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon">📦</div>
                    <div><p class="admin-stat-num"><?php echo $totalListings; ?></p><p class="admin-stat-label">Total Listings</p></div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon">🔴</div>
                    <div><p class="admin-stat-num"><?php echo $totalLost; ?></p><p class="admin-stat-label">Lost Items</p></div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon">🟢</div>
                    <div><p class="admin-stat-num"><?php echo $totalFound; ?></p><p class="admin-stat-label">Found Items</p></div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon">📋</div>
                    <div><p class="admin-stat-num"><?php echo $totalClaims; ?></p><p class="admin-stat-label">Total Claims</p></div>
                </div>
                <div class="admin-stat-card" style="border-color:rgba(255,200,0,0.2);">
                    <div class="admin-stat-icon">⏳</div>
                    <div><p class="admin-stat-num" style="color:#ffc800;"><?php echo $pendingClaims; ?></p><p class="admin-stat-label">Pending Claims</p></div>
                </div>
                <div class="admin-stat-card" style="border-color:rgba(34,197,94,0.2);">
                    <div class="admin-stat-icon">✅</div>
                    <div><p class="admin-stat-num" style="color:#22c55e;"><?php echo $approvedClaims; ?></p><p class="admin-stat-label">Approved Claims</p></div>
                </div>
                <div class="admin-stat-card" style="border-color:rgba(255,100,0,0.2);">
                    <div class="admin-stat-icon">🚀</div>
                    <div><p class="admin-stat-num" style="color:orange;"><?php echo $boostRequests; ?></p><p class="admin-stat-label">Boost Requests</p></div>
                </div>
                <?php if($flaggedCount > 0): ?>
                <div class="admin-stat-card" style="border-color:rgba(255,165,0,0.2);">
                    <div class="admin-stat-icon">🚩</div>
                    <div><p class="admin-stat-num" style="color:orange;"><?php echo $flaggedCount; ?></p><p class="admin-stat-label">Flagged Listings</p></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="admin-charts-row">
                <div class="admin-chart-box">
                    <h3>Listings This Week</h3>
                    <canvas id="lineChart"></canvas>
                </div>
                <div class="admin-chart-box">
                    <h3>Listings by Category</h3>
                    <canvas id="doughnutChart"></canvas>
                </div>
                <div class="admin-chart-box">
                    <h3>Claims Breakdown</h3>
                    <canvas id="claimsChart"></canvas>
                </div>
            </div>

        </div>

        <div id="tab-claims" class="admin-tab">

            <div class="admin-page-header">
                <h1>All Claims</h1>
                <p><?php echo $totalClaims; ?> total — <?php echo $pendingClaims; ?> pending</p>
            </div>

            <div class="admin-search-bar">
                <input type="text" placeholder="🔍  Search by item or claimant..." oninput="searchTable(this, 'claimsTable')">
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table" id="claimsTable">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Claimant</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($row = mysqli_fetch_assoc($allClaims)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['claimant_name']); ?></td>
                            <td class="td-message"><?php echo htmlspecialchars($row['message']); ?></td>
                            <td>
                                <?php
                                    $s = $row['status'];
                                    if($s=='pending')   echo '<span class="claim-status status-pending">🟡 Pending</span>';
                                    if($s=='approved')  echo '<span class="claim-status status-approved">🟢 Approved</span>';
                                    if($s=='rejected')  echo '<span class="claim-status status-rejected">🔴 Rejected</span>';
                                    if($s=='collected') echo '<span class="claim-status status-collected">✅ Collected</span>';
                                ?>
                            </td>
                            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <?php if($row['status'] == 'pending'): ?>
                                    <a href="admin.php?action=approve&id=<?php echo $row['id']; ?>" class="admin-action-btn btn-approve" onclick="return confirm('Approve?')">Approve</a>
                                    <a href="admin.php?action=reject&id=<?php echo $row['id']; ?>"  class="admin-action-btn btn-reject"  onclick="return confirm('Reject?')">Reject</a>
                                <?php elseif($row['status'] == 'approved'): ?>
                                    <a href="admin.php?action=collected&id=<?php echo $row['id']; ?>" class="admin-action-btn btn-collected" onclick="return confirm('Mark as collected?')">Collected</a>
                                <?php else: ?>
                                    <span style="color:#333; font-size:13px;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <div id="tab-boosts" class="admin-tab">

            <div class="admin-page-header">
                <h1>Boost Requests</h1>
                <p><?php echo $boostRequests; ?> pending</p>
            </div>

            <?php
            $boostListings2 = mysqli_query($conn, "
                SELECT listings.*, users.username FROM listings
                JOIN users ON listings.user_id = users.id
                WHERE boost_requested=1 AND is_boosted=0
                ORDER BY listings.created_at DESC
            ");
            if(mysqli_num_rows($boostListings2) == 0): ?>
                <div class="admin-empty">No pending boost requests.</div>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr><th>Title</th><th>Owner</th><th>Type</th><th>Location</th><th>Date</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php while($row = mysqli_fetch_assoc($boostListings2)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><span class="type-badge type-<?php echo $row['listing_type']; ?>" style="position:static;"><?php echo strtoupper($row['listing_type']); ?></span></td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <a href="admin.php?boost_action=approve&id=<?php echo $row['id']; ?>" class="admin-action-btn btn-approve" onclick="return confirm('Approve boost?')">Approve</a>
                                    <a href="admin.php?boost_action=reject&id=<?php echo $row['id']; ?>"  class="admin-action-btn btn-reject"  onclick="return confirm('Reject boost?')">Reject</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>

        <div id="tab-flagged" class="admin-tab">

            <div class="admin-page-header">
                <h1>Flagged Listings</h1>
                <p>Listings reported by users as suspicious or inappropriate</p>
            </div>

            <?php if(mysqli_num_rows($flaggedListings) == 0): ?>
                <div class="admin-empty">✅ No flagged listings. Everything looks clean.</div>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr><th>Title</th><th>Owner</th><th>Reason</th><th>Type</th><th>Date</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php while($row = mysqli_fetch_assoc($flaggedListings)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td style="color:#ff9900; font-style:italic;"><?php echo htmlspecialchars($row['flag_reason'] ?? '—'); ?></td>
                                <td><span class="type-badge type-<?php echo $row['listing_type']; ?>" style="position:static;"><?php echo strtoupper($row['listing_type']); ?></span></td>
                                <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <a href="item.php?id=<?php echo $row['id']; ?>" class="admin-action-btn" style="background:#222; border-color:#333;">View</a>
                                    <a href="admin.php?unflag=<?php echo $row['id']; ?>" class="admin-action-btn btn-approve" onclick="return confirm('Clear this flag?')">Clear Flag</a>
                                    <a href="admin.php?delete_listing=<?php echo $row['id']; ?>" class="admin-action-btn btn-reject" onclick="return confirm('Delete this listing permanently?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>

        <div id="tab-listings" class="admin-tab">

            <div class="admin-page-header">
                <h1>All Listings</h1>
                <p>Showing latest 50</p>
            </div>

            <div class="admin-search-bar">
                <input type="text" placeholder="🔍  Search by title, owner or location..." oninput="searchTable(this, 'listingsTable')">
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table" id="listingsTable">
                    <thead>
                        <tr><th>Title</th><th>Owner</th><th>Type</th><th>Category</th><th>Location</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php while($row = mysqli_fetch_assoc($allListings)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><span class="type-badge type-<?php echo $row['listing_type']; ?>" style="position:static;"><?php echo strtoupper($row['listing_type']); ?></span></td>
                            <td><?php echo ucfirst($row['category'] ?? 'item'); ?></td>
                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                            <td>
                                <?php 
                                    if($row['is_archived'] == 1) {
                                        echo '<span style="color:#a3a3a3; font-weight:bold;">📦 Archived</span>';
                                    } elseif($row['is_boosted']) {
                                        echo '<span style="color:gold;">⭐ Boosted</span>';
                                    } else {
                                        echo '<span style="color:#22c55e;">🟢 Active</span>';
                                    }
                                ?>
                            </td>
                            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <a href="item.php?id=<?php echo $row['id']; ?>" class="admin-action-btn" style="background:#222; border-color:#333;">View</a>
                                
                                <?php if($row['is_archived'] == 0): ?>
                                    <a href="admin.php?archive_listing=<?php echo $row['id']; ?>" class="admin-action-btn" style="background:#3a2a1e; color:#fb923c; border-color:#5a3a2e;" onclick="return confirm('Archive this listing?')">Archive</a>
                                <?php else: ?>
                                    <a href="admin.php?restore_listing=<?php echo $row['id']; ?>" class="admin-action-btn" style="background:#1e3a1e; color:#4ade80; border-color:#2e5a2e;" onclick="return confirm('Restore this listing back to active index?')">Restore</a>
                                <?php endif; ?>

                                <a href="admin.php?force_collect=<?php echo $row['id']; ?>" class="admin-action-btn btn-collected" onclick="return confirm('Force mark as collected?')">Collect</a>
                                <a href="admin.php?delete_listing=<?php echo $row['id']; ?>" class="admin-action-btn btn-reject" onclick="return confirm('Delete permanently?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <div id="tab-users" class="admin-tab">

            <div class="admin-page-header">
                <h1>All Users</h1>
                <p><?php echo $totalUsers; ?> registered users</p>
            </div>

            <div class="admin-search-bar">
                <input type="text" placeholder="🔍  Search by username or email..." oninput="searchTable(this, 'usersTable')">
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table" id="usersTable">
                    <thead>
                        <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php while($row = mysqli_fetch_assoc($allUsers)): ?>
                        <tr>
                            <td style="color:#555;">#<?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td style="color:#888;"><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <?php if($row['role'] == 'admin'): ?>
                                    <span style="color:gold; font-size:13px; font-weight:700;">👑 Admin</span>
                                <?php else: ?>
                                    <span style="color:#666; font-size:13px;">User</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <?php if($row['role'] != 'admin'): ?>
                                    <a href="admin.php?delete_user=<?php echo $row['id']; ?>" class="admin-action-btn btn-reject" onclick="return confirm('Delete this user?')">Delete</a>
                                <?php else: ?>
                                    <span style="color:#333; font-size:13px;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <div id="tab-announcements" class="admin-tab">

            <div class="admin-page-header">
                <h1>Announcements</h1>
                <p>Post site-wide notices that appear on the homepage for all users</p>
            </div>

            <div class="announcement-form-box">
                <h3>Post New Announcement</h3>
                <form method="POST">
                    <textarea name="announcement_msg" placeholder="e.g. System maintenance on Sunday 10PM — 12AM. Listings may be temporarily unavailable." rows="3" required></textarea>
                    <button type="submit" name="add_announcement" class="auth-submit-btn" style="margin-top:12px;">📢 Post Announcement</button>
                </form>
            </div>

            <div class="admin-table-wrap" style="margin-top:24px;">
                <table class="admin-table">
                    <thead>
                        <tr><th>Message</th><th>Status</th><th>Posted</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php
                    $announcements2 = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC");
                    if(mysqli_num_rows($announcements2) == 0):
                    ?>
                        <tr><td colspan="4" style="text-align:center; color:#444; padding:30px;">No announcements yet.</td></tr>
                    <?php else: ?>
                    <?php while($row = mysqli_fetch_assoc($announcements2)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['message']); ?></td>
                            <td>
                                <?php if($row['is_active']): ?>
                                    <span class="claim-status status-approved">🟢 Active</span>
                                <?php else: ?>
                                    <span class="claim-status status-rejected">⚫ Hidden</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d M Y, g:i A', strtotime($row['created_at'])); ?></td>
                            <td>
                                <a href="admin.php?toggle_announcement=<?php echo $row['id']; ?>" class="admin-action-btn btn-approve">
                                    <?php echo $row['is_active'] ? 'Hide' : 'Show'; ?>
                                </a>
                                <a href="admin.php?delete_announcement=<?php echo $row['id']; ?>" class="admin-action-btn btn-reject" onclick="return confirm('Delete this announcement?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
        
        <div id="tab-payments" class="admin-tab">

            <div class="admin-page-header">
                <h1>Payment History</h1>
                <p>All boost and reward payments made on the platform</p>
            </div>

            <?php
            $allPayments = mysqli_query($conn, "
                SELECT payments.*, users.username, listings.title as listing_title
                FROM payments
                JOIN users    ON payments.user_id    = users.id
                JOIN listings ON payments.listing_id = listings.id
                ORDER BY payments.created_at DESC
            ");

            $totalRevenue   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM payments WHERE status='paid'"))['t'] ?? 0;
            $totalBoostPay = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM payments WHERE status='paid' AND type='boost'"))['t'] ?? 0;
            $totalRewardPay= mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM payments WHERE status='paid' AND type='reward'"))['t'] ?? 0;
            $totalCount    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM payments"))['c'];
            ?>

            <div class="admin-stats-grid" style="margin-bottom:24px;">
                <div class="admin-stat-card" style="border-color:rgba(34,197,94,0.2);">
                    <div class="admin-stat-icon">💰</div>
                    <div>
                        <p class="admin-stat-num" style="color:#22c55e;">RM<?php echo number_format($totalRevenue, 2); ?></p>
                        <p class="admin-stat-label">Total Revenue</p>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon">🚀</div>
                    <div>
                        <p class="admin-stat-num">RM<?php echo number_format($totalBoostPay, 2); ?></p>
                        <p class="admin-stat-label">Boost Revenue</p>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon">🏆</div>
                    <div>
                        <p class="admin-stat-num">RM<?php echo number_format($totalRewardPay, 2); ?></p>
                        <p class="admin-stat-label">Reward Payments</p>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon">📋</div>
                    <div>
                        <p class="admin-stat-num"><?php echo $totalCount; ?></p>
                        <p class="admin-stat-label">Total Transactions</p>
                    </div>
                </div>
            </div>

            <div class="admin-search-bar">
                <input type="text" placeholder="🔍  Search by user, listing or reference..." oninput="searchTable(this, 'paymentsTable')">
            </div>

            <?php if(mysqli_num_rows($allPayments) == 0): ?>
                <div class="admin-empty">No payments yet.</div>
            <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table" id="paymentsTable">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>User</th>
                            <th>Listing</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($row = mysqli_fetch_assoc($allPayments)): ?>
                        <tr>
                            <td style="font-family:monospace; color:#888; font-size:12px;"><?php echo htmlspecialchars($row['reference']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td class="td-message"><?php echo htmlspecialchars($row['listing_title']); ?></td>
                            <td>
                                <?php echo $row['type'] == 'boost' ? '🚀 Boost' : '🏆 Reward'; ?>
                            </td>
                            <td style="font-weight: bold; color: #22c55e;">RM<?php echo number_format($row['amount'], 2); ?></td>
                            <td><?php echo strtoupper(htmlspecialchars($row['payment_method'] ?? 'Stripe')); ?></td>
                            <td>
                                <?php echo $row['status'] == 'paid' ? '<span class="claim-status status-approved">Paid</span>' : '<span class="claim-status status-rejected">Failed</span>'; ?>
                            </td>
                            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

        </div>

        <div id="tab-activity" class="admin-tab">
            <div class="admin-page-header">
                <h1>Recent Activity Logs</h1>
                <p>System structural movements track registry</p>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Type</th><th>Event Context Log</th><th>Timestamp</th></tr></thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($recentActivity)): ?>
                        <tr>
                            <td><span class="claim-status status-pending" style="text-transform:uppercase; font-size:11px;"><?php echo $row['type']; ?></span></td>
                            <td><?php echo htmlspecialchars($row['detail']); ?></td>
                            <td style="color:#777; font-size:13px;"><?php echo date('d M Y, g:i A', strtotime($row['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
function showTab(tabId, el) {
    document.querySelectorAll('.admin-tab').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.admin-nav-link').forEach(link => link.classList.remove('active'));
    
    document.getElementById('tab-' + tabId).classList.add('active');
    if(el) el.classList.add('active');
    window.location.hash = tabId;
}

function searchTable(input, tableId) {
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}

// Keep active menu option synchronized across direct anchors
window.addEventListener('DOMContentLoaded', () => {
    const hash = window.location.hash.replace('#', '');
    if(hash) {
        const link = document.querySelector(`[onclick*="showTab('${hash}'"]`);
        if(link) showTab(hash, link);
    }
});
</script>

<script>
const lineCtx = document.getElementById('lineChart')?.getContext('2d');
if(lineCtx) {
    new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chartLabels); ?>,
            datasets: [
                { label: 'Lost', data: <?php echo json_encode($chartLost); ?>, borderColor: '#ff4444', tension: 0.3 },
                { label: 'Found', data: <?php echo json_encode($chartFound); ?>, borderColor: '#22c55e', tension: 0.3 }
            ]
        },
        options: { responsive: true, plugins: { legend: { labels: { color: '#fff' } } } }
    });
}

const doughnutCtx = document.getElementById('doughnutChart')?.getContext('2d');
if(doughnutCtx) {
    new Chart(doughnutCtx, {
        type: 'doughnut',
        data: {
            labels: ['Items', 'Pets', 'Vehicles', 'Persons'],
            datasets: [{
                data: [<?php echo "$catItems, $catPets, $catVehicle, $catPerson"; ?>],
                backgroundColor: ['#3b82f6', '#ec4899', '#eab308', '#a855f7']
            }]
        },
        options: { responsive: true, plugins: { legend: { labels: { color: '#fff' } } } }
    });
}

const claimsCtx = document.getElementById('claimsChart')?.getContext('2d');
if(claimsCtx) {
    new Chart(claimsCtx, {
        type: 'bar',
        data: {
            labels: ['Pending', 'Approved', 'Rejected'],
            datasets: [{
                label: 'Claims Status',
                data: [<?php echo "$pendingClaims, $approvedClaims, $rejectedClaims"; ?>],
                backgroundColor: ['#eab308', '#22c55e', '#ef4444']
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
}
</script>

</body>
</html>