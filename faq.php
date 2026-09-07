<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>FAQ – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container" style="max-width:800px; padding:50px 20px;">

    <div class="report-header">
        <h1>❓ Frequently Asked Questions</h1>
        <p>Everything you need to know about using the Lost & Found Portal</p>
    </div>

    <div class="faq-list">

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                How do I report a lost item?
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-a">
                <p>Click <strong>Report Lost</strong> in the navigation bar (you must be signed in). Fill in the title, category, description, location, and upload a photo. The more detail you provide, the better your chances of recovery.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                I found something. How do I report it?
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-a">
                <p>Click <strong>Report Found</strong> in the navigation bar. Describe the item as accurately as possible — but avoid listing very specific identifying details publicly. The real owner will be able to prove ownership through the claim process.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                How does claiming an item work?
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-a">
                <p>When you find a listing that matches your lost item, click <strong>Submit Claim</strong> and describe why you believe it's yours. The listing owner will review your claim and can approve or reject it. If approved, a private chat opens for you to arrange collection.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                Is this platform free to use?
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-a">
                <p>Yes — reporting lost/found items and submitting claims is completely free. We offer an optional <strong>Boost</strong> feature (from RM5.49/week) that places your listing at the top of the browse page for extra visibility.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                How does the reward system work?
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-a">
                <p>When reporting a lost item, you can optionally set a reward amount (e.g. RM50). If someone returns your item and you approve their claim, you pay the reward — either in cash when you meet up, or via online transfer through ToyyibPay.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                Is my personal information safe?
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-a">
                <p>Your contact details are never shown publicly. Communication between claimants and owners happens through our private in-platform chat. We comply with Malaysia's <strong>Personal Data Protection Act (PDPA) 2010</strong>. See our <a href="privacy.php" style="color:red;">Privacy Policy</a> for more.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                What categories are supported?
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-a">
                <p>We support four categories: <strong>Items</strong> (bags, wallets, phones, etc.), <strong>Pets</strong> (dogs, cats, and other animals), <strong>Vehicles</strong> (cars, motorcycles, bicycles), and <strong>Persons</strong> (missing individuals). Each category has a tailored report form.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                Can I delete my listing after it's resolved?
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-a">
                <p>Yes. Go to your <a href="profile.php" style="color:red;">Profile</a> page, find the listing, and click <strong>Delete</strong>. Alternatively, once a claim is marked as Collected, the listing will be automatically flagged as resolved and greyed out for other visitors.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                What happens if a false claim is submitted?
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-a">
                <p>You are in full control as the listing owner — you decide whether to approve or reject any claim. If you believe a user is acting in bad faith, you can flag it and our admin team will review it. False reports or scam attempts may result in account suspension.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                I have a problem not listed here. How do I get help?
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-a">
                <p>Visit our <a href="contact.php" style="color:red;">Contact page</a> or call us directly at <strong>019-782-6953</strong>. We're happy to help.</p>
            </div>
        </div>

    </div>

</div>

<?php include 'footer.php'; ?>

<script>
function toggleFaq(btn) {
    const item   = btn.parentElement;
    const answer = item.querySelector('.faq-a');
    const arrow  = btn.querySelector('.faq-arrow');
    const isOpen = item.classList.contains('open');

    // Close all
    document.querySelectorAll('.faq-item.open').forEach(i => {
        i.classList.remove('open');
        i.querySelector('.faq-a').style.maxHeight    = '0';
        i.querySelector('.faq-arrow').textContent = '▼';
    });

    // Open this one if it was closed
    if (!isOpen) {
        item.classList.add('open');
        answer.style.maxHeight    = answer.scrollHeight + 'px';
        arrow.textContent = '▲';
    }
}
</script>

</body>
</html>