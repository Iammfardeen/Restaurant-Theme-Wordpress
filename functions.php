<?php
/**
 * Zahra Restaurant Theme - Functions
 * Auto-creates all pages, menus, and settings on activation
 */
defined('ABSPATH') || exit;

define('ZAHRA_VER', '2.0.0');
define('ZAHRA_URI', get_template_directory_uri());
define('ZAHRA_DIR', get_template_directory());

/* ============================================================
   THEME SETUP
   ============================================================ */
add_action('after_setup_theme', function () {
    load_theme_textdomain('zahra', ZAHRA_DIR . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','script','style']);
    add_theme_support('custom-logo', ['height'=>80,'width'=>200,'flex-width'=>true,'flex-height'=>true]);
    add_theme_support('customize-selective-refresh-widgets');
    register_nav_menus(['primary' => 'Primary Menu', 'footer' => 'Footer Menu']);
    add_image_size('zahra-dish', 600, 440, true);
    add_image_size('zahra-gallery', 900, 700, true);
});

/* ============================================================
   ENQUEUE ASSETS
   ============================================================ */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('zahra-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Nunito:wght@400;600;700&display=swap',
        [], null);
    wp_enqueue_style('zahra-main', ZAHRA_URI . '/css/zahra.css', [], ZAHRA_VER);
    wp_enqueue_script('zahra-js', ZAHRA_URI . '/js/zahra.js', [], ZAHRA_VER, true);
    wp_localize_script('zahra-js', 'zahraData', [
        'ajax'      => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('zahra_nonce'),
        'whatsapp'  => '919355643665',
        'themeUri'  => ZAHRA_URI,
    ]);
});

/* ============================================================
   AUTO-SETUP ON ACTIVATION  ← THE KEY PART
   Creates all 7 pages with full HTML content built-in
   ============================================================ */
add_action('after_switch_theme', 'zahra_auto_setup');

function zahra_auto_setup() {
    $u = get_template_directory_uri();

    // Define all pages: [title, slug, template, content_function]
    $pages = [
        ['Home',             'home',             'page-home.php',            'zahra_home_content'],
        ['Menu',             'menu',             'page-menu.php',            'zahra_menu_content'],
        ['Gallery',          'gallery',          'page-gallery.php',         'zahra_gallery_content'],
        ['About Us',         'about-us',         'page-about.php',           'zahra_about_content'],
        ['Contact',          'contact',          'page-contact.php',         'zahra_contact_content'],
        ['Privacy Policy',   'privacy-policy',   'page-policy.php',          'zahra_privacy_content'],
        ['Terms & Conditions','terms-conditions','page-terms.php',           'zahra_terms_content'],
    ];

    $created = [];
    foreach ($pages as [$title, $slug, $template, $content_fn]) {
        // Skip if page already exists
        $existing = get_page_by_path($slug);
        if ($existing) {
            $created[$slug] = $existing->ID;
            // Still update template meta
            update_post_meta($existing->ID, '_wp_page_template', $template);
            continue;
        }
        $content = function_exists($content_fn) ? call_user_func($content_fn) : '';
        $id = wp_insert_post([
            'post_title'     => $title,
            'post_name'      => $slug,
            'post_content'   => $content,
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'comment_status' => 'closed',
        ]);
        if ($id && !is_wp_error($id)) {
            update_post_meta($id, '_wp_page_template', $template);
            $created[$slug] = $id;
        }
    }

    // Set Home as front page
    if (isset($created['home'])) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $created['home']);
    }

    // Create navigation menu
    zahra_setup_nav_menu($created);

    // Store setup flag
    update_option('zahra_setup_done', '1');
}

function zahra_setup_nav_menu($pages) {
    $menu_name = 'Zahra Main Menu';
    $existing  = wp_get_nav_menu_object($menu_name);
    if ($existing) return; // already created

    $menu_id = wp_create_nav_menu($menu_name);
    if (is_wp_error($menu_id)) return;

    $items = [
        'Home'             => 'home',
        'Menu'             => 'menu',
        'Gallery'          => 'gallery',
        'About Us'         => 'about-us',
        'Contact'          => 'contact',
    ];
    $order = 1;
    foreach ($items as $label => $slug) {
        if (isset($pages[$slug])) {
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title'     => $label,
                'menu-item-object'    => 'page',
                'menu-item-object-id' => $pages[$slug],
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
                'menu-item-position'  => $order++,
            ]);
        }
    }

    // Assign to Primary location
    $locations = get_theme_mod('nav_menu_locations', []);
    $locations['primary'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
}

/* ============================================================
   PAGE CONTENT BUILDERS
   Each function returns the full HTML content for that page.
   These are stored in the WP database as page content.
   ============================================================ */

function zahra_home_content() {
    $u = get_template_directory_uri();
    ob_start(); ?>
[zahra_home_hero]
[zahra_features_bar]
[zahra_popular_dishes]
[zahra_gallery_preview]
[zahra_about_preview]
[zahra_opening_hours]
[zahra_testimonials]
[zahra_order_cta]
[zahra_social_follow]
[zahra_contact_map]
<?php return ob_get_clean();
}

function zahra_menu_content() {
    return '[zahra_menu_page]';
}

function zahra_gallery_content() {
    return '[zahra_gallery_page]';
}

function zahra_about_content() {
    return '[zahra_about_page]';
}

function zahra_contact_content() {
    return '[zahra_contact_page]';
}

function zahra_privacy_content() {
    return '[zahra_privacy_page]';
}

function zahra_terms_content() {
    return '[zahra_terms_page]';
}

/* ============================================================
   SHORTCODES  ← Each section rendered via shortcode
   ============================================================ */
add_action('init', function() {
    $codes = [
        'zahra_home_hero','zahra_features_bar','zahra_popular_dishes',
        'zahra_gallery_preview','zahra_about_preview','zahra_opening_hours',
        'zahra_testimonials','zahra_order_cta','zahra_social_follow',
        'zahra_contact_map','zahra_menu_page','zahra_gallery_page',
        'zahra_about_page','zahra_contact_page','zahra_privacy_page','zahra_terms_page',
    ];
    foreach ($codes as $code) {
        add_shortcode($code, 'zahra_render_' . $code);
    }
});

