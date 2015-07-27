<?php
/**
 * Created by PhpStorm.
 * User: will
 * Date: 04/04/15
 * Time: 2:39 PM
 */

class InciteInvite_TeamManager extends InciteInvite_TeamUser {

    /**
     * Role name
     */
    public $role = 'iiteam_manager';

    public function check_register_new_manager() {
        if(!self::check_create_or_update_forms()) {
            return;
        }

        global $errors;
        if( empty($errors) )
            $errors = new WP_Error();

        $user_email = $_POST['user_email'];
        $user_login = $_POST['user_login'];
        $teamname   = $_POST['iiteam_name'];
        if(isset($_POST['iiteam_member_id']))
            $user_id = $_POST['iiteam_member_id'];

        //validate them fields
        self::validate_user_email($errors, $user_email);
        self::validate_user_login($errors, $user_login);


        //now we need to tell the difference between updating a manager and
        // registering one
        if( empty($user_id) ) {
//            $errors = $this->validate_fields($errors, $user_login, $user_email);

            if( InciteInvite_Team::team_exists($_POST['iiteam_name']) ) {
                $errors->add('iiteam_name_warning',
                    __('<strong>Sorry</strong>: Team already exists.', 'inciteinvite'));
            }
        }

        if( !$errors->get_error_code() ) {
            $userdata = array(
                'user_login'    => stripslashes($user_login),
                'user_email'    => stripslashes($user_email),
                'user_pass'     => wp_generate_password()
            );
            $user_id = wp_insert_user($userdata);

            if( is_wp_error($user_id) ) {
                $errors->add( $user_id->get_error_code(), $user_id->get_error_message() );
                return $errors;
            } else {
                $this->save_user_meta($user_id);
                $errors->add('iiregister_success',
                    __('<strong>Success</strong>: Please check your email for your password and login instructions!.', 'inciteinvite'));
                wp_new_user_notification($user_id, $userdata['user_pass']);
            }

//            $teamPage = InciteInvite_Team::createTeamPage($user_id, $teamname);

//            if( ! is_wp_error($teamPage)) {
//                $this->save_user_meta($user_id);
//            } else {
//                $errors->add($teamPage);
//                return $errors;
//            }

        } else {
            return $errors;
        }


    }

    /**
     * Redirect user after successful login.
     *
     * @param string $redirect_to URL to redirect to.
     * @param string $request URL the user is coming from.
     * @param object $user Logged user's data.
     * @return string
     */
    public static function iiteammanager_login_redirect( $redirect_to, $request, $user ) {
        //is there a user to check?
        global $user;
        if ( isset( $user->roles ) && is_array( $user->roles ) ) {
            //check for admins
            if ( in_array( 'administrator', $user->roles ) ) {
                // redirect them to the default place
                return $redirect_to;
            } elseif ( in_array( 'iiteam_manager', $user->roles ) ) {
                $page = get_page_by_title(get_user_meta($user->ID, 'iiteam_name', true), OBJECT, 'iiteam');
                if(!is_null($page)) {
                    return get_page_uri($page);
                } else {
                    return home_url();
                }

            } else {
                return home_url();
            }
        } else {
            return $redirect_to;
        }
    }

    /**
     * Redirect After Successful Registration
     *
     * Sends the user to the home page after they sign up
     *
     * @return string|void  The home page URL
     */
    public static function iiteammanager_register_redirect() {
        return home_url('?checkemail=registered');
    }

    /**
     * Include the register view
     */
    private static function echo_registration_form() {
        include plugin_dir_path( __FILE__ ) . '../views/view-inciteinvite-teammanager-register.php';
    }

    /**
     * Function that is called by the add_shortcode to return the contents of the register view.
     * @return string   HTML    registration form
     */
    public static function render_registration_form() {
        ob_start();
        InciteInvite_TeamManager::echo_registration_form();
        $output = ob_get_clean();
        return $output;

    }

    /**
     * The edit team form
     */
    private function echo_edit_team_form() {
        include_once plugin_dir_path( __FILE__ ) . '../views/view-inciteinvite-teammanager-team-edit.php';
    }

    /**
     * The add/edit team member form
     */
    private function echo_edit_member_form($iiteam_member_id = '', $iiteammember_display_name = '', $iiteammember_user_login = '', $iiteammember_user_email = '') {
        include plugin_dir_path( __FILE__ ) . '../views/view-inciteinvite-teammanager-member-edit.php';
    }

    /**
     * Collects and returns the edit form
     * TODO: Check permissions earlier up the call stack.
     */
    private function build_edit_team_form() {
//        if( current_user_can('edit_published_posts')) {}
        ob_start();
        printf("<h3>%s</h3>", __("Team Details", 'inciteinvite'));
        $this->echo_edit_team_form();
        return ob_get_clean();

    }

