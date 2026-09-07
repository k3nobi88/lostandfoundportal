<?php include 'config.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Archive Vault – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Contextual archive status badges styles */
        .reason-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            margin-top: 5px;
        }
        .reason-collected { background: #1e3a1e; color: #4ade80; }
        .reason-expired { background: #3a2a1e; color: #fb923c; }
        .reason-manual { background: #2a2a2a; color: #a3a3a3; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="browse-hero" style="background: linear-gradient(135deg, #111 0%, #222 100%);">
    <h1>📦 Historical Archive Vault</h1>
    <p>Transparency registry for closed, resolved, or older historical listings</p>
</div>

<div class="container">

    <div class="filter-bar">
        <input type="text" id="searchInput" placeholder="🔍 Search archived items by title or location..." oninput="filterCards()">

        <div class="filter-row">
            <div class="filter-group">
                <span class="filter-label">Type</span>
                <div class="filter-tabs" id="typeFilter">
                    <button class="filter-tab active" data-type="all" onclick="setType(this, 'all')">All</button>
                    <button class="filter-tab" data-type="lost" onclick="setType(this, 'lost')">🔴 Lost</button>
                    <button class="filter-tab" data-type="found" onclick="setType(this, 'found')">🟢 Found</button>
                </div>
            </div>

            <div class="filter-group">
                <span class="filter-label">Category</span>
                <div class="filter-tabs" id="catFilter">
                    <button class="filter-tab active" data-cat="all" onclick="setCat(this, 'all')">All</button>
                    <button class="filter-tab" data-cat="item" onclick="setCat(this, 'item')">🎒 Items</button>
                    <button class="filter-tab" data-cat="pet" onclick="setCat(this, 'pet')">🐾 Pets</button>
                    <button class="filter-tab" data-cat="vehicle" onclick="setCat(this, 'vehicle')">🚗 Vehicles</button>
                    <button class="filter-tab" data-cat="person" onclick="setCat(this, 'person')">👤 Persons</button>
                </div>
            </div>
        </div>
    </div>

    <div class="results-bar">
        <p class="results-count" id="resultsCount">Loading archive data...</p>
    </div>

    <div style="text-align: left; margin-bottom: 20px;">
        <a href="browse.php" style="color: #bbb; font-size: 14px; text-decoration: none; font-weight: 500;">
            ← Back to Active Listings
        </a>
    </div>

    <div class="card-container" id="cardGrid">

    <?php
    // STEP 4: FETCH ARCHIVED DATA ONLY
    $sql = "SELECT * FROM listings WHERE is_archived = 1 ORDER BY archived_at DESC";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 0):
        echo '<p style="color:#aaa; padding: 20px 0;">The archive vault is currently empty.</p>';
    endif;

    while($row = mysqli_fetch_assoc($result)):
        $category = !empty($row['category']) ? $row['category'] : 'item';
    ?>

        <a href="item.php?id=<?php echo $row['id']; ?>"
           class="card-link"
           data-type="<?php echo $row['listing_type']; ?>"
           data-cat="<?php echo $category; ?>"
           data-title="<?php echo strtolower(htmlspecialchars($row['title'])); ?>"
           data-location="<?php echo strtolower(htmlspecialchars($row['location'])); ?>">

            <div class="card" style="opacity: 0.75;"> <?php if(!empty($row['image'])): ?>
                    <img src="<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                <?php else: ?>
                    <img src="images/default.jpg" alt="No image">
                <?php endif; ?>

                <div class="type-badge type-<?php echo $row['listing_type']; ?>">
                    <?php echo strtoupper($row['listing_type']); ?>
                </div>

                <div class="card-content">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p>📍 <?php echo htmlspecialchars($row['location']); ?></p>
                    
                    <?php if($row['archived_reason'] == 'collected'): ?>
                        <span class="reason-badge reason-collected">🤝 Reunited / Collected</span>
                    <?php elseif($row['archived_reason'] == '60_days_no_resolution'): ?>
                        <span class="reason-badge reason-expired">⏳ Expired (60+ Days)</span>
                    <?php else: ?>
                        <span class="reason-badge reason-manual">📦 Archived</span>
                    <?php endif; ?>

                    <p class="card-date" style="margin-top:8px;">Archived: <?php echo date('d M Y', strtotime($row['archived_at'])); ?></p>
                </div>

            </div>
        </a>

    <?php endwhile; ?>

    </div>

    <p class="no-results" id="noResults" style="display:none;">
        😕 No historical records fit your search criteria.
    </p>

</div>

<?php include 'footer.php'; ?>

<script>
let activeType = 'all';
let activeCat  = 'all';

function setType(btn, type) {
    activeType = type;
    document.querySelectorAll('#typeFilter .filter-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    filterCards();
}

function setCat(btn, cat) {
    activeCat = cat;
    document.querySelectorAll('#catFilter .filter-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    filterCards();
}

function filterCards() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const links = document.querySelectorAll(".card-link");
    let visible = 0;

    links.forEach(link => {
        const title    = link.getAttribute("data-title");
        const location = link.getAttribute("data-location");
        const type     = link.getAttribute("data-type");
        const cat      = link.getAttribute("data-cat");

        const matchSearch = title.includes(input) || location.includes(input);
        const matchType   = (activeType === 'all' || activeType === type);
        const matchCat    = (activeCat  === 'all' || activeCat  === cat);

        if(matchSearch && matchType && matchCat){
            link.style.display = 'block';
            visible++;
        } else {
            link.style.display = 'none';
        }
    });

    document.getElementById("resultsCount").textContent = visible + ' archived listing' + (visible !== 1 ? 's' : '') + ' found';
    document.getElementById("noResults").style.display = visible === 0 ? 'block' : 'none';
}

window.addEventListener('DOMContentLoaded', filterCards);
</script>

</body>
</html>