function zahra_render_zahra_home_hero($atts) {
    $u = ZAHRA_URI;
    $wa = zahra_wa_url('I want to reserve a table at Zahra Restaurant');
    ob_start(); ?>
<section id="hero">
  <div class="hero-bg"></div>
  <div class="hero-overlay"></div>
  <div class="container">
    <div class="hero-content">
      <div class="hero-badge"><span class="hero-dot"></span>Jamia Nagar's Finest Restaurant</div>
      <h1 class="hero-title">Authentic Flavors of<span class="accent">Mughlai Cuisine</span></h1>
      <p class="hero-tagline">Authentic Biryani &amp; Fast Food in Jamia Nagar, Okhla — where every plate tells a story of heritage, spice, and love.</p>
      <div class="cta-stack">
        <div class="btn-row">
          <a href="<?php echo esc_url($wa); ?>" class="btn btn-primary" target="_blank" rel="noopener">📅 Reserve a Table</a>
          <a href="<?php echo esc_url(get_permalink(get_page_by_path('menu'))); ?>" class="btn btn-outline">🍛 Explore Menu</a>
          <a href="tel:+919355643665" class="btn btn-outline">📞 Call Now</a>
        </div>
        <div class="btn-row">
          <a href="<?php echo esc_url(zahra_zomato()); ?>" class="btn btn-zomato btn-sm" target="_blank" rel="noopener">🔴 Order on Zomato</a>
          <a href="<?php echo esc_url(zahra_swiggy()); ?>" class="btn btn-swiggy btn-sm" target="_blank" rel="noopener">🟠 Order on Swiggy</a>
        </div>
      </div>
    </div>
  </div>
  <span class="hero-scroll-hint">Scroll</span>
</section>
<?php return ob_get_clean();
}

function zahra_render_zahra_features_bar($atts) {
    ob_start(); ?>
<div class="features-bar">
  <div class="container">
    <div class="features-grid">
      <div class="feature-item"><span class="fi">🍛</span><div><strong>Authentic Biryani</strong><span>Dum-cooked to perfection</span></div></div>
      <div class="feature-item"><span class="fi">🔥</span><div><strong>Live Tandoor</strong><span>Fresh from the clay oven</span></div></div>
      <div class="feature-item"><span class="fi">🛵</span><div><strong>Fast Delivery</strong><span>Zomato &amp; Swiggy available</span></div></div>
      <div class="feature-item"><span class="fi">📅</span><div><strong>Easy Reservations</strong><span>Book via WhatsApp instantly</span></div></div>
    </div>
  </div>
</div>
<?php return ob_get_clean();
}

function zahra_render_zahra_popular_dishes($atts) {
    $u = ZAHRA_URI;
    $dishes = [
        ['Chicken Handi Biryani','Slow-cooked in a traditional handi with saffron, whole spices &amp; tender chicken.','₹200','handi-biryani.webp','Best Seller','badge-hot'],
        ['Malai Tikka','Creamy marinated chicken grilled in our tandoor, served with mint chutney.','₹240','malai-tikka.webp','Chef\'s Pick','badge-chef'],
        ['Chicken Tikka','Classic chargrilled chicken with bold spices, fresh salad &amp; chutney.','₹220','chicken-tikka.webp','Popular','badge-hot'],
        ['Hyderabadi Dum Biryani','The legendary Hyderabadi preparation with long-grain basmati &amp; aromatic spices.','₹100','biryani.jpg','Best Seller','badge-hot'],
        ['Paneer Tikka','Juicy paneer cubes in spiced yogurt, grilled &amp; drizzled with cream.','₹200','paneer-tikka.webp','Veg','badge-veg'],
        ['Traditional Kheer','Rich rice pudding topped with almonds &amp; cardamom — perfect sweet finish.','₹80','kheer.webp','Dessert','badge-dessert'],
    ];
    ob_start(); ?>
<section class="section bg-dark" id="popular-dishes">
  <div class="container">
    <div class="text-center">
      <span class="section-subtitle">Our Specialties</span>
      <h2 class="section-title">Popular Dishes</h2>
      <div class="divider"><span>✦</span></div>
      <p class="section-desc">From our signature Hyderabadi Dum Biryani to melt-in-your-mouth Malai Tikka — every dish crafted with authentic spice blends.</p>
    </div>
    <div class="dishes-grid">
      <?php foreach ($dishes as $d): ?>
      <div class="dish-card anim">
        <div class="dish-img">
          <img src="<?php echo esc_url($u . '/images/' . $d[3]); ?>" alt="<?php echo esc_attr($d[0]); ?>" loading="lazy">
          <span class="dish-badge <?php echo $d[5]; ?>"><?php echo $d[4]; ?></span>
        </div>
        <div class="dish-info">
          <h3><?php echo $d[0]; ?></h3>
          <p><?php echo $d[1]; ?></p>
          <div class="dish-footer">
            <span class="dish-price"><?php echo $d[2]; ?> <small>/ qtr</small></span>
            <a href="<?php echo esc_url(zahra_zomato()); ?>" class="btn btn-sm btn-primary" target="_blank" rel="noopener">Order</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-lg">
      <a href="<?php echo esc_url(get_permalink(get_page_by_path('menu'))); ?>" class="btn btn-outline">View Full Menu →</a>
    </div>
  </div>
</section>
<?php return ob_get_clean();
}

function zahra_render_zahra_gallery_preview($atts) {
    $u = ZAHRA_URI;
    ob_start(); ?>
<section class="section bg-darker" id="gallery-preview">
  <div class="container">
    <div class="text-center">
      <span class="section-subtitle">Visual Stories</span>
      <h2 class="section-title">A Feast for the Eyes</h2>
      <div class="divider"><span>✦</span></div>
    </div>
    <div class="gallery-grid preview-gallery">
      <div class="gitem wide tall"><img src="<?php echo $u; ?>/images/front.jpg" alt="Zahra Exterior" loading="lazy"><div class="goverlay"><span>🔍</span></div></div>
      <div class="gitem"><img src="<?php echo $u; ?>/images/handi-biryani.webp" alt="Handi Biryani" loading="lazy"><div class="goverlay"><span>🔍</span></div></div>
      <div class="gitem"><img src="<?php echo $u; ?>/images/chicken-tikka.webp" alt="Chicken Tikka" loading="lazy"><div class="goverlay"><span>🔍</span></div></div>
      <div class="gitem"><img src="<?php echo $u; ?>/images/interior1.jpg" alt="Interior" loading="lazy"><div class="goverlay"><span>🔍</span></div></div>
      <div class="gitem"><img src="<?php echo $u; ?>/images/malai-tikka.webp" alt="Malai Tikka" loading="lazy"><div class="goverlay"><span>🔍</span></div></div>
    </div>
    <div class="text-center mt-md">
      <a href="<?php echo esc_url(get_permalink(get_page_by_path('gallery'))); ?>" class="btn btn-outline">View Full Gallery →</a>
    </div>
  </div>
</section>
<?php return ob_get_clean();
}

