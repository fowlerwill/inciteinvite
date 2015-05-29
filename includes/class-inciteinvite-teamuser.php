<?php
/**
 * Created by PhpStorm.
 * User: will
 * Date: 04/04/15
 * Time: 2:37 PM
 */

class InciteInvite_TeamUser {

    /**
     * Role name stub
     */
    public $role = '';

    public function __construct() {
        $this->overwrite_registration_email();
    }

    /**
     * For sneaky users trying to access other pages besides their own.
     * redirect them to the home page.
     */
    public function redirect_if_not_their_team() {

        //if it's not an iiteam, we don't care.
        if(get_post_type() != 'iiteam') {
            return;
        }

        // if the user is not logged in, or they are trying to access a team page that is
        // not theirs, send them back to the front page with an error.
        $userteams = $this->get_users_team();
        if(!is_array($userteams)) { $userteams = array($userteams); }
        $title = get_the_title(get_the_ID());
        //TODO: redirect to their team page instead.
        if(!is_user_logged_in() || !in_array($title, $userteams) ) {
//            wp_die('jaccuse!', var_dump($title), var_dump($userteams));
            wp_redirect(home_url());
        }

    }

    /**
     * Add the Login/Logout & Register buttons to the navigation
     * @param $items
     * @param $args
     * @return string
     */
    public function add_login_button($items, $args) {

        if ($args->theme_location == 'main_nav') {
            // give logout button
            if( is_user_logged_in() ) {
                $items .= '<li class="menu-item"><a href="' . wp_logout_url( site_url() ) . '">Logout</a></li>';
            } else {
                $loginandregpage = get_page_by_title('register');
                $items .= '<li class="menu-item"><a href="' . get_permalink($loginandregpage->ID) . '">Login/Register</a></li>';
            }
        }
        return $items;
    }

    private function overwrite_registration_email() {
        // Redefine user notification function
        if ( !function_exists('wp_new_user_notification') ) {
            function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {
                $user = new WP_User($user_id);

                // Send administrator email
                $user_login = stripslashes($user->user_login);
                $user_email = stripslashes($user->user_email);

                $message  = sprintf(__('New user registration on your blog %s:'), get_option('blogname')) . "\r\n\r\n";
                $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
                $message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";

                @wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), get_option('blogname')), $message);

                if ( empty($plaintext_pass) )
                    return;

                // send registrant email
                $message  = __('Hi there,') . "\r\n\r\n";
                $message .= sprintf(__("Welcome to %s! Here's how to log in:"), get_option('blogname')) . "\r\n\r\n";
                $message .= wp_login_url() . "\r\n";
                $message .= sprintf(__('Username: %s'), $user_login) . "\r\n";
                $message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n\r\n";
                $message .= sprintf(__('If you have any problems, please contact me at %s.'), get_option('admin_email')) . "\r\n\r\n";
                $message .= __('Adios!');

                wp_mail($user_email, sprintf(__('[%s] Your username and password'), get_option('blogname')), $message);

            }
        }
    }

    /**
     * Add the team field to the registration fields for the default
     * register form
     */
    public function register_fields() {
        //add the field
        $iiteam_name = (!empty($_POST['iiteam_name'])) ? trim($_POST['iiteam_name']) : '';

        ?>
        <p>
            <label for="iiteam_name"><?php _e('Team Name', 'inciteinvite') ?><br/>
                <input type="text" name="iiteam_name" id="iiteam_name" class="input"
                       value="<?php echo esc_attr(wp_unslash($iiteam_name)); ?>" size="25"/></label>
        </p>
    <?php
    }

    /**
     * Delete the current user
     * Cannot be tested programmatically
     * TODO: Make user settings page with button that links to $_SERVER['HTTP_REFERER'] . '?DelAcct=yes'
     */
