<?php
class DC_Event_Check_In {
    public function __construct() {
        add_action('wp_ajax_dc_event_check_in', array($this, 'handle_check_in'));
        add_action('wp_ajax_nopriv_dc_event_check_in', array($this, 'handle_check_in'));
        add_shortcode('dc_event_check_in', array($this, 'render_check_in_form'));
    }

    public function handle_check_in() {
        check_ajax_referer('dc_event_check_in', 'nonce');

        $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
        $user_email = isset($_POST['user_email']) ? sanitize_email(wp_unslash($_POST['user_email'])) : '';
        
        if (!$event_id || !$user_email) {
            wp_send_json_error('Invalid event ID or email');
        }

        $user = get_user_by('email', $user_email);
        if (!$user) {
            wp_send_json_error('User not found');
        }

        $check_ins = get_post_meta($event_id, '_event_check_ins', true);
        if (!is_array($check_ins)) {
            $check_ins = array();
        }

        if (in_array($user->ID, $check_ins)) {
            wp_send_json_error('User already checked in');
        }

        $check_ins[] = $user->ID;
        update_post_meta($event_id, '_event_check_ins', $check_ins);

        do_action('dc_event_after_check_in', $user->ID, $event_id);

        wp_send_json_success('Check-in successful');
    }

    public function render_check_in_form($atts) {
        $atts = shortcode_atts(array(
            'event_id' => get_the_ID(),
        ), $atts);

        $event_id = intval($atts['event_id']);

        ob_start();
        ?>
        <form id="dc-event-check-in-form" class="dc-event-check-in-form">
            <input type="hidden" name="event_id" value="<?php echo esc_attr($event_id); ?>">
            <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('dc_event_check_in')); ?>">
            <p>
                <label for="user_email">Email:</label>
                <input type="email" id="user_email" name="user_email" required>
            </p>
            <p>
                <input type="submit" value="Check In">
            </p>
        </form>
        <div id="dc-event-check-in-message"></div>
        <script>
        jQuery(document).ready(function($) {
            $('#dc-event-check-in-form').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                $.ajax({
                    url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                    type: 'POST',
                    data: formData + '&action=dc_event_check_in',
                    success: function(response) {
                        if (response.success) {
                            $('#dc-event-check-in-message').html('<p class="success">' + response.data + '</p>');
                        } else {
                            $('#dc-event-check-in-message').html('<p class="error">' + response.data + '</p>');
                        }
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
}