function zahra_render_zahra_about_preview($atts) {
    $u = ZAHRA_URI;
    $wa = zahra_wa_url('I want to reserve a table');
    ob_start(); ?>
<section class="section bg-dark" id="about">
  <div class="container">
    <div class="about-grid">
      <div class="about-imgs anim">
        <div class="about-main"><img src="<?php echo $u; ?>/images/interior2.jpg" alt="Zahra Interior"></div>
        <div class="about-accent"><img src="<?php echo $u; ?>/images/interior3.jpg" alt="Zahra Dining"></div>
        <div class="about-badge-float"><span class="n">5★</span><span class="l">Rated</span></div>
      </div>
      <div class="about-text-col anim">
        <span class="section-subtitle">Our Story</span>
        <h2 class="section-title">Where Tradition Meets <span class="gold">Taste</span></h2>
        <div class="divider left"><span>✦</span></div>
        <p class="body-text">Zahra Restaurant &amp; Café was born from a passion for authentic Mughlai and Hyderabadi flavors — the kind that transport you to the royal kitchens of the past.</p>
        <p class="body-text">Located in the heart of Jamia Nagar, Okhla, we've been serving the community with our signature biryani, hand-crafted kebabs, and freshly made tandoori dishes using family recipes passed down through generations.</p>
        <div class="about-stats">
          <div><span class="n">50+</span><span class="l">Menu Items</span></div>
          <div><span class="n">1000+</span><span class="l">Happy Guests</span></div>
          <div><span class="n">5★</span><span class="l">Google Rating</span></div>
        </div>
        <div class="btn-row mt-md">
          <a href="<?php echo esc_url(get_permalink(get_page_by_path('about-us'))); ?>" class="btn btn-outline">Our Story →</a>
          <a href="<?php echo esc_url($wa); ?>" class="btn btn-whatsapp" target="_blank" rel="noopener">💬 Reserve Table</a>
        </div>
      </div>
    </div>
  </div>
</section>
<?php return ob_get_clean();
}

function zahra_render_zahra_opening_hours($atts) {
    ob_start(); ?>
<section class="section bg-darker">
  <div class="container">
    <div class="text-center">
      <span class="section-subtitle">Visit Us</span>
      <h2 class="section-title">Opening Hours</h2>
      <div class="divider"><span>✦</span></div>
    </div>
    <div class="hours-card anim">
      <ul class="hours-list">
        <?php $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        foreach ($days as $day): ?>
        <li><span class="day"><?php echo $day; ?></span><span class="time">11:00 AM – 11:30 PM</span></li>
        <?php endforeach; ?>
      </ul>
      <div class="text-center mt-md"><div class="hours-open">🟢 Open Now — Welcome!</div></div>
    </div>
  </div>
</section>
<?php return ob_get_clean();
}

function zahra_render_zahra_testimonials($atts) {
    $reviews = [
        ['Mohammad Raza','Jamia Nagar · Google','MR','The Handi Biryani here is absolutely incredible! The rice is perfectly cooked, the chicken is tender, and the aroma — oh my God. Best biryani in Okhla, period.','5'],
        ['Sana Ahmed','Okhla · Zomato','SA','Visited with family for dinner. The Malai Tikka was phenomenal — so soft and creamy. Great ambiance too. Will definitely come back!','5'],
        ['Arjun Kapoor','Sarita Vihar · Swiggy','AK','Ordered via Swiggy and the food arrived hot. The Chicken Handi Biryani was rich and flavorful. Packaging was great. Zahra never disappoints!','5'],
        ['Farhan Noor','Abul Fazal Enclave · Google','FN','The Turkish Tea and Shawarma combo here is a must-try. Cozy atmosphere, friendly staff, and value for money. My go-to spot in Jamia Nagar.','4'],
        ['Nisha Patel','Jasola · Google','NP','Had the Paneer Tikka for the first time here and I am hooked! Perfectly spiced. The Kheer for dessert was the cherry on top.','5'],
        ['Imran Khan','Shaheen Bagh · Google','IK','Reserved via WhatsApp — super smooth. The restaurant looks beautiful inside. Food was exceptional — especially the Lucknowi Biryani!','5'],
    ];
    ob_start(); ?>
<section class="section bg-dark" id="reviews">
  <div class="container">
    <div class="text-center">
      <span class="section-subtitle">Customer Love</span>
      <h2 class="section-title">What Our Guests Say</h2>
      <div class="divider"><span>✦</span></div>
    </div>
    <div class="slider-wrap">
      <div class="slider-track" id="reviewTrack">
        <?php foreach ($reviews as $r): ?>
        <div class="review-card">
          <div class="review-stars"><?php echo str_repeat('★', (int)$r[4]) . str_repeat('☆', 5-(int)$r[4]); ?></div>
          <p class="review-text"><?php echo esc_html($r[3]); ?></p>
          <div class="review-author">
            <div class="review-avatar"><?php echo esc_html($r[2]); ?></div>
            <div><div class="review-name"><?php echo esc_html($r[0]); ?></div><div class="review-loc"><?php echo esc_html($r[1]); ?></div></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="slider-controls">
      <button class="slider-btn" id="rPrev">←</button>
      <div class="slider-dots" id="rDots"></div>
      <button class="slider-btn" id="rNext">→</button>
    </div>
  </div>
</section>
<?php return ob_get_clean();
}

function zahra_render_zahra_order_cta($atts) {
    ob_start(); ?>
<section class="section bg-darker">
  <div class="container text-center">
    <span class="section-subtitle">Order Online</span>
    <h2 class="section-title">Craving Something Delicious?</h2>
    <div class="divider"><span>✦</span></div>
    <p class="section-desc">Get your favourite Zahra dishes delivered right to your doorstep. Available on Zomato &amp; Swiggy.</p>
    <div class="btn-row center mt-md">
      <a href="<?php echo esc_url(zahra_zomato()); ?>" class="btn btn-zomato" target="_blank" rel="noopener">🔴 Order on Zomato</a>
      <a href="<?php echo esc_url(zahra_swiggy()); ?>" class="btn btn-swiggy" target="_blank" rel="noopener">🟠 Order on Swiggy</a>
      <a href="<?php echo esc_url(zahra_wa_url('I want to place an order')); ?>" class="btn btn-whatsapp" target="_blank" rel="noopener">💬 WhatsApp Order</a>
    </div>
  </div>
</section>
<?php return ob_get_clean();
}

