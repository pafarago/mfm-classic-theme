<?php
/**
 * Template Name: Embed-All Page (fallback)
 * Renders /embed?id=... via [embed-all] shortcode.
 */
get_header();

$id        = isset($_GET['id'])        ? sanitize_text_field($_GET['id'])    : '';
$Id        = isset($_GET['Id'])        ? sanitize_text_field($_GET['Id'])    : '';
$top       = isset($_GET['top'])       ? sanitize_text_field($_GET['top'])   : '';
$title     = isset($_GET['title'])     ? sanitize_text_field($_GET['title']) : '';
if ( $Id != '') {
       $id = $Id;	
}
echo "<!-- embed-all entry id=" . esc_html($id) . " -->\n";
?>
<main id="site-content">
  <div class="content-box">
    <?php
    if ($id === '') {
        echo "<p class='embed-all-error'>Missing ?id= parameter.</p>";
    } else {
        echo do_shortcode('[embed-all id="' . esc_attr($id) . '" top="' . esc_attr($top). '" title="' . esc_attr($title) . '"]');
    }
    ?>
  </div>
</main>
<?php get_footer(); ?>
