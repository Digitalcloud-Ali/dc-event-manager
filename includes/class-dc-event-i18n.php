<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://digitalcloud.no
 * @since      1.0.0
 *
 * @package    DC_Event_Manager
 * @subpackage DC_Event_Manager/includes
 */

class DC_Event_i18n {

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain(
            'dc-event-manager',
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );

    }

    /**
     * Translate strings in JavaScript files.
     *
     * @since    1.0.0
     */
    public function localize_scripts() {
        wp_localize_script( 'dc-event-manager-public', 'dcEventManageri18n', array(
            'viewInAR' => __('View in AR', 'dc-event-manager'),
            'exportToGoogle' => __('Export to Google Calendar', 'dc-event-manager'),
            'exportToiCal' => __('Export to iCal', 'dc-event-manager'),
            'eventViews' => __('Event Views', 'dc-event-manager'),
            'eventAttendance' => __('Event Attendance', 'dc-event-manager'),
            'conversionRate' => __('Conversion Rate (%)', 'dc-event-manager'),
            'engagementRate' => __('Engagement Rate (%)', 'dc-event-manager'),
            'popularityTrend' => __('Event Popularity Trend', 'dc-event-manager'),
            'userEngagement' => __('User Engagement', 'dc-event-manager'),
        ) );
    }

}