<?php
class DC_Event_API {
    private $logger;

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
        $this->logger = new DC_Event_Logger();
    }

    public function register_routes() {
        register_rest_route('dc-event-manager/v1', '/events', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_events'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('dc-event-manager/v1', '/event/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_event'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('dc-event-manager/v1', '/event', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_event'),
            'permission_callback' => array($this, 'check_create_permission'),
        ));

        register_rest_route('dc-event-manager/v1', '/event/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_event'),
            'permission_callback' => array($this, 'check_update_permission'),
        ));

        register_rest_route('dc-event-manager/v1', '/event/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_event'),
            'permission_callback' => array($this, 'check_delete_permission'),
        ));
    }

    public function get_events($request) {
        $args = array(
            'post_type' => 'dc_event',
            'posts_per_page' => -1,
        );
        $events = get_posts($args);

        $data = array();
        foreach ($events as $event) {
            $data[] = $this->prepare_event_data($event);
        }

        return new WP_REST_Response($data, 200);
    }

    public function get_event($request) {
        $event_id = $request['id'];
        $event = get_post($event_id);

        if (empty($event) || $event->post_type !== 'dc_event') {
            return new WP_Error('no_event', 'Event not found', array('status' => 404));
        }

        $data = $this->prepare_event_data($event);
        return new WP_REST_Response($data, 200);
    }

    public function create_event($request) {
        try {
            $event_data = $this->prepare_event_for_database($request);
            $event_id = wp_insert_post($event_data);

            if (is_wp_error($event_id)) {
                throw new DC_Event_Exception('Failed to create event', 500, array('wp_error' => $event_id->get_error_message()));
            }

            $event = get_post($event_id);
            $data = $this->prepare_event_data($event);
            $this->logger->log('Event created successfully: ' . $event_id);
            return new WP_REST_Response($data, 201);
        } catch (Exception $e) {
            $this->handle_error('Failed to create event: ' . $e->getMessage(), $e->getCode(), array('request' => $request));
            return new WP_Error('create_failed', 'An unexpected error occurred', array('status' => 500));
        }
    }

    public function update_event($request) {
        $event_id = $request['id'];
        $event = get_post($event_id);

        if (empty($event) || $event->post_type !== 'dc_event') {
            return new WP_Error('no_event', 'Event not found', array('status' => 404));
        }

        $event_data = $this->prepare_event_for_database($request);
        $event_data['ID'] = $event_id;

        $updated = wp_update_post($event_data);

        if (is_wp_error($updated)) {
            return new WP_Error('update_failed', 'Failed to update event', array('status' => 500));
        }

        $event = get_post($event_id);
        $data = $this->prepare_event_data($event);

        return new WP_REST_Response($data, 200);
    }

    public function delete_event($request) {
        $event_id = $request['id'];
        $event = get_post($event_id);

        if (empty($event) || $event->post_type !== 'dc_event') {
            return new WP_Error('no_event', 'Event not found', array('status' => 404));
        }

        $result = wp_delete_post($event_id, true);

        if (!$result) {
            return new WP_Error('delete_failed', 'Failed to delete event', array('status' => 500));
        }

        return new WP_REST_Response(null, 204);
    }

    private function prepare_event_data($event) {
        return array(
            'id' => $event->ID,
            'title' => $event->post_title,
            'description' => $event->post_content,
            'start_date' => get_post_meta($event->ID, '_event_start_date', true),
            'end_date' => get_post_meta($event->ID, '_event_end_date', true),
            'location' => get_post_meta($event->ID, '_event_location', true),
        );
    }

    private function prepare_event_for_database($request) {
        $event_data = array(
            'post_type' => 'dc_event',
            'post_title' => sanitize_text_field($request['title']),
            'post_content' => wp_kses_post($request['description']),
            'post_status' => 'publish',
        );

        return $event_data;
    }

    public function check_create_permission() {
        return current_user_can('publish_posts');
    }

    public function check_update_permission($request) {
        $event_id = $request['id'];
        $event = get_post($event_id);

        if (empty($event)) {
            return false;
        }

        return current_user_can('edit_post', $event_id);
    }

    public function check_delete_permission($request) {
        $event_id = $request['id'];
        $event = get_post($event_id);

        if (empty($event)) {
            return false;
        }

        return current_user_can('delete_post', $event_id);
    }
}