class DC_Event_Dashboard_Widget {
    public function __construct() {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
    }

    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'dc_event_quick_management',
            'DC Event Quick Management',
            array($this, 'render_dashboard_widget')
        );
    }

    public function render_dashboard_widget() {
        $recent_events = get_posts(array(
            'post_type' => 'dc_event',
            'posts_per_page' => 5,
            'orderby' => 'date',
            'order' => 'DESC'
        ));

        echo '<h3>Recent Events</h3>';
        echo '<ul>';
        foreach ($recent_events as $event) {
            echo '<li><a href="' . get_edit_post_link($event->ID) . '">' . esc_html($event->post_title) . '</a></li>';
        }
        echo '</ul>';
        echo '<p><a href="' . admin_url('post-new.php?post_type=dc_event') . '">Create New Event</a></p>';
    }
}