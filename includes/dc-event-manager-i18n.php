<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 */

function dc_event_manager_load_plugin_textdomain() {
    load_plugin_textdomain(
        'dc-event-manager',
        false,
        dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
    );
}
add_action('plugins_loaded', 'dc_event_manager_load_plugin_textdomain');

function dc_event_manager_get_strings() {
    return array(
        'event_title' => __('Event Title', 'dc-event-manager'),
        'event_description' => __('Event Description', 'dc-event-manager'),
        'start_date' => __('Start Date', 'dc-event-manager'),
        'end_date' => __('End Date', 'dc-event-manager'),
        'location' => __('Location', 'dc-event-manager'),
        'ar_model_url' => __('AR Model URL', 'dc-event-manager'),
        'ticket_price' => __('Ticket Price', 'dc-event-manager'),
        'available_tickets' => __('Available Tickets', 'dc-event-manager'),
        'submit_event' => __('Submit Event', 'dc-event-manager'),
        'event_submitted' => __('Event submitted successfully and is pending review', 'dc-event-manager'),
        'event_submission_failed' => __('Failed to submit event', 'dc-event-manager'),
        'view_in_ar' => __('View in AR', 'dc-event-manager'),
        'carbon_footprint' => __('Carbon Footprint', 'dc-event-manager'),
        'kg_co2' => __('kg CO2', 'dc-event-manager'),
        'event_attendees' => __('Event Attendees', 'dc-event-manager'),
        'no_attendees_yet' => __('No attendees yet.', 'dc-event-manager'),
        // Add more strings as needed
    );
}