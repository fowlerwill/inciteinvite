<?php
/**
 * User: will
 * Date: 30/03/15
 * Time: 11:39 PM
 */

class InciteInvite_Team {

    /**
     * Add the My Team button to the navigation
     * @param $items
     * @param $args
     * @return string
     */
    public function add_team_button($items, $args) {

        $userid = get_current_user_id();
        $teamname = get_user_meta($userid, 'iiteam_name', true);
        if($teamname) {
            $teampage = get_page_by_title($teamname, OBJECT, 'iiteam');

            if ($args->theme_location == 'main_nav') {
                $items .= '<li class="menu-item"><a href="' . get_permalink($teampage->ID) . '">My Team</a></li>';
            }
        }
	    return $items;
    }

    /**
     * Function to update the team when the team manager submits the team-edit page.
     */
    public function update_team($user_id = '') {

        (empty($user_id)) ? $user_id = get_current_user_id() : $user_id;


        // don't want to interrupt the normal flow
        if( !'POST' == $_SERVER['REQUEST_METHOD'] ) {
            return;
        }

        // only looking for the right form.
        if(isset($_POST['teamid'])) {
            $team_id = $_POST['teamid'];
        } else {
            return;
        }

        // we only want the right form!
        if( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'edit_iiteam_'.$team_id) ) {
            if( isset($_POST['iiteam_name']) ) {
                wp_nonce_ays(home_url());
            } else {
                return;
            }
        }

        global $errors;
        if( empty($errors) )
            $errors = new WP_Error();

        $team_name  = $_POST['iiteam_name'];
        InciteInvite_Team::validate_team_name($errors, $team_name);
        $team_desc  = $_POST['iiteam_description'];
        $timezone   = $_POST['iiteam_timezone'];
        InciteInvite_Team::validate_team_timezone($errors, $timezone);


