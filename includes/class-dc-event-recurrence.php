<?php
class DC_Event_Recurrence {
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_recurrence_meta_box'));
        add_action('save_post', array($this, 'save_recurrence_meta'));
        add_action('dc_event_after_save', array($this, 'generate_recurring_events'));
    }

    public function add_recurrence_meta_box() {
        add_meta_box(
            'dc_event_recurrence',
            'Event Recurrence',
            array($this, 'render_recurrence_meta_box'),
            'dc_event',
            'normal',
            'high'
        );
    }

    public function render_recurrence_meta_box($post) {
        wp_nonce_field('dc_event_recurrence', 'dc_event_recurrence_nonce');
        $recurrence = get_post_meta($post->ID, '_event_recurrence', true);
        ?>
        <p>
            <label for="event_recurrence">Recurrence:</label>
            <select id="event_recurrence" name="event_recurrence">
                <option value="none" <?php selected($recurrence, 'none'); ?>>None</option>
                <option value="daily" <?php selected($recurrence, 'daily'); ?>>Daily</option>
                <option value="weekly" <?php selected($recurrence, 'weekly'); ?>>Weekly</option>
                <option value="monthly" <?php selected($recurrence, 'monthly'); ?>>Monthly</option>
                <option value="yearly" <?php selected($recurrence, 'yearly'); ?>>Yearly</option>
            </select>
        </p>
        <?php
    }

    public function save_recurrence_meta($post_id) {
        if (!isset($_POST['dc_event_recurrence_nonce']) || !wp_verify_nonce(sanitize_key(wp_unslash($_POST['dc_event_recurrence_nonce'])), 'dc_event_recurrence')) {
            return;
        }

        if (isset($_POST['event_recurrence'])) {
            $recurrence = sanitize_text_field(wp_unslash($_POST['event_recurrence']));
            update_post_meta($post_id, '_event_recurrence', $recurrence);
        }
    }

    public function generate_recurring_events($post_id) {
        $recurrence = get_post_meta($post_id, '_event_recurrence', true);
        if ($recurrence === 'none') {
            return;
        }

        $start_date = get_post_meta($post_id, '_event_start_date', true);
        $end_date = get_post_meta($post_id, '_event_end_date', true);

        $interval = $this->get_recurrence_interval($recurrence);
        $next_start = strtotime($start_date . ' +' . $interval);
        $next_end = strtotime($end_date . ' +' . $interval);

        // Generate the next occurrence
        $this->create_recurring_event($post_id, gmdate('Y-m-d H:i:s', $next_start), gmdate('Y-m-d H:i:s', $next_end));
    }

    private function get_recurrence_interval($recurrence) {
        switch ($recurrence) {
            case 'daily':
                return '1 day';
            case 'weekly':
                return '1 week';
            case 'monthly':
                return '1 month';
            case 'yearly':
                return '1 year';
            default:
                return '';
        }
    }

    private function create_recurring_event($original_event_id, $start_date, $end_date) {
        $original_event = get_post($original_event_id);

        $new_event_id = wp_insert_post(array(
            'post_type' => 'dc_event',
            'post_title' => $original_event->post_title,
            'post_content' => $original_event->post_content,
            'post_status' => 'publish',
        ));

        if (!is_wp_error($new_event_id)) {
            update_post_meta($new_event_id, '_event_start_date', $start_date);
            update_post_meta($new_event_id, '_event_end_date', $end_date);
            update_post_meta($new_event_id, '_event_location', get_post_meta($original_event_id, '_event_location', true));
            update_post_meta($new_event_id, '_event_recurrence', get_post_meta($original_event_id, '_event_recurrence', true));
        }
    }

    public function save_event_recurrence($post_id) {
        if (!isset($_POST['dc_event_recurrence_nonce']) || !wp_verify_nonce(wp_unslash($_POST['dc_event_recurrence_nonce']), 'dc_event_recurrence')) {
            return;
        }
        
        if (isset($_POST['event_recurrence'])) {
            $recurrence = sanitize_text_field(wp_unslash($_POST['event_recurrence']));
            update_post_meta($post_id, 'event_recurrence', $recurrence);
        }
    }

    public function get_formatted_date($timestamp) {
        return gmdate('Y-m-d H:i:s', $timestamp);
    }

    public function get_next_occurrence($event_id) {
        $recurrence = get_post_meta($event_id, '_event_recurrence', true);
        $start_date = get_post_meta($event_id, '_event_start_date', true);
        
        // Calculate next occurrence based on recurrence pattern
        $next_date = gmdate('Y-m-d H:i:s', strtotime($start_date . ' +1 ' . $recurrence));
        
        return $next_date;
    }
}