function zahra_render_zahra_social_follow($atts) {
    ob_start(); ?>
<section class="section bg-dark">
  <div class="container text-center">
    <span class="section-subtitle">Stay Connected</span>
    <h2 class="section-title">Follow Our Journey</h2>
    <div class="divider"><span>✦</span></div>
    <p class="section-desc">Follow us on Instagram for the latest dishes, special offers, and behind-the-scenes from our kitchen.</p>
    <div class="social-row">
      <a href="https://instagram.com/zehra_restaurant" class="social-card ig" target="_blank" rel="noopener"><span class="si">📸</span><div><span class="sn">Instagram</span><span class="sh">@zehra_restaurant</span></div></a>
      <a href="<?php echo esc_url(zahra_wa_url()); ?>" class="social-card wa" target="_blank" rel="noopener"><span class="si">💬</span><div><span class="sn">WhatsApp</span><span class="sh">Chat &amp; Reserve</span></div></a>
      <a href="<?php echo esc_url(zahra_zomato()); ?>" class="social-card zm" target="_blank" rel="noopener"><span class="si">🍽️</span><div><span class="sn">Zomato</span><span class="sh">Order &amp; Review</span></div></a>
    </div>
  </div>
</section>
<?php return ob_get_clean();
}

function zahra_render_zahra_contact_map($atts) {
    $wa = zahra_wa_url('I want to reserve a table');
    ob_start(); ?>
<section class="section bg-darker" id="contact">
  <div class="container">
    <div class="text-center">
      <span class="section-subtitle">Find Us</span>
      <h2 class="section-title">Visit Zahra Restaurant</h2>
      <div class="divider"><span>✦</span></div>
    </div>
    <div class="contact-grid">
      <div class="contact-cards">
        <?php $items = [
            ['📍','Address','268, Tikona Park Marg, Jamia Nagar, Okhla, New Delhi – 110025'],
            ['📞','Phone','<a href="tel:+919355643665">+91 93556 43665</a>'],
            ['💬','WhatsApp','<a href="'.esc_url($wa).'" target="_blank" rel="noopener">Chat to Reserve a Table</a>'],
            ['📸','Instagram','<a href="https://instagram.com/zehra_restaurant" target="_blank" rel="noopener">@zehra_restaurant</a>'],
            ['🕐','Hours','Open Daily: 11:00 AM – 11:30 PM'],
        ]; foreach ($items as $i): ?>
        <div class="cc">
          <div class="cc-icon"><?php echo $i[0]; ?></div>
          <div><h4><?php echo $i[1]; ?></h4><p><?php echo $i[2]; ?></p></div>
        </div>
        <?php endforeach; ?>
        <div class="btn-row mt-md">
          <a href="https://maps.google.com/?q=Zahra+Restaurant+Cafe+Jamia+Nagar+New+Delhi" target="_blank" rel="noopener" class="btn btn-outline">📍 Get Directions</a>
          <a href="tel:+919355643665" class="btn btn-outline">📞 Call Now</a>
        </div>
      </div>
      <div class="map-wrap anim">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3505.2758985879365!2d77.27730261508044!3d28.561169982449386!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390ce3b4b7b7e3a3%3A0x3b0e3a3b4b7b7e3a!2sJamia%20Nagar%2C%20Okhla%2C%20New%20Delhi%2C%20Delhi%20110025!5e0!3m2!1sen!2sin!4v1620000000000!5m2!1sen!2sin"
          width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Zahra Restaurant Location"></iframe>
      </div>
    </div>
  </div>
</section>
<?php return ob_get_clean();
}

