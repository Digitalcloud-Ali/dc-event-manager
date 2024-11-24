<?php
/**
 * The main plugin class.
 *
 * This class defines all core functionality of the plugin.
 *
 * @since      1.0.0
 * @package    DC_Event_Manager
 * @subpackage DC_Event_Manager/includes
 */
class DC_Event_Manager {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      DC_Event_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->setup_cron_job();
        $this->optimize_performance();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-loader.php';
        require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'includes/class-dc-event-i18n.php';
        require_once DC_EVENT_MANAGER_PLUGIN_DIR . 'admin/class-dc-event-admin.php';
        
        $this->loader = new DC_Event_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new DC_Event_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new DC_Event_Admin($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
    }

    /**
     * Register all of the hooks related to the public-facing functionality of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $this->loader->add_action('wp_enqueue_scripts', $this, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $this, 'enqueue_scripts');

        // Initialize new features
        $ar_integration = new DC_Event_AR_Integration();
        $networking = new DC_Event_Networking();
        $carbon_tracker = new DC_Event_Carbon_Tracker();
        $gamification = new DC_Event_Gamification();

        // Add hooks for new features
        $this->loader->add_action('dc_event_after_save', $carbon_tracker, 'calculate_carbon_footprint');
        $this->loader->add_action('dc_event_after_attendance', $gamification, 'award_attendance_badge', 10, 2);

        // Add error handling
        add_action('wp_ajax_nopriv_dc_event_error', array($this, 'handle_ajax_error'));
        add_action('wp_ajax_dc_event_error', array($this, 'handle_ajax_error'));
    }

    public function handle_ajax_error() {
        check_ajax_referer('dc_event_ajax_nonce', 'nonce');
        $error = isset($_POST['error']) ? sanitize_text_field(wp_unslash($_POST['error'])) : 'Unknown error';
        error_log('DC Event Manager Error: ' . $error);
        wp_send_json_error('An error occurred. Please try again later.');
    }

    public function handle_error($message, $error_code = '', $context = array()) {
        $logger = new DC_Event_Logger();
        $logger->log($message, 'error', $context);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("DC Event Manager Error: $message");
        }

        if (is_admin()) {
            add_action('admin_notices', function() use ($message) {
                echo '<div class="error"><p>' . esc_html($message) . '</p></div>';
            });
        }
    }

    public function handle_errors() {
        if (isset($_POST['error']) && check_admin_referer('dc_event_error_nonce')) {
            $error = sanitize_text_field(wp_unslash($_POST['error']));
            // Process the error
        }
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, DC_EVENT_MANAGER_PLUGIN_URL . 'assets/css/dc-event-manager-public.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, DC_EVENT_MANAGER_PLUGIN_URL . 'assets/js/dc-event-manager-public.js', array('jquery'), $this->version, false);
        
        // Add AJAX URL and nonce for security
        wp_localize_script($this->plugin_name, 'dc_event_manager', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dc_export_analytics')
        ));
    }

    public function update_cached_data() {
        global $wpdb;
        
        // Use caching for events
        $events = wp_cache_get('all_events', 'dc_event_manager');
        if (false === $events) {
            $events = get_posts(array('post_type' => 'dc_event', 'posts_per_page' => -1));
            wp_cache_set('all_events', $events, 'dc_event_manager', 3600);
        }

        // Use caching for analytics data
        $event_data = wp_cache_get('event_analytics_data', 'dc_event_manager');
        if (false === $event_data) {
            $analytics = new DC_Event_Analytics();
            $event_data = $analytics->get_event_data();
            wp_cache_set('event_analytics_data', $event_data, 'dc_event_manager', 3600);
        }

        // Log the cache update
        global $dc_event_logger;
        $dc_event_logger->log('Cached data updated by cron job');
    }

    /**
     * Set up cron job for regular tasks.
     *
     * @since    1.0.0
     * @access   private
     */
    private function setup_cron_job() {
        add_action('dc_event_update_cache', array($this, 'update_cached_data'));
        if (!wp_next_scheduled('dc_event_update_cache')) {
            wp_schedule_event(time(), 'hourly', 'dc_event_update_cache');
        }
    }

    public static function activate() {
        // Activation logic
        flush_rewrite_rules();
        wp_schedule_event(time(), 'hourly', 'dc_event_update_cache');
    }

    public static function deactivate() {
        // Deactivation logic
        flush_rewrite_rules();
        wp_clear_scheduled_hook('dc_event_update_cache');
    }

    /**
     * Optimize performance by implementing caching and database indexing.
     *
     * @since    1.0.0
     * @access   private
     */
    private function optimize_performance() {
        // Implement database indexing
        add_action('init', array($this, 'create_custom_indexes'));

        // Optimize queries
        add_filter('posts_where', array($this, 'optimize_event_queries'), 10, 2);

        // Implement transient caching
        add_action('save_post_dc_event', array($this, 'clear_event_cache'));
    }

    public function create_custom_indexes() {
        // This function should be removed or replaced with a more WordPress-friendly approach
        // Consider using custom tables or optimizing queries instead of creating custom indexes
    }

    public function optimize_event_queries($where, $query) {
        if ($query->get('post_type') === 'dc_event') {
            $where .= " AND post_date > '" . gmdate('Y-m-d', strtotime('-1 year')) . "'";
        }
        return $where;
    }

    public function clear_event_cache($post_id) {
        delete_transient('dc_event_upcoming_events');
        delete_transient('dc_event_analytics_data');
    }

    public function get_formatted_date($timestamp) {
        return gmdate('Y-m-d H:i:s', $timestamp);
    }

    public function get_event_date($event_id) {
        return gmdate('Y-m-d H:i:s', get_post_meta($event_id, 'event_date', true));
    }

    // Add caching to database queries
    public function get_event_count() {
        $cache_key = 'dc_event_count';
        $event_count = wp_cache_get($cache_key);
        
        if (false === $event_count) {
            $event_count = wp_count_posts('dc_event')->publish;
            wp_cache_set($cache_key, $event_count, '', 3600); // Cache for 1 hour
        }
        
        return $event_count;
    }
}