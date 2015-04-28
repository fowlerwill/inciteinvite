<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    InciteInvite
 * @subpackage InciteInvite/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    InciteInvite
 * @subpackage InciteInvite/includes
 * @author     Your Name <email@example.com>
 */
class InciteInvite {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      InciteInvite_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $inciteinvite    The string used to uniquely identify this plugin.
	 */
	protected $inciteinvite;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The Team object we'll be using.
	 * @since	1.0.0
	 * @access	protected
	 * @var		object	$team 	an instance of inciteinvite_Team
	 */
	protected  $team;

    /**
	 * The Event object we'll be using.
	 * @since	1.0.0
	 * @access	protected
	 * @var		object	$team 	an instance of inciteinvite_Event
	 */
	protected  $event;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->inciteinvite = 'inciteinvite';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - InciteInvite_Loader. Orchestrates the hooks of the plugin.
	 * - InciteInvite_i18n. Defines internationalization functionality.
	 * - InciteInvite_Admin. Defines all hooks for the admin area.
	 * - InciteInvite_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-inciteinvite-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-inciteinvite-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-inciteinvite-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-inciteinvite-public.php';

		/**
		 * The Team Class
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-inciteinvite-team.php';

        /**
         * The Event Class
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-inciteinvite-event.php';

		/**
		 * The Team User Class
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-inciteinvite-teamuser.php';

		/**
		 * The Team Manager Class
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-inciteinvite-teammanager.php';

		/**
		 * The Team Member Class
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-inciteinvite-teammember.php';

		$this->loader 	= new InciteInvite_Loader();
		$this->team		= new InciteInvite_Team();
		$this->event    = new InciteInvite_Event();

	}

    /**
     * Echo the inputted error messages in the appropriate html
     * @param array $error_messages
     */
    public static function display_errors($content) {
        global $errors;
        if(empty($errors))
            $errors = new WP_Error();
//        wp_die(var_dump($errors));
        ob_start();
        foreach($errors->errors as $errorcode => $errormsg) {
            include plugin_dir_path( __FILE__ ) . '../views/view-inciteinvite-error.php';
        }
        $theErrors = ob_get_clean();

        return $theErrors . $content;

    }

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the InciteInvite_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new InciteInvite_i18n();
		$plugin_i18n->set_domain( $this->get_inciteinvite() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new InciteInvite_Admin( $this->get_inciteinvite(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * Generally, what I"ve tried to do is make the classes themselves contain the functions for
	 * their own actions.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public  = new InciteInvite_Public( $this->get_inciteinvite(), $this->get_version() );
		$user           = new InciteInvite_TeamUser();
		$member         = new InciteInvite_TeamMember();
		$manager        = new InciteInvite_TeamManager();

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'the_content', $this, 'display_errors', 20, 1 );
        $this->loader->add_filter('wp_mail_from_name', $this, 'mail_from_name');
		$this->loader->add_action( 'init', $this->team, 'register_type' );
		$this->loader->add_filter( 'wp_nav_menu_items', $this->team, 'add_team_button', 10, 2 );
		$this->loader->add_action( 'init', $this->event, 'register_type' );
		$this->loader->add_action( 'template_redirect', $this->event, 'update_attendance' );
		$this->loader->add_filter( 'the_content', $this->event, 'show_event_info' );
		$this->loader->add_action( 'template_redirect', $this->event, 'save_event' );
        $this->loader->add_action( 'template_redirect', $this->team, 'update_team');

		$this->define_user_hooks($user);
		$this->define_member_hooks($member);
		$this->define_manager_hooks($manager);
	}

    /**
     * Defines the hooks that all users will call.
     * @param $user InciteInvite_TeamUser
     */
    private function define_user_hooks($user) {
        $this->loader->add_action( 'register_form', $user, 'register_fields');
//        $this->loader->add_action( 'template_redirect', $user, 'register_new_user' );
        $this->loader->add_action( 'template_redirect', $user, 'redirect_if_not_their_team' );
//        $this->loader->add_action( 'init', $user, 'remove_logged_in_user');
        $this->loader->add_filter( 'registration_errors', $user, 'validate_team_field', 10, 3 );
        $this->loader->add_filter( 'user_register', $user, 'save_user_meta', 10, 3 );
        $this->loader->add_filter( 'pre_option_default_role', $user, 'set_user_default_role', 10, 1 );
        $this->loader->add_filter( 'the_content', $user, 'render_team_page');
    }

    /**
     * Defines the hooks that only members use.
     * @param $member InciteInvite_TeamMember
     */
    private function define_member_hooks($member) {
        $this->loader->add_action('template_redirect', $member, 'check_register_new_member');
    }

    /**
     * Defines the hooks that only managers will call.
     * @param $manager InciteInvite_TeamManager
     */
    private function define_manager_hooks($manager) {
        $this->loader->add_action( 'template_redirect', $manager, 'check_register_new_manager' );

        $this->loader->add_filter('login_redirect', $manager, 'iiteammanager_login_redirect', 10, 3);
        $this->loader->add_filter('registration_redirect', $manager, 'iiteammanager_register_redirect');
        $this->loader->add_shortcode( 'iirender_registration_form', $manager, 'render_registration_form' );

    }

    public function mail_from_name( $original_email_from ) {
        return 'Incite Invite';
    }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_inciteinvite() {
		return $this->inciteinvite;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    InciteInvite_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Should probably use this one with caution
	 * Deletes all posts of type iiteam
	 */
	public function deleteAllTeams() {
		$iiteams = get_pages( array( 'post_type' => 'iiteam' ) );
		foreach( $iiteams as $eachTeam ) {
			wp_delete_post( $eachTeam->ID, true);
		}
	}

}
