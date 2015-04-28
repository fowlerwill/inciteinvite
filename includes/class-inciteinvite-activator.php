<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    InciteInvite
 * @subpackage InciteInvite/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    InciteInvite
 * @subpackage InciteInvite/includes
 * @author     Your Name <email@example.com>
 */
class InciteInvite_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if( self::addManagerRole() instanceof WP_Role
			&& self::addMemberRole() instanceof WP_Role ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Adding the manager role
	 * @returns WP_Role object on success, null if that role already exists.
	 */
	private static function addManagerRole() {
		// add the team manager and team member roles
		return add_role(
			'iiteam_manager',
			__('Team Manager'),
			array(
				'read' => true,
				'edit_posts' => true,
				'edit_published_posts' => true,
				'publish_posts' => true,
				'delete_posts' => true,
				'delete_published_posts' => true,
				'upload_files'	=> true,
                'list_users'    => true,
                'remove_users'  => true,
                'create_users'  => true,
                'delete_users'  => true,
            ));
	}

	/**
	 * Adding the member role
	 * @returns WP_Role object on success, null if that role already exists.
	 */
	private static function addMemberRole() {
		return add_role(
			'iiteam_member',
			__('Team Member'),
			array(
				'read' => true,
            ));
	}

}
