<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
$role    = $_SESSION['role'] ?? null;

$isLoggedIn = $user_id ? true : false;
$isAdmin    = ($role === 'admin');
?>

<nav class="navbar">

    <!-- LEFT: Logo -->
    <a href="index.php" class="logo-container">
        <img src="images/bofindomb.jpeg" class="logo-img">
        <div class="logo">LOST & <span>FOUND</span></div>
    </a>

    <!-- CENTER: Main nav links (desktop) -->
    <div class="nav-center">
        <a href="browse.php" class="nav-link">Browse</a>

        <?php if($isLoggedIn && !$isAdmin): ?>
            <a href="lostitem.php" class="nav-link">Report Lost</a>
            <a href="founditem.php" class="nav-link">Report Found</a>
        <?php endif; ?>

        <?php if($isAdmin): ?>
            <a href="admin.php" class="nav-link">Admin Panel</a>
        <?php endif; ?>
    </div>

    <!-- RIGHT: Auth / Avatar -->
    <div class="nav-right">

        <?php if($isLoggedIn): ?>
		
			<!-- NOTIFICATION BELL -->
            <div class="notif-wrap" id="notifWrap">
                <button class="notif-btn" onclick="toggleNotifMenu()" aria-label="Notifications">
                    🔔
                    <span class="notif-badge" id="notifBadge" style="display:none;">0</span>
                </button>
                <div class="notif-dropdown" id="notifDropdown">
                    <div class="notif-header">
                        <span>Notifications</span>
                        <button onclick="markAllRead()" class="notif-mark-read">Mark all read</button>
                    </div>
                    <div id="notifList">
                        <p class="notif-empty">No new notifications</p>
                    </div>
                </div>
            </div>

            <div class="avatar-wrap" onclick="toggleAvatarMenu()">
                <div class="avatar-circle">
					<?php
				$profilePic = '';
				if (isset($conn) && $conn instanceof mysqli) {
					$stmt = $conn->prepare('SELECT profile_pic FROM users WHERE id = ?');
					if ($stmt) {
						$stmt->bind_param('i', $_SESSION['user_id']);
						$stmt->execute();
						$result = $stmt->get_result();
						$navUser = $result->fetch_assoc();
						$stmt->close();
						$profilePic = $navUser['profile_pic'] ?? '';
					}
				}
				if(!empty($profilePic)): ?>
					<img src="<?php echo htmlspecialchars($profilePic, ENT_QUOTES, 'UTF-8'); ?>" style="width:32px; height:32px; object-fit:cover; border-radius:50%; display:block; flex-shrink:0;">
				<?php endif; ?>
				</div>
                <span class="avatar-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <span style="opacity:0.6; font-size:11px;">▼</span>
            </div>

            <div id="avatarMenu" class="avatar-menu">
				<?php if(!$isAdmin): ?>
					<a href="profile.php">👤 Profile</a>
					<a href="myclaims.php">📋 My Claims</a>
					<a href="incoming_claims.php">📥 Incoming Claims</a>
					<a href="rewards.php">🏆 Rewards</a>
				<?php else: ?>
					<a href="profile.php">👤 My Profile</a>
					<a href="admin.php">⚙️ Admin Panel</a>
				<?php endif; ?>
				<div class="avatar-divider"></div>
				<a href="logout.php" style="color:#ff4444;">🚪 Logout</a>
			</div>

        <?php else: ?>

            <a href="login.php" class="nav-btn-outline">Sign In</a>
            <a href="signup.php" class="nav-btn-solid">Sign Up</a>

        <?php endif; ?>

    </div>

    <!-- Mobile hamburger -->
    <div class="menu-btn" onclick="toggleMenu()">
        <span></span><span></span><span></span>
    </div>

    <!-- Mobile dropdown -->
    <div id="navLinks" class="nav-links">
        <a href="browse.php">Browse</a>
        <?php if($isLoggedIn && !$isAdmin): ?>
            <a href="lostitem.php">Report Lost</a>
            <a href="founditem.php">Report Found</a>
            <a href="profile.php">Profile</a>
            <a href="myclaims.php">My Claims</a>
            <a href="incoming_claims.php">Incoming Claims</a>
            <a href="rewards.php">Rewards</a>
        <?php elseif($isAdmin): ?>
            <a href="admin.php">Admin Panel</a>
        <?php else: ?>
            <a href="login.php">Sign In</a>
            <a href="signup.php">Sign Up</a>
        <?php endif; ?>
        <?php if($isLoggedIn): ?>
            <a href="logout.php" style="color:#ff4444;">Logout</a>
        <?php endif; ?>
    </div>

