<?php
class DC_Event_Features_Test extends WP_UnitTestCase {
    public function test_ar_integration() {
        $ar_integration = new DC_Event_AR_Integration();
        $event_id = $this->factory->post->create(array('post_type' => 'dc_event'));
        $ar_model_url = 'https://example.com/model.gltf';
        update_post_meta($event_id, '_event_ar_model', $ar_model_url);

        $shortcode_output = $ar_integration->render_ar_view(array('event_id' => $event_id));
        $this->assertStringContainsString($ar_model_url, $shortcode_output);
    }

    public function test_carbon_tracking() {
        $carbon_tracker = new DC_Event_Carbon_Tracker();
        $event_id = $this->factory->post->create(array('post_type' => 'dc_event'));
        update_post_meta($event_id, '_event_start_date', '2023-06-01 10:00:00');
        update_post_meta($event_id, '_event_end_date', '2023-06-01 18:00:00');
        update_post_meta($event_id, '_event_location', 'New York, NY');

        $footprint = $carbon_tracker->calculate_carbon_footprint($event_id);
        $this->assertIsFloat($footprint);
        $this->assertGreaterThan(0, $footprint);
    }

    public function test_gamification() {
        $gamification = new DC_Event_Gamification();
        $user_id = $this->factory->user->create();
        $event_id = $this->factory->post->create(array('post_type' => 'dc_event'));

        $gamification->award_attendance_badge($user_id, $event_id);
        $user_badges = get_user_meta($user_id, 'dc_event_badges', true);
        $this->assertContains('attendance', $user_badges);

        $gamification->award_organizer_badge($event_id);
        $organizer_id = get_post_field('post_author', $event_id);
        $organizer_badges = get_user_meta($organizer_id, 'dc_event_badges', true);
        $this->assertContains('organizer', $organizer_badges);
    }

    public function test_recurring_events() {
        $recurrence = new DC_Event_Recurrence();
        $event_id = $this->factory->post->create(array('post_type' => 'dc_event'));
        update_post_meta($event_id, '_event_start_date', '2023-06-01 10:00:00');
        update_post_meta($event_id, '_event_end_date', '2023-06-01 18:00:00');
        update_post_meta($event_id, '_event_recurrence', 'weekly');

        $recurrence->generate_recurring_events($event_id);
        
        $cache_key = 'dc_event_recurring_test_' . $event_id;
        $recurring_events = wp_cache_get($cache_key);

        if (false === $recurring_events) {
            $recurring_events = get_posts(array(
                'post_type' => 'dc_event',
                'meta_query' => array(
                    array(
                        'key' => '_event_start_date',
                        'value' => '2023-06-08 10:00:00',
                        'compare' => '=',
                    )
                )
            ));
            wp_cache_set($cache_key, $recurring_events, 'dc_event_manager', 3600);
        }

        $this->assertCount(1, $recurring_events);
    }

    public function test_check_in_system() {
        $check_in = new DC_Event_Check_In();
        $event_id = $this->factory->post->create(array('post_type' => 'dc_event'));
        $user_id = $this->factory->user->create();

        $_POST['event_id'] = $event_id;
        $_POST['user_email'] = get_userdata($user_id)->user_email;
        $_POST['nonce'] = wp_create_nonce('dc_event_check_in');

        $check_in->handle_check_in();

        $check_ins = get_post_meta($event_id, '_event_check_ins', true);
        $this->assertContains($user_id, $check_ins);
    }

    public function test_analytics() {
        $analytics = new DC_Event_Analytics();
        $event_id = $this->factory->post->create(array('post_type' => 'dc_event'));
        update_post_meta($event_id, '_event_views', 100);
        update_post_meta($event_id, '_event_attendees', array(1, 2, 3));

        $data = $analytics->get_event_data();

        $this->assertArrayHasKey('views', $data);
        $this->assertArrayHasKey('attendance', $data);
        $this->assertArrayHasKey('conversion', $data);
    }

    public function test_event_creation() {
        $event_post_type = new DC_Event_Post_Type();
        $event_id = $this->factory->post->create(array('post_type' => 'dc_event'));
        $this->assertEquals('dc_event', get_post_type($event_id));
    }

    public function test_event_meta_fields() {
        $event_id = $this->factory->post->create(array('post_type' => 'dc_event'));
        update_post_meta($event_id, '_event_start_date', '2023-07-01 10:00:00');
        update_post_meta($event_id, '_event_end_date', '2023-07-01 18:00:00');
        update_post_meta($event_id, '_event_location', 'Test Location');

        $this->assertEquals('2023-07-01 10:00:00', get_post_meta($event_id, '_event_start_date', true));
        $this->assertEquals('2023-07-01 18:00:00', get_post_meta($event_id, '_event_end_date', true));
        $this->assertEquals('Test Location', get_post_meta($event_id, '_event_location', true));
    }

    public function test_event_shortcodes() {
        $shortcodes = new DC_Event_Shortcodes();
        $event_id = $this->factory->post->create(array('post_type' => 'dc_event'));
        $shortcode_output = $shortcodes->event_details_shortcode(array('id' => $event_id));
        $this->assertStringContainsString('dc-event-details', $shortcode_output);
    }

    public function test_ticketing_system() {
        $ticketing = new DC_Event_Ticketing();
        $event_id = $this->factory->post->create(array('post_type' => 'dc_event'));
        update_post_meta($event_id, '_event_ticket_price', 10);
        update_post_meta($event_id, '_event_ticket_quantity', 100);

        $shortcode_output = $ticketing->render_ticket_form(array('event_id' => $event_id));
        $this->assertStringContainsString('dc-event-ticket-form', $shortcode_output);
        $this->assertStringContainsString('Price: $10', $shortcode_output);
        $this->assertStringContainsString('Available Tickets: 100', $shortcode_output);
    }

    public function test_event_creation_with_invalid_data() {
        $api = new DC_Event_API();
        $request = new WP_REST_Request('POST', '/dc-event-manager/v1/event');
        $request->set_param('title', ''); // Invalid title

        $response = $api->create_event($request);
        $this->assertInstanceOf(WP_Error::class, $response);
        $this->assertEquals('invalid_event_data', $response->get_error_code());
    }

    public function test_carbon_footprint_calculation_with_no_attendees() {
        $carbon_tracker = new DC_Event_Carbon_Tracker();
        $event_id = $this->factory->post->create(array('post_type' => 'dc_event'));
        update_post_meta($event_id, '_event_start_date', '2023-06-01 10:00:00');
        update_post_meta($event_id, '_event_end_date', '2023-06-01 18:00:00');
        update_post_meta($event_id, '_event_location', 'New York, NY');
        update_post_meta($event_id, '_event_attendees', array());

        $footprint = $carbon_tracker->calculate_carbon_footprint($event_id);
        $this->assertEquals(0, $footprint);
    }

    public function test_recurring_event_with_invalid_recurrence() {
        $recurrence = new DC_Event_Recurrence();
        $event_id = $this->factory->post->create(array('post_type' => 'dc_event'));
        update_post_meta($event_id, '_event_start_date', '2023-06-01 10:00:00');
        update_post_meta($event_id, '_event_end_date', '2023-06-01 18:00:00');
        update_post_meta($event_id, '_event_recurrence', 'invalid_recurrence');

        $this->expectException(InvalidArgumentException::class);
        $recurrence->generate_recurring_events($event_id);
    }
}