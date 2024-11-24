<?php
class DC_Event_Analytics {
    public function __construct() {
        add_action('wp_footer', array($this, 'track_event_views'));
        add_action('dc_event_after_attendance', array($this, 'track_event_attendance'), 10, 2);
        add_action('admin_menu', array($this, 'add_analytics_menu'));
        add_action('wp_ajax_dc_event_interaction', array($this, 'track_user_interaction'));
        add_action('wp_ajax_dc_export_analytics', array($this, 'export_analytics_data'));
    }

    public function track_event_views() {
        if (is_singular('dc_event')) {
            $event_id = get_the_ID();
            $views = get_post_meta($event_id, '_event_views', true);
            update_post_meta($event_id, '_event_views', intval($views) + 1);
        }
    }

    public function track_event_attendance($user_id, $event_id) {
        $attendees = get_post_meta($event_id, '_event_attendees', true);
        if (!is_array($attendees)) {
            $attendees = array();
        }
        $attendees[] = $user_id;
        update_post_meta($event_id, '_event_attendees', array_unique($attendees));
    }

    public function track_user_interaction() {
        check_ajax_referer('dc_event_interaction', 'nonce');
        $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
        $interaction_type = isset($_POST['interaction_type']) ? sanitize_text_field(wp_unslash($_POST['interaction_type'])) : '';
        
        if (!$event_id || !$interaction_type) {
            wp_send_json_error('Invalid event ID or interaction type');
        }
        
        $interactions = get_post_meta($event_id, '_event_interactions', true);
        if (!is_array($interactions)) {
            $interactions = array();
        }
        if (!isset($interactions[$interaction_type])) {
            $interactions[$interaction_type] = 0;
        }
        $interactions[$interaction_type]++;
        update_post_meta($event_id, '_event_interactions', $interactions);
        
        wp_send_json_success();
    }

    public function add_analytics_menu() {
        add_submenu_page(
            'dc-event-manager',
            'Event Analytics',
            'Analytics',
            'manage_options',
            'dc-event-analytics',
            array($this, 'render_analytics_page')
        );
    }

    public function render_analytics_page() {
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.7.1', true);
        wp_enqueue_script('dc-event-analytics', DC_EVENT_MANAGER_PLUGIN_URL . 'assets/js/dc-event-analytics.js', array('jquery', 'chart-js'), DC_EVENT_MANAGER_VERSION, true);

        $event_data = $this->get_event_data();
        wp_localize_script('dc-event-analytics', 'dcEventAnalyticsData', $event_data);

        echo '<div class="wrap"><h1>Event Analytics</h1>';
        echo '<button id="dc-export-analytics" class="button button-primary">Export Analytics Data</button>';
        echo '<div id="dc-event-views-chart" style="width: 100%; max-width: 800px;"><canvas></canvas></div>';
        echo '<div id="dc-event-attendance-chart" style="width: 100%; max-width: 800px;"><canvas></canvas></div>';
        echo '<div id="dc-event-conversion-chart" style="width: 100%; max-width: 800px;"><canvas></canvas></div>';
        echo '<div id="dc-event-engagement-chart" style="width: 100%; max-width: 800px;"><canvas></canvas></div>';
        echo '<div id="dc-event-popularity-trend-chart" style="width: 100%; max-width: 800px;"><canvas></canvas></div>';
        echo '<div id="dc-event-user-engagement-chart" style="width: 100%; max-width: 800px;"><canvas></canvas></div>';
        echo '</div>';
    }

    public function export_analytics_data() {
        check_ajax_referer('dc_export_analytics', 'nonce');

        $data = $this->get_event_data();
        $csv = $this->generate_csv($data);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="event_analytics_export.csv"');
        echo esc_html($csv);
        wp_die();
    }

    private function generate_csv($data) {
        $output = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($output, array('Event', 'Views', 'Attendance', 'Conversion Rate (%)', 'Engagement Rate (%)'));

        // Add data
        foreach ($data['views'] as $index => $event) {
            fputcsv($output, array(
                $event['label'],
                $event['value'],
                $data['attendance'][$index]['value'],
                $data['conversion'][$index]['value'],
                $data['engagement'][$index]['value']
            ));
        }

        return ob_get_clean();
    }

    public function get_event_data() {
        global $dc_event_cache;
        $cached_data = $dc_event_cache->get('event_analytics_data');

        if ($cached_data !== false) {
            return $cached_data;
        }

        $events = get_posts(array('post_type' => 'dc_event', 'posts_per_page' => -1));
        $views_data = array();
        $attendance_data = array();
        $conversion_data = array();
        $engagement_data = array();
        $popularity_trend = array();
        $user_engagement_patterns = array();

        foreach ($events as $event) {
            $views = intval(get_post_meta($event->ID, '_event_views', true));
            $attendees = get_post_meta($event->ID, '_event_attendees', true);
            $attendees_count = is_array($attendees) ? count($attendees) : 0;
            $interactions = get_post_meta($event->ID, '_event_interactions', true);
            
            $views_data[] = array(
                'label' => $event->post_title,
                'value' => $views
            );

            $attendance_data[] = array(
                'label' => $event->post_title,
                'value' => $attendees_count
            );

            $conversion_data[] = array(
                'label' => $event->post_title,
                'value' => $views > 0 ? ($attendees_count / $views) * 100 : 0
            );

            $total_interactions = is_array($interactions) ? array_sum($interactions) : 0;
            $engagement_data[] = array(
                'label' => $event->post_title,
                'value' => $views > 0 ? ($total_interactions / $views) * 100 : 0
            );

            // Popularity trend (based on views over time)
            $event_date = get_post_meta($event->ID, '_event_start_date', true);
            $days_since_event = (time() - strtotime($event_date)) / (60 * 60 * 24);
            $popularity_trend[] = array(
                'label' => $event->post_title,
                'value' => $days_since_event > 0 ? $views / $days_since_event : 0
            );

            // User engagement patterns
            $user_engagement = array();
            if (is_array($interactions)) {
                foreach ($interactions as $type => $count) {
                    $user_engagement[] = array(
                        'type' => $type,
                        'count' => $count
                    );
                }
            }
            $user_engagement_patterns[] = array(
                'label' => $event->post_title,
                'engagements' => $user_engagement
            );
        }

        $data = array(
            'views' => $views_data,
            'attendance' => $attendance_data,
            'conversion' => $conversion_data,
            'engagement' => $engagement_data,
            'popularity_trend' => $popularity_trend,
            'user_engagement_patterns' => $user_engagement_patterns
        );

        $dc_event_cache->set('event_analytics_data', $data, 3600);

        return $data;
    }
}