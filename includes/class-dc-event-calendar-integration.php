<?php
class DC_Event_Calendar_Integration {
    public function __construct() {
        add_action('dc_event_after_details', array($this, 'add_calendar_buttons'));
        add_action('wp_ajax_dc_export_to_calendar', array($this, 'export_to_calendar'));
        add_action('wp_ajax_nopriv_dc_export_to_calendar', array($this, 'export_to_calendar'));
    }

    public function add_calendar_buttons() {
        ?>
        <div class="dc-event-calendar-integration">
            <button class="dc-export-google-calendar">Add to Google Calendar</button>
            <button class="dc-export-ical">Export to iCal</button>
        </div>
        <?php
    }

    public function export_to_calendar() {
        check_ajax_referer('dc_export_to_calendar', 'nonce');
        
        $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
        $calendar_type = isset($_POST['calendar_type']) ? sanitize_text_field(wp_unslash($_POST['calendar_type'])) : '';

        if (!$event_id || !in_array($calendar_type, array('google', 'ical'))) {
            wp_send_json_error('Invalid request');
        }

        $event = get_post($event_id);
        $start_date = get_post_meta($event_id, '_event_start_date', true);
        $end_date = get_post_meta($event_id, '_event_end_date', true);
        $location = get_post_meta($event_id, '_event_location', true);

        if ($calendar_type === 'google') {
            $url = $this->get_google_calendar_url($event, $start_date, $end_date, $location);
            wp_send_json_success(array('url' => $url));
        } else {
            $ical_content = $this->get_ical_content($event, $start_date, $end_date, $location);
            wp_send_json_success(array('ical' => $ical_content));
        }
    }

    private function get_google_calendar_url($event, $start_date, $end_date, $location) {
        $base_url = 'https://www.google.com/calendar/render?action=TEMPLATE';
        $params = array(
            'text' => urlencode($event->post_title),
            'dates' => urlencode(gmdate('Ymd\THis', strtotime($start_date)) . '/' . gmdate('Ymd\THis', strtotime($end_date))),
            'details' => urlencode(wp_strip_all_tags($event->post_content)),
            'location' => urlencode($location),
        );

        return $base_url . '&' . http_build_query($params);
    }

    private function get_ical_content($event, $start_date, $end_date, $location) {
        $content = "BEGIN:VCALENDAR\r\n";
        $content .= "VERSION:2.0\r\n";
        $content .= "PRODID:-//DC Event Manager//EN\r\n";
        $content .= "BEGIN:VEVENT\r\n";
        $content .= "UID:" . md5($event->ID . $start_date) . "@" . wp_parse_url(home_url(), PHP_URL_HOST) . "\r\n";
        $content .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
        $content .= "DTSTART:" . gmdate('Ymd\THis\Z', strtotime($start_date)) . "\r\n";
        $content .= "DTEND:" . gmdate('Ymd\THis\Z', strtotime($end_date)) . "\r\n";
        $content .= "SUMMARY:" . $this->ical_escape($event->post_title) . "\r\n";
        $content .= "DESCRIPTION:" . $this->ical_escape(wp_strip_all_tags($event->post_content)) . "\r\n";
        $content .= "LOCATION:" . $this->ical_escape($location) . "\r\n";
        $content .= "END:VEVENT\r\n";
        $content .= "END:VCALENDAR\r\n";

        return $content;
    }

    private function ical_escape($string) {
        return preg_replace('/([\,;])/','\\\$1', $string);
    }

    public function get_current_url() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
        $uri = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        return $protocol . '://' . $host . $uri;
    }
}