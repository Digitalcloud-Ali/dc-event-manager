<?php
class DC_Event_Ticketing {
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_ticket_meta_box'));
        add_action('save_post', array($this, 'save_ticket_meta'));
        add_shortcode('dc_event_tickets', array($this, 'render_ticket_form'));
        add_action('wp_ajax_dc_purchase_ticket', array($this, 'process_ticket_purchase'));
        add_action('wp_ajax_nopriv_dc_purchase_ticket', array($this, 'process_ticket_purchase'));
    }

    public function add_ticket_meta_box() {
        add_meta_box(
            'dc_event_tickets',
            'Event Tickets',
            array($this, 'render_ticket_meta_box'),
            'dc_event',
            'normal',
            'high'
        );
    }

    public function render_ticket_meta_box($post) {
        wp_nonce_field('dc_event_tickets', 'dc_event_tickets_nonce');
        $ticket_price = get_post_meta($post->ID, '_event_ticket_price', true);
        $ticket_quantity = get_post_meta($post->ID, '_event_ticket_quantity', true);
        ?>
        <p>
            <label for="event_ticket_price">Ticket Price:</label>
            <input type="number" step="0.01" id="event_ticket_price" name="event_ticket_price" value="<?php echo esc_attr($ticket_price); ?>">
        </p>
        <p>
            <label for="event_ticket_quantity">Available Tickets:</label>
            <input type="number" id="event_ticket_quantity" name="event_ticket_quantity" value="<?php echo esc_attr($ticket_quantity); ?>">
        </p>
        <?php
    }

    public function save_ticket_meta($post_id) {
        if (isset($_POST['dc_event_tickets_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['dc_event_tickets_nonce'])), 'dc_event_tickets')) {
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            if (!current_user_can('edit_post', $post_id)) {
                return;
            }

            if (isset($_POST['event_ticket_price'])) {
                $price = sanitize_text_field(wp_unslash($_POST['event_ticket_price']));
                update_post_meta($post_id, '_event_ticket_price', $price);
            }
            if (isset($_POST['event_ticket_quantity'])) {
                $quantity = intval(wp_unslash($_POST['event_ticket_quantity']));
                update_post_meta($post_id, '_event_ticket_quantity', $quantity);
            }
        }
    }

    public function render_ticket_form($atts) {
        $atts = shortcode_atts(array(
            'event_id' => get_the_ID(),
        ), $atts);

        $event_id = intval($atts['event_id']);
        $ticket_price = get_post_meta($event_id, '_event_ticket_price', true);
        $ticket_quantity = get_post_meta($event_id, '_event_ticket_quantity', true);

        ob_start();
        ?>
        <form class="dc-event-ticket-form" method="post">
            <?php wp_nonce_field('dc_purchase_ticket', 'dc_ticket_nonce'); ?>
            <input type="hidden" name="event_id" value="<?php echo esc_attr($event_id); ?>">
            <p>Price: $<?php echo esc_html($ticket_price); ?></p>
            <p>Available Tickets: <?php echo esc_html($ticket_quantity); ?></p>
            <p>
                <label for="ticket_quantity">Number of Tickets:</label>
                <input type="number" id="ticket_quantity" name="ticket_quantity" min="1" max="<?php echo esc_attr($ticket_quantity); ?>" required>
            </p>
            <input type="submit" value="Purchase Tickets">
        </form>
        <div id="dc-ticket-message"></div>
        <script>
        jQuery(document).ready(function($) {
            $('.dc-event-ticket-form').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                $.ajax({
                    url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                    type: 'POST',
                    data: formData + '&action=dc_purchase_ticket',
                    success: function(response) {
                        if (response.success) {
                            $('#dc-ticket-message').html('<p class="success">' + response.data + '</p>');
                        } else {
                            $('#dc-ticket-message').html('<p class="error">' + response.data + '</p>');
                        }
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public function process_ticket_purchase() {
        check_ajax_referer('dc_purchase_ticket', 'dc_ticket_nonce');

        $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
        $quantity = isset($_POST['ticket_quantity']) ? intval($_POST['ticket_quantity']) : 0;

        if (!$event_id || !$quantity) {
            wp_send_json_error('Invalid request');
        }

        $available_tickets = get_post_meta($event_id, '_event_ticket_quantity', true);
        if ($quantity > $available_tickets) {
            wp_send_json_error('Not enough tickets available');
        }

        // Process payment here (integrate with payment gateway)

        // Update available tickets
        update_post_meta($event_id, '_event_ticket_quantity', $available_tickets - $quantity);

        // Record the purchase
        $this->record_ticket_purchase($event_id, get_current_user_id(), $quantity);

        wp_send_json_success('Tickets purchased successfully');
    }

    private function record_ticket_purchase($event_id, $user_id, $quantity) {
        $purchases = get_post_meta($event_id, '_event_ticket_purchases', true);
        if (!is_array($purchases)) {
            $purchases = array();
        }
        $purchases[] = array(
            'user_id' => $user_id,
            'quantity' => $quantity,
            'timestamp' => current_time('mysql')
        );
        update_post_meta($event_id, '_event_ticket_purchases', $purchases);
    }
}