/* ---- MENU PAGE ---- */
function zahra_render_zahra_menu_page($atts) {
    $u = ZAHRA_URI;
    ob_start(); ?>
<section class="section bg-dark">
  <div class="container">
    <div class="text-center mb-xl">
      <span class="section-subtitle">Official Menu Cards</span>
      <h2 class="section-title">Browse Our Menu</h2>
      <div class="divider"><span>✦</span></div>
    </div>
    <div class="menu-imgs-row">
      <div class="menu-img-card"><img src="<?php echo $u; ?>/images/menu1.png" alt="Menu - Appetizers &amp; Biryani" loading="lazy"></div>
      <div class="menu-img-card"><img src="<?php echo $u; ?>/images/menu2.png" alt="Menu - Main Course &amp; Kebabs" loading="lazy"></div>
      <div class="menu-img-card"><img src="<?php echo $u; ?>/images/menu3.png" alt="Menu - Breads, Desserts &amp; Drinks" loading="lazy"></div>
    </div>

    <div class="text-center mt-xxl mb-xl">
      <span class="section-subtitle">Quick Browse</span>
      <h2 class="section-title">Menu by Category</h2>
      <div class="divider"><span>✦</span></div>
    </div>
    <div class="menu-tabs">
      <button class="mtab active" data-tab="appetizers">🍗 Appetizers</button>
      <button class="mtab" data-tab="biryani">🍛 Biryani</button>
      <button class="mtab" data-tab="main">🥘 Main Course</button>
      <button class="mtab" data-tab="kebabs">🍢 Kebabs</button>
      <button class="mtab" data-tab="breads">🫓 Breads</button>
      <button class="mtab" data-tab="desserts">🍮 Desserts</button>
      <button class="mtab" data-tab="drinks">☕ Drinks</button>
    </div>

    <div class="menu-section active" id="tab-appetizers">
      <div class="msec-title">🍗 Appetizers</div>
      <div class="menu-grid">
        <?php $apps = [
          ['Tangdi Kebab','Juicy leg piece kebab','₹180','₹360','₹720'],
          ['Chicken Burra','Whole chicken marinated in spices','₹210','₹420','₹840'],
          ['Chicken Fry','Crispy fried chicken','₹180','₹360','₹720'],
          ['Bhatti Ka Murgh','Rustic flame-grilled chicken','₹210','₹420','₹840'],
          ['Malai Tikka','Creamy marinated tikka','₹240','₹480','₹960'],
          ['Malai Tikka Gravy','Malai tikka in rich gravy','₹220','₹440','₹980'],
          ['Chicken Tikka','Classic chargrilled tikka','₹220','₹460','₹920'],
          ['Afghani Chicken Dry','Kabuli-style dry chicken','₹240','₹480','₹960'],
          ['Afghani Chicken Gravy','Afghani chicken in gravy','₹230','₹460','₹920'],
          ['Tandoori Chicken Dry','Classic tandoor chicken','₹230','₹460','₹920'],
          ['Tandoori Chicken Gravy','Tandoor chicken with gravy','₹240','₹480','₹960'],
          ['Chicken Lollipop','Crispy chicken lollipops','₹190','₹380','₹760'],
          ['White Chicken BBQ (Tahir)','Signature white barbeque','₹230','₹460','₹920'],
          ['Mutton Burra','Tender mutton burra','₹350','₹700','₹1400'],
          ['Paneer Tikka 🟢','Grilled paneer with peppers','₹200','₹400','₹800'],
          ['Paneer Malai Tikka 🟢','Creamy grilled paneer','₹210','₹420','₹840'],
          ['Malai Soya Chaap 🟢','Soya chaap in malai','₹200','₹300','₹400'],
        ]; foreach($apps as $a): ?>
        <div class="menu-item">
          <div><h4><?php echo $a[0]; ?></h4><p><?php echo $a[1]; ?></p></div>
          <div class="miprice"><span class="price"><?php echo $a[2]; ?></span><span class="sizes">H <?php echo $a[3]; ?> · F <?php echo $a[4]; ?></span></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="menu-section" id="tab-biryani">
      <div class="msec-title">🍛 Biryani</div>
      <div class="menu-grid">
        <?php $b=[['Hyderabadi Dum Biryani','Slow-cooked dum biryani','₹100','₹150','₹290'],['Lucknowi Biryani','Awadhi-style biryani','₹100','₹150','₹290'],['Chicken Handi Biryani','Cooked in traditional handi','₹200','₹300','₹400']];
        foreach($b as $i): ?>
        <div class="menu-item"><div><h4><?php echo $i[0]; ?></h4><p><?php echo $i[1]; ?></p></div><div class="miprice"><span class="price"><?php echo $i[2]; ?></span><span class="sizes">H <?php echo $i[3]; ?> · F <?php echo $i[4]; ?></span></div></div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="menu-section" id="tab-main">
      <div class="msec-title">🥘 Main Course</div>
      <div class="menu-grid">
        <?php $m=[['Zahra Special (Chicken Gravy)','Signature chicken preparation','₹240','₹480','₹960'],['Butter Chicken','Creamy tomato butter gravy','₹240','₹480','₹960'],['Chicken Handi','Handi-cooked chicken curry','₹260','₹520','₹1040'],['Kadhai Chicken','Spicy kadhai-style chicken','₹260','₹520','₹1040'],['Chicken Labadar','Onion-tomato chicken masala','₹280','₹560','₹1120'],['Chicken Deewani Handi','Rich handi chicken','₹280','₹560','₹1120'],['Butter Chicken Masala','Spiced butter chicken','₹260','₹520','₹1040'],['Chicken Qorma','Mild aromatic qorma','₹240','₹480','₹960'],['Chicken Changezi','Delhi-style changezi','₹260','₹520','₹1040'],['Lahori Mutton','Punchy Lahori spices','₹320','₹640','₹1280'],['Mutton Handi','Slow-cooked mutton handi','₹320','₹640','₹1280'],['Mutton Nihari','Classic breakfast nihari','₹300','₹600','₹1200'],['Mutton Deewani Handi','Rich mutton handi','₹320','₹640','₹1280'],['Mutton Kadhai Gosht','Spicy kadhai mutton','₹320','₹640','₹1280']];
        foreach($m as $i): ?>
        <div class="menu-item"><div><h4><?php echo $i[0]; ?></h4><p><?php echo $i[1]; ?></p></div><div class="miprice"><span class="price"><?php echo $i[2]; ?></span><span class="sizes">H <?php echo $i[3]; ?> · F <?php echo $i[4]; ?></span></div></div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="menu-section" id="tab-kebabs">
      <div class="msec-title">🍢 Kebabs (4 Pcs)</div>
      <div class="menu-grid">
        <?php $k=[['Shami Kebab','Minced meat patties','₹240'],['Turkish Seekh Kebab','Turkish-style seekh','₹260'],['Chicken Malai Kebab','Creamy malai seekh','₹260'],['Zahra Seekh Kebab','Signature seekh kebab','₹260'],['Turkish Mutton Kebab','Tender mutton seekh','₹400']];
        foreach($k as $i): ?>
        <div class="menu-item"><div><h4><?php echo $i[0]; ?></h4><p><?php echo $i[1]; ?></p></div><div class="miprice"><span class="price"><?php echo $i[2]; ?></span><span class="sizes">4 Pieces</span></div></div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="menu-section" id="tab-breads">
      <div class="msec-title">🫓 Indian Flat Breads</div>
      <div class="menu-grid">
        <?php $br=[['Butter Naan 🟢','Buttery soft naan','₹35'],['Butter Roti 🟢','Whole wheat butter roti','₹20'],['Khameri Roti 🟢','Leavened bread','₹20'],['Roomali Roti 🟢','Thin handkerchief bread','₹10']];
        foreach($br as $i): ?>
        <div class="menu-item"><div><h4><?php echo $i[0]; ?></h4><p><?php echo $i[1]; ?></p></div><div class="miprice"><span class="price"><?php echo $i[2]; ?></span><span class="sizes">Per Piece</span></div></div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="menu-section" id="tab-desserts">
      <div class="msec-title">🍮 Desserts</div>
      <div class="menu-grid">
        <div class="menu-item"><div><h4>Kheer 🟢</h4><p>Traditional rice pudding with almonds &amp; cardamom</p></div><div class="miprice"><span class="price">₹80</span><span class="sizes">Per Serving</span></div></div>
      </div>
    </div>

    <div class="menu-section" id="tab-drinks">
      <div class="msec-title">☕ Beverages &amp; Shakes</div>
      <div class="menu-grid">
        <?php $d=[['Turkish Tea','Authentic Turkish çay','₹25'],['KitKat Shake','Chocolatey milkshake','₹80'],['Oreo Shake','Creamy Oreo shake','₹80'],['Butterscotch Apple Shake','Sweet butterscotch apple','₹80'],['Mango Shake','Fresh mango shake','₹80'],['Pineapple Shake','Tropical pineapple','₹80'],['Strawberry Shake','Fresh strawberry','₹80'],['Cold Coffee','Chilled coffee shake','₹80'],['Virgin Mojito','Refreshing mint mojito','₹80'],['Green Apple Mojito','Apple &amp; mint twist','₹80'],['Blue Shock Mojito','Blueberry mojito','₹80'],['Soft Drinks','Pepsi, 7UP etc.','MRP'],['Bottled Water','Packaged water','MRP']];
        foreach($d as $i): ?>
        <div class="menu-item"><div><h4><?php echo $i[0]; ?></h4><p><?php echo $i[1]; ?></p></div><div class="miprice"><span class="price"><?php echo $i[2]; ?></span></div></div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="order-cta-box text-center mt-xl">
      <h3>Ready to Order?</h3>
      <p>Order online or reserve your table now.</p>
      <div class="btn-row center mt-md">
        <a href="<?php echo esc_url(zahra_zomato()); ?>" class="btn btn-zomato" target="_blank" rel="noopener">🔴 Order on Zomato</a>
        <a href="<?php echo esc_url(zahra_swiggy()); ?>" class="btn btn-swiggy" target="_blank" rel="noopener">🟠 Order on Swiggy</a>
        <a href="<?php echo esc_url(zahra_wa_url('I want to reserve a table')); ?>" class="btn btn-whatsapp" target="_blank" rel="noopener">💬 Reserve Table</a>
      </div>
    </div>
  </div>
</section>
<?php return ob_get_clean();
}

