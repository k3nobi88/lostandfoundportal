<?php require_once 'config.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Lost & Found Portal</title>
    <link rel="stylesheet" href="style.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include 'navbar.php'; ?>

<?php
$announcement = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM announcements WHERE is_active=1 ORDER BY created_at DESC LIMIT 1"));
if($announcement):
?>
<div class="announcement-bar">
    <span>📢</span>
    <div class="announcement-ticker">
        <?php echo htmlspecialchars($announcement['message']); ?>
        &nbsp;&nbsp;&nbsp;•&nbsp;&nbsp;&nbsp;
        <?php echo htmlspecialchars($announcement['message']); ?>
		&nbsp;&nbsp;&nbsp;•&nbsp;&nbsp;&nbsp;
        <?php echo htmlspecialchars($announcement['message']); ?>
		&nbsp;&nbsp;&nbsp;•&nbsp;&nbsp;&nbsp;
        <?php echo htmlspecialchars($announcement['message']); ?>
		&nbsp;&nbsp;&nbsp;•&nbsp;&nbsp;&nbsp;
        <?php echo htmlspecialchars($announcement['message']); ?>
		&nbsp;&nbsp;&nbsp;•&nbsp;&nbsp;&nbsp;
        <?php echo htmlspecialchars($announcement['message']); ?>
    </div>
</div>
<?php endif; ?>

<!-- ====== HERO ====== -->
<div class="hero">

    <canvas id="heroCanvas"></canvas>
    <div class="hero-overlay"></div>

    <div class="hero-content-wrap">

        <!-- LEFT: Text + CTA -->
        <div class="hero-content">

            <div class="hero-tag">🇲🇾 Malaysia's Lost & Found Community</div>

            <h1>Lost Something?<br><span class="hero-accent">We'll Help You Find It.</span></h1>

            <p>Report lost items, browse found listings, and reconnect with what matters most — all in one place.</p>

            <div class="hero-btns">
                <a href="lostitem.php" class="btn">📢 Report Lost Item</a>
                <a href="browse.php"   class="btn btn-outline">🔍 Browse Listings</a>
            </div>

            <div class="hero-scroll-hint">↓ Scroll to explore</div>

        </div>

        <!-- RIGHT: Sponsored cards ticker -->
        <div class="hero-cards-wrap">

            <p class="hero-cards-label">🔥 Sponsored Listings</p>

            <div class="hero-cards-ticker">
                <div class="hero-cards-track" id="heroTrack">

                <?php
                $heroItems = mysqli_query($conn, "SELECT * FROM listings WHERE is_boosted=1 ORDER BY created_at DESC LIMIT 6");
                $heroRows  = [];
                while($r = mysqli_fetch_assoc($heroItems)) $heroRows[] = $r;

                // If less than 3 boosted, fill with recent listings
                if(count($heroRows) < 3){
                    $fill = mysqli_query($conn, "SELECT * FROM listings ORDER BY created_at DESC LIMIT 6");
                    $heroRows = [];
                    while($r = mysqli_fetch_assoc($fill)) $heroRows[] = $r;
                }

                // Duplicate for seamless loop
                $allRows = array_merge($heroRows, $heroRows);
                foreach($allRows as $row):
                ?>
                    <a href="item.php?id=<?php echo $row['id']; ?>" class="hero-mini-card">
                        <?php if(!empty($row['image'])): ?>
                            <img src="<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                        <?php else: ?>
                            <img src="images/default.jpg" alt="No image">
                        <?php endif; ?>
                        <div class="hero-mini-card-body">
                            <div class="type-badge type-<?php echo $row['listing_type']; ?>" style="position:static; font-size:10px; padding:3px 8px; display:inline-block; margin-bottom:6px;">
                                <?php echo strtoupper($row['listing_type']); ?>
                            </div>
                            <p class="hero-mini-title"><?php echo htmlspecialchars($row['title']); ?></p>
                            <p class="hero-mini-location">📍 <?php echo htmlspecialchars($row['location']); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>

                </div>
            </div>

        </div>

    </div>

</div>

<!-- ====== HOW IT WORKS ====== -->
<div class="section">

    <h2 class="section-title">How It Works</h2>
    <p class="section-sub">Three simple steps to reunite with your belongings</p>

    <div class="steps-grid">

        <div class="step-card">
            <div class="step-number">01</div>
            <div class="step-icon">📝</div>
            <h3>Report</h3>
            <p>Submit a detailed report of your lost or found item with photos, location, and description.</p>
        </div>

        <div class="step-card">
            <div class="step-number">02</div>
            <div class="step-icon">🔎</div>
            <h3>Browse & Match</h3>
            <p>Browse listings and find matches. Our categories cover items, pets, vehicles, and missing persons.</p>
        </div>

        <div class="step-card">
            <div class="step-number">03</div>
            <div class="step-icon">🤝</div>
            <h3>Claim & Reunite</h3>
            <p>Submit a claim, chat with the reporter, and arrange a safe handover to get your item back.</p>
        </div>

    </div>

