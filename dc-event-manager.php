<?php
/**
 * Plugin Name: DC Event Manager
 * Plugin URI: https://github.com/Digitalcloud-Ali/DC-Event-Manager
 * Description: A comprehensive event management plugin for WordPress
 * Version: 1.0.1
 * Author: Syed Ali
 * Author URI: https://digitalcloud.no
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: dc-event-manager
 * Domain Path: /languages
 *
 * @package DC_Event_Manager
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('DC_EVENT_MANAGER_VERSION', '1.0.1');
define('DC_EVENT_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DC_EVENT_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include the configuration file
require_once plugin_dir_path(__FILE__) . 'includes/config.php';

// Include necessary files
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-manager.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-loader.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-i18n.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-post-type.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-calendar.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-shortcodes.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-widgets.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-notifications.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-frontend-submission.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-ar-integration.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-networking.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-carbon-tracker.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-gamification.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'admin/class-dc-event-admin.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-attendees.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-logger.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-analytics.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-cache.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-calendar-integration.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-api.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-ticketing.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-recurrence.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-check-in.php';
require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-google-maps.php';

// Activation and deactivation hooks
register_activation_hook(__FILE__, array('DC_Event_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('DC_Event_Activator', 'deactivate'));

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_dc_event_manager() {
    $plugin = new DC_Event_Manager();
    $plugin->run();

    // Initialize internationalization
    $plugin_i18n = new DC_Event_i18n();
    $plugin_i18n->load_plugin_textdomain();
    add_action('wp_enqueue_scripts', array($plugin_i18n, 'localize_scripts'));

    // Initialize logger
    $logger = new DC_Event_Logger();
    $GLOBALS['dc_event_logger'] = $logger;

    // Initialize analytics
    new DC_Event_Analytics();

    // Initialize cache
    $cache = new DC_Event_Cache();
    $GLOBALS['dc_event_cache'] = $cache;

    // Cache frequently accessed data
    $events = get_posts(array('post_type' => 'dc_event', 'posts_per_page' => -1));
    $cache->set('all_events', $events, DC_EVENT_MANAGER_CACHE_EXPIRATION);

    // Initialize other components
    new DC_Event_Calendar_Integration();
    new DC_Event_API();
    new DC_Event_Ticketing();
    new DC_Event_Recurrence();
    new DC_Event_Check_In();
    new DC_Event_Google_Maps();

}
run_dc_event_manager();

function dc_event_manager_enqueue_admin_scripts($hook) {
    if ('post.php' != $hook && 'post-new.php' != $hook) {
        return;
    }
    wp_enqueue_script('dc-event-admin-tabs', DC_EVENT_MANAGER_PLUGIN_URL . 'assets/js/dc-event-admin-tabs.js', array('jquery'), DC_EVENT_MANAGER_VERSION, true);
}
add_action('admin_enqueue_scripts', 'dc_event_manager_enqueue_admin_scripts');