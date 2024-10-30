<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://codeboxr.com
 * @since      1.0.0
 *
 * @package    CBXRBooking
 * @subpackage CBXRBooking/includes
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
 * @package    CBXRBooking
 * @subpackage CBXRBooking/includes
 * @author     Codeboxr <info@codeboxr.com>
 */
class CBXRBooking {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      CBXRBooking_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;


	private $settings_api;

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

		$this->plugin_name = CBXRBOOKING_PLUGIN_NAME;
		$this->version     = CBXRBOOKING_PLUGIN_VERSION;

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
	 * - CBXRBooking_Loader. Orchestrates the hooks of the plugin.
	 * - CBXRBooking_i18n. Defines internationalization functionality.
	 * - CBXRBooking_Admin. Defines all hooks for the admin area.
	 * - CBXRBooking_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxrbooking-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxrbooking-i18n.php';

		/**
		 * The class responsible for defining settings functionality of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxrbooking-setting.php';

		/**
		 * The class responsible for defining helper methods
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cbxrbooking-functions.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxrbooking-helper.php';

		/**
		 * Load Class for meta settings class.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxrbooking-formmeta-settings.php';


		/**
		 * Load Classes related emails
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/Html2Text.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/Html2TextException.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/emogrifier.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxrbooking-mailtemplate.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cbxrbooking-mail.php';


		/**
		 * Widgets of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/cbxrbooking-widget/cbxrbooking-widget.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cbxrbooking-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-cbxrbooking-public.php';


		$this->loader = new CBXRBooking_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the CBXRBooking_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new CBXRBooking_i18n();

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

		$plugin_admin = new CBXRBooking_Admin( $this->get_plugin_name(), $this->get_version() );


		//adding the setting action
		$this->loader->add_action( 'admin_init', $plugin_admin, 'setting_init', 0 );

		//plugin data reset
		$this->loader->add_action( 'admin_init', $plugin_admin, 'plugin_fullreset', 1 );

		$this->loader->add_action( 'wp_trash_post', $plugin_admin, 'before_delete_cbxrbookingform' );
		$this->loader->add_action( 'before_delete_post', $plugin_admin, 'before_delete_cbxrbookingform' );

		//add new post type- cbxrbooking
		$this->loader->add_action( 'init', $plugin_admin, 'create_cbxrbooking_post_type', 0 );


		//add metabox for custom post type cbxrbooking
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_meta_boxes_cbxrbooking_form' );

		//Add admin menu action hook; for adding setting menu
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );


		//remove admin menu add new booking form button
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'remove_menus_forms' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'remove_menus_branch' );

		//actual menu remove from core
		$this->loader->add_action( 'cbxrbooking_remove_multiple_form', $plugin_admin, 'cbxrbooking_remove_core_menu_form', 10 );
		$this->loader->add_action( 'cbxrbooking_remove_multiple_branch', $plugin_admin, 'cbxrbooking_remove_core_menu_branch', 10 );

		$this->loader->add_action( 'admin_init', $plugin_admin, 'cbxrbooking_error_notice_forms' ); ///more than one form create error notice
		$this->loader->add_action( 'admin_init', $plugin_admin, 'cbxrbooking_error_notice_branch' ); ///more than one branch create error notice
		$this->loader->add_action( 'admin_init', $plugin_admin, 'cbxrbooking_form_delete_error_notice' ); //form delete error when there is log


		$this->loader->add_filter( 'set-screen-option', $plugin_admin, 'cbxrbooking_log_results_per_page', 10, 3 );
		//$this->loader->add_filter( 'manage_cbxrbooking_page_cbxrbookinglogs_columns', $plugin_admin, 'cbxrbooking_log_results_columns' );
		//$this->loader->add_filter( 'manage_edit-post_columns', $plugin_admin, 'cbxrbooking_log_results_columns' );


		//custom column header in listing for forms
		$this->loader->add_filter( 'manage_cbxrbooking_posts_columns', $plugin_admin, 'columns_header' ); // show or remove extra column
		$this->loader->add_action( 'manage_cbxrbooking_posts_custom_column', $plugin_admin, 'custom_column_row', 10, 2 ); // modify column's row data to display
		$this->loader->add_filter( 'manage_edit-cbxrbooking_sortable_columns', $plugin_admin, 'custom_column_sortable' );

		//add metabox for feedback button
		$this->loader->add_action( 'save_post', $plugin_admin, 'save_post_cbxrbooking_form', 10, 2 );

		$this->loader->add_action( 'wp_ajax_cbxrbooking_form_enable_disable_action', $plugin_admin, 'cbxrbooking_form_enable_disable' );
		$this->loader->add_action( 'wp_ajax_cbxrbooking_form_resetcounter_action', $plugin_admin, 'cbxrbooking_form_form_resetcounter' );

		$this->loader->add_action( 'wp_ajax_cbxrbooking_branch_selection_action', $plugin_admin, 'cbxrbooking_branch_selection' );

		$this->loader->add_action( 'wp_ajax_cbxrbooking_branch_to_form_action', $plugin_admin, 'cbxrbooking_branch_to_form' );

		//if ( ( isset( $_POST['rbooking_backend_entrysubmit'] ) && intval( $_POST['rbooking_backend_entrysubmit'] ) == 1 ) ) {
			//$this->loader->add_action( 'admin_init', $plugin_admin, 'rbooking_backend_entrysubmit' );
		//}
		$this->loader->add_action( 'wp_ajax_rbooking_backend_entrysubmit_action', $plugin_admin, 'rbooking_backend_entrysubmit' );

		//$this->loader->add_action( 'cbxrbooking_log_status_change', $plugin_admin, 'cbxrbooking_status_change_send_email', 10, 5 ); //todo

		//new account manager
		$this->loader->add_action( 'wp_ajax_add_new_branch_manager_acc', $plugin_admin, 'add_new_branch_manager_acc' );

		$this->loader->add_action( 'admin_head', $plugin_admin, 'remove_date_dropdown' );

		$this->loader->add_action( 'restrict_manage_posts', $plugin_admin, 'admin_posts_filter_add_branch_dropdown' );
		$this->loader->add_action( 'parse_query', $plugin_admin, 'admin_posts_parse_query_modification' );

		// for enqueue scripts and styles
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_meta_boxes_form' );

		$this->loader->add_action( 'post_row_actions', $plugin_admin, 'post_row_actions_cbxrbooking_forms', 10, 2 );

		$this->loader->add_filter( 'plugin_action_links_' . CBXRBOOKING_BASE_NAME, $plugin_admin, 'plugin_action_links' );
		$this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'plugin_row_meta', 10, 4 );

		//upgrade and admin notice
		$this->loader->add_action( 'upgrader_process_complete', $plugin_admin, 'plugin_upgrader_process_complete', 10, 2 );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'plugin_activate_upgrade_notices' );

		$this->loader->add_filter( 'pre_set_site_transient_update_plugins', $plugin_admin, 'pre_set_transient_update_plugins_pro_addon' );
		$this->loader->add_filter( 'pre_set_site_transient_update_plugins', $plugin_admin, 'pre_set_transient_update_plugins_frontend_log_addon' );
		$this->loader->add_action( 'in_plugin_update_message-' . 'cbxrbookingproaddon/cbxrbookingproaddon.php', $plugin_admin, 'plugin_update_message_pro_addons' );
		$this->loader->add_action( 'in_plugin_update_message-' . 'cbxrbookingfrontendlogaddon/cbxrbookingfrontendlogaddon.php', $plugin_admin, 'plugin_update_message_pro_addons' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new CBXRBooking_Public( $this->get_plugin_name(), $this->get_version() );

		// init cookie
		//$this->loader->add_action( 'template_redirect', $plugin_public, 'init_session', 0 );

		//$this->loader->add_action( 'template_redirect', $plugin_public, 'frontend_entrysubmit' );
		$this->loader->add_action( 'template_redirect', $plugin_public, 'frontend_cancel_request' );
		$this->loader->add_action( 'template_redirect', $plugin_public, 'frontend_guest_activation' );

		$this->loader->add_action( 'init', $plugin_public, 'init_shortcodes' );


		$this->loader->add_action( 'wp_ajax_rbooking_frontend_entrysubmit_action', $plugin_public, 'frontend_entrysubmit' );
		$this->loader->add_action( 'wp_ajax_nopriv_rbooking_frontend_entrysubmit_action', $plugin_public, 'frontend_entrysubmit' );

		//widget
		$this->loader->add_action( 'widgets_init', $plugin_public, 'register_widget' );

		//this is disabled from here but added from shortcode method as this is not needed everywhere in frontend
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		//elementor
		//Elementor Widget
		$this->loader->add_action( 'elementor/widgets/widgets_registered', $plugin_public, 'init_elementor_widgets' );
		$this->loader->add_action( 'elementor/elements/categories_registered', $plugin_public, 'add_elementor_widget_categories' );
		$this->loader->add_action( 'elementor/editor/before_enqueue_scripts', $plugin_public, 'elementor_icon_loader', 99999 );

		//$this->loader->add_action( 'cbxrbooking_early_enqueue_style', $plugin_public, 'elementor_early_enqueue_style', 99999 );
		//$this->loader->add_action( 'cbxrbooking_early_enqueue_script', $plugin_public, 'elementor_early_enqueue_script', 99999 );

		//visual composer widget
		$this->loader->add_action( 'vc_before_init', $plugin_public, 'vc_before_init_actions' );

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
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    CBXRBooking_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

}
