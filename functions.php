<?php

// -------------------------------------------------
// Theme setup
// -------------------------------------------------
function mfm_classic_setup() {
    // Make theme available for translation
    load_theme_textdomain('mfm-classic', get_template_directory() . '/languages');

    // Add default posts and comments RSS feed links to head.
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title.
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails.
    add_theme_support('post-thumbnails');

    // Register navigation menus
    register_nav_menus(array(
        'primary'   => __('Primary Menu', 'mfm-classic'),
        'footer'    => __('Footer Menu', 'mfm-classic'),
    ));

    // Switch default core markup for search form, comment form, etc. to HTML5.
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));
}
add_action('after_setup_theme', 'mfm_classic_setup');

// -------------------------------------------------
// Enqueue scripts and styles
// -------------------------------------------------
function mfm_classic_enqueue_assets() {
    // Main stylesheet
    wp_enqueue_style(
        'mfm-classic-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get('Version')
    );

    // Theme JS (example)
    wp_enqueue_script(
        'mfm-scripts',
        get_template_directory_uri() . '/js/mfm-scripts.js',
        array('jquery'),
        wp_get_theme()->get('Version'),
        true
    );
}
add_action('wp_enqueue_scripts', 'mfm_classic_enqueue_assets');

// -------------------------------------------------
// Example small template filter (safe to keep 
// -------------------------------------------------
function mfm_classic_excerpt_length($length) {
    return 30; // words
}
add_filter('excerpt_length', 'mfm_classic_excerpt_length');

/**
 * Register custom block styles used in layout-overlays.css
 */
function mfm_register_custom_block_styles() {
    if ( function_exists( 'register_block_style' ) ) {
        register_block_style( 'core/group', array(
            'name'  => 'bordered',
            'label' => 'Bordered',
        ) );
        register_block_style( 'core/group', array(
            'name'  => 'shadowed',
            'label' => 'Shadowed',
        ) );
        register_block_style( 'core/columns', array(
            'name'  => 'gapless',
            'label' => 'Gapless',
        ) );
        register_block_style( 'core/columns', array(
            'name'  => 'asymmetric',
            'label' => 'Asymmetric',
        ) );
        register_block_style( 'core/cover', array(
            'name'  => 'fixed-bg',
            'label' => 'Fixed Background',
        ) );
        register_block_style( 'core/cover', array(
            'name'  => 'dark-overlay',
            'label' => 'Dark Overlay',
        ) );
        register_block_style( 'core/media-text', array(
            'name'  => 'vertical-stack',
            'label' => 'Vertical Stack',
        ) );
        register_block_style( 'core/heading', array(
            'name'  => 'accent',
            'label' => 'Accent Heading',
        ) );
        register_block_style( 'core/button', array(
            'name'  => 'outline',
            'label' => 'Outline Button',
        ) );
        register_block_style( 'core/button', array(
            'name'  => 'ghost',
            'label' => 'Ghost Button',
        ) );
        register_block_style( 'core/quote', array(
            'name'  => 'pull',
            'label' => 'Pull Quote',
        ) );
        register_block_style( 'core/separator', array(
            'name'  => 'dotted',
            'label' => 'Dotted Line',
        ) );
        register_block_style( 'core/separator', array(
            'name'  => 'gradient',
            'label' => 'Gradient Line',
        ) );
    }
}
add_action( 'init', 'mfm_register_custom_block_styles' );

/**
 * Enqueue layout overlays stylesheet after theme stylesheet
 */
function mfm_enqueue_layout_overlays() {
    // Ensure main theme stylesheet is registered/enqueued with this handle:
    // 'mfm-classic-style' is used elsewhere in this theme so we declare it as a dependency.
    $theme_version = wp_get_theme()->get('Version');

    wp_enqueue_style(
        'mfm-layout-overlays',
        get_stylesheet_directory_uri() . '/css/layout-overlays.css',
        array( 'mfm-classic-style' ), // load after theme stylesheet
        $theme_version
    );
}
add_action( 'wp_enqueue_scripts', 'mfm_enqueue_layout_overlays', 20 );

/**
 * Enqueue layout-overlays.css into the block editor for preview parity
 */
function mfm_enqueue_layout_overlays_editor() {
    $theme_version = wp_get_theme()->get('Version');

    wp_enqueue_style(
        'mfm-layout-overlays-editor',
        get_stylesheet_directory_uri() . '/css/layout-overlays.css',
        array( 'wp-edit-blocks' ), // ensure editor base styles are loaded first
        $theme_version
    );
}
add_action( 'enqueue_block_editor_assets', 'mfm_enqueue_layout_overlays_editor' );
