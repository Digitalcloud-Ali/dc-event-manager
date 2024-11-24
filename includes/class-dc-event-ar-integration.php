<?php
class DC_Event_AR_Integration {
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_ar_scripts'));
        add_shortcode('dc_event_ar_view', array($this, 'render_ar_view'));
    }

    public function enqueue_ar_scripts() {
        wp_enqueue_script('aframe', 'https://aframe.io/releases/1.2.0/aframe.min.js', array(), '1.2.0', true);
        wp_enqueue_script('ar-js', 'https://raw.githack.com/AR-js-org/AR.js/master/aframe/build/aframe-ar.js', array('aframe'), '3.3.1', true);
        wp_enqueue_script('dc-event-ar', DC_EVENT_MANAGER_PLUGIN_URL . 'assets/js/dc-event-ar.js', array('jquery'), DC_EVENT_MANAGER_VERSION, true);
        wp_enqueue_style('dc-event-ar', DC_EVENT_MANAGER_PLUGIN_URL . 'assets/css/dc-event-ar.css', array(), DC_EVENT_MANAGER_VERSION);
    }

    public function render_ar_view($atts) {
        $atts = shortcode_atts(array(
            'event_id' => get_the_ID(),
        ), $atts);

        $event_id = intval($atts['event_id']);
        $ar_model = get_post_meta($event_id, '_event_ar_model', true);

        if (!$ar_model) {
            global $dc_event_logger;
            $dc_event_logger->log("AR model not found for event ID: $event_id", 'warning');
            return '<p>AR view not available for this event.</p>';
        }

        $event_title = get_the_title($event_id);
        $event_date = get_post_meta($event_id, '_event_start_date', true);
        $event_location = get_post_meta($event_id, '_event_location', true);

        $event_info = wp_json_encode(array(
            'title' => $event_title,
            'date' => $event_date,
            'location' => $event_location,
        ));

        ob_start();
        ?>
        <div class="dc-event-ar-container">
            <button class="dc-event-ar-view" data-model="<?php echo esc_url($ar_model); ?>" data-info="<?php echo esc_attr($event_info); ?>">
                View in AR
            </button>
        </div>
        <?php
        return ob_get_clean();
    }
}