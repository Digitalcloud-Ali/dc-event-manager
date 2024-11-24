<?php
class DC_Event_Shortcodes {
    public function __construct() {
        add_shortcode('dc_event_list', array($this, 'event_list_shortcode'));
        add_shortcode('dc_event_details', array($this, 'event_details_shortcode'));
    }

    public function event_list_shortcode($atts) {
        $args = array(
            'post_type' => 'dc_event',
            'posts_per_page' => 10,
        );
        $query = new WP_Query($args);
        
        ob_start();
        if ($query->have_posts()) {
            echo '<ul class="dc-event-list">';
            while ($query->have_posts()) {
                $query->the_post();
                echo '<li><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></li>';
            }
            echo '</ul>';
        } else {
            echo esc_html__('No events found.', 'dc-event-manager');
        }
        wp_reset_postdata();
        return ob_get_clean();
    }

    public function event_details_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => get_the_ID(),
        ), $atts);

        $event_id = intval($atts['id']);
        
        // Verify that the current user has permission to view this event
        if (!current_user_can('read_post', $event_id)) {
            return __('You do not have permission to view this event.', 'dc-event-manager');
        }

        $start_date = get_post_meta($event_id, '_event_start_date', true);
        $end_date = get_post_meta($event_id, '_event_end_date', true);
        $location = get_post_meta($event_id, '_event_location', true);

        $carbon_footprint = get_post_meta($event_id, '_event_carbon_footprint', true);
        $ar_model = get_post_meta($event_id, '_event_ar_model', true);

        ob_start();
        ?>
        <div class="dc-event-details">
            <?php wp_nonce_field('dc_event_details', 'dc_event_details_nonce'); ?>
            <p><strong><?php esc_html_e('Start Date:', 'dc-event-manager'); ?></strong> <?php echo esc_html($start_date); ?></p>
            <p><strong><?php esc_html_e('End Date:', 'dc-event-manager'); ?></strong> <?php echo esc_html($end_date); ?></p>
            <p><strong><?php esc_html_e('Location:', 'dc-event-manager'); ?></strong> <?php echo esc_html($location); ?></p>
            <?php echo do_shortcode('[dc_event_map id="' . $event_id . '"]'); ?>
            <?php if ($carbon_footprint) : ?>
                <p><strong><?php esc_html_e('Carbon Footprint:', 'dc-event-manager'); ?></strong> <?php echo esc_html($carbon_footprint); ?> <?php esc_html_e('kg CO2', 'dc-event-manager'); ?></p>
            <?php endif; ?>
            <?php if ($ar_model) : ?>
                <p><a href="#" class="dc-event-ar-view" data-model="<?php echo esc_url($ar_model); ?>" data-event-id="<?php echo esc_attr($event_id); ?>"><?php esc_html_e('View in AR', 'dc-event-manager'); ?></a></p>
            <?php endif; ?>
            <?php echo do_shortcode('[dc_event_attendees event_id="' . esc_attr($event_id) . '"]'); ?>
            <?php echo do_shortcode('[dc_event_user_badges]'); ?>
        </div>
        <?php
        return ob_get_clean();
    }
}