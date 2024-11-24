<?php
class DC_Event_Logger {
    private $log_file;

    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->log_dir = $upload_dir['basedir'] . '/dc-event-logs/';
        
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
            WP_Filesystem();
        }
        
        if (!$wp_filesystem->is_dir($this->log_dir)) {
            $wp_filesystem->mkdir($this->log_dir, 0755);
        }
        
        $this->log_file = $this->log_dir . 'dc-event-log.txt';
        if (!$wp_filesystem->exists($this->log_file)) {
            $wp_filesystem->touch($this->log_file);
        }
    }

    public function log($message, $level = 'info') {
        $log_entry = [
            'timestamp' => gmdate('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
        ];
        
        $log_line = wp_json_encode($log_entry) . "\n";
        
        global $wp_filesystem;
        $wp_filesystem->put_contents($this->log_file, $log_line, FILE_APPEND);
    }

    public function get_logs($limit = 100) {
        if (!file_exists($this->log_file)) {
            return array();
        }

        $logs = file($this->log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $logs = array_reverse($logs);
        return array_slice($logs, 0, $limit);
    }

    public function clear_logs() {
        WP_Filesystem();
        global $wp_filesystem;
        $wp_filesystem->delete( $this->log_file );
    }
}