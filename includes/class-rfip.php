<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://app.raoforms.com
 * @since      1.0.0
 *
 * @package    RFIP
 * @subpackage RFIP/includes
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
 * @package    RFIP
 * @subpackage RFIP/includes
 * @author     Your Name <email@example.com>
 */
class RFIP {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      RFIP_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $rao_forms    The string used to uniquely identify this plugin.
	 */
	protected $rao_forms;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

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
		if ( defined( 'RFIP_VERSION' ) ) {
			$this->version = RFIP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->rao_forms = 'rao-forms';

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
	 * - RFIP_Loader. Orchestrates the hooks of the plugin.
	 * - RFIP_i18n. Defines internationalization functionality.
	 * - RFIP_Admin. Defines all hooks for the admin area.
	 * - RFIP_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rfip-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rfip-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-rfip-admin.php';

		/**
		 * This file is responsible to collect all classes
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/functions/general-functions.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-rfip-public.php';

		$this->loader = new RFIP_Loader();
		

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the RFIP_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new RFIP_i18n();

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

		$plugin_admin = new RFIP_Admin( $this->get_rao_forms(), $this->get_version() );
		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'load_rfip_menu');
		$this->loader->add_action( 'admin_init', $plugin_admin, "check_rao_access_token" );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_post_rfb_authorize', $plugin_admin, "rfb_authorize");
		$this->loader->add_action( 'wp_ajax_rfb_authorize', $plugin_admin, "rfb_authorize");
		$this->loader->add_action( 'wp_ajax_add_form_connection', $plugin_admin, "add_form_connection" );
		$this->loader->add_action( 'wp_ajax_edit_form_connection', $plugin_admin, "edit_form_connection" );
		$this->loader->add_action( 'wp_ajax_remove_form_connection', $plugin_admin, "remove_form_connection" );
		$this->loader->add_action( 'delete_post', $plugin_admin, "validate_form_connections", 9999, 2 );
		$this->loader->add_action( 'admin_notices', $plugin_admin, "display_online_offline_notice" );
		
		//Add extra tab on CF7 form settings and save additional settings
		$this->loader->add_filter( 'wpcf7_editor_panels', $plugin_admin, "add_raoforms_tab_cf7", 10);
		$this->loader->add_action( 'wpcf7_save_contact_form', $plugin_admin, "save_cf7_connections", 10, 3);

		//Add extra tab in WPForms
		$this->loader->add_filter( 'wpforms_builder_settings_sections', $plugin_admin, "add_raoforms_tab_wpforms", 10,2);
		$this->loader->add_action( 'wpforms_form_settings_panel_content', $plugin_admin, "render_raoforms_content", 10);
		$this->loader->add_action( 'wpforms_builder_save_form', $plugin_admin, "save_wpforms_connections", 10, 2);

		//Add extra tab in Ninja Forms
		//$this->loader->add_action( 'ninja_forms_register_actions', $plugin_admin, "register_my_nf_action");
		$this->loader->add_filter( 'ninja_forms_from_settings_types', $plugin_admin, "add_rao_forms_tab_ninjaforms", 10);
		$this->loader->add_filter( 'ninja_forms_localize_forms_settings', $plugin_admin, "add_rao_forms_content_ninjaforms", 10 );
		$this->loader->add_action( 'ninja_forms_save_form', $plugin_admin, "save_ninjaform_connections", 10);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new RFIP_Public( $this->get_rao_forms(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wpcf7_before_send_mail', $plugin_public, "send_cf7_data" );
		$this->loader->add_action( 'wpforms_process_complete', $plugin_public, "send_wpforms_data",10,4 );
		$this->loader->add_action( 'ninja_forms_after_submission', $plugin_public, "send_ninjaform_data",10,1 );


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
	public function get_rao_forms() {
		return $this->rao_forms;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    RFIP_Loader    Orchestrates the hooks of the plugin.
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

}