/* ---- GALLERY PAGE ---- */
function zahra_render_zahra_gallery_page($atts) {
    $u = ZAHRA_URI;
    $food = [
        ['biryani.jpg','Hyderabadi Dum Biryani','wide tall'],
        ['handi-biryani.webp','Chicken Handi Biryani',''],
        ['malai-tikka.webp','Malai Tikka',''],
        ['chicken-tikka.webp','Chicken Tikka',''],
        ['kebab.webp','Seekh Kebab',''],
        ['paneer-tikka.webp','Paneer Tikka','wide'],
        ['chicken-lollipop.webp','Chicken Lollipop',''],
        ['shawarma.webp','Shawarma',''],
        ['kheer.webp','Traditional Kheer',''],
        ['turkish-tea.webp','Turkish Tea',''],
    ];
    $interior = [
        ['front.jpg','Zahra Exterior','wide'],
        ['interior1.jpg','Dining Area',''],
        ['interior2.jpg','Cosy Seating',''],
        ['interior3.jpg','Restaurant Ambiance',''],
    ];
    ob_start(); ?>
<section class="section bg-dark">
  <div class="container">
    <div class="text-center mb-xl">
      <span class="section-subtitle">Our Dishes</span>
      <h2 class="section-title">Food Gallery</h2>
      <div class="divider"><span>✦</span></div>
    </div>
    <div class="gallery-grid" id="galleryGrid">
      <?php foreach($food as $f): ?>
      <div class="gitem <?php echo $f[2]; ?>" data-src="<?php echo $u.'/images/'.$f[0]; ?>" data-caption="<?php echo esc_attr($f[1]); ?>">
        <img src="<?php echo $u.'/images/'.$f[0]; ?>" alt="<?php echo esc_attr($f[1]); ?>" loading="lazy">
        <div class="goverlay"><span>🔍</span></div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-xxl mb-xl">
      <span class="section-subtitle">Ambiance</span>
      <h2 class="section-title">Inside Zahra</h2>
      <div class="divider"><span>✦</span></div>
    </div>
    <div class="gallery-grid" id="interiorGrid" style="grid-template-columns:repeat(3,1fr);grid-auto-rows:280px;">
      <?php foreach($interior as $i): ?>
      <div class="gitem <?php echo $i[2]; ?>" data-src="<?php echo $u.'/images/'.$i[0]; ?>" data-caption="<?php echo esc_attr($i[1]); ?>">
        <img src="<?php echo $u.'/images/'.$i[0]; ?>" alt="<?php echo esc_attr($i[1]); ?>" loading="lazy">
        <div class="goverlay"><span>🔍</span></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<!-- Lightbox -->
<div class="lightbox" id="lightbox">
  <button class="lb-close" id="lbClose">✕</button>
  <img src="" alt="" id="lbImg">
  <div class="lb-nav"><button id="lbPrev">← Prev</button><button id="lbNext">Next →</button></div>
</div>
<?php return ob_get_clean();
}

