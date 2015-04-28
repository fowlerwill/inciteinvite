<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://inciteinvite.com
 * @since             1.0.0
 * @package           InciteInvite
 *
 * @wordpress-plugin
 * Plugin Name:       Incite Invite
 * Plugin URI:        http://inciteinvite.com
 * Description:       A team management plugin based like RosterBot or TeamSnap
 * Version:           1.0.0
 * Author:            Will Fowler - Incite Social Promotions
 * Author URI:        http://incitepromo.com.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       inciteinvite
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-inciteinvite-activator.php
 */
function activate_inciteinvite() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-inciteinvite-activator.php';
	return InciteInvite_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-inciteinvite-deactivator.php
 */
function deactivate_inciteinvite() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-inciteinvite-deactivator.php';
	return InciteInvite_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_inciteinvite' );
register_deactivation_hook( __FILE__, 'deactivate_inciteinvite' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-inciteinvite.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_inciteinvite() {

	$plugin = new InciteInvite();

	// Only create an instance of the plugin if it doesn't already exists in GLOBALS
	if( ! array_key_exists( 'inciteinvite', $GLOBALS ) ) {
		// Store a reference to the plugin in GLOBALS so that our unit tests can access it
		$GLOBALS['inciteinvite'] = $plugin;
	}

	$plugin->run();
}
run_inciteinvite();
