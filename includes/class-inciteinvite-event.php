<?php
/**
 * Created by PhpStorm.
 * User: will
 * Date: 25/04/15
 * Time: 11:53 AM
 */

class InciteInvite_Event {

    public $type = 'iievent';

    public function show_event_info($content) {

        if(!is_single() || get_post_type() != 'iievent') { return $content; }

        $date = get_post_meta(get_the_ID(), 'iievent_date', true);
        $duration = get_post_meta(get_the_ID(), 'iievent_duration', true);
        $attendance = get_post_meta(get_the_ID(), 'iievent_attendance', true);

        $inCount = 0;
        $outCount = 0;
        foreach($attendance as $response) {
            if($response == 'in')
                $inCount++;
            elseif($response == 'out')
                $outCount++;
        }

        $content .= '<h3>' . $date->format('l jS \of F Y h:i A') . '</h3>';
        $content .= '<h4>For: ' . $duration . ' hour</h4>';
        $content .= '<h4>With: <strong>' . $inCount . '</strong> members attending, and <strong>' . $outCount
            . '</strong> unable to attend so far</h4>';
        return $content;
    }

    /**
     * updating attendance for an event
     */
    public function update_attendance() {
        if(isset($_GET['member']) && isset($_GET['response'])) {
            $member     = $_GET['member'];
            $response   = $_GET['response'];
        } else {
            return;
        }

        if(empty($member) || empty($response)) { return; }

        $user = get_user_by('id', $member);
        $teamid = get_post_meta(get_the_ID(), 'iiteam_id', true);
        $teamname = InciteInvite_Team::get_teamname($teamid);

        if(!$user && get_user_meta($user->ID, 'iiteam_name', true) != $teamname
            && ( $response != 'in' || $response != 'out') ) { return; }

        global $errors;
        if(empty($errors))
            $errors = new WP_Error();

        $iievent = get_post();
        $responseUpdate = false;
        $responseAlreadyRecorded = false;
        $update = false;
        $current_attendance = get_post_meta($iievent->ID, 'iievent_attendance', true);
        if(is_array($current_attendance) && array_key_exists($member, $current_attendance)) {
            $responseUpdate = true;
            if($current_attendance[$member] == $response) {
                $responseAlreadyRecorded = true;
            }
        }

        if($responseUpdate && !$responseAlreadyRecorded ) {
            $old_attendance = $current_attendance;
            $current_attendance[$member] = $response;
            $update = update_post_meta($iievent->ID, 'iievent_attendance', $current_attendance, $old_attendance);
        } elseif($responseAlreadyRecorded) {
            $errors->add('iievent_response_success',
                _x('<strong>Success</strong>: Your response is still recorded. Thank you', 'inciteinvite'));
        } else {
            $current_attendance[$member] = $response;
            $update = update_post_meta($iievent->ID, 'iievent_attendance', $current_attendance);
        }

        if($update) {
            if($responseUpdate) {
                $errors->add('iievent_response_success',
                    _x('<strong>Success</strong>: Your response update has been recorded. Thank you', 'inciteinvite'));
            } else {
                $errors->add('iievent_response_success',
                    _x('<strong>Success</strong>: Your response has been recorded. Thank you', 'inciteinvite'));
            }
        } elseif(!$responseAlreadyRecorded)  {
            $errors->add('iievent_response_error',
                _x('<strong>Error</strong>: We were unable to record your response.', 'inciteinvite'));
        }

    }


    /**
     * Save an event, calls either save_new or update
     */
    public function save_event() {
        // don't want to interrupt the normal flow
        if( !'POST' == $_SERVER['REQUEST_METHOD'] ) { return; }

        // Not a big fan of robots.
        if( !empty($_POST['kill_most_robots']) ) { wp_die(); }

        // we only want the right form!
        if(!isset($_REQUEST['_wpnonce'])) { return; }

        $verified = wp_verify_nonce( $_REQUEST['_wpnonce'], 'cu_iievent');
        if(!$verified) { return; }

        $iievent_id             = $_POST['iievent_id'];
        $iiteam_id              = $_POST['iiteam_id'];
        $iievent_title          = $_POST['iievent_title'];
        $iievent_description    = $_POST['iievent_description'];
        $iievent_date           = $_POST['iievent_date'];
        $iievent_duration       = $_POST['iievent_duration'];

        global $errors;
        if( empty($errors) )
            $errors = new WP_Error();
        $this->verify_all_fields($iievent_title, $iievent_description, $iievent_date, $iievent_duration, $iiteam_id);

        $iievent_date = $this->translate_date($iiteam_id, $iievent_date);


        //if no errors, save
        if(!$errors->get_error_code()) {
            if( empty($iievent_id) ) {
                //saving a new event
                $this->save_new_event($iievent_title, $iievent_description, $iievent_date, $iievent_duration,
                    $iiteam_id);
            } else {
                //updating existing event
                $this->update_event($iievent_id, $iievent_title, $iievent_description, $iievent_date, $iievent_duration,
                    $iiteam_id);
            }
        }
    }

