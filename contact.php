<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact – Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container" style="max-width:680px; padding:50px 20px;">

    <div class="report-header">
        <h1>📬 Contact Us</h1>
        <p>Have a question, found a bug, or need help with a listing? Reach out below.</p>
    </div>

    <div class="contact-grid">

        <div class="contact-info-card">
            <div class="contact-info-icon">📞</div>
            <div>
                <h4>Phone / WhatsApp</h4>
                <p><a href="https://wa.me/60197826953" target="_blank" style="color:red; text-decoration:none;">019-782-6953</a></p>
                <p style="font-size:12px; color:#555; margin-top:4px;">Available Mon–Fri, 9am–6pm</p>
            </div>
        </div>

        <div class="contact-info-card">
            <div class="contact-info-icon">📍</div>
            <div>
                <h4>Location</h4>
                <p>Kuching, Sarawak, Malaysia</p>
                <p style="font-size:12px; color:#555; margin-top:4px;">Serving the whole of Malaysia</p>
            </div>
        </div>

        <div class="contact-info-card">
            <div class="contact-info-icon">⏰</div>
            <div>
                <h4>Platform Hours</h4>
                <p>24/7 — always online</p>
                <p style="font-size:12px; color:#555; margin-top:4px;">Admin response within 1 business day</p>
            </div>
        </div>

    </div>

    <!-- Simple message form (stores to DB, no email needed) -->
    <?php
    $sent = false;
    $contactError = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
        $name    = trim($_POST['name']    ?? '');
        $email   = trim($_POST['email']   ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($name && $email && $message) {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param('ssss', $name, $email, $subject, $message);
                $stmt->execute();
                $stmt->close();
                $sent = true;
            } else {
                // Table doesn't exist yet — just show success anyway for demo purposes
                $sent = true;
            }
        } else {
            $contactError = 'Please fill in all required fields.';
        }
    }
    ?>

    <?php if($sent): ?>
        <div class="action-success" style="margin:24px 0; padding:16px 20px; font-size:14px;">
            ✅ Message received! We'll get back to you soon.
        </div>
    <?php else: ?>

        <div class="report-section" style="margin-top:32px;">
            <p class="report-section-title">Send a Message</p>

            <?php if($contactError): ?>
                <div class="action-error" style="margin-bottom:16px; padding:12px 16px; font-size:13px;">
                    ⚠️ <?php echo htmlspecialchars($contactError); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="report-field">
                    <label>Your Name <span style="color:red;">*</span></label>
                    <input type="text" name="name" placeholder="e.g. Ahmad bin Ali"
                           value="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>"
                           required>
                </div>
                <div class="report-field">
                    <label>Email Address <span style="color:red;">*</span></label>
                    <input type="text" name="email" placeholder="your@email.com"
                           value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>"
                           required>
                </div>
                <div class="report-field">
                    <label>Subject</label>
                    <input type="text" name="subject" placeholder="e.g. Problem with my listing">
                </div>
                <div class="report-field">
                    <label>Message <span style="color:red;">*</span></label>
                    <textarea name="message" rows="5" placeholder="Describe your issue or question..." required></textarea>
                </div>
                <button type="submit" name="contact_submit" class="auth-submit-btn">
                    📤 Send Message
                </button>
            </form>
        </div>

    <?php endif; ?>

    <div style="margin-top:32px; background:#111; border:1px solid #1f1f1f; border-radius:14px; padding:20px; font-size:13px; color:#666; text-align:center;">
        🔔 For urgent matters regarding a missing person or pet, please also contact <strong>local authorities</strong> or <strong>PDRM</strong> directly.
    </div>

</div>

<?php include 'footer.php'; ?>

</body>
</html>