        if( !$errors->get_error_code() ) {
            $team_post_updated = array(
                "ID"            => $team_id,
                "post_title"    => $team_name,
                "post_content"  => $team_desc
            );

            $team_id = wp_update_post($team_post_updated);

            if(!in_array($team_name, get_user_meta($user_id, 'iiteam_name'))) {
                update_user_meta(get_current_user_id(), 'iiteam_name', $team_name);
            }

            if ( isset( $timezone ) ) {
                update_post_meta( $team_id, 'iitimezone', sanitize_text_field( $timezone ) );
            }

            global $errors;
            if( empty($errors) )
                $errors = new WP_Error();

            $errors->add('iiteam_update_success',
                __('<strong>Success</strong>: Team Updated', 'inciteinvite'));

        }
    }

    /**
     * Is the timezone a valid one?
     * @param $tz Timezone
     * @return bool
     */
    public static function validate_team_timezone(&$errors, $tz) {
        if(! in_array($tz, DateTimeZone::listIdentifiers(DateTimeZone::ALL)) ) {
            $errors->add('iiteam_timezone_error',
                __('<strong>Error</strong>: Invalid Timezone.', 'inciteinvite'));
        }
        return $errors;
    }

    public static function get_team_timezone($team_id) {
        return get_post_meta($team_id, 'iitimezone', true);
    }

    /**
     * Creates a new WP_Post of type 'iiteam'
     *
     * @param $user_id      int     User id from WP
     * @param $teamName     String  The Team name given during registration ("iiteam_name")
     * @return ID | WP_Error        The post id if successful, a WP error if not.
     */
    public static function createTeamPage($user_id, $teamName) {
        $postdata = array(
            'post_content'   => '', // Eventually will be team description
            'post_name'      => $teamName,
            'post_title'     => $teamName,
            'post_status'    => 'publish',
            'post_type'      => 'iiteam',
            'post_author'    => $user_id,
            'ping_status'    => 'closed',
            'comment_status' => 'closed'
        );
        $post = wp_insert_post($postdata, true);
        if( !is_wp_error($post) ) {
            update_post_meta( $post, 'iitimezone', 'America/Edmonton');
            return $post;
        } else {
            return $post;
        }
    }



    /**
     * Validate the team name.
     * under 100 chars, and can't contain stupid symbols.
     * @param $errors WP_Error obj
     * @param $name String  The desired team name.
     * @return WP_Error
     */
    public static function validate_team_name(&$errors, $name) {
        if (empty($name) ||
            !empty($name) &&
            trim($name) == ''
        ) {
            $errors->add('iiteam_name_error',
                __('<strong>Error</strong>: You must include a team name.'));
        } if( strlen($_POST['iiteam_name']) >= 100) {
            $errors->add('iiteam_name_warning',
                __('<strong>Sorry</strong>: Team Name must be under 100 characters.', 'inciteinvite'));
        } elseif( preg_match('/[^-_@.!$# 0-9A-Za-z]/',$_POST['iiteam_name']) ) {
            $errors->add('iiteam_name_warning',
                __('<strong>Sorry</strong>: Team Name can only contain these symbols -_@.!&$#', 'inciteinvite'));
        }
        return $errors;
    }

    /**
     * Returns whether a team exists yet or not
     * TODO: This works by seeing if anyone's got this in their user meta... could have problems for future Will
     *
     * @param $teamName - the value expected in iiteam_name
     * @return bool - true if team exists
     */
    public static function team_exists($teamName) {
        $args = array(
            'meta_key'      => 'iiteam_name',
            'meta_value'    => $teamName,
            'number'        => 1
        );
        $user_query = new WP_User_Query( $args );
        return count($user_query->results) > 0;
    }

    /**
     * Get the teamname from the post id
     * @param $post_id
     * @return string
     */
    public static function get_teamname($post_id) {
        $teampost = get_post($post_id);
        $teamname = $teampost->post_title;
        return $teamname;
    }

    /**
     * Determine if a team exists based on ID
     * @param $errors
     * @param $teamid
     * @return bool|WP_Post
     */
    public static function verify_team_id(&$errors, $teamid) {
        $team = get_post($teamid);
        if( $team->post_type == 'iiteam' ) {
            return $team;
        } else {
            return false;
        }
    }

    /**
     * Return array of all members, including manager
     */
    public static function get_all_members($teamName) {
        $args = array(
            'meta_key'      => 'iiteam_name',
            'meta_value'    => $teamName,
        );
        $user_query = new WP_User_Query( $args );
        return $user_query->results;
    }


    /**
     * Unregister the iiteam custom post type
     */
    public function unregister_type() {

        $post_type = 'iiteam';
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
            'name'               => _x( 'Teams', 'post type general name', 'inciteinvite' ),
            'singular_name'      => _x( 'Team', 'post type singular name', 'inciteinvite' ),
            'menu_name'          => _x( 'Teams', 'admin menu', 'inciteinvite' ),
            'name_admin_bar'     => _x( 'Team', 'add new on admin bar', 'inciteinvite' ),
            'add_new'            => _x( 'Add New', 'team', 'inciteinvite' ),
            'add_new_item'       => __( 'Add New Team', 'inciteinvite' ),
            'new_item'           => __( 'New Team', 'inciteinvite' ),
            'edit_item'          => __( 'Edit Team', 'inciteinvite' ),
            'view_item'          => __( 'View Team', 'inciteinvite' ),
            'all_items'          => __( 'All Teams', 'inciteinvite' ),
            'search_items'       => __( 'Search Teams', 'inciteinvite' ),
            'parent_item_colon'  => __( 'Parent Teams:', 'inciteinvite' ),
            'not_found'          => __( 'No teams found.', 'inciteinvite' ),
            'not_found_in_trash' => __( 'No team found in Trash.', 'inciteinvite' )
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'team' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail' )
        );


        return register_post_type('iiteam', $args);
    }
}