//    public function remove_logged_in_user() {
//        if(is_user_logged_in() && $_GET['DelAcct'] == 'yes') {
//            require_once(ABSPATH.'wp-admin/includes/user.php' );
//            $current_user = wp_get_current_user();
//            return wp_delete_user( $current_user->ID );
//        }
//    }

    /**
     * Set the users that are registering as a iiteam_manager by default
     * @param $default_role
     * @return string
     */
    public function set_user_default_role($default_role) {
        return 'iiteam_manager';
    }

    /**
     * Function to run against the teamname field when registering through the default
     * user registration
     *
     * @param $errors
     * @param $sanitized_user_login
     * @param $user_email
     * @return mixed
     */
    public function validate_team_field($errors, $sanitized_user_login, $user_email) {
        InciteInvite_Team::validate_team_name($errors, $_POST['iiteam_name']);

        if(InciteInvite_Team::team_exists($_POST['iiteam_name']) ) {
            $errors->add('iiteam_name_warning',
                __('<strong>Sorry</strong>: Team already exists.', 'inciteinvite'));
        }
        return $errors;
    }

    /**
     * Validates the username, email fields for new user registration
     *
     * @param $errors
     * @param $sanitized_user_login
     * @param $user_email
     * @return mixed
     */
    public function validate_fields($errors, $username, $user_email) {

        $this->validate_user_login($errors, $username);

        $this->validate_team_field($errors, $username, $user_email);

        $this->validate_user_email($errors, $user_email);

        if( username_exists($username) ) {
            $errors->add('iiregister_warning',
                __('<strong>Sorry</strong>: Username already exists', 'inciteinvite'));
        } elseif( email_exists($user_email) ) {
            $errors->add('iiregister_warning',
                __('<strong>Error</strong>: That email has already been registered.', 'inciteinvite'));
        }
        return $errors;
    }

    /**
     * Validates the user email against emptiness and wp is email
     *
     * @param $errors
     * @param $user_email
     */
    public function validate_user_email(&$errors, $user_email) {
        if( empty($user_email) ) {
            $errors->add('iiregister_error',
                __('<strong>Error</strong>: Missing email.', 'inciteinvite'));
        } elseif( !is_email($user_email)  ) {
            $errors->add('iiregister_error',
                __('<strong>Error</strong>: That email appears invalid.', 'inciteinvite'));
        }
    }

    /**
     * validate the username for emptiness and wp validity.
     * NOT if exists.
     * @param $errors
     * @param $username
     */
    public function validate_user_login(&$errors, $username) {
        if ( empty( $username ) ) {
            $errors->add('iiregister_error',
                __('<strong>Error</strong>: Missing username.', 'inciteinvite'));
        } elseif( !validate_username($username) ) {
            $errors->add('iiregister_error',
                __('<strong>Error</strong>: Invalid username.', 'inciteinvite'));
        }
        return $errors;
    }

    /**
     * Saves the submitted iiteam_name, in case they're using the default registration page
     *
     * @param $user_id int user id
     */
    public function save_user_meta( $user_id ) {
        global $errors;
        if(empty($errors))
            $errors = new WP_Error();

        $teamname = $_POST['iiteam_name'];

        if( !InciteInvite_Team::team_exists($teamname) ) {
            $teamPage = InciteInvite_Team::createTeamPage($user_id, $teamname);
        }

        $this->save_user_team($user_id, $teamname);
    }

    /**
     * Save the users team name
     * @param $teamname string team name
     * @return int|bool false if fail
     */
    public function save_user_team($user_id, $teamname) {
        if ( isset( $teamname ) ) {
            return update_user_meta($user_id, 'iiteam_name', $teamname);
        } else {
            return false;
        }
    }

    public function check_create_or_update_forms() {
        // don't want to interrupt the normal flow
        if( !'POST' == $_SERVER['REQUEST_METHOD'] ) { return; }

        // Not a big fan of robots.
        if( !empty($_POST['kill_most_robots']) ) { wp_die(); }

        // we only want the right form!

//        wp_die(wp_verify_nonce( $_REQUEST['_wpnonce'], 'cu_'.$this->role));
        if(!isset($_REQUEST['_wpnonce'])) { return; }
        return wp_verify_nonce( $_REQUEST['_wpnonce'], 'cu_'.$this->role);
    }

    public function render_login_form() {
        if( is_user_logged_in() ) {
            return '';
        }
        return wp_login_form( array( 'echo' => false ) );
    }

    /**
     * Keep the baddies out of the admin dashboard.
     */
    public function redirect_admin_dashboard() {
        if( !current_user_can('edit_others_pages') ) {
            wp_redirect( site_url() );
            exit;
        }
    }

    /**
     * Turn off the admin bar for all except the administrator.
     */
    public function disable_admin_bar() {
        if( !current_user_can('edit_others_pages') ) {
            add_filter('show_admin_bar', '__return_false');
        }
    }

    /**
     * Save the user meta if creating a team page is successful
     * @param $user_id
     * @return bool|ID|int|WP_Error
     */
    public function register_new_user($user_id) {

        // don't want to interrupt the normal flow
        if( !'POST' == $_SERVER['REQUEST_METHOD'] ) { return; }

        // Not a big fan of robots.
        if( !empty($_POST['kill_most_robots']) ) { header('location: http://oprah.com/'); wp_die(); }

        // only looking for the right form.
        if(isset($_POST['user_email'])) {
             $user_email = $_POST['user_email'];
        } else {
            return;
        }

        // we only want the right form!
        if( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'register_new_iiteam_manager') ) {
            if( isset($_POST['user_email']) ) {
                wp_nonce_ays(home_url());
            } else {
                return;
            }
        }

        global $errors;
        if( empty($errors) )
            $errors = new WP_Error();

        $username   = $_POST['user_login'];
        $teamname   = $_POST['iiteam_name'];

        $errors = $this->validate_fields($errors, $username, $user_email);

        if( InciteInvite_Team::team_exists($_POST['iiteam_name']) ) {
            $errors->add('iiteam_name_warning',
                __('<strong>Sorry</strong>: Team already exists.', 'inciteinvite'));
        }

        if( !$errors->get_error_code() ) {
            $userdata = array(
                'user_login'    => stripslashes($username),
                'user_email'    => stripslashes($user_email),
                'user_pass'     => wp_generate_password()
            );
            $user_id = wp_insert_user($userdata);

            if( is_wp_error($user_id) ) {
                $errors->add($user_id);
                return $errors;
            } else {
                $errors->add('iiregister_success',
                    __('<strong>Success</strong>: Please check your email for your password and login instructions!.', 'inciteinvite'));
                wp_new_user_notification($user_id, $userdata['user_pass']);
            }

//            $teamPage = InciteInvite_Team::createTeamPage($user_id, $teamname);

            if( ! is_wp_error($teamPage)) {
                return $this->save_user_meta($user_id);
            } else {
                $errors->add($teamPage);
                return $errors;
            }

        } else {
            return $errors;
        }
    }

    /**
     * Get the current User's team name.
     * @return mixed
     */
    public function get_users_team() {
        return get_user_meta(get_current_user_id(), 'iiteam_name', true);
    }

    /**
     * Alters the team page contents to suit the user role.
     * @param $content -
     * @return string
     */
    public function render_team_page($content) {
        if(get_post_type() == 'iiteam' && is_user_logged_in()) {
            $theUser = wp_get_current_user();
            if(in_array('iiteam_manager', $theUser->roles)) {
                $manager = new InciteInvite_TeamManager();
                return $manager->render_edit_team($content);
            } elseif(in_array('iiteam_member', $theUser->roles)) {
                return $content . ' <br>Member functions coming soon!';
            } else {
                return $content;
            }
        } else {
            return $content;
        }

    }
}