<?php

/**
 * Unified desktop breakpoint for theme
 */
function get_desktop_breakpoint() {
    return 1000; // single source of truth
}

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
