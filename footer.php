<footer class="footer-new">
    <div class="footer-inner">

        <div class="footer-brand">
            <div class="footer-logo">LOST & <span>FOUND</span></div>
            <p>Malaysia's community platform for reuniting people with their lost belongings.</p>
            <p style="margin-top:8px; font-size:12px; color:#444;">📞 019-782-6953 (BOFIN.DOMB)</p>
        </div>

        <div class="footer-links">
            <h4>Platform</h4>
            <a href="browse.php">Browse Listings</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="lostitem.php">Report Lost Item</a>
                <a href="founditem.php">Report Found Item</a>
            <?php else: ?>
                <a href="login.php">Sign In</a>
                <a href="signup.php">Create Account</a>
            <?php endif; ?>
        </div>

        <div class="footer-links">
            <h4>Support</h4>
            <a href="faq.php">FAQ</a>
            <a href="about.php">About Us</a>
            <a href="contact.php">Contact</a>
        </div>

        <div class="footer-links">
            <h4>Legal</h4>
            <a href="privacy.php">Privacy Policy</a>
            <a href="terms.php">Terms of Service</a>
        </div>

    </div>

    <div class="footer-bottom">
        <p>© <?php echo date('Y'); ?> Lost & Found Portal. All rights reserved.</p>
        <p style="margin-top:4px; font-size:11px; color:#333;">
            This platform complies with Malaysia's Personal Data Protection Act (PDPA) 2010.
        </p>
    </div>
	
	
	<button id="backToTop" onclick="window.scrollTo({top:0,behavior:'smooth'})" title="Back to top">↑</button>

	<style>
	#backToTop {
		position: fixed;
		bottom: 28px;
		right: 28px;
		background: red;
		color: white;
		border: none;
		width: 42px;
		height: 42px;
		border-radius: 50%;
		font-size: 18px;
		cursor: pointer;
		opacity: 0;
		pointer-events: none;
		transition: opacity 0.3s;
		z-index: 999;
		box-shadow: 0 4px 14px rgba(200,0,0,0.4);
	}
	#backToTop.visible {
		opacity: 1;
		pointer-events: all;
	}
	</style>

	<script>
	window.addEventListener('scroll', function() {
		document.getElementById('backToTop').classList.toggle('visible', window.scrollY > 400);
	});
	</script>
</footer>