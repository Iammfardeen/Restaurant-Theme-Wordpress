<?php get_header(); ?>
<div class="page-hero">
  <div class="container"><div class="page-hero-inner text-center">
    <?php if(have_posts()): while(have_posts()): the_post(); ?>
    <h1 class="section-title"><?php the_title(); ?></h1>
    <?php endwhile; endif; ?>
  </div></div>
</div>
<section class="section bg-dark">
  <div class="container">
    <?php if(have_posts()): while(have_posts()): the_post(); ?>
    <div class="policy-wrap"><?php the_content(); ?></div>
    <?php endwhile; else: ?>
    <p style="text-align:center;color:var(--muted)">No content found.</p>
    <?php endif; ?>
  </div>
</section>
<?php get_footer(); ?>