</div>

<!-- ====== WHY US ====== -->
<div class="section">

    <h2 class="section-title">Why Use Our Portal?</h2>
    <p class="section-sub">Everything you need to recover what's lost — in one place</p>

    <div class="why-grid">

        <div class="why-card">
            <div class="why-icon">🆓</div>
            <h3>Completely Free</h3>
            <p>Reporting and browsing lost & found items costs nothing. No hidden fees, no subscriptions.</p>
        </div>

        <div class="why-card">
            <div class="why-icon">💬</div>
            <h3>Direct Chat</h3>
            <p>Communicate directly with the reporter through our built-in chat system — no middleman needed.</p>
        </div>

        <div class="why-card">
            <div class="why-icon">📍</div>
            <h3>Location Tracking</h3>
            <p>Every report is tagged with a location so you can find items lost near you faster.</p>
        </div>

        <div class="why-card">
            <div class="why-icon">🐾</div>
            <h3>All Categories</h3>
            <p>From personal items to pets, vehicles, and missing persons — we cover every type of report.</p>
        </div>

        <div class="why-card">
            <div class="why-icon">🏆</div>
            <h3>Rewards System</h3>
            <p>Offer a reward for your lost item or earn one by returning something found.</p>
        </div>

        <div class="why-card">
            <div class="why-icon">🔒</div>
            <h3>Safe & Secure</h3>
            <p>Your contact details stay private until a claim is approved. You control who sees your info.</p>
        </div>

    </div>

</div>

<!-- ====== STATS ====== -->
<div class="stats-banner">
    <?php
    $totalItems  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM listings"))['c'];
    $totalUsers  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
    $totalClaims = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM claims WHERE status='collected'"))['c'];
    ?>

    <div class="stat-item">
        <span class="stat-num"><?php echo $totalItems; ?>+</span>
        <span class="stat-label">Items Reported</span>
    </div>

    <div class="stat-divider"></div>

    <div class="stat-item">
        <span class="stat-num"><?php echo $totalUsers; ?>+</span>
        <span class="stat-label">Registered Users</span>
    </div>

    <div class="stat-divider"></div>

    <div class="stat-item">
        <span class="stat-num"><?php echo $totalClaims; ?>+</span>
        <span class="stat-label">Items Reunited</span>
    </div>

    <div class="stat-divider"></div>

    <div class="stat-item">
        <span class="stat-num">24/7</span>
        <span class="stat-label">Always Available</span>
    </div>

</div>

<!-- ====== CATEGORIES ====== -->
<div class="section">

    <h2 class="section-title">Browse by Category</h2>
    <p class="section-sub">Find what you're looking for faster</p>

    <div class="category-grid">
        <a href="browse.php?cat=item" class="category-card">
            <div class="category-icon">🎒</div>
            <span>Items</span>
        </a>
        <a href="browse.php?cat=pet" class="category-card">
            <div class="category-icon">🐾</div>
            <span>Pets</span>
        </a>
        <a href="browse.php?cat=vehicle" class="category-card">
            <div class="category-icon">🚗</div>
            <span>Vehicles</span>
        </a>
        <a href="browse.php?cat=person" class="category-card">
            <div class="category-icon">👤</div>
            <span>Persons</span>
        </a>
    </div>

</div>

<!-- ====== RECENT LISTINGS ====== -->
<div class="section">

    <div class="section-header-row">
        <div>
            <h2 class="section-title" style="margin-bottom:5px;">Recent Listings</h2>
            <p class="section-sub" style="margin-bottom:0;">Latest reports from the community</p>
        </div>
        <a href="browse.php" class="btn-text-link">View All →</a>
    </div>

    <div class="card-container">
    <?php
    $recent = mysqli_query($conn, "SELECT * FROM listings ORDER BY created_at DESC LIMIT 8");
    while($row = mysqli_fetch_assoc($recent)):
    ?>
        <a href="item.php?id=<?php echo $row['id']; ?>" style="text-decoration:none; color:white;">
            <div class="card">

                <?php if(!empty($row['image'])): ?>
                    <img src="<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                <?php else: ?>
                    <img src="images/default.jpg" alt="No image">
                <?php endif; ?>

                <?php if($row['is_boosted'] == 1): ?>
                    <div class="badge">⭐ SPONSORED</div>
                <?php endif; ?>

                <div class="type-badge type-<?php echo $row['listing_type']; ?>">
                    <?php echo strtoupper($row['listing_type']); ?>
                </div>

                <div class="card-content">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p>📍 <?php echo htmlspecialchars($row['location']); ?></p>
                </div>

            </div>
        </a>
    <?php endwhile; ?>
    </div>