/* ---- ABOUT PAGE ---- */
function zahra_render_zahra_about_page($atts) {
    $u = ZAHRA_URI;
    $wa = zahra_wa_url('I want to visit Zahra Restaurant');
    ob_start(); ?>
<section class="section bg-dark">
  <div class="container">
    <div class="about-grid">
      <div class="about-imgs anim">
        <div class="about-main"><img src="<?php echo $u; ?>/images/front.jpg" alt="Zahra Exterior"></div>
        <div class="about-accent"><img src="<?php echo $u; ?>/images/interior2.jpg" alt="Interior"></div>
        <div class="about-badge-float"><span class="n">❤️</span><span class="l">Made with Love</span></div>
      </div>
      <div class="about-text-col anim">
        <span class="section-subtitle">Who We Are</span>
        <h2 class="section-title">Where Heritage Meets <span class="gold">Hospitality</span></h2>
        <div class="divider left"><span>✦</span></div>
        <p class="body-text">Zahra Restaurant &amp; Café was founded with a simple yet powerful vision: to bring the authentic flavors of Mughlai and Hyderabadi cuisine to the heart of New Delhi's Jamia Nagar.</p>
        <p class="body-text">Every recipe we use is steeped in tradition — from the slow-cooked Dum Biryani that takes hours of careful preparation, to the hand-crafted seekh kebabs formed and grilled over live charcoal.</p>
        <p class="body-text">We take immense pride in using fresh, high-quality ingredients sourced daily. Our spices are ground in-house, and our dairy is always fresh. This commitment to quality is what sets Zahra apart.</p>
        <div class="about-stats">
          <div><span class="n">50+</span><span class="l">Menu Items</span></div>
          <div><span class="n">1000+</span><span class="l">Happy Guests</span></div>
          <div><span class="n">5★</span><span class="l">Google Rating</span></div>
        </div>
        <a href="<?php echo esc_url($wa); ?>" class="btn btn-whatsapp" target="_blank" rel="noopener">💬 Book a Table</a>
      </div>
    </div>
  </div>
</section>
<section class="section bg-darker">
  <div class="container text-center">
    <span class="section-subtitle">What We Stand For</span>
    <h2 class="section-title">Our Values</h2>
    <div class="divider"><span>✦</span></div>
    <div class="values-grid">
      <?php $vals=[['🔥','Authentic Recipes','We preserve traditional cooking methods — dum cooking, live tandoor, and slow-simmered gravies.'],['✨','Fresh Ingredients','Every ingredient is carefully sourced fresh daily. No shortcuts, ever.'],['❤️','Warm Hospitality','You are family at Zahra. Every guest is treated with warmth and care.']];
      foreach($vals as $v): ?>
      <div class="val-card anim">
        <div class="val-icon"><?php echo $v[0]; ?></div>
        <h3><?php echo $v[1]; ?></h3>
        <p><?php echo $v[2]; ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<section class="section bg-dark">
  <div class="container text-center">
    <span class="section-subtitle">Our Space</span>
    <h2 class="section-title">The Zahra Experience</h2>
    <div class="divider"><span>✦</span></div>
    <div class="interior-grid">
      <?php foreach(['interior1.jpg','interior2.jpg','interior3.jpg'] as $img): ?>
      <div class="int-img"><img src="<?php echo $u; ?>/images/<?php echo $img; ?>" alt="Zahra Interior" loading="lazy"></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php return ob_get_clean();
}

/* ---- CONTACT PAGE ---- */
function zahra_render_zahra_contact_page($atts) {
    $wa = zahra_wa_url('I want to reserve a table at Zahra Restaurant');
    ob_start(); ?>
<section class="section bg-dark">
  <div class="container">
    <div class="contact-grid">
      <div class="contact-cards">
        <?php $items=[['📍','Address','268, Tikona Park Marg, Jamia Nagar, Okhla, New Delhi – 110025'],['📞','Phone','<a href="tel:+919355643665">+91 93556 43665</a>'],['💬','WhatsApp','<a href="'.esc_url($wa).'" target="_blank" rel="noopener">+91 93556 43665</a>'],['📸','Instagram','<a href="https://instagram.com/zehra_restaurant" target="_blank" rel="noopener">@zehra_restaurant</a>'],['🕐','Hours','Open Daily: 11:00 AM – 11:30 PM']];
        foreach($items as $i): ?>
        <div class="cc"><div class="cc-icon"><?php echo $i[0]; ?></div><div><h4><?php echo $i[1]; ?></h4><p><?php echo $i[2]; ?></p></div></div>
        <?php endforeach; ?>
        <div class="btn-row mt-md">
          <a href="<?php echo esc_url($wa); ?>" class="btn btn-whatsapp" target="_blank" rel="noopener">💬 Reserve via WhatsApp</a>
          <a href="https://maps.google.com/?q=Zahra+Restaurant+Jamia+Nagar+New+Delhi" target="_blank" rel="noopener" class="btn btn-outline">📍 Get Directions</a>
        </div>
      </div>
      <div class="form-col">
        <div class="form-card">
          <h3>Send Us a Message</h3>
          <p>We'll get back to you within a few hours.</p>
          <div id="formMsg" class="form-msg" style="display:none"></div>
          <form id="contactForm">
            <?php wp_nonce_field('zahra_contact','zahra_nonce'); ?>
            <div class="fg"><label>Your Name *</label><input type="text" name="cname" placeholder="Enter your name" required></div>
            <div class="fg"><label>Phone Number *</label><input type="tel" name="cphone" placeholder="+91 XXXXX XXXXX" required></div>
            <div class="fg"><label>Message</label><textarea name="cmsg" placeholder="Reservation, enquiry, feedback..."></textarea></div>
            <button type="submit" class="btn btn-primary" style="width:100%">Send Message →</button>
          </form>
        </div>
        <div class="map-wrap mt-md">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3505.2758985879365!2d77.27730261508044!3d28.561169982449386!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390ce3b4b7b7e3a3%3A0x3b0e3a3b4b7b7e3a!2sJamia%20Nagar%2C%20Okhla%2C%20New%20Delhi%2C%20Delhi%20110025!5e0!3m2!1sen!2sin!4v1620000000000!5m2!1sen!2sin"
            width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Location Map"></iframe>
        </div>
      </div>
    </div>
  </div>
</section>
<?php return ob_get_clean();
}

/* ---- PRIVACY PAGE ---- */
function zahra_render_zahra_privacy_page($atts) {
    ob_start(); ?>
<section class="section bg-dark">
  <div class="container"><div class="policy-wrap">
    <p>Welcome to Zahra Restaurant &amp; Café. This Privacy Policy explains how we collect, use, and safeguard your information.</p>
    <h2>1. Information We Collect</h2>
    <p>We may collect your name, phone number, and message when you submit our contact form or interact with us via WhatsApp.</p>
    <h2>2. How We Use It</h2>
    <p>We use your information to respond to enquiries, process reservations, and improve our services. We never sell your data.</p>
    <h2>3. Third-Party Services</h2>
    <p>Our site links to Zomato, Swiggy, WhatsApp, Instagram, and Google Maps. Please review their respective privacy policies.</p>
    <h2>4. Data Security</h2>
    <p>We implement appropriate technical measures to protect your information. No internet transmission is 100% secure.</p>
    <h2>5. Your Rights</h2>
    <p>You may request access, correction, or deletion of your data by contacting us at +91 93556 43665.</p>
    <h2>6. Contact</h2>
    <p>268, Tikona Park Marg, Jamia Nagar, Okhla, New Delhi – 110025 · Phone: +91 93556 43665</p>
  </div></div>
</section>
<?php return ob_get_clean();
}

