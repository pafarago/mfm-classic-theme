<?php get_header(); ?>

<main id="site-content">
  <?php if ( have_posts() ) : ?>
    <?php while ( have_posts() ) : the_post(); ?>

      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <header class="entry-header">
          <?php
          // For lists (home, archives): link post titles; for pages or singular, don’t.
          if ( 'post' === get_post_type() && ! is_singular() ) {
            the_title(
              '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">',
              '</a></h2>'
            );
          } else {
            the_title( '<h2 class="entry-title">', '</h2>' );
          }
          ?>
        </header>

	<div class="entry-content">
	  <div class="excerpt-clamp">
	    <?php the_excerpt(); ?>
	  </div>
	  <p class="more-wrap">
	    <a class="more-link" href="<?php the_permalink(); ?>">Read More</a>
	  </p>
	</div>
      </article>

    <?php endwhile; ?>

    <?php the_posts_navigation(); ?>

  <?php else : ?>
    <p>No content found.</p>
  <?php endif; ?>
</main>

<?php get_footer(); ?>
