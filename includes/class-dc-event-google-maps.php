<?php
class DC_Event_Google_Maps {
    private $api_key;

    public function __construct() {
        $this->api_key = get_option('dc_event_google_maps_api_key');
        add_action('admin_init', array($this, 'register_google_maps_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_scripts'));
        add_action('add_meta_boxes', array($this, 'add_location_meta_box'));
        add_action('save_post', array($this, 'save_location_meta'));
        add_shortcode('dc_event_map', array($this, 'render_event_map'));
    }

    public function register_google_maps_settings() {
        register_setting('dc_event_manager_settings', 'dc_event_google_maps_api_key');
        add_settings_field(
            'dc_event_google_maps_api_key',
            'Google Maps API Key',
            array($this, 'google_maps_api_key_callback'),
            'dc-event-manager',
            'dc_event_manager_general_section'
        );
    }

    public function google_maps_api_key_callback() {
        $api_key = get_option('dc_event_google_maps_api_key');
        echo '<input type="text" name="dc_event_google_maps_api_key" value="' . esc_attr($api_key) . '" />';
    }

    public function enqueue_admin_scripts($hook) {
        if ('post.php' != $hook && 'post-new.php' != $hook) {
            return;
        }
        wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $this->api_key . '&libraries=places', array(), DC_EVENT_MANAGER_VERSION, true);
        wp_enqueue_script('dc-event-admin-maps', DC_EVENT_MANAGER_PLUGIN_URL . 'assets/js/dc-event-admin-maps.js', array('jquery', 'google-maps'), DC_EVENT_MANAGER_VERSION, true);
    }

    public function enqueue_public_scripts() {
        wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $this->api_key, array(), DC_EVENT_MANAGER_VERSION, true);
        wp_enqueue_script('dc-event-public-maps', DC_EVENT_MANAGER_PLUGIN_URL . 'assets/js/dc-event-public-maps.js', array('jquery', 'google-maps'), DC_EVENT_MANAGER_VERSION, true);
    }

    public function add_location_meta_box() {
        add_meta_box(
            'dc_event_location_map',
            'Event Location',
            array($this, 'render_location_meta_box'),
            'dc_event',
            'normal',
            'high'
        );
    }

    public function render_location_meta_box($post) {
        wp_nonce_field('dc_event_location', 'dc_event_location_nonce');
        $location = get_post_meta($post->ID, '_event_location', true);
        $latitude = get_post_meta($post->ID, '_event_latitude', true);
        $longitude = get_post_meta($post->ID, '_event_longitude', true);
        ?>
        <p>
            <label for="event_location">Location:</label>
            <input type="text" id="event_location" name="event_location" value="<?php echo esc_attr($location); ?>" style="width: 100%;">
        </p>
        <input type="hidden" id="event_latitude" name="event_latitude" value="<?php echo esc_attr($latitude); ?>">
        <input type="hidden" id="event_longitude" name="event_longitude" value="<?php echo esc_attr($longitude); ?>">
        <div id="map" style="height: 300px; width: 100%;"></div>
        <?php
    }

    public function save_location_meta($post_id) {
        if (isset($_POST['dc_event_location_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['dc_event_location_nonce'])), 'dc_event_location')) {
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
            if (isset($_POST['event_location'])) {
                $location = sanitize_text_field(wp_unslash($_POST['event_location']));
                update_post_meta($post_id, '_event_location', $location);
            }
            if (isset($_POST['event_latitude']) && isset($_POST['event_longitude'])) {
                update_post_meta($post_id, '_event_latitude', floatval($_POST['event_latitude']));
                update_post_meta($post_id, '_event_longitude', floatval($_POST['event_longitude']));
            }
        }
    }

    public function render_event_map($atts) {
        $atts = shortcode_atts(array(
            'id' => get_the_ID(),
            'width' => '100%',
            'height' => '300px'
        ), $atts);

        $event_id = intval($atts['id']);
        $location = get_post_meta($event_id, '_event_location', true);
        $latitude = get_post_meta($event_id, '_event_latitude', true);
        $longitude = get_post_meta($event_id, '_event_longitude', true);

        if (!$latitude || !$longitude) {
            return '<p>No location set for this event.</p>';
        }

        $map_id = 'dc-event-map-' . $event_id;
        $output = '<div id="' . esc_attr($map_id) . '" style="width: ' . esc_attr($atts['width']) . '; height: ' . esc_attr($atts['height']) . ';"></div>';
        $output .= '<script>
            jQuery(document).ready(function($) {
                var map = new google.maps.Map(document.getElementById("' . $map_id . '"), {
                    center: {lat: ' . $latitude . ', lng: ' . $longitude . '},
                    zoom: 15
                });
                var marker = new google.maps.Marker({
                    position: {lat: ' . $latitude . ', lng: ' . $longitude . '},
                    map: map,
                    title: "' . esc_js($location) . '"
                });
            });
        </script>';

        return $output;
    }

    public function enqueue_scripts() {
        wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $this->api_key, array(), DC_EVENT_MANAGER_VERSION, true);
        wp_enqueue_script('dc-event-google-maps', plugin_dir_url(__FILE__) . 'js/dc-event-google-maps.js', array('google-maps'), DC_EVENT_MANAGER_VERSION, true);
    }
}