<?php
class DC_Event_Calendar {
    public function __construct() {
        add_shortcode('dc_event_calendar', array($this, 'render_calendar'));
    }

    public function render_calendar($atts) {
        wp_enqueue_script('dc-event-calendar', DC_EVENT_MANAGER_PLUGIN_URL . 'assets/js/dc-event-calendar.js', array('jquery'), DC_EVENT_MANAGER_VERSION, true);
        wp_enqueue_style('dc-event-calendar', DC_EVENT_MANAGER_PLUGIN_URL . 'assets/css/dc-event-calendar.css', array(), DC_EVENT_MANAGER_VERSION);

        $events = $this->get_events();
        $calendar_data = wp_json_encode($events);
        
        ob_start();
        ?>
        <div id="dc-event-calendar" data-events='<?php echo esc_attr($calendar_data); ?>'></div>
        <?php
        return ob_get_clean();
    }

    private function get_events() {
        $args = array(
            'post_type' => 'dc_event',
            'posts_per_page' => -1,
        );
        $query = new WP_Query($args);
        $events = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $events[] = array(
                    'title' => get_the_title(),
                    'start' => get_post_meta(get_the_ID(), '_event_start_date', true),
                    'end' => get_post_meta(get_the_ID(), '_event_end_date', true),
                    'url' => get_permalink(),
                );
            }
        }
        wp_reset_postdata();

        return $events;
    }
}