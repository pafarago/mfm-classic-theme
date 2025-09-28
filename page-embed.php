<?php
/**
 * Template Name: Embed Page
 *
 * Dynamic handler for /embed/?Id=... URLs
 * - If Id is a file → show [mfm_doc]
 * - If Id is a folder → show [mfm_toc]
 */

get_header();

$id  = isset($_GET['Id']) ? sanitize_text_field($_GET['Id']) : '';
$toc = isset($_GET['toc']) ? sanitize_text_field($_GET['toc']) : '';

printf("<-- page-embed entry id=%s toc=%s -->\n", $id, $toc);
?>
<main id="site-content">
  <div class="content-box">
    <?php
    if (empty($id)) {
        echo "<p class='mfm-doc-error'>Missing ?Id= parameter.</p>";
    } else {
        // Use Google API helper to detect MIME type
        if (function_exists('mfm_googleapi_detect_mime')) {
            $mime = mfm_googleapi_detect_mime($id);
	    printf("<-- page-embed  mime = [%s] -->\n", $mime);
        } else {
            $mime = null; // Fallback: treat unknown as file
        }
	if ($mime === 'application/vnd.google-apps.folder') {
    		list($folder_mime, $folder_name) = mfm_doc_fetch_metadata($id);
    		if (!empty($folder_name)) {
        		echo '<h1 class="entry-title">' . esc_html($folder_name) . '</h1>';
		}
    		echo do_shortcode('[mfm_toc folder="' . esc_attr($id) . '"]');
        } else {
            // Render single document with optional back-to-TOC
            $shortcode = '[mfm_doc id="' . esc_attr($id) . '"';
            if (!empty($toc)) {
                $shortcode .= ' toc="' . esc_attr($toc) . '"';
            }
            $shortcode .= ']';
            echo do_shortcode($shortcode);
        }
    }
    ?>
  </div>
</main>
<?php get_footer(); ?>
