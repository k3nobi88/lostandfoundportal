<?php include 'config.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Browse – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="browse-hero">
    <h1>Browse Listings</h1>
    <p>Search through lost and found reports from the community</p>
</div>

<div class="container">

<?php
$showResolved = isset($_GET['view']) && $_GET['view'] === 'resolved';
?>

<?php if (!$showResolved): ?>
<!-- MAP VIEW -->
<div class="browse-map-wrap">
    <div class="browse-map-header">
        <h3>🗺️ Live Map</h3>
        <button class="browse-map-toggle" onclick="toggleMap()">Show Map</button>
    </div>
    <div id="browseMapContainer" style="display:none;">
        <div id="browseMap"></div>
    </div>
</div>
<?php endif; ?>

    <!-- FILTER BAR -->
    <form method="GET" id="filterForm" class="filter-bar">

        <input type="text" name="q" id="searchInput"
               placeholder="🔍  Search by title or location..."
               value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">

        <div class="filter-row">

            <div class="filter-group">
                <span class="filter-label">Type</span>
                <div class="filter-tabs" id="typeFilter">
                    <?php
                    $activeType = $_GET['type'] ?? 'all';
                    foreach(['all'=>'All','lost'=>'🔴 Lost','found'=>'🟢 Found'] as $val=>$label):
                    ?>
                    <button type="button"
                            class="filter-tab <?php echo $activeType==$val?'active':''; ?>"
                            onclick="setFilter('type','<?php echo $val; ?>')">
                        <?php echo $label; ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="type" id="typeInput" value="<?php echo htmlspecialchars($activeType); ?>">
            </div>

            <div class="filter-group">
                <span class="filter-label">Category</span>
                <div class="filter-tabs" id="catFilter">
                    <?php
                    $activeCat = $_GET['cat'] ?? 'all';
                    $cats = ['all'=>'All','item'=>'🎒 Items','pet'=>'🐾 Pets','vehicle'=>'🚗 Vehicles','person'=>'👤 Persons'];
                    foreach($cats as $val=>$label):
                    ?>
                    <button type="button"
                            class="filter-tab <?php echo $activeCat==$val?'active':''; ?>"
                            onclick="setFilter('cat','<?php echo $val; ?>')">
                        <?php echo $label; ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="cat" id="catInput" value="<?php echo htmlspecialchars($activeCat); ?>">
            </div>

            <!-- DATE RANGE -->
            <div class="filter-group">
                <span class="filter-label">Date range</span>
                <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <input type="date" name="date_from" id="dateFrom"
                           value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>"
                           style="background:#1a1a1a; border:1px solid #2a2a2a; color:white; border-radius:8px; padding:7px 10px; font-size:13px;"
                           onchange="submitFilters()">
                    <span style="color:#555; font-size:13px;">to</span>
                    <input type="date" name="date_to" id="dateTo"
                           value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>"
                           style="background:#1a1a1a; border:1px solid #2a2a2a; color:white; border-radius:8px; padding:7px 10px; font-size:13px;"
                           onchange="submitFilters()">
                    <?php if(!empty($_GET['date_from']) || !empty($_GET['date_to'])): ?>
                        <a href="browse.php" style="color:#ff4444; font-size:12px; text-decoration:none;">✕ Clear</a>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Resolved toggle button -->
        <div style="display:flex; justify-content:flex-end; margin-top:12px;">
            <?php if ($showResolved): ?>
                <a href="browse.php" class="btn btn-outline" style="font-size:13px;">← Back to Active Listings</a>
            <?php else: ?>
                <a href="browse.php?view=resolved" style="font-size:13px; color:#888; text-decoration:none; border:1px solid #2a2a2a; padding:7px 16px; border-radius:8px;">
                    ✅ View Resolved Listings
                </a>
            <?php endif; ?>
        </div>

    </form>

    <?php
    // Build WHERE clause
    $showResolved = isset($_GET['view']) && $_GET['view'] === 'resolved';
    $where  = $showResolved ? ["resolved_status = 'resolved'"] : ["(resolved_status IS NULL OR resolved_status != 'resolved')"];
    $params = [];
    $types  = '';

    if (!empty($_GET['q'])) {
        $where[]  = '(title LIKE ? OR location LIKE ?)';
        $q = '%' . $_GET['q'] . '%';
        $params[] = $q; $params[] = $q;
        $types   .= 'ss';
    }
    if (!empty($_GET['type']) && $_GET['type'] !== 'all') {
        $where[]  = 'listing_type = ?';
        $params[] = $_GET['type'];
        $types   .= 's';
    }
    if (!empty($_GET['cat']) && $_GET['cat'] !== 'all') {
        $where[]  = 'category = ?';
        $params[] = $_GET['cat'];
        $types   .= 's';
    }
    if (!empty($_GET['date_from'])) {
        $where[]  = 'DATE(created_at) >= ?';
        $params[] = $_GET['date_from'];
        $types   .= 's';
    }
    if (!empty($_GET['date_to'])) {
        $where[]  = 'DATE(created_at) <= ?';
        $params[] = $_GET['date_to'];
        $types   .= 's';
    }

    $whereSQL = implode(' AND ', $where);

    // Count total
    $totalCount = 0;
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM listings WHERE $whereSQL");
    if ($countStmt) {
        if ($types) $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $countStmt->bind_result($totalCount);
        $countStmt->fetch();
        $countStmt->close();
    }

    // Pagination
    $perPage     = 12;
    $currentPage = max(1, (int)($_GET['page'] ?? 1));
    $totalPages  = max(1, ceil($totalCount / $perPage));
    $offset      = ($currentPage - 1) * $perPage;

    // Fetch page
    $stmt = $conn->prepare("
        SELECT * FROM listings
        WHERE $whereSQL
        ORDER BY is_boosted DESC, created_at DESC
        LIMIT ? OFFSET ?
    ");
    $pageParams  = $params;
    $pageParams[] = $perPage;
    $pageParams[] = $offset;
    $pageTypes   = $types . 'ii';
    $stmt->bind_param($pageTypes, ...$pageParams);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    ?>

    <!-- RESULTS COUNT -->
    <div class="results-bar">
        <p class="results-count">
            <?php echo $totalCount; ?> listing<?php echo $totalCount != 1 ? 's' : ''; ?> found
            <?php if($totalPages > 1): ?>
                · Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?>
            <?php endif; ?>
        </p>
    </div>

    <!-- SKELETON LOADING (shown while page loads) -->
    <div class="card-container skeleton-grid" id="skeletonGrid">
        <?php for($s=0;$s<8;$s++): ?>
        <div class="card skeleton-card">
            <div class="skeleton skeleton-img"></div>
            <div class="card-content">
                <div class="skeleton skeleton-title"></div>
                <div class="skeleton skeleton-line"></div>
                <div class="skeleton skeleton-line short"></div>
            </div>
        </div>
        <?php endfor; ?>
    </div>

    <!-- CARD GRID (hidden until page fully loaded) -->
    <div class="card-container" id="cardGrid" style="display:none;">

    <?php if($result->num_rows == 0): ?>
        <p class="no-results">😕 No listings match your filters. Try a different search or clear the date range.</p>
    <?php endif; ?>

    <?php while($row = $result->fetch_assoc()):
        $category = !empty($row['category']) ? $row['category'] : 'item';
    ?>
        <a href="item.php?id=<?php echo $row['id']; ?>" style="text-decoration:none; color:white;">
            <div class="card">
                <?php if(!empty($row['image'])): ?>
                    <img src="<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>"
                         loading="lazy">
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
                    <h3>
                        <?php echo htmlspecialchars($row['title']); ?>
                        <?php if(!empty($row['resolved_status']) && $row['resolved_status'] == 'resolved'): ?>
                            <span class="resolved-card-badge">✅ Resolved</span>
                        <?php endif; ?>
                    </h3>
                    <p>📍 <?php echo htmlspecialchars($row['location']); ?></p>
                    <?php if(!empty($row['reward'])): ?>
                        <p class="reward">💰 Reward: RM<?php echo htmlspecialchars($row['reward']); ?></p>
                    <?php endif; ?>
                    <p class="card-date">🕐 <?php echo date('d M Y', strtotime($row['created_at'])); ?></p>
                </div>
            </div>
        </a>
    <?php endwhile; ?>

    </div>

    <!-- PAGINATION -->
    <?php if($totalPages > 1): ?>
    <div class="pagination-wrap">
        <?php
        $qParams = $_GET;
        unset($qParams['page']);
        $qBase = http_build_query($qParams);
        $qBase = $qBase ? '&' . $qBase : '';
        ?>

        <?php if($currentPage > 1): ?>
            <a href="browse.php?page=1<?php echo $qBase; ?>" class="page-btn page-btn-skip" title="First page">«« First</a>
            <a href="browse.php?page=<?php echo $currentPage-1 . $qBase; ?>" class="page-btn">← Prev</a>
        <?php else: ?>
            <span class="page-btn page-btn-skip disabled">«« First</span>
            <span class="page-btn disabled">← Prev</span>
        <?php endif; ?>

        <?php for($p = max(1,$currentPage-2); $p <= min($totalPages,$currentPage+2); $p++): ?>
            <a href="browse.php?page=<?php echo $p . $qBase; ?>"
               class="page-btn <?php echo $p==$currentPage?'active':''; ?>">
                <?php echo $p; ?>
            </a>
        <?php endfor; ?>

        <?php if($currentPage < $totalPages): ?>
            <a href="browse.php?page=<?php echo $currentPage+1 . $qBase; ?>" class="page-btn">Next →</a>
            <a href="browse.php?page=<?php echo $totalPages . $qBase; ?>" class="page-btn page-btn-skip" title="Last page">Last »»</a>
        <?php else: ?>
            <span class="page-btn disabled">Next →</span>
            <span class="page-btn page-btn-skip disabled">Last »»</span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<?php include 'footer.php'; ?>

<?php
$mapListings = $conn->prepare("
    SELECT id, title, location, image, listing_type, category, lat, lng
    FROM listings
    WHERE lat IS NOT NULL AND lng IS NOT NULL
    AND lat != '' AND lng != ''
    AND (resolved_status IS NULL OR resolved_status != 'resolved')
");
$mapListings->execute();
$mapResult = $mapListings->get_result();
$mapListings->close();
$mapData = $mapResult->fetch_all(MYSQLI_ASSOC);
?>

<script>
// Show real cards, hide skeletons after load
window.addEventListener('load', function() {
    document.getElementById('skeletonGrid').style.display = 'none';
    document.getElementById('cardGrid').style.display     = '';
});

function setFilter(field, value) {
    document.getElementById(field + 'Input').value = value;
    document.querySelectorAll('#' + field + 'Filter .filter-tab').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
    submitFilters();
}

function submitFilters() {
    document.getElementById('filterForm').submit();
}

// Trigger search on typing (debounced)
let searchTimer;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(submitFilters, 600);
});
</script>

