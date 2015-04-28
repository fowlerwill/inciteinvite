<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    InciteInvite
 * @subpackage InciteInvite/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    InciteInvite
 * @subpackage InciteInvite/public
 * @author     Your Name <email@example.com>
 */
class InciteInvite_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $inciteinvite    The ID of this plugin.
	 */
	private $inciteinvite;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $inciteinvite       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $inciteinvite, $version ) {

		$this->inciteinvite = $inciteinvite;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in InciteInvite_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The InciteInvite_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->inciteinvite, plugin_dir_url( __FILE__ ) . 'css/inciteinvite-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'datetimepicker', plugin_dir_url( __FILE__ ) . 'css/jquery.datetimepicker.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in InciteInvite_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The InciteInvite_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
        wp_enqueue_script( 'datepicker', plugin_dir_url( __FILE__ ) . 'js/jquery.datetimepicker.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->inciteinvite, plugin_dir_url( __FILE__ ) . 'js/inciteinvite-public.js', array( 'jquery' ), $this->version, false );


	}

}