</nav>

<script>
function toggleMenu() {
    document.getElementById("navLinks").classList.toggle("active");
}

function toggleAvatarMenu() {
    document.getElementById("avatarMenu").classList.toggle("active");
}

document.addEventListener("click", function(e) {
    const mobileMenu = document.getElementById("navLinks");
    const avatarMenu = document.getElementById("avatarMenu");
    const menuBtn    = document.querySelector(".menu-btn");
    const avatarWrap = document.querySelector(".avatar-wrap");

    if (mobileMenu && menuBtn && !mobileMenu.contains(e.target) && !menuBtn.contains(e.target)) {
        mobileMenu.classList.remove("active");
    }
    if (avatarMenu && avatarWrap && !avatarWrap.contains(e.target)) {
        avatarMenu.classList.remove("active");
    }
});

// ---- Notification bell ----
function toggleNotifMenu() {
    document.getElementById('notifDropdown').classList.toggle('active');
    document.getElementById('avatarMenu').classList.remove('active');
    if (document.getElementById('notifDropdown').classList.contains('active')) {
        markAllRead();
    }
}

function markAllRead() {
    fetch('notifications_read.php').then(() => {
        document.getElementById('notifBadge').style.display = 'none';
        document.getElementById('notifBadge').textContent = '0';
    });
}

function pollNotifications() {
    fetch('notifications_poll.php')
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notifBadge');
            const list  = document.getElementById('notifList');
            if (!badge || !list) return;

            if (data.count > 0) {
                badge.style.display = 'flex';
                badge.textContent   = data.count;
                list.innerHTML = data.items.map(n => `
                    <a href="${n.link || '#'}" class="notif-item">
                        <span class="notif-msg">${n.message}</span>
                        <span class="notif-time">${formatTime(n.created_at)}</span>
                    </a>`).join('');
            } else {
                badge.style.display = 'none';
                list.innerHTML = '<p class="notif-empty">No new notifications</p>';
            }
        });
}

function formatTime(dateStr) {
    const d = new Date(dateStr.replace(' ', 'T'));
    const diff = Math.floor((Date.now() - d) / 1000);
    if (diff < 60)   return diff + 's ago';
    if (diff < 3600) return Math.floor(diff/60) + 'm ago';
    return Math.floor(diff/3600) + 'h ago';
}

// Also close notif dropdown on outside click
document.addEventListener('click', function(e) {
    const notifWrap = document.getElementById('notifWrap');
    const notifDD   = document.getElementById('notifDropdown');
    if (notifWrap && notifDD && !notifWrap.contains(e.target)) {
        notifDD.classList.remove('active');
    }
});

// Poll every 10 seconds
if (document.getElementById('notifBadge')) {
    pollNotifications();
    setInterval(pollNotifications, 10000);
}


// Highlight active nav link
(function() {
    const path = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.getAttribute('href') === path) {
            link.style.color = 'red';
        }
    });
})();


// Highlight active nav link based on current page
(function() {
    const page = window.location.pathname.split('/').pop() || 'index.php';
    // Desktop nav links
    document.querySelectorAll('.nav-center .nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href && href === page) {
            link.classList.add('active');
        }
    });
    // Mobile nav links
    document.querySelectorAll('.nav-links a').forEach(link => {
        const href = link.getAttribute('href');
        if (href && href === page) {
            link.style.color = 'red';
            link.style.fontWeight = '700';
        }
    });
    // Avatar menu links (profile, my claims, etc.)
    document.querySelectorAll('.avatar-menu a').forEach(link => {
        const href = link.getAttribute('href');
        if (href && href === page) {
            link.style.color = 'red';
            link.style.fontWeight = '700';
        }
    });
})();


</script>