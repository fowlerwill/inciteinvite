<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    InciteInvite
 * @subpackage InciteInvite/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    InciteInvite
 * @subpackage InciteInvite/includes
 * @author     Your Name <email@example.com>
 */
class InciteInvite_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		self::removeManagerRole();
		self::removeMemberRole();
		return is_null(get_role('iiteam_manager')) && is_null(get_role('iiteam_member'));
	}

	/**
	 * Removes the manager role
	 */
	private static function removeManagerRole() {
		remove_role('iiteam_manager');
	}

	/**
	 * Removes the member role
	 */
	private static function removeMemberRole() {
		remove_role('iiteam_member');
	}
}
