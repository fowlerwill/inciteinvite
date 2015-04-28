<?php
/**
 * Created by PhpStorm.
 * User: will
 * Date: 04/04/15
 * Time: 2:39 PM
 */

class InciteInvite_TeamMember extends InciteInvite_TeamUser {
    /**
     * Role name
     */
    public $role = 'iiteam_member';

    /**
     * Registering a new member.
     *
     * @return none
     */
    public function check_register_new_member() {
        if(!parent::check_create_or_update_forms()) {
            return;
        }

        if(!isset($_POST['user_login']) || !isset($_POST['user_email']) || !isset($_POST['iiteam_id']) )
            return;

        $user_id    = $_POST['iiteam_member_id'];
        $user_login = $_POST['user_login'];
        if( isset($_POST['display_name']) )
            $display_name = $_POST['display_name'];
        else
            $display_name = '';
            $user_email = $_POST['user_email'];
        $user_team_id  = $_POST['iiteam_id'];

        global $errors;
        if(empty($errors))
            $errors = new WP_Error();

        $user_team = get_the_title($user_team_id);

        if(empty($user_team)) {
            $errors->add('iiteam_error',
                _x("<strong>Error:</strong>: Couldn't find the team name", 'inciteinvite'));
        } else {
            $_POST['iiteam_name'] = $user_team;
        }

//        $this->validate_fields($errors, $user_login, $user_email);

        //now, we have two paths, if they're new, create them. if they're updating, update
        if(! $errors->get_error_code() ) {
            $user_login = sanitize_user($user_login);
            $user_email = sanitize_email($user_email);
            $userdata = array(
                'ID'          =>  $user_id,
                'user_login'  =>  $user_login,
                'user_email'  =>  $user_email,
                'display_name'=>  $display_name,
                'user_pass'   => wp_generate_password()
            );
            $newuser = false;

            if(empty($user_id)) {
                $userdata['role'] = $this->role;
                $newuser = true;
            }

            if($newuser) {
                $this->validate_user_email($errors, $user_email);
                $this->validate_user_login($errors, $user_login);
                if(!$errors->get_error_code()) {
                    $user_id = wp_insert_user($userdata);
                    wp_new_user_notification($user_id, $userdata['user_pass']);
                }
            } else {
                // TODO: shouldn't be able to delete teammanagers
                if( isset($_POST['remove']) ){
                    if(user_can($user_id, 'create_users')) {
                        $errors->add('iiteam_manager_error',
                            _x("<strong>Error</strong>: Cannot remove a manager", 'inciteinvite'));
                    } else {
                        require_once(ABSPATH.'wp-admin/includes/user.php' );
                        if( wp_delete_user($user_id, get_current_user_id()) ) {
                            $errors->add('iiteam_member_success',
                                _x("<strong>Success</strong>: Removed user", 'inciteinvite'));
                        } else {
                            $errors->add('iiteam_manager_error',
                                _x("<strong>Error</strong>: Could not remove user", 'inciteinvite'));
                        }
                    }

                } else {
                    $user_id = wp_update_user($userdata);
                }
            }

            if(is_wp_error($user_id)) {
                $errors->add($user_id->get_error_code(), $user_id->get_error_message());
            } else {

                if(!$this->save_user_team($user_id, $user_team)) {
                    //Successful create/update!
                    $errors->add('iiteam_member_success',
                        _x("<strong>Success</strong>: Team members updated", 'inciteinvite'));
                } else {
                    //remove the created user since the meta wasn't saved.
                    if($newuser)
                        wp_delete_user($user_id);
                }
            }
        } else {
            return $errors;
        }

    }
}