    public static function translate_date($iiteam_id, $iievent_date) {
        return new DateTime($iievent_date, new DateTimeZone(InciteInvite_Team::get_team_timezone($iiteam_id)));
    }

    /**
     *
     * @param $iievent_title
     * @param $iievent_description
     * @param $iievent_date
     * @param $iievent_duration
     */
    public function verify_all_fields($iievent_title, $iievent_description, $iievent_date, $iievent_duration,
                                      $iiteam_id) {
        global $errors;
        if( empty($errors) )
            $errors = new WP_Error();
        InciteInvite_Team::verify_team_id($errors, $iiteam_id);
        $this->verify_title($errors, $iievent_title);
        $this->verify_description($errors, $iievent_description);
        $this->verify_date($errors, $iievent_date, $iiteam_id);
        $this->verify_duration($errors, $iievent_duration);

    }

    private function verify_title(&$errors, $title) {
        if(strlen($title) > 60) {
            $errors->add('iievent_title_error',
                _x('<strong>Error</strong>: Titles must be under 60 characters.', 'inciteinvite'));
        } elseif( preg_match('/[^@! 0-9A-Za-z]/', $title ) ) {
            $errors->add('iievent_title_error',
                _x('<strong>Error</strong>: Titles can only contain letters, numbers, @ and ! symbols.',
                    'inciteinvite'));
        }
    }

    private function verify_description(&$errors, $description) {
        if( strlen($description) > 240 ) {
            $errors->add('iievent_description_error',
                _x('<strong>Error</strong>: Description must be under 240 characters.', 'inciteinvite'));
        }
    }

    private function verify_date(&$errors, $date, $team_id) {
        if(!$date instanceof DateTime) {
            $date = new DateTime($date, new DateTimeZone(InciteInvite_Team::get_team_timezone($team_id)));
        }

        if ( !$date ) {
            $errors->add('iievent_date_error',
                _x('<strong>Error</strong>: Could not interpret date.', 'inciteinvite'));
        }
    }

    private function verify_duration(&$errors, $duration) {
        if(!is_numeric($duration) || !is_int((int)$duration)) {
            $errors->add('iievent_duration_error',
                _x('<strong>Error</strong>: Duration must be a whole integer.', 'inciteinvite'));
        } elseif( $duration < 1 ) {
            $errors->add('iievent_duration_error',
                _x('<strong>Error</strong>: Duration must be greater than 1.', 'inciteinvite'));
        }
    }

    /**
     * Saving a new event.
     * @param $iievent_title
     * @param $iievent_description
     * @param $iievent_date
     * @param $iievent_duration
     * @return int|WP_Error
     */
    public function save_new_event($iievent_title, $iievent_description, $iievent_date, $iievent_duration, $iiteam_id) {

        //build the event
        $eventdata = array(
            'post_type'     => $this->type,
            'post_status'    => 'publish',
            'ping_status'    => 'closed',
            'comment_status' => 'closed',
            'post_author'    => get_current_user_id(),
            'post_title'    => $iievent_title,
            'post_content'  => $iievent_description,
        );

        $event = wp_insert_post($eventdata);

        global $errors;
        if( empty($errors) )
            $errors = new WP_Error();

        if(is_wp_error($event)) {
            //return the error
            $errors->add($event->get_error_code(), $event->get_error_message());
        } else {
            //update the post meta
            if($this->save_date_and_duration($event, $iievent_date, $iievent_duration) &&
                update_post_meta($event, 'iiteam_id', $iiteam_id)) {
                $this->schedule_invite_email($event);
                $errors->add('iievent_save_success',
                    _x("<strong>Success</strong>: Saved event information.", 'inciteinvite'));
            } else {
                //error with adding the event info
                $errors->add('iievent_meta_error',
                    _x("<strong>Error</strong>: Could not save date and duration.", 'inciteinvite'));
            }
        }
    }

