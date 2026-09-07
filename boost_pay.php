<?php
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id    = (int)$_SESSION['user_id'];
$listing_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$paymentError = isset($_GET['error']) ? 'Payment gateway error. Please try again.' : '';

if(!$listing_id){ header("Location: browse.php"); exit(); }

$listing = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM listings WHERE id=$listing_id AND user_id=$user_id"
));

if(!$listing){ header("Location: browse.php"); exit(); }
if($listing['is_boosted']){ header("Location: item.php?id=$listing_id"); exit(); }

$plans = [
    'week'  => ['label' => '1 Week',  'days' => 7,  'price' => 5.49],
    'month' => ['label' => '1 Month', 'days' => 30, 'price' => 18.90],
];

$selectedPlan = (isset($_GET['plan']) && isset($plans[$_GET['plan']])) ? $_GET['plan'] : 'week';
$plan = $plans[$selectedPlan];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Boost Listing – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container" style="max-width:680px; padding:50px 20px;">

    <div class="report-header">
        <h1>🚀 Boost Your Listing</h1>
        <p>Get more visibility by sponsoring your listing</p>
    </div>

    <!-- Listing preview -->
    <div class="boost-preview-card">
        <div class="boost-preview-img">
            <?php if(!empty($listing['image'])): ?>
                <img src="<?php echo $listing['image']; ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>">
            <?php else: ?>
                <img src="images/default.jpg" alt="No image">
            <?php endif; ?>
        </div>
        <div class="boost-preview-body">
            <div class="type-badge type-<?php echo $listing['listing_type']; ?>"
                 style="position:static;font-size:11px;padding:4px 10px;display:inline-block;margin-bottom:8px;">
                <?php echo strtoupper($listing['listing_type']); ?>
            </div>
            <h3><?php echo htmlspecialchars($listing['title']); ?></h3>
            <p>📍 <?php echo htmlspecialchars($listing['location']); ?></p>
        </div>
    </div>


    <?php if($paymentError): ?>
    <div class="action-error" style="margin-bottom:20px; padding:14px 16px;">
        ⚠️ <?php echo $paymentError; ?>
    </div>
    <?php endif; ?>


    <!-- Plan selector -->
    <div class="report-section" style="margin-top:24px;">
        <p class="report-section-title">Choose a Plan</p>

        <div class="boost-plan-grid">
            <?php foreach($plans as $key => $p): ?>
            <a href="boost_pay.php?id=<?php echo $listing_id; ?>&plan=<?php echo $key; ?>"
               class="boost-plan-card <?php echo $selectedPlan===$key?'selected':''; ?>">
                <div class="boost-plan-label"><?php echo $p['label']; ?></div>
                <div class="boost-plan-price">RM<?php echo number_format($p['price'],2); ?></div>
                <div class="boost-plan-per">
                    <?php echo $key==='week' ? 'RM'.number_format($p['price']/7,2).'/day' : 'Best value'; ?>
                </div>
                <?php if($key==='month'): ?>
                    <div class="boost-plan-badge">💰 Save <?php echo round((1-($p['price']/30)/($plans['week']['price']/7))*100); ?>%</div>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- What you get -->
    <div class="report-section" style="margin-top:20px;">
        <p class="report-section-title">What You Get</p>
        <div class="boost-benefits">
            <div class="boost-benefit">
                <span>⭐</span>
                <div><strong>Sponsored Badge</strong><p>Gold "Sponsored" badge visible to all users</p></div>
            </div>
            <div class="boost-benefit">
                <span>🔝</span>
                <div><strong>Top Placement</strong><p>Appears at the top of browse and on the homepage</p></div>
            </div>
            <div class="boost-benefit">
                <span>📅</span>
                <div><strong><?php echo $plan['label']; ?> Duration</strong><p>Listing stays boosted for <?php echo $plan['days']; ?> days from payment</p></div>
            </div>
        </div>
    </div>

    <!-- Payment summary -->
    <div class="report-section" style="margin-top:16px;">
        <p class="report-section-title">Payment Summary</p>
        <div class="boost-summary">
            <div class="boost-summary-row">
                <span>Plan</span>
                <span><?php echo $plan['label']; ?></span>
            </div>
            <div class="boost-summary-row">
                <span>Duration</span>
                <span><?php echo $plan['days']; ?> days</span>
            </div>
            <div class="boost-summary-row">
                <span>Listing</span>
                <span><?php echo htmlspecialchars($listing['title']); ?></span>
            </div>
            <div class="boost-summary-divider"></div>
            <div class="boost-summary-row boost-total">
                <span>Total</span>
                <span>RM<?php echo number_format($plan['price'], 2); ?></span>
            </div>
        </div>
    </div>

    <!-- Pay button -->
    <div class="boost-pay-btns">
        <form method="POST" action="boost_pay_process.php">
            <input type="hidden" name="listing_id" value="<?php echo $listing_id; ?>">
            <input type="hidden" name="amount"     value="<?php echo $plan['price']; ?>">
            <input type="hidden" name="days"       value="<?php echo $plan['days']; ?>">
            <input type="hidden" name="plan_label" value="<?php echo $plan['label']; ?>">
            <button type="submit" class="auth-submit-btn boost-pay-btn">
                💳 Pay RM<?php echo number_format($plan['price'],2); ?> via ToyyibPay
            </button>
        </form>
        <p class="boost-pay-note">🔒 Secure payment powered by ToyyibPay</p>
    </div>

    <a href="item.php?id=<?php echo $listing_id; ?>" class="item-back-link" style="display:block; margin-top:20px;">← Back to Listing</a>

</div>

<?php include 'footer.php'; ?>

</body>
</html>