</div>

<!-- ====== SPONSORED ====== -->
<?php
$boosted = mysqli_query($conn, "SELECT * FROM listings WHERE is_boosted=1 ORDER BY created_at DESC LIMIT 4");
if(mysqli_num_rows($boosted) > 0):
?>
<div class="section section-dark">

    <h2 class="section-title">🔥 Sponsored Listings</h2>
    <p class="section-sub">Boosted by our community members</p>

    <div class="card-container">
    <?php while($row = mysqli_fetch_assoc($boosted)): ?>
        <a href="item.php?id=<?php echo $row['id']; ?>" style="text-decoration:none; color:white;">
            <div class="card">
                <?php if(!empty($row['image'])): ?>
                    <img src="<?php echo $row['image']; ?>">
                <?php else: ?>
                    <img src="images/default.jpg">
                <?php endif; ?>
                <div class="badge">⭐ SPONSORED</div>
                <div class="card-content">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p>📍 <?php echo htmlspecialchars($row['location']); ?></p>
                </div>
            </div>
        </a>
    <?php endwhile; ?>
    </div>

</div>
<?php endif; ?>

<!-- ====== CTA BANNER ====== -->
<div class="cta-banner">
    <h2>Found something that doesn't belong to you?</h2>
    <p>Be a hero. Report it now and help someone get their belongings back.</p>
    <div class="hero-btns">
        <a href="founditem.php" class="btn">Report a Found Item</a>
        <a href="browse.php" class="btn btn-outline">Browse Lost Items</a>
    </div>
</div>

<!-- ====== COOKIE POPUP ====== -->
<div class="cookie-popup" id="cookiePopup">
    <div class="cookie-content">
        <h3>🍪 Cookies Consent</h3>
        <p>This website uses cookies to improve user experience and website functionality.</p>
        <button onclick="acceptCookies()">Accept</button>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
function acceptCookies() {
    localStorage.setItem("cookiesAccepted", "true");
    document.getElementById("cookiePopup").style.display = "none";
}
window.onload = function () {
    if(localStorage.getItem("cookiesAccepted") === "true") {
        document.getElementById("cookiePopup").style.display = "none";
    }
};
</script>

<script>
// Animated particle canvas background
const canvas = document.getElementById('heroCanvas');
const ctx = canvas.getContext('2d');

let particles = [];
const COUNT = 80;

function resize() {
    canvas.width  = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;
}

function randomParticle() {
    return {
        x:     Math.random() * canvas.width,
        y:     Math.random() * canvas.height,
        r:     Math.random() * 1.5 + 0.3,
        speedX: (Math.random() - 0.5) * 0.4,
        speedY: (Math.random() - 0.5) * 0.4,
        alpha:  Math.random() * 0.5 + 0.1
    };
}

function init() {
    resize();
    particles = Array.from({ length: COUNT }, randomParticle);
}

function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // draw connection lines
    for (let i = 0; i < particles.length; i++) {
        for (let j = i + 1; j < particles.length; j++) {
            const dx = particles[i].x - particles[j].x;
            const dy = particles[i].y - particles[j].y;
            const dist = Math.sqrt(dx * dx + dy * dy);
            if (dist < 130) {
                ctx.beginPath();
                ctx.strokeStyle = `rgba(180, 0, 0, ${0.08 * (1 - dist / 130)})`;
                ctx.lineWidth = 0.5;
                ctx.moveTo(particles[i].x, particles[i].y);
                ctx.lineTo(particles[j].x, particles[j].y);
                ctx.stroke();
            }
        }
    }

    // draw dots
    particles.forEach(p => {
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(255, 80, 80, ${p.alpha})`;
        ctx.fill();

        p.x += p.speedX;
        p.y += p.speedY;

        if (p.x < 0 || p.x > canvas.width)  p.speedX *= -1;
        if (p.y < 0 || p.y > canvas.height) p.speedY *= -1;
    });

    requestAnimationFrame(draw);
}

init();
draw();
window.addEventListener('resize', init);
</script>

</body>
</html>