<?php
function mfm_enqueue_styles() {
    wp_enqueue_style('mfm-style', get_stylesheet_uri());
}
add_action('wp_enqueue_scripts', 'mfm_enqueue_styles');

function mfm_register_menus() {
    register_nav_menus(array(
        'main-menu' => __('Main Menu', 'mfm'),
    ));
}
add_action('init', 'mfm_register_menus');

function mfm_enqueue_scripts() {
    wp_enqueue_script(
        'mfm-scripts',
        get_template_directory_uri() . '/js/mfm-scripts.js',
        array(),
        null,
        true
    );
}
add_action('wp_enqueue_scripts', 'mfm_enqueue_scripts');
