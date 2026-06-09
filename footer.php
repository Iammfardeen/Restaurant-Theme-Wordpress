</main>

<footer id="zahra-footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <a href="<?php echo home_url('/'); ?>" class="zahra-logo" style="margin-bottom:14px;display:inline-flex">
          <img src="<?php echo ZAHRA_URI; ?>/images/logo.png" alt="Zahra Logo" height="48">
          <div class="logo-txt"><span class="lname">ZAHRA</span><span class="lsub">Restaurant &amp; Café</span></div>
        </a>
        <p class="footer-desc">Serving authentic Mughlai &amp; Hyderabadi flavors in Jamia Nagar, New Delhi. Your favourite biryani destination.</p>
        <div class="f-social">
          <a href="https://instagram.com/zehra_restaurant" target="_blank" rel="noopener" aria-label="Instagram">📸</a>
          <a href="<?php echo esc_url(zahra_wa_url()); ?>" target="_blank" rel="noopener" aria-label="WhatsApp">💬</a>
          <a href="<?php echo esc_url(zahra_zomato()); ?>" target="_blank" rel="noopener" aria-label="Zomato">🍽️</a>
          <a href="<?php echo esc_url(zahra_swiggy()); ?>" target="_blank" rel="noopener" aria-label="Swiggy">🛵</a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Quick Links</h4>
        <ul class="f-links">
          <li><a href="<?php echo home_url('/'); ?>">Home</a></li>
          <li><a href="<?php echo get_permalink(get_page_by_path('menu')); ?>">Menu</a></li>
          <li><a href="<?php echo get_permalink(get_page_by_path('gallery')); ?>">Gallery</a></li>
          <li><a href="<?php echo get_permalink(get_page_by_path('about-us')); ?>">About Us</a></li>
          <li><a href="<?php echo get_permalink(get_page_by_path('contact')); ?>">Contact</a></li>
          <li><a href="<?php echo get_permalink(get_page_by_path('privacy-policy')); ?>">Privacy Policy</a></li>
          <li><a href="<?php echo get_permalink(get_page_by_path('terms-conditions')); ?>">Terms &amp; Conditions</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Order Online</h4>
        <ul class="f-links">
          <li><a href="<?php echo esc_url(zahra_zomato()); ?>" target="_blank" rel="noopener">🔴 Order on Zomato</a></li>
          <li><a href="<?php echo esc_url(zahra_swiggy()); ?>" target="_blank" rel="noopener">🟠 Order on Swiggy</a></li>
          <li><a href="<?php echo esc_url(zahra_wa_url('I want to place an order')); ?>" target="_blank" rel="noopener">💬 WhatsApp Order</a></li>
          <li><a href="<?php echo esc_url(zahra_wa_url('I want to reserve a table')); ?>" target="_blank" rel="noopener">📅 Reserve a Table</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Find Us</h4>
        <div class="f-ci"><span>📍</span><span>268, Tikona Park Marg, Jamia Nagar, Okhla, New Delhi – 110025</span></div>
        <div class="f-ci"><span>📞</span><a href="tel:+919355643665" style="color:var(--muted)">+91 93556 43665</a></div>
        <div class="f-ci"><span>🕐</span><span>Daily: 11:00 AM – 11:30 PM</span></div>
        <div class="f-ci"><span>📸</span><a href="https://instagram.com/zehra_restaurant" target="_blank" rel="noopener" style="color:var(--muted)">@zehra_restaurant</a></div>
      </div>
    </div>
    <div class="footer-bottom">
      <p class="footer-copy">&copy; <?php echo date('Y'); ?> <strong style="color:var(--gold)">Zahra Restaurant &amp; Café</strong>. All rights reserved.</p>
      <div class="f-legal">
        <a href="<?php echo get_permalink(get_page_by_path('privacy-policy')); ?>">Privacy Policy</a>
        <a href="<?php echo get_permalink(get_page_by_path('terms-conditions')); ?>">Terms &amp; Conditions</a>
      </div>
    </div>
  </div>
</footer>

<!-- Floating Buttons -->
<div class="fab-wrap">
  <a href="<?php echo esc_url(zahra_wa_url()); ?>" class="fab fab-wa" data-tip="WhatsApp Us" target="_blank" rel="noopener" aria-label="WhatsApp">💬</a>
  <a href="<?php echo esc_url(zahra_zomato()); ?>" class="fab fab-order" data-tip="Order Now" target="_blank" rel="noopener" aria-label="Order Now">🍽️</a>
</div>
<button class="scroll-top-btn" aria-label="Scroll to top">↑</button>

<?php wp_footer(); ?>
</body>
</html>