    public function get_event_team_id($eventid) {
        return get_post_meta($eventid, 'iiteam_id', true);
    }

    /**
     * Shedule the event invitation to be sent out
     * @param $eventid int ID of the event Post
     */
    public function schedule_invite_email($eventid) {
        $date = get_post_meta($eventid, 'iievent_date', true);
        $team_id = $this->get_event_team_id($eventid);
        if(!$date instanceof DateTime) {
            $date = new DateTime($date, new DateTimeZone(InciteInvite_Team::get_team_timezone($team_id)));
        }
        // Invitation Premption Time
        $date->sub(new DateInterval('P3D'));

        wp_schedule_single_event($date->format('U'), 'sendinviteemail', array($team_id, $eventid));
    }

    public function update_event($iievent_id, $iievent_title, $iievent_description, $iievent_date, $iievent_duration,
                                 $iiteam_id) {

        global $errors;
        if( empty($errors) )
            $errors = new WP_Error();

        if(isset($_POST['remove']) && $_POST['remove'] == 'Remove') {
            if( wp_delete_post($iievent_id) ) {
                // notify users if event date hasn't passed
                $errors->add('iievent_delete_success',
                    _x("<strong>Success</strong>: Event removed.", 'inciteinvite'));
            } else {
                $errors->add('iievent_delete_error',
                    _x("<strong>Error</strong>: deleting event.", 'inciteinvite'));
            }

        } else {
//            $iievent_id, $iievent_title, $iievent_description, $iievent_date, $iievent_duration
            $eventdata = array(
                'ID'            => $iievent_id,
                'post_type'     => $this->type,
                'post_status'    => 'publish',
                'ping_status'    => 'closed',
                'comment_status' => 'closed',
                'post_author'    => get_current_user_id(),
                'post_title'    => $iievent_title,
                'post_content'  => $iievent_description,
            );
            $update = wp_update_post($eventdata);

            if(!$update) {
                $errors->add('iievent_update_error',
                    _x("<strong>Error</strong>: Unable to update event", 'inciteinvite'));
            } else {
                if(!$this->save_date_and_duration($iievent_id, $iievent_date, $iievent_duration) ) {
                    $errors->add('iievent_update_error',
                        _x("<strong>Error</strong>: Unable to update event date", 'inciteinvite'));
                } else {
                    $this->email_team_event_update($iievent_id, $iievent_title, $iievent_description,
                        $iievent_date, $iievent_duration, $iiteam_id);
                    $errors->add('iievent_update_success',
                        _x("<strong>Success</strong>: Event successfully updated and members notified", 'inciteinvite'));
                }
            }
        }
    }

    private function email_team_event_update($event_id, $iievent_title, $iievent_description, $iievent_date, $iievent_duration, $iiteam_id) {
        $team_name = InciteInvite_Team::get_teamname($iiteam_id);
        $bcc = InciteInvite_Team::get_all_members($team_name);

        foreach($bcc as $member) {
            $user_email = $member->get('user_email');
            $message = "An event you were previously invited to has been updated, please see the new details below";
            $message .= "\nEvent title: " . $iievent_title;
            $message .= "\nDescription: " . $iievent_description;
            $message .= "\nDate / Time: " . $iievent_date->format('l jS \of F Y h:i A');
            $message .= "\nDuration: " . $iievent_duration . "hrs";
            $message .= "\n if you're going to attend, please click this link: "
                . get_permalink($event_id) . '?member=' . $member->ID . '&response=in';
            $message .= "\n if you're unable to make it, please click this link: "
                . get_permalink($event_id) . '?member=' . $member->ID . '&response=out';
            wp_mail($user_email, "The event: " . $iievent_title . " was updated", $message);
        }
    }