<script>
let browseMap = null;
let mapVisible = false;

function toggleMap() {
    const container = document.getElementById('browseMapContainer');
    const btn       = document.querySelector('.browse-map-toggle');
    if(!mapVisible){
        container.style.display = 'block';
        btn.textContent = 'Hide Map';
        mapVisible = true;
        if(!browseMap) initBrowseMap();
    } else {
        container.style.display = 'none';
        btn.textContent = 'Show Map';
        mapVisible = false;
    }
}

function initBrowseMap() {
    browseMap = new google.maps.Map(document.getElementById('browseMap'), {
        center: { lat: 4.2105, lng: 108.9758 },
        zoom: 6,
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

    const listings   = <?php echo json_encode($mapData); ?>;
    const infoWindow = new google.maps.InfoWindow();

    listings.forEach(function(item) {
        const isLost   = item.listing_type === 'lost';
        const pinColor = isLost ? '#ff4444' : '#22c55e';
        const marker   = new google.maps.Marker({
            position:  { lat: parseFloat(item.lat), lng: parseFloat(item.lng) },
            map:       browseMap,
            title:     item.title,
            animation: google.maps.Animation.DROP,
            icon: {
                path:         google.maps.SymbolPath.CIRCLE,
                scale:        9,
                fillColor:    pinColor,
                fillOpacity:  1,
                strokeColor:  '#ffffff',
                strokeWeight: 2
            }
        });
        const image    = item.image ? `<img src="${item.image}" style="width:100%;height:100px;object-fit:cover;border-radius:8px;margin-bottom:10px;display:block;">` : '';
        const typeBadge = isLost
            ? `<span style="background:#ff4444;color:white;font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;">Lost</span>`
            : `<span style="background:#22c55e;color:white;font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;">Found</span>`;
        const content  = `<div style="width:200px;font-family:sans-serif;background:#1a1a1a;border-radius:10px;overflow:hidden;padding:0;">${image}<div style="padding:10px;">${typeBadge}<p style="font-weight:700;font-size:14px;margin:8px 0 4px 0;color:white;">${item.title}</p><p style="font-size:12px;color:#888;margin:0 0 10px 0;">📍 ${item.location}</p><a href="item.php?id=${item.id}" style="display:block;background:#cc0000;color:white;text-align:center;padding:7px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:700;">View Details →</a></div></div>`;
        marker.addListener('click', function() {
            infoWindow.setContent(content);
            infoWindow.open(browseMap, marker);
        });
    });
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyChzn_JIEytG6xbhuf3Wu1UGO-3de_4m0A&callback=Function.prototype" async defer></script>

</body>
</html>