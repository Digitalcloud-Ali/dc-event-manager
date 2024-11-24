<?php
class DC_Event_Attendees {
    public function __construct() {
        add_action('dc_event_after_check_in', array($this, 'update_attendee_list'), 10, 2);
    }

    public function update_attendee_list($user_id, $event_id) {
        $attendees = get_post_meta($event_id, '_event_attendees', true);
        if (!is_array($attendees)) {
            $attendees = array();
        }
        if (!in_array($user_id, $attendees)) {
            $attendees[] = $user_id;
            update_post_meta($event_id, '_event_attendees', $attendees);
        }
    }

    public function get_event_attendees($event_id) {
        $attendees = get_post_meta($event_id, '_event_attendees', true);
        if (!is_array($attendees)) {
            return array();
        }
        return array_map('get_userdata', $attendees);
    }

    public function render_attendee_list($event_id) {
        $attendees = $this->get_event_attendees($event_id);
        ob_start();
        ?>
        <div class="dc-event-attendees">
        <h3><?php esc_html_e('Event Attendees', 'dc-event-manager'); ?></h3>
        <?php if (!empty($attendees)) : ?>
                <ul>
                    <?php foreach ($attendees as $attendee) : ?>
                        <li><?php echo esc_html($attendee->display_name); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p><?php esc_html_e('No attendees yet.', 'dc-event-manager'); ?></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}