<?php
class DC_Event_Frontend_Submission {
    public function __construct() {
        add_shortcode('dc_event_submission_form', array($this, 'render_submission_form'));
        add_action('wp_ajax_dc_submit_event', array($this, 'handle_event_submission'));
        add_action('wp_ajax_nopriv_dc_submit_event', array($this, 'handle_event_submission'));
    }

    public function render_submission_form() {
        ob_start();
        ?>
        <form id="dc-event-submission-form" class="dc-event-submission-form">
            <?php wp_nonce_field('dc_submit_event', 'dc_event_nonce'); ?>
            <p>
                <label for="event_title">Event Title:</label>
                <input type="text" id="event_title" name="event_title" required>
            </p>
            <p>
                <label for="event_description">Event Description:</label>
                <textarea id="event_description" name="event_description" required></textarea>
            </p>
            <p>
                <label for="event_start_date">Start Date:</label>
                <input type="datetime-local" id="event_start_date" name="event_start_date" required>
            </p>
            <p>
                <label for="event_end_date">End Date:</label>
                <input type="datetime-local" id="event_end_date" name="event_end_date" required>
            </p>
            <p>
                <label for="event_location">Location:</label>
                <input type="text" id="event_location" name="event_location" required>
            </p>
            <p>
                <label for="event_ar_model">AR Model URL:</label>
                <input type="url" id="event_ar_model" name="event_ar_model">
            </p>
            <p>
                <label for="event_ticket_price">Ticket Price:</label>
                <input type="number" step="0.01" id="event_ticket_price" name="event_ticket_price">
            </p>
            <p>
                <label for="event_ticket_quantity">Available Tickets:</label>
                <input type="number" id="event_ticket_quantity" name="event_ticket_quantity">
            </p>
            <p>
                <label for="event_categories">Event Categories:</label>
                <?php wp_dropdown_categories(array('taxonomy' => 'event_category', 'name' => 'event_categories[]', 'multiple' => true, 'hide_empty' => false)); ?>
            </p>
            <p>
                <label for="event_tags">Event Tags:</label>
                <input type="text" id="event_tags" name="event_tags" placeholder="Enter tags separated by commas">
            </p>
            <p>
                <input type="submit" value="Submit Event">
            </p>
        </form>
        <div id="dc-event-submission-message"></div>
        <script>
        jQuery(document).ready(function($) {
            $('#dc-event-submission-form').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                $.ajax({
                    url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                    type: 'POST',
                    data: formData + '&action=dc_submit_event',
                    success: function(response) {
                        if (response.success) {
                            $('#dc-event-submission-message').html('<p class="success">' + response.data + '</p>');
                        } else {
                            $('#dc-event-submission-message').html('<p class="error">' + response.data + '</p>');
                        }
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public function handle_event_submission() {
        check_ajax_referer('dc_submit_event', 'dc_event_nonce');

        $event_data = array(
            $event_title = isset($_POST['event_title']) ? sanitize_text_field(wp_unslash($_POST['event_title'])) : '';
            $event_description = isset($_POST['event_description']) ? wp_kses_post(wp_unslash($_POST['event_description'])) : '';
            'post_status'  => 'pending',
            'post_type'    => 'dc_event',
        );

        $event_id = wp_insert_post($event_data);

        if (!is_wp_error($event_id)) {
            // Set categories
            if (isset($_POST['event_categories'])) {
                $categories = array_map('intval', $_POST['event_categories']);
                wp_set_object_terms($event_id, $categories, 'event_category');
            }

            // Set tags
            if (isset($_POST['event_tags'])) {
                $tags = isset($_POST['event_tags']) ? explode(',', sanitize_text_field(wp_unslash($_POST['event_tags']))) : array();
                wp_set_object_terms($event_id, $tags, 'event_tag');
            }

            $start_date = isset($_POST['event_start_date']) ? sanitize_text_field(wp_unslash($_POST['event_start_date'])) : '';
            $end_date = isset($_POST['event_end_date']) ? sanitize_text_field(wp_unslash($_POST['event_end_date'])) : '';
            $location = isset($_POST['event_location']) ? sanitize_text_field(wp_unslash($_POST['event_location'])) : '';
            $ar_model = isset($_POST['event_ar_model']) ? esc_url_raw(wp_unslash($_POST['event_ar_model'])) : '';
            $ticket_price = isset($_POST['event_ticket_price']) ? floatval($_POST['event_ticket_price']) : 0;
            $ticket_quantity = isset($_POST['event_ticket_quantity']) ? intval($_POST['event_ticket_quantity']) : 0;

            wp_send_json_success('Event submitted successfully and is pending review');
        } else {
            wp_send_json_error('Failed to submit event');
        }
    }
}