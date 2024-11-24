<?php
class DC_Event_Widgets {
    public function __construct() {
        add_action('widgets_init', array($this, 'register_widgets'));
    }

    public function register_widgets() {
        register_widget('DC_Event_Upcoming_Events_Widget');
    }
}

class DC_Event_Upcoming_Events_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'dc_event_upcoming_events',
            'DC Event: Upcoming Events',
            array('description' => 'Display a list of upcoming events.')
        );
    }

    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        $number = isset($instance['number']) ? absint($instance['number']) : 5;

        echo wp_kses_post($args['before_widget']);
        if (!empty($title)) {
            echo wp_kses_post($args['before_title'] . $title . $args['after_title']);
        }

        $cache_key = 'dc_event_upcoming_widget_' . $this->id;
        $events = wp_cache_get($cache_key);

        if (false === $events) {
            $query_args = array(
                'post_type' => 'dc_event',
                'posts_per_page' => $number,
                'meta_key' => '_event_start_date',
                'orderby' => 'meta_value',
                'order' => 'ASC',
                'meta_query' => array(
                    array(
                        'key' => '_event_start_date',
                        'value' => current_time('mysql'),
                        'compare' => '>=',
                        'type' => 'DATETIME'
                    )
                )
            );
            $events = new WP_Query($query_args);
            wp_cache_set($cache_key, $events, 'dc_event_manager', 3600);
        }

        if ($events->have_posts()) {
            echo '<ul class="dc-event-upcoming-list">';
            foreach ($events->posts as $event) {
                echo '<li><a href="' . esc_url(get_permalink($event->ID)) . '">' . esc_html($event->post_title) . '</a></li>';
            }
            echo '</ul>';
        } else {
            echo '<p>' . esc_html__('No upcoming events.', 'dc-event-manager') . '</p>';
        }

        echo wp_kses_post($args['after_widget']);
    }

    public function form($instance) {
        $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
        $number = isset($instance['number']) ? absint($instance['number']) : 5;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'dc-event-manager'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('number')); ?>"><?php esc_html_e('Number of events to show:', 'dc-event-manager'); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('number')); ?>" name="<?php echo esc_attr($this->get_field_name('number')); ?>" type="number" step="1" min="1" value="<?php echo esc_attr($number); ?>" size="3">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['number'] = (!empty($new_instance['number'])) ? absint($new_instance['number']) : 5;
        return $instance;
    }

    protected function get_upcoming_events($limit = 5) {
        $cache_key = 'dc_event_widget_upcoming_events';
        $events = get_transient($cache_key);

        if (false === $events) {
            global $wpdb;
            $current_time = current_time('mysql');
            $events = $wpdb->get_results($wpdb->prepare(
                "SELECT p.*, pm.meta_value as start_date FROM {$wpdb->posts} p
                JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = 'dc_event'
                AND p.post_status = 'publish'
                AND pm.meta_key = '_event_start_date'
                AND pm.meta_value >= %s
                ORDER BY pm.meta_value ASC
                LIMIT %d",
                $current_time,
                $limit
            ));

            set_transient($cache_key, $events, HOUR_IN_SECONDS);
        }

        return $events;
    }
}