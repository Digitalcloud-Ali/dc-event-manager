<?php
// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all the plugin data
function dc_event_delete_all_data() {
    $args = array(
        'post_type' => 'dc_event',
        'posts_per_page' => -1,
        'fields' => 'ids',
    );
    $event_ids = get_posts($args);
    
    foreach ($event_ids as $id) {
        wp_delete_post($id, true);
    }

    // Delete user meta
    delete_metadata('user', 0, 'dc_event_badges', '', true);

    // Delete options
    $option_names = array(
        'dc_event_manager_settings',
        'dc_event_google_maps_api_key',
        'dc_event_manager_advanced_settings',
        // Add other option names here
    );

    foreach ($option_names as $option) {
        delete_option($option);
    }

    // Remove any scheduled cron jobs
    wp_clear_scheduled_hook('dc_event_update_cache');

    // Clear any cached data
    wp_cache_flush();
}

dc_event_delete_all_data();

// Remove all posts of type 'dc_event'
$events = get_posts(array('post_type' => 'dc_event', 'numberposts' => -1));
foreach ($events as $event) {
    wp_delete_post($event->ID, true);
}

// Remove all options related to the plugin
delete_option('dc_event_manager_settings');
delete_option('dc_event_google_maps_api_key');