    /**
     * Save the date and duration meta data for the iievent
     * @param $event_id             int         iievent id
     * @param $iievent_date         datetime    UNIX timestamp for date in UTC
     * @param $iievent_duration     int         hour duration of event
     * @return bool                 true if successful
     */
    private function save_date_and_duration($event_id, $iievent_date, $iievent_duration) {
        if( get_post_meta($event_id, 'iievent_date', true) ) {
            delete_post_meta($event_id, 'iievent_date');
        }
        if( get_post_meta($event_id, 'iievent_duration', true) ) {
            delete_post_meta($event_id, 'iievent_duration');
        }
        $date = update_post_meta($event_id, 'iievent_date', $iievent_date);
        $duration = update_post_meta($event_id, 'iievent_duration', $iievent_duration);

        if(isset($date) && isset($duration) && $date && $duration) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Getting all of the events pertaining to a team name.
     * @param $teamname String  iiteam_name
     * @return array    Array of relevant event info
     */
    public static function get_all_events($teamname) {

        $teamname = get_page_by_title($teamname, OBJECT, 'iiteam');

        $teamid = $teamname->ID;

        $events = array();
        $args = array(
            'post_type'     => 'iievent',
            'meta_key'      => 'iiteam_id',
            'meta_value'    => $teamid
        );
        $query = new WP_Query( $args );

        while ( $query->have_posts() ) {
            $query->the_post();
            $iievent_id = get_the_ID();
            $date = get_post_meta($iievent_id, 'iievent_date', true);

            $events[] = array(
                'iievent_id'            => $iievent_id,
                'iievent_title'         => get_the_title(),
                'iievent_description'   => get_the_content(),
                'iievent_date'          => $date->format("Y/m/j G:i"),
                'iievent_duration'      => get_post_meta($iievent_id, 'iievent_duration', true),
            );
        }
        wp_reset_postdata();
        return $events;
    }

    /**
     * Unregister the iiteam custom post type
     */
    public function unregister_type() {

        $post_type = $this->type;
        global $wp_post_types;

        if ( isset( $wp_post_types[ $post_type ] ) ) {
            unset( $wp_post_types[ $post_type ] );

            $slug = ( !$slug ) ? 'edit.php?post_type=' . $post_type : $slug;
            remove_menu_page( $slug );
        }
    }


    /**
     * Registering Teams as a custom post type
     * @return object|WP_Error
     */
    public function register_type() {

        $labels = array(
            'name'               => _x( 'Event', 'post type general name', 'inciteinvite' ),
            'singular_name'      => _x( 'Event', 'post type singular name', 'inciteinvite' ),
            'menu_name'          => _x( 'Events', 'admin menu', 'inciteinvite' ),
            'name_admin_bar'     => _x( 'Event', 'add new on admin bar', 'inciteinvite' ),
            'add_new'            => _x( 'Add New', 'event', 'inciteinvite' ),
            'add_new_item'       => __( 'Add New Event', 'inciteinvite' ),
            'new_item'           => __( 'New Event', 'inciteinvite' ),
            'edit_item'          => __( 'Edit Event', 'inciteinvite' ),
            'view_item'          => __( 'View Event', 'inciteinvite' ),
            'all_items'          => __( 'All Events', 'inciteinvite' ),
            'search_items'       => __( 'Search Events', 'inciteinvite' ),
            'parent_item_colon'  => __( 'Parent Events:', 'inciteinvite' ),
            'not_found'          => __( 'No events found.', 'inciteinvite' ),
            'not_found_in_trash' => __( 'No event found in Trash.', 'inciteinvite' )
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'event' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'register_meta_box_cb' => array($this, 'add_events_metaboxes'),
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail' )
        );
        return register_post_type('iievent', $args);
    }

    public function add_events_metaboxes() {
	    add_meta_box('wpt_events_date', 'Event Date', array($this, 'echo_event_date_form'));
	}

    public function echo_event_date_form() {
        echo "<input placeholder='eventdate' />";
    }
}

/**
 * cron jobs require non OO hooks :/
 */
function send_the_invite_email($team_id, $event_id) {
//    error_log("\n" . get_the_title($team_id), 3, '/var/tmp/wperror.log');
    $team_name = get_the_title($team_id);
    $bcc = InciteInvite_Team::get_all_members($team_name);
    $event = get_post($event_id);

    $iievent_date = get_post_meta($event_id, 'iievent_date', true);

    foreach($bcc as $member) {
        $user_email = $member->get('user_email');
        $message = "You've been invited to the event: " . $event->post_title;
        $message .= "\n" . $event->post_content;
        $message .= "\nThe event takes place: " . $iievent_date->format('l jS \of F Y h:i A');
        $message .= "\n and will last: " . get_post_meta($event_id, 'iievent_duration', true) . "hrs";
        $message .= "\n if you're going to attend, please click this link: "
            . get_permalink($event->ID) . '?member=' . $member->ID . '&response=in';
        $message .= "\n if you're unable to make it, please click this link: "
            . get_permalink($event->ID) . '?member=' . $member->ID . '&response=out';
        wp_mail($user_email, "You're invited to: " . $event->post_title, $message);
    }
}
add_action( 'sendinviteemail', 'send_the_invite_email', 10, 2 );
