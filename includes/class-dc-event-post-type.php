<?php
class DC_Event_Post_Type {
    public function __construct() {
        add_action('init', array($this, 'register_event_post_type'));
        add_action('init', array($this, 'register_event_taxonomies'));
        add_action('add_meta_boxes', array($this, 'add_event_meta_boxes'));
        add_action('save_post', array($this, 'save_event_meta'));
    }

    public function register_event_post_type() {
        $args = array(
            'public' => true,
            'label'  => 'Events',
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
            'has_archive' => true,
            'rewrite' => array('slug' => 'events'),
            'menu_icon' => 'dashicons-calendar-alt',
        );
        register_post_type('dc_event', $args);
    }

    public function register_event_taxonomies() {
        register_taxonomy('event_category', 'dc_event', array(
            'label' => 'Event Categories',
            'hierarchical' => true,
        ));
        register_taxonomy('event_tag', 'dc_event', array(
            'label' => 'Event Tags',
            'hierarchical' => false,
        ));
    }

    public function add_event_meta_boxes() {
        add_meta_box(
            'dc_event_details',
            'Event Details',
            array($this, 'render_event_details_meta_box'),
            'dc_event',
            'normal',
            'high'
        );
    }

    public function render_event_details_meta_box($post) {
        wp_nonce_field('dc_event_details', 'dc_event_details_nonce');
        $start_date = get_post_meta($post->ID, '_event_start_date', true);
        $end_date = get_post_meta($post->ID, '_event_end_date', true);
        $location = get_post_meta($post->ID, '_event_location', true);
        ?>
        <div class="dc-event-tabs">
            <ul class="dc-event-tab-nav">
                <li class="active"><a href="#basic-info">Basic Info</a></li>
                <li><a href="#location">Location</a></li>
                <li><a href="#tickets">Tickets</a></li>
                <li><a href="#ar-integration">AR Integration</a></li>
            </ul>
            <div class="dc-event-tab-content">
                <div id="basic-info" class="dc-event-tab-pane active">
                    <p>
                        <label for="event_start_date">Start Date:</label>
                        <input type="datetime-local" id="event_start_date" name="event_start_date" value="<?php echo esc_attr($start_date); ?>">
                    </p>
                    <p>
                        <label for="event_end_date">End Date:</label>
                        <input type="datetime-local" id="event_end_date" name="event_end_date" value="<?php echo esc_attr($end_date); ?>">
                    </p>
                </div>
                <div id="location" class="dc-event-tab-pane">
                    <p>
                        <label for="event_location">Location:</label>
                        <input type="text" id="event_location" name="event_location" value="<?php echo esc_attr($location); ?>">
                    </p>
                    <?php $this->render_google_map(); ?>
                </div>
                <div id="tickets" class="dc-event-tab-pane">
                    <?php $this->render_ticket_fields(); ?>
                </div>
                <div id="ar-integration" class="dc-event-tab-pane">
                    <?php $this->render_ar_fields(); ?>
                </div>
            </div>
        </div>
        <?php
    }

    public function save_event_meta($post_id) {
        if (!isset($_POST['dc_event_details_nonce']) || !wp_verify_nonce(sanitize_key(wp_unslash($_POST['dc_event_details_nonce'])), 'dc_event_details')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['event_start_date'])) {
            $start_date = sanitize_text_field(wp_unslash($_POST['event_start_date']));
            update_post_meta($post_id, '_event_start_date', $start_date);
        }
        if (isset($_POST['event_end_date'])) {
            $end_date = sanitize_text_field(wp_unslash($_POST['event_end_date']));
            update_post_meta($post_id, '_event_end_date', $end_date);
        }
        if (isset($_POST['event_location'])) {
            $location = sanitize_text_field(wp_unslash($_POST['event_location']));
            update_post_meta($post_id, '_event_location', $location);
        }
    }

    public function save_event_details($post_id) {
        if (!isset($_POST['dc_event_details_nonce']) || !wp_verify_nonce(sanitize_key(wp_unslash($_POST['dc_event_details_nonce'])), 'dc_event_details')) {
            return;
        }
        
        if (isset($_POST['event_start_date'])) {
            $start_date = sanitize_text_field(wp_unslash($_POST['event_start_date']));
            update_post_meta($post_id, 'event_start_date', $start_date);
        }
        
        if (isset($_POST['event_end_date'])) {
            $end_date = sanitize_text_field(wp_unslash($_POST['event_end_date']));
            update_post_meta($post_id, 'event_end_date', $end_date);
        }
        
        if (isset($_POST['event_location'])) {
            $location = sanitize_text_field(wp_unslash($_POST['event_location']));
            update_post_meta($post_id, 'event_location', $location);
        }
    }
}