    /**
     * Collect all of the players on a team, and output their edit forms
     * TODO: Check permissions earlier up the call stack.
     */
    private function collect_and_echo_member_forms() {

        // make sure they've got the right permissions
//        if( current_user_can('list_users') ||
//            current_user_can('remove_users') ||
//            current_user_can('create_users') ||
//            current_user_can('delete_users') ) {}
            // print one line for a new team member
        ob_start();
        printf("<h3>%s</h3>", __("Team Members", 'inciteinvite'));
        printf("<h4>%s</h4>", __("Add a new team member", 'inciteinvite'));
        $this->echo_edit_member_form();

        printf("<hr><h4>%s</h4>", _x("Edit existing team members", 'inciteinvite'));
        // loop through the members and build their forms them.
        $members = InciteInvite_Team::get_all_members($this->get_users_team());
//        wp_die(var_dump($members));
        if( !empty($members) ) {
            foreach($members as $member) {
                $this->echo_edit_member_form($member->ID, $member->display_name, $member->user_login, $member->user_email);
            }
        }

        return ob_get_clean();

    }

    private function echo_edit_event_form($iievent_id = '', $iievent_title = '', $iievent_description = '',
                                          $iievent_date = '', $iievent_duration = '') {
        include plugin_dir_path( __FILE__ ) . '../views/view-inciteinvite-teammanager-event-edit.php';
    }

    /**
     * Collect and echo the events in chronological order for the given month
     * @param DateTime $date - the date to start from.
     * @return string
     */
    private function collect_and_echo_event_forms($date) {
        ob_start();
//        printf("<h3>%s</h3>", _x("Team Events", 'inciteinvite'));
//        printf("<h4>%s</h4>", _x("Add a new event", 'inciteinvite'));
        $this->echo_edit_event_form();
        $newEvent = ob_get_contents();
        ob_end_clean();

        ob_start();
        $events = InciteInvite_Event::get_all_events($this->get_users_team());


        //loop through the given month
        $date->modify($date->format('Y-m') . '-01');

        $nextMonth = new DateTime( $date->format('Y-m-t'), $date->getTimezone() );
        $nextMonth->add( new DateInterval('P1D') );

        $prevMonth = new DateTime( $date->format('Y-m-d'), $date->getTimezone() );
        $prevMonth->sub( new DateInterval('P1M') );

        echo '<div class="row nopadding">';

        echo '<div class="page-header">
                <div class="col-xs-8">
                  <h1>Team Events for ' . $date->format('F, Y') . '</h1>
                </div>
                <div class="col-xs-4">
                    <nav>
                        <ul class="pager">
                            <li><a href="' . get_the_permalink() . '?mo='
            . $prevMonth->format('Ymd') . '">Last month</a></li>
                            <li><a href="' . get_the_permalink() . '?mo='
            . $nextMonth->format('Ymd') . '">Next month</a></li>
                        </ul>
                    </nav>
                    ' . $newEvent . '
                </div>
              </div>';

        $date->sub( new DateInterval('P' . $date->format('N') . 'D'));              // sub days so we start at beginning of week

        while ($date <= $nextMonth) {

            $eventsThisDay = array();
            if( !empty($events) ) {
                foreach($events as $i => $event) {
                    $tomorrow = new DateTime( $date->format('Y-m-d'), $date->getTimezone() );
                    $tomorrow->add( new DateInterval('P1D') );
                    if( $event['iievent_date'] > $date && $event['iievent_date'] < $tomorrow ) {
                        $eventsThisDay[] = $event;
                        unset( $events[$i] );
                    }
                }
            }
            $hidden = (count($eventsThisDay) == 0 ) ? "hidden-xs" : "";

            echo '<div class="col-md-cust nopadding ' . $hidden .'">
                        <div class="panel panel-default iievent_day" data-date="' . $date->format('Y/m/d ') . '12:00">
                        <div class="panel-heading">
                            <h3 class="panel-title text-right">' . $date->format('d') . '</h3>
                        </div>
                        <div class="panel-body">
                            ';

            foreach( $eventsThisDay as $event ) {
                $this->echo_edit_event_form($event['iievent_id'], $event['iievent_title'],
                    $event['iievent_description'], $event['iievent_date']->format("Y/m/j G:i"), $event['iievent_duration']);
            }

            echo '
                        </div>
                    </div>
                </div>';
            if( $date->format('N') == 6 ) {
                echo '</div><div class="row nopadding">';
            }
            $date->add( new DateInterval('P1D') );
        }
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Build the whole team view page for the manager.
     * @param $content  string|html page content.
     * @return string   string|html updated page content.
     */
    public function render_edit_team($content, $date) {

        ob_start();
        include plugin_dir_path( __FILE__ ) . '../views/view-inciteinvite-teammanager-dashboard.php';
        $content .= ob_get_contents();
        ob_end_clean();

        return $content;
    }
}