<?php
class DC_Event_Activator {
    public static function activate() {
        // Create custom database tables if needed
        self::create_custom_tables();

        // Set up default options
        self::set_default_options();

        // Schedule cron jobs
        self::schedule_cron_jobs();

        // Create default pages
        self::create_default_pages();

        // Set capabilities
        self::set_capabilities();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Trigger activation hook
        do_action('dc_event_manager_activated');
    }

    public static function deactivate() {
        // Unschedule cron jobs
        self::unschedule_cron_jobs();

        // Remove capabilities
        self::remove_capabilities();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Trigger deactivation hook
        do_action('dc_event_manager_deactivated');
    }

    private static function create_custom_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$wpdb->prefix}dc_event_attendees (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            event_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            check_in_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function set_default_options() {
        $default_options = array(
            'enable_ar' => 1,
            'enable_carbon_tracking' => 1,
            'enable_gamification' => 1,
            'analytics_retention_period' => 90,
        );

        foreach ($default_options as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }

    private static function schedule_cron_jobs() {
        if (!wp_next_scheduled('dc_event_daily_maintenance')) {
            wp_schedule_event(time(), 'daily', 'dc_event_daily_maintenance');
        }
    }

    private static function unschedule_cron_jobs() {
        $timestamp = wp_next_scheduled('dc_event_daily_maintenance');
        wp_unschedule_event($timestamp, 'dc_event_daily_maintenance');
    }

    private static function create_default_pages() {
        $pages = array(
            'events' => 'Events',
            'submit-event' => 'Submit Event',
            'event-dashboard' => 'Event Dashboard'
        );

        foreach ($pages as $slug => $title) {
            if (!get_page_by_path($slug)) {
                wp_insert_post(array(
                    'post_title' => $title,
                    'post_name' => $slug,
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_content' => '[dc_event_' . str_replace('-', '_', $slug) . ']'
                ));
            }
        }
    }

    private static function set_capabilities() {
        $roles = array('administrator', 'editor');
        $capabilities = array(
            'publish_events',
            'edit_events',
            'delete_events',
            'read_private_events'
        );

        foreach ($roles as $role) {
            $role_obj = get_role($role);
            if ($role_obj) {
                foreach ($capabilities as $cap) {
                    $role_obj->add_cap($cap);
                }
            }
        }
    }

    private static function remove_capabilities() {
        $roles = array('administrator', 'editor');
        $capabilities = array(
            'publish_events',
            'edit_events',
            'delete_events',
            'read_private_events'
        );

        foreach ($roles as $role) {
            $role_obj = get_role($role);
            if ($role_obj) {
                foreach ($capabilities as $cap) {
                    $role_obj->remove_cap($cap);
                }
            }
        }
    }
}