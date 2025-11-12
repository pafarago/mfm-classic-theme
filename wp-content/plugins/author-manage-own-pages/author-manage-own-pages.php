<?php
/*
Plugin Name: Author Manage Own Pages
Description: Allows Authors to create, publish, edit, and delete their own Pages, but not others’.
Version: 1.0
Author: Midlothian Friends
*/

// --- Activation: grant own-page caps, remove others-page caps ---
function mfm_author_pages_activate() {
    $role = get_role('author');
    if (!$role) return;

    // Add self-owned Page capabilities
    foreach (['edit_pages', 'publish_pages', 'edit_published_pages', 'delete_pages', 'delete_published_pages'] as $cap) {
        if (!$role->has_cap($cap)) {
            $role->add_cap($cap);
        }
    }

    // Remove any “others” caps for safety
    foreach (['edit_others_pages', 'delete_others_pages', 'delete_others_published_pages', 'publish_others_pages'] as $cap) {
        if ($role->has_cap($cap)) {
            $role->remove_cap($cap);
        }
    }
}
register_activation_hook(__FILE__, 'mfm_author_pages_activate');


// --- Deactivation: remove all Page caps we granted ---
function mfm_author_pages_deactivate() {
    $role = get_role('author');
    if (!$role) return;

    foreach (['edit_pages', 'publish_pages', 'edit_published_pages', 'delete_pages', 'delete_published_pages'] as $cap) {
        if ($role->has_cap($cap)) {
            $role->remove_cap($cap);
        }
    }
}
register_deactivation_hook(__FILE__, 'mfm_author_pages_deactivate');


// --- Uninstall: also remove Page caps just in case ---
function mfm_author_pages_uninstall() {
    $role = get_role('author');
    if (!$role) return;

    foreach (['edit_pages', 'publish_pages', 'edit_published_pages', 'delete_pages', 'delete_published_pages',
              'edit_others_pages', 'delete_others_pages', 'delete_others_published_pages', 'publish_others_pages'] as $cap) {
        if ($role->has_cap($cap)) {
            $role->remove_cap($cap);
        }
    }
}
register_uninstall_hook(__FILE__, 'mfm_author_pages_uninstall');

// --- Admin page: show capabilities by role ---
add_action('admin_menu', function() {
    add_users_page(
        'Role Capabilities',
        'Role Capabilities',
        'manage_options', // only admins can view
        'mfm-role-capabilities',
        'mfm_render_role_caps_page'
    );
});

function mfm_render_role_caps_page() {
    echo '<div class="wrap"><h1>Role Capabilities</h1>';
    global $wp_roles;
    foreach ($wp_roles->roles as $role_name => $role_info) {
        echo '<h2>' . esc_html($role_info['name']) . ' (' . esc_html($role_name) . ')</h2>';
        echo '<ul style="columns:3;-webkit-columns:3;-moz-columns:3;">';
        foreach ($role_info['capabilities'] as $cap => $granted) {
            $color = $granted ? 'green' : 'red';
            echo '<li style="color:' . $color . '">' . esc_html($cap) . '</li>';
        }
        echo '</ul>';
    }
    echo '</div>';
}
