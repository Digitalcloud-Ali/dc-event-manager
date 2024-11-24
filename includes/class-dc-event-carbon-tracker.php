<?php
class DC_Event_Carbon_Tracker {
    private $cache;

    public function __construct() {
        $this->cache = new DC_Event_Cache();
        add_action('dc_event_after_save', array($this, 'calculate_carbon_footprint'));
        add_shortcode('dc_event_carbon_footprint', array($this, 'render_carbon_footprint'));
    }

    public function calculate_carbon_footprint($event_id) {
        $cache_key = 'event_carbon_footprint_' . $event_id;
        $footprint = $this->cache->get($cache_key);

        if ($footprint === false) {
            $attendees = $this->get_event_attendees($event_id);
            $location = get_post_meta($event_id, '_event_location', true);
            $duration = $this->calculate_event_duration($event_id);

            $footprint = $this->calculate_travel_footprint($attendees, $location) +
                         $this->calculate_venue_footprint($duration, count($attendees));

            update_post_meta($event_id, '_event_carbon_footprint', $footprint);
            $this->cache->set($cache_key, $footprint, 3600); // Cache for 1 hour
        }

        return $footprint;
    }

    private function calculate_travel_footprint($attendees, $location) {
        $total_footprint = 0;
        foreach ($attendees as $attendee) {
            $distance = $this->estimate_travel_distance($attendee, $location);
            $total_footprint += $distance * 0.1; // Assuming 0.1 kg CO2 per km
        }
        return $total_footprint;
    }

    private function estimate_travel_distance($attendee, $event_location) {
        // Use Google Maps Distance Matrix API for more accurate distance calculation
        $api_key = get_option('dc_event_google_maps_api_key');
        $attendee_location = get_user_meta($attendee->ID, 'location', true);
        
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . urlencode($attendee_location) . "&destinations=" . urlencode($event_location) . "&key=" . $api_key;
        
        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            return 100; // Default to 100 km if API call fails
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (isset($data['rows'][0]['elements'][0]['distance']['value'])) {
            return $data['rows'][0]['elements'][0]['distance']['value'] / 1000; // Convert meters to kilometers
        }
        
        return 100; // Default to 100 km if data is not available
    }

    private function calculate_venue_footprint($duration, $attendee_count) {
        // Assuming 0.5 kg CO2 per person per hour for venue energy use
        return $duration * $attendee_count * 0.5;
    }

    private function calculate_event_duration($event_id) {
        $start_date = get_post_meta($event_id, '_event_start_date', true);
        $end_date = get_post_meta($event_id, '_event_end_date', true);
        return (strtotime($end_date) - strtotime($start_date)) / 3600; // Duration in hours
    }

    public function render_carbon_footprint($atts) {
        $atts = shortcode_atts(array(
            'event_id' => get_the_ID(),
        ), $atts);

        $event_id = intval($atts['event_id']);
        $footprint = get_post_meta($event_id, '_event_carbon_footprint', true);

        ob_start();
        ?>
        <div class="dc-event-carbon-footprint">
            <h3>Event Carbon Footprint</h3>
            <p><?php echo esc_html($footprint); ?> kg CO2</p>
        </div>
        <?php
        return ob_get_clean();
    }

    private function get_event_attendees($event_id) {
        // This is a placeholder. In a real implementation, you'd query your database for actual attendees.
        return get_users(array('number' => 10));
    }
}