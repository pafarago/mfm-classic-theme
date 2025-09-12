<?php get_header(); ?>

<main id="site-content" class="single-wrap">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

	<?php
	  // Default to home (or use home_url('/blog/') if you have a blog page)
	  $back_url   = home_url('/');
	  $back_label = '← Return to Archive';

	  // If this post has categories, pick the first one
	  $cats = get_the_category();
	  if ( $cats ) {
	    $back_url = get_category_link( $cats[0]->term_id );
 	 }
	?>

    <nav class="post-back top"><a href="<?php echo esc_url( $back_url ); ?>"><?php echo esc_html( $back_label ); ?></a></nav>

    <article id="post-<?php the_ID(); ?>" <?php post_class('single-card'); ?>>
      <header class="entry-header">
        <?php the_title('<h1 class="entry-title">','</h1>'); // no link on single ?>
      </header>

      <div class="entry-content">
        <?php the_content(); ?>
      </div>
    </article>

    <nav class="post-back bottom"><a href="<?php echo esc_url( $back_url ); ?>"><?php echo esc_html( $back_label ); ?></a></nav>

  <?php endwhile; else : ?>
    <p>No content found.</p>
  <?php endif; ?>
</main>

<?php get_footer(); ?>
