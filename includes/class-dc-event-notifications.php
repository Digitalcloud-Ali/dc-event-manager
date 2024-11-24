<?php
class DC_Event_Notifications {
    public function __construct() {
        add_action('dc_event_after_save', array($this, 'schedule_event_notifications'), 10, 2);
        add_action('dc_event_send_notification', array($this, 'send_event_notification'), 10, 2);
    }

    public function schedule_event_notifications($event_id, $event_data) {
        $start_date = strtotime($event_data['_event_start_date']);
        $one_day_before = $start_date - (24 * 60 * 60);
        $one_hour_before = $start_date - (60 * 60);

        wp_schedule_single_event($one_day_before, 'dc_event_send_notification', array($event_id, 'one_day'));
        wp_schedule_single_event($one_hour_before, 'dc_event_send_notification', array($event_id, 'one_hour'));
    }

    public function send_event_notification($event_id, $notification_type) {
        $event = get_post($event_id);
        $attendees = $this->get_event_attendees($event_id);

        foreach ($attendees as $attendee) {
            $to = $attendee->user_email;
            $subject = $this->get_notification_subject($event, $notification_type);
            $message = $this->get_notification_message($event, $notification_type);
            wp_mail($to, $subject, $message);
        }
    }

    private function get_notification_subject($event, $notification_type) {
        if ($notification_type === 'one_day') {
            return 'Reminder: ' . $event->post_title . ' is tomorrow';
        } elseif ($notification_type === 'one_hour') {
            return 'Reminder: ' . $event->post_title . ' starts in 1 hour';
        }
    }

    private function get_notification_message($event, $notification_type) {
        $message = "Hello,\n\n";
        $message .= "This is a reminder for the event: " . $event->post_title . "\n\n";
        
        if ($notification_type === 'one_day') {
            $message .= "The event is scheduled for tomorrow.\n";
        } elseif ($notification_type === 'one_hour') {
            $message .= "The event starts in 1 hour.\n";
        }

        $message .= "Event details:\n";
        $message .= "Date: " . get_post_meta($event->ID, '_event_start_date', true) . "\n";
        $message .= "Location: " . get_post_meta($event->ID, '_event_location', true) . "\n\n";
        $message .= "We look forward to seeing you there!\n\n";
        $message .= "Best regards,\nThe Event Team";

        return $message;
    }

    private function get_event_attendees($event_id) {
        $attendees = get_post_meta($event_id, '_event_attendees', true);
        if (!is_array($attendees)) {
            return array();
        }
        return array_map('get_userdata', $attendees);
    }
}