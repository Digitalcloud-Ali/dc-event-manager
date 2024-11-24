<?php
class DC_Event_Gamification {
    private $badges = array(
        'attendance' => array('name' => 'Event Attendee', 'description' => 'Attended an event'),
        'organizer' => array('name' => 'Event Organizer', 'description' => 'Organized an event'),
        'networker' => array('name' => 'Super Networker', 'description' => 'Connected with 10 people'),
        'eco_warrior' => array('name' => 'Eco Warrior', 'description' => 'Reduced carbon footprint by 50%'),
    );

    public function __construct() {
        add_action('dc_event_after_attendance', array($this, 'award_attendance_badge'), 10, 2);
        add_action('dc_event_after_save', array($this, 'award_organizer_badge'));
        add_action('dc_event_network_milestone', array($this, 'award_networker_badge'));
        add_action('dc_event_eco_action', array($this, 'award_eco_warrior_badge'));
        add_shortcode('dc_event_user_badges', array($this, 'render_user_badges'));
    }

    public function award_badge($user_id, $badge_key) {
        if (!isset($this->badges[$badge_key])) {
            return;
        }

        $user_badges = get_user_meta($user_id, 'dc_event_badges', true);
        if (!is_array($user_badges)) {
            $user_badges = array();
        }

        if (!in_array($badge_key, $user_badges)) {
            $user_badges[] = $badge_key;
            update_user_meta($user_id, 'dc_event_badges', $user_badges);
            do_action('dc_event_badge_awarded', $user_id, $badge_key);
        }
    }

    public function award_attendance_badge($user_id, $event_id) {
        $this->award_badge($user_id, 'attendance');
    }

    public function award_organizer_badge($event_id) {
        $organizer_id = get_post_field('post_author', $event_id);
        $this->award_badge($organizer_id, 'organizer');
    }

    public function award_networker_badge($user_id) {
        $this->award_badge($user_id, 'networker');
    }

    public function award_eco_warrior_badge($user_id) {
        $this->award_badge($user_id, 'eco_warrior');
    }

    public function render_user_badges($atts) {
        $atts = shortcode_atts(array(
            'user_id' => get_current_user_id(),
        ), $atts);

        $user_id = intval($atts['user_id']);
        $user_badges = get_user_meta($user_id, 'dc_event_badges', true);

        ob_start();
        ?>
        <div class="dc-event-user-badges">
            <h3>User Badges</h3>
            <?php if (is_array($user_badges) && !empty($user_badges)) : ?>
                <ul>
                    <?php foreach ($user_badges as $badge_key) : ?>
                        <li>
                            <strong><?php echo esc_html($this->badges[$badge_key]['name']); ?></strong>
                            <p><?php echo esc_html($this->badges[$badge_key]['description']); ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p>No badges earned yet.</p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}