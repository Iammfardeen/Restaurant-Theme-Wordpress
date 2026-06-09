<?php
/**
 * Default Page Template
 * Renders the page content (including shortcodes auto-inserted on activation)
 */
get_header();

$template = get_post_meta(get_the_ID(), '_wp_page_template', true);
$is_inner = !in_array($template, ['page-home.php','default']);
?>

<?php if ($is_inner): ?>
<div class="page-hero">
  <div class="container">
    <div class="page-hero-inner text-center">
      <nav class="breadcrumb">
        <a href="<?php echo home_url('/'); ?>">Home</a>
        <span class="sep">/</span>
        <span class="cur"><?php the_title(); ?></span>
      </nav>
      <span class="section-subtitle">Zahra Restaurant &amp; Café</span>
      <h1 class="section-title"><?php the_title(); ?></h1>
      <div class="divider"><span>✦</span></div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if (have_posts()): while (have_posts()): the_post(); ?>
  <?php the_content(); ?>
<?php endwhile; endif; ?>

<?php get_footer(); ?>
