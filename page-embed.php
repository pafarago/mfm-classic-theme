<?php
/**
 * Template Name: Embed-All Page (fallback)
 * Renders /embed?id=... via [embed-all] shortcode.
 */
get_header();

$id    = isset($_GET['id'])    ? sanitize_text_field($_GET['id'])    : '';
$title = isset($_GET['title']) ? sanitize_text_field($_GET['title']) : '';

echo "<!-- embed-all entry id=" . esc_html($id) . " -->\n";
?>
<main id="site-content">
  <div class="content-box">
    <?php
    if ($id === '') {
        echo "<p class='embed-all-error'>Missing ?id= parameter.</p>";
    } else {
        echo do_shortcode('[embed-all id="' . esc_attr($id) . '" title="' . esc_attr($title) . '"]');
    }
    ?>
  </div>
</main>
<?php get_footer(); ?>
