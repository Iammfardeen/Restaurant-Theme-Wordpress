<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header id="zahra-header">
  <div class="container">
    <div class="header-inner">
      <a href="<?php echo home_url('/'); ?>" class="zahra-logo">
        <img src="<?php echo ZAHRA_URI; ?>/images/logo.png" alt="Zahra Restaurant Logo" width="52" height="52">
        <div class="logo-txt">
          <span class="lname">ZAHRA</span>
          <span class="lsub">Restaurant &amp; Café</span>
        </div>
      </a>

      <nav id="zahra-nav">
        <ul>
          <li><a href="<?php echo home_url('/'); ?>" <?php if(is_front_page()) echo 'class="active"'; ?>>Home</a></li>
          <li><a href="<?php echo get_permalink(get_page_by_path('menu')); ?>" <?php if(is_page('menu')) echo 'class="active"'; ?>>Menu</a></li>
          <li><a href="<?php echo get_permalink(get_page_by_path('gallery')); ?>" <?php if(is_page('gallery')) echo 'class="active"'; ?>>Gallery</a></li>
          <li><a href="<?php echo get_permalink(get_page_by_path('about-us')); ?>" <?php if(is_page('about-us')) echo 'class="active"'; ?>>About Us</a></li>
          <li><a href="<?php echo get_permalink(get_page_by_path('contact')); ?>" <?php if(is_page('contact')) echo 'class="active"'; ?>>Contact</a></li>
          <li><a href="<?php echo esc_url(zahra_wa_url('I want to reserve a table at Zahra Restaurant')); ?>" class="nav-cta" target="_blank" rel="noopener">📅 Reserve Table</a></li>
        </ul>
      </nav>

      <button class="hamburger" id="zahraHam" aria-label="Toggle menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<nav class="mob-nav" id="zahraMobNav">
  <a href="<?php echo home_url('/'); ?>">Home</a>
  <a href="<?php echo get_permalink(get_page_by_path('menu')); ?>">Menu</a>
  <a href="<?php echo get_permalink(get_page_by_path('gallery')); ?>">Gallery</a>
  <a href="<?php echo get_permalink(get_page_by_path('about-us')); ?>">About Us</a>
  <a href="<?php echo get_permalink(get_page_by_path('contact')); ?>">Contact</a>
  <a href="<?php echo esc_url(zahra_wa_url('I want to reserve a table')); ?>" style="color:var(--gold)" target="_blank" rel="noopener">📅 Reserve Table</a>
</nav>

<main id="main-content">
