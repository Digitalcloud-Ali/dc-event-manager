<?php
class DC_Event_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'DC Event Manager',
            'DC Events',
            'manage_options',
            'dc-event-manager',
            array($this, 'display_settings_page'),
            'dashicons-calendar-alt',
            30
        );
    }

    public function register_settings() {
        register_setting('dc_event_manager_settings', 'dc_event_manager_settings');

        add_settings_section(
            'dc_event_manager_general_section',
            'General Settings',
            array($this, 'general_section_callback'),
            'dc-event-manager'
        );

        add_settings_field(
            'enable_ar',
            'Enable AR Integration',
            array($this, 'checkbox_field_callback'),
            'dc-event-manager',
            'dc_event_manager_general_section',
            array('label_for' => 'enable_ar')
        );

        add_settings_field(
            'enable_carbon_tracking',
            'Enable Carbon Tracking',
            array($this, 'checkbox_field_callback'),
            'dc-event-manager',
            'dc_event_manager_general_section',
            array('label_for' => 'enable_carbon_tracking')
        );

        // Add more settings fields as needed
    }

    public function display_settings_page() {
        ?>
        <div class="wrap">
            <h1>DC Event Manager Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('dc_event_manager_settings');
                do_settings_sections('dc-event-manager');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function general_section_callback() {
        echo '<p>General settings for DC Event Manager</p>';
    }

    public function checkbox_field_callback($args) {
        $options = get_option('dc_event_manager_settings');
        $value = isset($options[$args['label_for']]) ? $options[$args['label_for']] : '0';
        echo '<input type="checkbox" id="' . esc_attr($args['label_for']) . '" name="dc_event_manager_settings[' . esc_attr($args['label_for']) . ']" value="1" ' . checked($value, '1', false) . '>';
    }

    public function display_dashboard() {
        ?>
        <div class="wrap dc-event-dashboard">
            <h1>DC Event Manager Dashboard</h1>
            <div class="dc-event-dashboard-grid">
                <div class="dc-event-card">
                    <h2>Quick Stats</h2>
                    <?php $this->display_quick_stats(); ?>
                </div>
                <div class="dc-event-card">
                    <h2>Recent Events</h2>
                    <?php $this->display_recent_events(); ?>
                </div>
                <div class="dc-event-card">
                    <h2>Quick Actions</h2>
                    <?php $this->display_quick_actions(); ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function display_quick_stats() {
        $total_events = wp_count_posts('dc_event')->publish;
        $upcoming_events = $this->get_upcoming_events_count();
        ?>
        <ul>
        echo '<li>Total Events: ' . esc_html($total_events) . '</li>';
        echo '<li>Upcoming Events: ' . esc_html($upcoming_events) . '</li>';
        </ul>
        <?php
    }

    private function display_recent_events() {
        $recent_events = get_posts(array('post_type' => 'dc_event', 'posts_per_page' => 5));
        if ($recent_events) {
            echo '<ul>';
            foreach ($recent_events as $event) {
                echo '<li><a href="' . esc_url(get_edit_post_link($event->ID)) . '">' . esc_html($event->post_title) . '</a></li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No recent events.</p>';
        }
    }

    private function display_quick_actions() {
        ?>
        echo '<a href="' . esc_url(admin_url('post-new.php?post_type=dc_event')) . '" class="button button-primary">Create New Event</a>';
        echo '<a href="' . esc_url(admin_url('edit.php?post_type=dc_event')) . '" class="button">Manage Events</a>';
        <?php
    }

    private function get_upcoming_events_count() {
        $cache_key = 'dc_event_upcoming_events_count';
        $count = get_transient($cache_key);

        if (false === $count) {
            global $wpdb;
            $current_time = current_time('mysql');
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} p
                JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = 'dc_event'
                AND p.post_status = 'publish'
                AND pm.meta_key = '_event_start_date'
                AND pm.meta_value >= %s",
                $current_time
            ));

            set_transient($cache_key, $count, HOUR_IN_SECONDS);
        }

        return $count;
    }

    public function get_events_by_date($date) {
        $args = array(
            'post_type' => 'dc_event',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'event_start_date',
                    'value' => $date,
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            ),
            'orderby' => 'meta_value',
            'order' => 'ASC'
        );
        
        return get_posts($args);
    }
}