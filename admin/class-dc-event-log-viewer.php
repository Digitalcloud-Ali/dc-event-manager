class DC_Event_Log_Viewer {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_log_viewer_menu'));
    }

    public function add_log_viewer_menu() {
        add_submenu_page(
            'dc-event-manager',
            'Log Viewer',
            'Log Viewer',
            'manage_options',
            'dc-event-log-viewer',
            array($this, 'display_log_viewer')
        );
    }

    public function display_log_viewer() {
        $logger = new DC_Event_Logger();
        $logs = $logger->get_logs();

        echo '<div class="wrap">';
        echo '<h1>DC Event Manager Log Viewer</h1>';
        echo '<table class="widefat">';
        echo '<thead><tr><th>Timestamp</th><th>Level</th><th>Message</th></tr></thead>';
        echo '<tbody>';
        foreach ($logs as $log) {
            $parts = explode('] [', $log);
            echo '<tr>';
            echo '<td>' . esc_html(trim($parts[0], '[]')) . '</td>';
            echo '<td>' . esc_html(trim($parts[1], '[]')) . '</td>';
            echo '<td>' . esc_html(substr($log, strpos($log, ']:') + 3)) . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }
}