/* ---- TERMS PAGE ---- */
function zahra_render_zahra_terms_page($atts) {
    ob_start(); ?>
<section class="section bg-dark">
  <div class="container"><div class="policy-wrap">
    <p>Please read these Terms and Conditions carefully before using the Zahra Restaurant &amp; Café website.</p>
    <h2>1. Use of Website</h2>
    <p>This website is for informational purposes about Zahra Restaurant &amp; Café. You agree to use it only for lawful purposes.</p>
    <h2>2. Reservations</h2>
    <p>Table reservations via WhatsApp or phone are subject to availability. For groups of 10+, please give 24-hour notice.</p>
    <h2>3. Online Orders</h2>
    <p>Orders via Zomato and Swiggy are governed by their respective terms. We are responsible for food quality, not delivery timelines.</p>
    <h2>4. Pricing</h2>
    <p>All prices are in Indian Rupees (INR) inclusive of applicable taxes. Prices are subject to change without notice.</p>
    <h2>5. Intellectual Property</h2>
    <p>All content including images and logos are property of Zahra Restaurant &amp; Café. Reproduction without written permission is prohibited.</p>
    <h2>6. Food Allergies</h2>
    <p>Our kitchen handles nuts, dairy, and gluten. Please inform staff of any allergies before ordering.</p>
    <h2>7. Governing Law</h2>
    <p>These Terms are governed by the laws of India. Disputes are subject to the jurisdiction of courts in New Delhi.</p>
    <h2>8. Contact</h2>
    <p>268, Tikona Park Marg, Jamia Nagar, Okhla, New Delhi – 110025 · Phone: +91 93556 43665</p>
  </div></div>
</section>
<?php return ob_get_clean();
}

/* ============================================================
   HELPER FUNCTIONS
   ============================================================ */
function zahra_wa_url($msg = '') {
    $phone = '919355643665';
    $text  = $msg ?: 'Hello! I would like to enquire about Zahra Restaurant.';
    return 'https://wa.me/' . $phone . '?text=' . rawurlencode($text);
}
function zahra_zomato() {
    return 'https://www.zomato.com/ncr/zahra-restaurant-cafe-jamia-nagar-new-delhi';
}
function zahra_swiggy() {
    return 'https://www.swiggy.com/restaurants/zahra-restaurant-cafe-new-delhi';
}

/* ============================================================
   AJAX CONTACT FORM
   ============================================================ */
add_action('wp_ajax_zahra_contact_form', 'zahra_process_contact');
add_action('wp_ajax_nopriv_zahra_contact_form', 'zahra_process_contact');

function zahra_process_contact() {
    check_ajax_referer('zahra_nonce', 'zahra_nonce');
    $name  = sanitize_text_field($_POST['cname']  ?? '');
    $phone = sanitize_text_field($_POST['cphone'] ?? '');
    $msg   = sanitize_textarea_field($_POST['cmsg'] ?? '');
    if (!$name || !$phone) {
        wp_send_json_error(['msg' => 'Please fill in all required fields.']);
    }
    $to      = get_option('admin_email');
    $subject = 'New Enquiry from ' . $name . ' — Zahra Restaurant';
    $body    = "Name: $name\nPhone: $phone\nMessage: $msg";
    wp_mail($to, $subject, $body);
    wp_send_json_success(['msg' => 'Thank you! We will contact you shortly. 🙏']);
}

/* ============================================================
   SEO / SCHEMA
   ============================================================ */
add_action('wp_head', function () {
    $schema = [
        '@context'  => 'https://schema.org',
        '@type'     => 'Restaurant',
        'name'      => 'Zahra Restaurant & Café',
        'description' => 'Authentic Biryani & Fast Food in Jamia Nagar, Okhla, New Delhi.',
        'url'       => home_url(),
        'telephone' => '+91-9355643665',
        'address'   => ['@type'=>'PostalAddress','streetAddress'=>'268, Tikona Park Marg, Jamia Nagar','addressLocality'=>'Okhla','addressRegion'=>'New Delhi','postalCode'=>'110025','addressCountry'=>'IN'],
        'geo'       => ['@type'=>'GeoCoordinates','latitude'=>'28.5612','longitude'=>'77.2773'],
        'openingHoursSpecification' => [['@type'=>'OpeningHoursSpecification','dayOfWeek'=>['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'],'opens'=>'11:00','closes'=>'23:30']],
        'servesCuisine' => ['Mughlai','Hyderabadi','North Indian','Fast Food'],
        'priceRange'    => '₹₹',
        'image'         => ZAHRA_URI . '/images/front.jpg',
        'logo'          => ZAHRA_URI . '/images/logo.png',
    ];
    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) . '</script>' . "\n";
    $desc = 'Zahra Restaurant & Café — Authentic Biryani, Tandoori & Fast Food in Jamia Nagar, Okhla, New Delhi. Order on Zomato & Swiggy or reserve via WhatsApp.';
    echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";
    echo '<meta property="og:image" content="' . esc_url(ZAHRA_URI . '/images/front.jpg') . '">' . "\n";
    echo '<meta property="og:type" content="restaurant">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}, 2);

/* ============================================================
   ADMIN NOTICE — setup status
   ============================================================ */
add_action('admin_notices', function () {
    if (get_option('zahra_setup_done')) {
        echo '<div class="notice notice-success is-dismissible"><p><strong>✅ Zahra Restaurant theme activated!</strong> All 7 pages have been created automatically. <a href="' . admin_url('edit.php?post_type=page') . '">View Pages →</a></p></div>';
    }
});

/* ============================================================
   THEME MODS / CUSTOMIZER
   ============================================================ */
add_action('customize_register', function ($wp_customize) {
    $wp_customize->add_section('zahra_settings', ['title' => 'Zahra Restaurant Settings', 'priority' => 30]);
    $fields = [
        ['zahra_phone',    'Phone Number',    '+91 93556 43665'],
        ['zahra_whatsapp', 'WhatsApp Number', '919355643665'],
        ['zahra_address',  'Address',         '268, Tikona Park Marg, Jamia Nagar, Okhla, New Delhi – 110025'],
        ['zahra_hours',    'Opening Hours',   'Open Daily: 11:00 AM – 11:30 PM'],
        ['zahra_zomato',   'Zomato URL',      'https://www.zomato.com/ncr/zahra-restaurant-cafe-jamia-nagar-new-delhi'],
        ['zahra_swiggy',   'Swiggy URL',      'https://www.swiggy.com/restaurants/zahra-restaurant-cafe-new-delhi'],
        ['zahra_instagram','Instagram URL',   'https://instagram.com/zehra_restaurant'],
    ];
    foreach ($fields as [$id, $label, $default]) {
        $wp_customize->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_text_field']);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'zahra_settings', 'type' => 'text']);
    }
});
