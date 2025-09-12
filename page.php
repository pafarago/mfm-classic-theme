<?php get_header(); ?>

<main id="site-content">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
      <header class="entry-header">
        <?php the_title('<h1 class="entry-title">','</h1>'); // never link page titles ?>
      </header>

      <div class="entry-content">
        <?php the_content(); ?>
      </div>
    </article>

  <?php endwhile; else : ?>
    <p>No content found.</p>
  <?php endif; ?>
</main>

<?php get_footer(); ?>
